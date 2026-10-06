<?php
/**
 * Mailversand über SMTP (1.23.0): Merzenich Aktuell → Mailversand.
 *
 * Formulare, Partner-Anträge und Kommentar-Benachrichtigungen schicken alle über
 * wp_mail(). Ohne Einstellung nimmt WordPress die PHP-Standardfunktion des
 * Servers; solche Mails kommen oft nicht oder im Spam an. Hier trägt der
 * Betreiber das Postfach bei IONOS ein (Vorgabe smtp.ionos.de, Port 587,
 * STARTTLS). Erst wenn der Schalter an ist und alles ausgefüllt ist, stellt
 * phpmailer_init auf SMTP um; sonst bleibt der Versand, wie er ist.
 *
 * Das Passwort steht nie im Klartext in der Datenbank (sodium_crypto_secretbox,
 * Schlüssel aus wp_salt('auth')) und nie im HTML der Seite. Ein leeres
 * Passwortfeld beim Speichern lässt das gespeicherte unverändert.
 */
if (!defined('ABSPATH')) { exit; }

const MA_MAIL_OPTION = 'ma_mailversand';
const MA_MAIL_ARTEN = ['tls' => 'STARTTLS (Port 587, empfohlen)', 'ssl' => 'SSL/TLS (Port 465)', 'keine' => 'keine (nur für Tests)'];

/** Gespeicherte Einstellungen mit Vorgaben für IONOS. */
function ma_mail_einstellungen(): array {
    $e = get_option(MA_MAIL_OPTION, []);
    return array_merge(['aktiv' => false, 'host' => 'smtp.ionos.de', 'port' => 587, 'art' => 'tls', 'benutzer' => '', 'passwort' => '', 'absender' => '', 'name' => 'Merzenich Aktuell'], is_array($e) ? $e : []);
}

/** Schlüssel für das Passwort, abgeleitet aus den Sicherheitsschlüsseln der Installation (wp-config.php). */
function ma_mail_schluessel(): string {
    return sodium_crypto_generichash(wp_salt('auth'), '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
}

function ma_mail_verschluesseln(string $klar, string $schluessel): string {
    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    return base64_encode($nonce . sodium_crypto_secretbox($klar, $nonce, $schluessel));
}

/** Leerer Text, wenn nichts gespeichert ist oder der Schlüssel nicht passt (etwa nach geänderten Salts). */
function ma_mail_entschluesseln(string $geheim, string $schluessel): string {
    $roh = base64_decode($geheim, true);
    if ($roh === false || strlen($roh) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) return '';
    $klar = sodium_crypto_secretbox_open(substr($roh, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES), substr($roh, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES), $schluessel);
    return $klar === false ? '' : $klar;
}

/** Eingeschaltet und vollständig? Nur dann wird SMTP benutzt. */
function ma_mail_bereit(array $e): bool {
    return !empty($e['aktiv']) && trim((string) $e['host']) !== '' && (int) $e['port'] > 0 && trim((string) $e['benutzer']) !== '' && (string) $e['passwort'] !== '';
}

/** Absender der Mails: eigene Adresse, sonst der Benutzername des Postfachs (bei IONOS die Adresse selbst). */
function ma_mail_absender(array $e): string {
    foreach ([(string) $e['absender'], (string) $e['benutzer']] as $a) if (is_email($a)) return $a;
    return '';
}

/** Formular übernehmen: Ein leeres Passwortfeld lässt das gespeicherte stehen, „Passwort löschen“ entfernt es. */
function ma_mail_aus_formular(array $alt, array $f, string $schluessel): array {
    $host = strtolower(trim((string) ($f['host'] ?? '')));
    $neu = [
        'aktiv' => !empty($f['aktiv']),
        'host' => preg_match('/^[a-z0-9.-]+$/', $host) ? $host : '',
        'port' => max(0, min(65535, (int) ($f['port'] ?? 0))),
        'art' => isset(MA_MAIL_ARTEN[(string) ($f['art'] ?? '')]) ? (string) $f['art'] : 'tls',
        'benutzer' => sanitize_text_field((string) ($f['benutzer'] ?? '')),
        'passwort' => (string) ($alt['passwort'] ?? ''),
        'absender' => is_email(trim((string) ($f['absender'] ?? ''))) ? trim((string) $f['absender']) : '',
        'name' => sanitize_text_field((string) ($f['name'] ?? '')) !== '' ? sanitize_text_field((string) $f['name']) : 'Merzenich Aktuell',
    ];
    if (!empty($f['passwort_loeschen'])) $neu['passwort'] = '';
    elseif ((string) ($f['passwort'] ?? '') !== '') $neu['passwort'] = ma_mail_verschluesseln((string) $f['passwort'], $schluessel);
    return $neu;
}

/** PHPMailer auf SMTP stellen. */
function ma_mail_phpmailer_setzen(object $m, array $e, string $passwort): void {
    $m->isSMTP();
    $m->Host = (string) $e['host'];
    $m->Port = (int) $e['port'];
    $m->SMTPAuth = true;
    $m->Username = (string) $e['benutzer'];
    $m->Password = $passwort;
    $m->SMTPSecure = $e['art'] === 'ssl' ? 'ssl' : ($e['art'] === 'tls' ? 'tls' : '');
    $m->SMTPAutoTLS = $e['art'] !== 'keine';
    $m->Timeout = 15;
}

add_action('phpmailer_init', function ($m): void {
    $e = ma_mail_einstellungen();
    if (!ma_mail_bereit($e)) return;
    $passwort = ma_mail_entschluesseln((string) $e['passwort'], ma_mail_schluessel());
    if ($passwort !== '') ma_mail_phpmailer_setzen($m, $e, $passwort);
});
add_filter('wp_mail_from', function ($von) {
    $e = ma_mail_einstellungen();
    return ma_mail_bereit($e) && ma_mail_absender($e) !== '' ? ma_mail_absender($e) : $von;
});
add_filter('wp_mail_from_name', function ($name) {
    $e = ma_mail_einstellungen();
    return ma_mail_bereit($e) ? (string) $e['name'] : $name;
});

/** Empfänger der Test-Mail: Redaktions-E-Mail, sonst die Admin-E-Mail. */
function ma_mail_test_empfaenger(): string {
    $r = (string) get_option('ma_editorial_email', '');
    return is_email($r) ? $r : (string) get_option('admin_email');
}

/** Kurzer Stand für Dashboard und Seite. */
function ma_mail_status_text(array $e): string {
    if (ma_mail_bereit($e)) return 'eingerichtet über ' . $e['host'] . ' (' . ($e['benutzer']) . ')';
    if (!empty($e['aktiv'])) return 'eingeschaltet, aber unvollständig: es gilt noch der PHP-Standard';
    return 'nicht eingerichtet: Mails gehen über den PHP-Standard des Servers und landen oft im Spam';
}

/* ---------------------------------------------------------- Backend-Seite */

add_action('admin_menu', function (): void {
    add_submenu_page('merzenich-aktuell', 'Mailversand', 'Mailversand', 'manage_options', 'ma-mailversand', 'ma_mail_seite_admin');
}, 30);

add_action('admin_post_ma_mailversand_speichern', function (): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.', 403);
    check_admin_referer('ma_mailversand_speichern');
    $f = wp_unslash($_POST);
    update_option(MA_MAIL_OPTION, ma_mail_aus_formular(ma_mail_einstellungen(), is_array($f) ? $f : [], ma_mail_schluessel()), false);
    wp_safe_redirect(admin_url('admin.php?page=ma-mailversand&gespeichert=1')); exit;
});

