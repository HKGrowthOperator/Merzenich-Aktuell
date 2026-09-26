<?php
/**
 * Antrag auf einen Partner-Zugang: [ma_partner_antrag].
 *
 * Polizei, Feuerwehr, Rathaus, der Fussballverein, Vereine, Makler und
 * Unternehmen koennen hier einen Zugang anfragen. Der Antrag geht per E-Mail
 * an die Administration; ein Konto wird NICHT automatisch angelegt. Das macht
 * die Redaktion nach Pruefung unter Benutzer -> Partner-Zugaenge.
 *
 * Attribut typ="unternehmen" (usw.) waehlt die Art vor.
 */
if (!defined('ABSPATH')) { exit; }

function ma_partner_antrag_typen(): array {
    return [
        'polizei' => 'Polizei',
        'feuerwehr' => 'Feuerwehr',
        'rathaus' => 'Rathaus',
        'sport' => 'Sport (Fußballverein)',
        'verein' => 'Verein',
        'immobilien' => 'Immobilien',
        'unternehmen' => 'Unternehmen',
    ];
}

/** Welche Rolle passt zur Art? Nur als Hinweis in der Mail. */
function ma_partner_antrag_rolle(string $typ): string {
    return [
        'polizei' => 'ma_polizei_partner',
        'feuerwehr' => 'ma_feuerwehr_partner',
        'rathaus' => 'ma_rathaus_partner',
        'sport' => 'ma_sport_partner',
        'verein' => 'ma_vereine_partner',
        'immobilien' => 'ma_immobilien_partner',
        'unternehmen' => 'ma_wirtschaft_partner',
    ][$typ] ?? '';
}

function ma_register_partner_antrag_hooks(): void {
    add_shortcode('ma_partner_antrag', 'ma_partner_antrag_shortcode');
    add_action('admin_post_nopriv_ma_partner_antrag', 'ma_partner_antrag_submit');
    add_action('admin_post_ma_partner_antrag', 'ma_partner_antrag_submit');
}

function ma_partner_antrag_fehlertext(string $code): string {
    return [
        'felder' => 'Bitte Organisation, Art, Ansprechperson und eine gültige E-Mail-Adresse angeben.',
        'einwilligung' => 'Bitte bestätigen Sie die Einwilligung zur Verarbeitung Ihrer Angaben.',
        'versand' => 'Der Antrag konnte gerade nicht versendet werden. Bitte versuchen Sie es später erneut oder schreiben Sie der Redaktion direkt.',
        'sitzung' => 'Das Formular ist abgelaufen. Bitte laden Sie die Seite neu und senden Sie es erneut.',
    ][$code] ?? '';
}

function ma_partner_antrag_shortcode($atts): string {
    $a = shortcode_atts(['typ' => ''], $atts, 'ma_partner_antrag');
    $vorauswahl = sanitize_key((string)$a['typ']);
    if (isset($_GET['partner-typ'])) $vorauswahl = sanitize_key(wp_unslash((string)$_GET['partner-typ']));
    $status = isset($_GET['antrag']) ? sanitize_key(wp_unslash((string)$_GET['antrag'])) : '';
    $fehler = isset($_GET['grund']) ? ma_partner_antrag_fehlertext(sanitize_key(wp_unslash((string)$_GET['grund']))) : '';

    if ($status === 'danke') {
        return '<div class="ma-partner-antrag ma-partner-antrag--danke" id="partner-antrag" role="status"><h2>Vielen Dank für Ihren Antrag</h2>'
            .'<p>Ihr Antrag ist bei der Redaktion eingegangen. Wir prüfen ihn und melden uns per E-Mail. Ein Zugang wird erst nach dieser Prüfung eingerichtet.</p></div>';
    }

    $datenschutz = function_exists('get_privacy_policy_url') ? (string)get_privacy_policy_url() : '';
    ob_start(); ?>
    <form class="ma-partner-antrag" id="partner-antrag" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
      <input type="hidden" name="action" value="ma_partner_antrag">
      <input type="hidden" name="started" value="<?php echo esc_attr((string)time()); ?>">
      <?php wp_nonce_field('ma_partner_antrag', 'ma_partner_antrag_nonce'); ?>
      <p class="ma-hp" aria-hidden="true" style="position:absolute;left:-9999px"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></p>
      <?php if ($status === 'fehler' && $fehler !== ''): ?><p class="ma-partner-antrag__fehler" role="alert"><?php echo esc_html($fehler); ?></p><?php endif; ?>
      <p><label for="ma-pa-organisation">Organisation / Unternehmen *</label><input id="ma-pa-organisation" name="organisation" required maxlength="160" autocomplete="organization"></p>
      <p><label for="ma-pa-typ">Art *</label><select id="ma-pa-typ" name="typ" required><option value="">Bitte wählen</option>
        <?php foreach (ma_partner_antrag_typen() as $key => $label): ?><option value="<?php echo esc_attr($key); ?>" <?php selected($vorauswahl, $key); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?>
      </select></p>
      <p><label for="ma-pa-name">Ansprechperson *</label><input id="ma-pa-name" name="name" required maxlength="120" autocomplete="name"></p>
      <p><label for="ma-pa-email">E-Mail *</label><input id="ma-pa-email" name="email" type="email" required maxlength="190" autocomplete="email"></p>
      <p><label for="ma-pa-telefon">Telefon</label><input id="ma-pa-telefon" name="telefon" type="tel" maxlength="40" autocomplete="tel"></p>
      <p><label for="ma-pa-nachricht">Nachricht</label><textarea id="ma-pa-nachricht" name="nachricht" rows="5" maxlength="4000"></textarea></p>
      <p class="ma-partner-antrag__consent"><label><input type="checkbox" name="einwilligung" value="1" required> Ich bin einverstanden, dass meine Angaben zur Bearbeitung dieses Antrags gespeichert und verwendet werden.<?php if ($datenschutz !== ''): ?> Details in der <a href="<?php echo esc_url($datenschutz); ?>">Datenschutzerklärung</a>.<?php endif; ?> *</label></p>
      <p><button type="submit">Zugang anfragen</button></p>
      <p class="ma-form-note">Der Antrag geht an die Redaktion. Ein Zugang wird nicht automatisch angelegt; alle Beiträge werden vor der Veröffentlichung von der Redaktion geprüft. Anzeigen für Unternehmen: Preis auf Anfrage.</p>
    </form>
    <?php return (string)ob_get_clean();
}

/**
 * Antrag pruefen und versenden. Getrennt vom Redirect, damit es testbar ist.
 *
 * @return array{ok:bool, fehler:string}
 */
function ma_partner_antrag_process(array $in): array {
    if (!isset($in['ma_partner_antrag_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash((string)$in['ma_partner_antrag_nonce'])), 'ma_partner_antrag')) {
        return ['ok' => false, 'fehler' => 'sitzung'];
    }
    // Honeypot oder zu schnell abgeschickt: stillschweigend "angenommen",
    // damit Bots keine Rueckmeldung bekommen - aber keine Mail.
    $started = (int)($in['started'] ?? 0);
    if (!empty($in['website']) || !$started || time() - $started < 3) return ['ok' => true, 'fehler' => 'spam'];

    $org = sanitize_text_field(wp_unslash((string)($in['organisation'] ?? '')));
    $typ = sanitize_key(wp_unslash((string)($in['typ'] ?? '')));
    $name = sanitize_text_field(wp_unslash((string)($in['name'] ?? '')));
    $email = sanitize_email(wp_unslash((string)($in['email'] ?? '')));
    $tel = trim(preg_replace('/[^0-9+\/\-() ]/', '', sanitize_text_field(wp_unslash((string)($in['telefon'] ?? '')))));
    $text = sanitize_textarea_field(wp_unslash((string)($in['nachricht'] ?? '')));
    $typen = ma_partner_antrag_typen();

    if ($org === '' || !isset($typen[$typ]) || $name === '' || !$email || !is_email($email)) return ['ok' => false, 'fehler' => 'felder'];
    if (empty($in['einwilligung'])) return ['ok' => false, 'fehler' => 'einwilligung'];

    $to = sanitize_email((string)get_option('admin_email'));
    if (!$to || !is_email($to)) return ['ok' => false, 'fehler' => 'versand'];

    $subject = '[Merzenich Aktuell] Antrag auf Partner-Zugang: '.$org.' ('.$typen[$typ].')';
    $body = "Neuer Antrag auf einen Partner-Zugang.\n\n";
    $body .= 'Organisation: '.$org."\n";
    $body .= 'Art: '.$typen[$typ]."\n";
    $body .= 'Ansprechperson: '.$name."\n";
    $body .= 'E-Mail: '.$email."\n";
    if ($tel !== '') $body .= 'Telefon: '.$tel."\n";
    $body .= "Einwilligung Datenverarbeitung: ja\n";
    if ($text !== '') $body .= "\nNachricht:\n".$text."\n";
    $rolle = ma_partner_antrag_rolle($typ);
    $body .= "\nEs wurde kein Konto angelegt. Nach Prüfung unter Benutzer → Partner-Zugänge anlegen";
    if ($rolle !== '' && function_exists('ma_partner_policies')) {
        $label = ma_partner_policies()[$rolle]['label'] ?? $rolle;
        $body .= ' (passende Rolle: '.$label.')';
    }
    $body .= ":\n".admin_url('users.php?page=ma-partner-zugaenge')."\n";

    $headers = ['Reply-To: '.str_replace(["\r", "\n", '<', '>'], '', $name).' <'.$email.'>'];
    if (!wp_mail($to, $subject, $body, $headers)) return ['ok' => false, 'fehler' => 'versand'];
    return ['ok' => true, 'fehler' => ''];
}

function ma_partner_antrag_submit(): void {
    $ergebnis = ma_partner_antrag_process($_POST);
    $zurueck = wp_get_referer() ?: home_url('/');
    $zurueck = remove_query_arg(['antrag','grund'], $zurueck);
    $ziel = $ergebnis['ok']
        ? add_query_arg('antrag', 'danke', $zurueck)
        : add_query_arg(['antrag' => 'fehler', 'grund' => $ergebnis['fehler']], $zurueck);
    wp_safe_redirect($ziel.'#partner-antrag', 303);
    exit;
}