add_action('admin_post_ma_mailversand_test', function (): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.', 403);
    check_admin_referer('ma_mailversand_test');
    $fehler = '';
    $merken = function (WP_Error $f) use (&$fehler): void { $fehler = $f->get_error_message(); };
    add_action('wp_mail_failed', $merken);
    $an = ma_mail_test_empfaenger();
    $ok = wp_mail($an, 'Test-Mail von Merzenich Aktuell', "Diese Mail bestätigt, dass der Mailversand der Website funktioniert.\n\nGesendet am " . wp_date('d.m.Y, H:i') . " Uhr über: " . ma_mail_status_text(ma_mail_einstellungen()) . "\n");
    remove_action('wp_mail_failed', $merken);
    // Das Passwort darf in keiner Fehlermeldung stehen.
    $pw = ma_mail_entschluesseln((string) ma_mail_einstellungen()['passwort'], ma_mail_schluessel());
    if ($pw !== '') $fehler = str_replace($pw, '***', $fehler);
    set_transient('ma_mail_test_' . get_current_user_id(), ['ok' => $ok, 'an' => $an, 'fehler' => $fehler], 300);
    wp_safe_redirect(admin_url('admin.php?page=ma-mailversand&getestet=1')); exit;
});

function ma_mail_seite_admin(): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
    $test = null;
    if (!empty($_GET['getestet'])) { $test = get_transient('ma_mail_test_' . get_current_user_id()) ?: null; delete_transient('ma_mail_test_' . get_current_user_id()); }
    echo ma_mail_seite_html(ma_mail_einstellungen(), ma_mail_test_empfaenger(), !empty($_GET['gespeichert']), is_array($test) ? $test : null);
}

/** HTML der Seite. Das Passwort selbst kommt hier nie vor, nur ob eines gespeichert ist. */
function ma_mail_seite_html(array $e, string $empfaenger, bool $gespeichert, ?array $test): string {
    $h = '<div class="wrap"><h1>Mailversand</h1>';
    if ($gespeichert) $h .= '<div class="notice notice-success is-dismissible"><p>Gespeichert.</p></div>';
    if ($test) $h .= $test['ok']
        ? '<div class="notice notice-success"><p>Test-Mail an ' . esc_html((string) $test['an']) . ' abgeschickt. Bitte im Postfach (auch im Spam-Ordner) nachsehen.</p></div>'
        : '<div class="notice notice-error"><p>Test-Mail an ' . esc_html((string) $test['an']) . ' nicht verschickt' . ((string) $test['fehler'] !== '' ? ': ' . esc_html((string) $test['fehler']) : '.') . ' Server, Port, Benutzer und Passwort prüfen.</p></div>';
    $h .= '<p><strong>Stand:</strong> ' . esc_html(ma_mail_status_text($e)) . '.</p>'
        . '<p>Alle Mails der Website (Formulare, Partner-Anträge, Kommentar-Benachrichtigungen) gehen über dieses Postfach, sobald der Schalter an ist und alles ausgefüllt ist. Für IONOS: Postfach im IONOS-Kundenkonto anlegen, hier die vollständige E-Mail-Adresse als Benutzer und das Postfach-Passwort eintragen. Server und Port sind schon richtig vorbelegt.</p>'
        . '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="ma_mailversand_speichern">' . wp_nonce_field('ma_mailversand_speichern', '_wpnonce', true, false)
        . '<table class="form-table" role="presentation"><tbody>'
        . '<tr><th scope="row">Versand über SMTP</th><td><label><input type="checkbox" name="aktiv" value="1"' . (!empty($e['aktiv']) ? ' checked' : '') . '> eingeschaltet</label></td></tr>'
        . '<tr><th scope="row"><label for="ma-mail-host">Server</label></th><td><input id="ma-mail-host" class="regular-text" name="host" value="' . esc_attr((string) $e['host']) . '" placeholder="smtp.ionos.de"></td></tr>'
        . '<tr><th scope="row"><label for="ma-mail-port">Port</label></th><td><input id="ma-mail-port" type="number" min="1" max="65535" name="port" value="' . (int) $e['port'] . '"></td></tr>'
        . '<tr><th scope="row"><label for="ma-mail-art">Verschlüsselung</label></th><td><select id="ma-mail-art" name="art">';
    foreach (MA_MAIL_ARTEN as $k => $l) $h .= '<option value="' . esc_attr($k) . '"' . ($e['art'] === $k ? ' selected' : '') . '>' . esc_html($l) . '</option>';
    $h .= '</select></td></tr>'
        . '<tr><th scope="row"><label for="ma-mail-benutzer">Benutzer</label></th><td><input id="ma-mail-benutzer" class="regular-text" name="benutzer" autocomplete="off" value="' . esc_attr((string) $e['benutzer']) . '" placeholder="redaktion@merzenich-aktuell.de"></td></tr>'
        . '<tr><th scope="row"><label for="ma-mail-passwort">Passwort</label></th><td><input id="ma-mail-passwort" class="regular-text" type="password" name="passwort" autocomplete="new-password" value=""> '
        . ((string) $e['passwort'] !== '' ? '<span class="description">gespeichert. Leer lassen, um es zu behalten.</span> <label><input type="checkbox" name="passwort_loeschen" value="1"> Passwort löschen</label>' : '<span class="description">noch keines gespeichert</span>') . '</td></tr>'
        . '<tr><th scope="row"><label for="ma-mail-absender">Absender-Adresse</label></th><td><input id="ma-mail-absender" class="regular-text" name="absender" value="' . esc_attr((string) $e['absender']) . '" placeholder="leer = Benutzer"><p class="description">Muss zum Postfach passen, sonst lehnen viele Empfänger die Mail ab.</p></td></tr>'
        . '<tr><th scope="row"><label for="ma-mail-name">Absender-Name</label></th><td><input id="ma-mail-name" class="regular-text" name="name" value="' . esc_attr((string) $e['name']) . '"></td></tr>'
        . '</tbody></table><p><button class="button button-primary">Speichern</button></p></form>'
        . '<h2>Test-Mail</h2><p>Schickt eine kurze Mail an ' . esc_html($empfaenger) . ' (die Redaktions-E-Mail auf der Seite Merzenich Aktuell, sonst die Admin-E-Mail).</p>'
        . '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="ma_mailversand_test">' . wp_nonce_field('ma_mailversand_test', '_wpnonce', true, false)
        . '<p><button class="button">Test-Mail schicken</button></p></form>'
        . '<p class="description">Danach in der Datenschutzerklärung ergänzen, über welchen Anbieter die Mails gehen (bei IONOS: den Hoster, der schon als Auftragsverarbeiter genannt ist). Siehe Merzenich Aktuell → Rechtstexte, offene Pflichtangaben.</p></div>';
    return $h;
}
