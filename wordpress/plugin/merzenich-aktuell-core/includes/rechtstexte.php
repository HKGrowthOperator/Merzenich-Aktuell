<?php
/**
 * Rechtstexte (02.10.2026): Impressum und Datenschutzerklärung als Seiten
 * der WordPress-Installation, verwaltet vom Plugin.
 *
 * Bisher liefen /impressum/ und /datenschutz/ auf 404 (die Fußzeile verlinkt
 * beide). Das Plugin legt die Seiten an, wenn sie fehlen, und aktualisiert sie
 * bei einer neuen Fassung (MA_RECHTSTEXTE_VERSION), solange der Text nicht von
 * Hand geändert wurde. Eine manuelle Änderung erkennt der Hash-Vergleich; dann
 * bleibt die Seite stehen und das Backend bietet an, die neue Fassung zu
 * übernehmen (Merzenich Aktuell → Rechtstexte).
 *
 * Inhalt nur aus belegten Tatsachen: Anbieterin KBS Management GmbH (Angaben
 * wie im Impressum der statischen Seite, abgeglichen mit dem Impressum von KBS
 * Management), die tatsächliche Technik dieser Installation (statistik.php,
 * live.php, werbung-stat.php, forms.php, eingang.php, comments.php,
 * weather.php / Theme). Keine erfundenen Register-, Kontakt- oder
 * Vertragsdaten; was fehlt, steht als offener Punkt im Backend, nicht auf
 * der öffentlichen Seite.
 */
if (!defined('ABSPATH')) { exit; }

const MA_RECHTSTEXTE_VERSION = '2026-10-03';

function ma_rechtstexte(): array {
    return [
        'impressum' => ['titel' => 'Impressum', 'anriss' => 'Anbieter und Kontakt von Merzenich Aktuell: KBS Management GmbH.', 'html' => 'ma_rechtstext_impressum'],
        'datenschutz' => ['titel' => 'Datenschutzhinweise', 'anriss' => 'Datenschutz bei Merzenich Aktuell, einem Angebot der KBS Management GmbH.', 'html' => 'ma_rechtstext_datenschutz'],
    ];
}

/** Offene Pflichtangaben, die nur der Betreiber liefern kann (nur im Backend sichtbar). */
function ma_rechtstexte_offen(): array {
    return [
        'Redaktionell verantwortliche Person nach § 18 Abs. 2 MStV (Name und Anschrift) für das Impressum.',
        'Auftragsverarbeitungsvertrag mit dem Hoster (IONOS) bestätigen und die Speicherdauer der Server-Protokolle beim Hoster erfragen.',
        'Mailversand (SMTP-Anbieter) für Formular- und Kommentarbenachrichtigungen: sobald eingerichtet, Anbieter in der Datenschutzerklärung nennen.',
    ];
}

function ma_rechtstext_anbieter(): string {
    return '<p><strong>KBS Management GmbH</strong><br>Rheinstr. 78a<br>51371 Leverkusen<br>Deutschland</p>'
        . '<p>Vertreten durch die Geschäftsführung: Barbara Koronkai und Anto-Sutharsan Jesuthasan.<br>Handelsregister: Amtsgericht Köln, HRB 104692.</p>';
}

function ma_rechtstext_impressum(): string {
    return '<h2 id="anbieter-nach-5-ddg">Anbieter nach § 5 DDG</h2>' . ma_rechtstext_anbieter()
        . '<h2 id="kontakt">Kontakt</h2><p>E-Mail: <a href="mailto:info@kbs-management.tv">info@kbs-management.tv</a></p>'
        . '<p>Die Gesellschaft ist Anbieterin von Merzenich Aktuell. Angaben zur Gesellschaft wurden mit dem Impressum von KBS Management abgeglichen.</p>'
        . '<h2 id="redaktionell-verantwortliche-person">Redaktionell verantwortliche Person</h2>'
        . '<p>Die für das journalistisch-redaktionelle Angebot verantwortliche natürliche Person nach § 18 Absatz 2 MStV wird an dieser Stelle mit Name und Anschrift benannt, sobald sie von der Herausgeberin bestätigt ist.</p>'
        . '<h2 id="anzeigen">Anzeigen und gesponserte Beiträge</h2>'
        . '<p>Werbung ist als „Anzeige“ gekennzeichnet, bezahlte Unternehmensbeiträge als „Anzeige · Gesponsert“. Musteranzeigen ohne Auftraggeber tragen die Kennzeichnung „Musteranzeige“ und werben für niemanden.</p>'
        . '<h2 id="bildnachweise">Bildnachweise</h2>'
        . '<p>Credits und Bildtypen stehen bei den jeweiligen Beiträgen. Archiv- und Symbolmotive werden ausdrücklich als solche gekennzeichnet. Eine Quellenangabe ersetzt keine Nutzungsfreigabe. Die Herkunft aller verwendeten Motive ist im Bildverzeichnis der Redaktion dokumentiert; Nutzungsrechte werden vor der Veröffentlichung geprüft.</p>'
        . '<p>Die Schriften Hanken Grotesk und Newsreader werden lokal bereitgestellt; die zugehörigen Lizenztexte liegen dem Projekt bei.</p>';
}

function ma_rechtstext_datenschutz(): string {
    $ich = ma_rechtstext_anbieter();
    return '<p>Diese Hinweise erklären, welche Daten beim Besuch von merzenich-aktuell.de verarbeitet werden, wofür und wie lange. Die Seite setzt keine Tracking-Cookies, bindet keine Analyse- oder Werbedienste Dritter ein und zählt Reichweite ohne Wiedererkennung über den Tag hinaus.</p>'
        . '<h2 id="verantwortliche-stelle">Verantwortliche Stelle</h2>' . $ich
        . '<p>Kontakt für Datenschutzanliegen: <a href="mailto:info@kbs-management.tv">info@kbs-management.tv</a>.</p>'
        . '<h2 id="hosting">Hosting und Server-Protokolle</h2>'
        . '<p>Die Website läuft als WordPress-Installation bei der IONOS SE, Montabaur, als Auftragsverarbeiterin. Beim Abruf einer Seite verarbeitet der Webserver technisch notwendige Verbindungsdaten (IP-Adresse, Zeitpunkt, angeforderte Adresse, Browserkennung, verweisende Seite), um die Seite auszuliefern und den Betrieb abzusichern. Rechtsgrundlage ist Artikel 6 Absatz 1 Buchstabe f DSGVO. Die Protokolle verwaltet der Hoster nach seinen Vorgaben; wir werten sie nicht aus.</p>'
        . '<p>Fehlt eine Datei des Seitenlayouts (Stylesheet, Schrift, Bild) auf diesem Server, leitet er die Anfrage auf unseren eigenen Server unter merzenichaktuell.hk-growthoperator.de um; auch dort fallen nur die genannten Verbindungsdaten an.</p>'
        . '<h2 id="reichweite">Reichweitenmessung ohne Cookies</h2>'
        . '<p>Wie viele Menschen welche Meldungen lesen, zählt die Seite selbst, ohne Cookies, ohne Dienste Dritter und ohne dauerhafte Wiedererkennung:</p><ul>'
        . '<li><strong>Seitenaufrufe:</strong> Jede Seite meldet beim Laden „einmal aufgerufen“ an unseren Server. Gespeichert wird nur die Summe je Tag und Meldung.</li>'
        . '<li><strong>Besucher:</strong> Um Besucher eines Tages nur einmal zu zählen, bildet der Server aus Ihrer IP-Adresse, Ihrer Browserkennung und einem Zufallswert, der jeden Tag neu entsteht und den alten ersetzt, einen Prüfwert (Hash). Die IP-Adresse selbst wird nicht gespeichert; nach dem Tageswechsel lässt sich der Prüfwert niemandem mehr zuordnen. Zu jedem Prüfwert speichern wir Zeitpunkt des ersten und letzten Aufrufs, Zahl der Seiten, Gerätetyp (Handy, Tablet, Desktop), Herkunft als Name (etwa Suchmaschine, direkt) und die zuletzt gelesene Seite. Solange ein Tab sichtbar ist, meldet der Browser alle 30 Sekunden, dass der Besuch andauert. Diese Einträge werden nach 40 Tagen gelöscht.</li>'
        . '<li>Angemeldete Redaktionsmitglieder und automatische Programme (Bots) werden nicht gezählt.</li></ul>'
        . '<p>Rechtsgrundlage ist Artikel 6 Absatz 1 Buchstabe f DSGVO (Interesse an einer datensparsamen Erfolgsmessung des redaktionellen Angebots).</p>'
        . '<h2 id="anzeigen">Anzeigen</h2>'
        . '<p>Für Anzeigen zählt die Seite, wie oft eine Anzeige mindestens zur Hälfte sichtbar war und wie oft sie angeklickt wurde, als Tagessumme je Anzeige und Platz. Es werden keine Cookies gesetzt und keine Profile gebildet. Musteranzeigen sind gekennzeichnet; sie werben für niemanden. Klicken Sie eine Anzeige an, gelangen Sie auf die Website des Werbenden; dort gelten dessen Datenschutzhinweise.</p>'
        . '<h2 id="wetter">Wetter im Seitenkopf</h2>'
        . '<p>Das Wetter für Merzenich fragt unser Server beim Wetterdienst Open-Meteo ab, speichert das Ergebnis zehn Minuten zwischen und reicht nur dieses Ergebnis an Ihren Browser weiter. Ihr Browser verbindet sich nicht mit Open-Meteo. Das zuletzt empfangene Wetter merkt sich Ihr Browser lokal, damit der Seitenkopf sofort gefüllt ist.</p>'
        . '<h2 id="einsendungen-und-kontakt">Einsendungen und Kontakt</h2>'
        . '<p>Formulare auf dieser Website (Meldung senden, Termin melden, Verein oder Betrieb eintragen, Korrekturhinweis, Kontakt, Werbeanfrage, Trauer- und Familienanzeigen) übermitteln Ihre Angaben (Name, E-Mail-Adresse, bei einigen Formularen Telefonnummer, Ihr Text, bei Bedarf eine Datei) an unseren Server. Dort werden sie mit Zeitpunkt im Eingang der Redaktion gespeichert und ausschließlich zur Bearbeitung Ihres Anliegens genutzt; mitgeschickte Dateien liegen in der Medienverwaltung der Redaktion. Zusätzlich erhält das Redaktionspostfach eine E-Mail mit denselben Angaben. Rechtsgrundlage ist Artikel 6 Absatz 1 Buchstabe b und f DSGVO, bei freiwilligen Angaben Ihre Einwilligung nach Buchstabe a. Haben Sie beim Absenden die Bild- und Nutzungsrechte bestätigt, wird diese Bestätigung mit gespeichert. Die Daten werden gelöscht, sobald das Anliegen erledigt ist und keine gesetzliche Aufbewahrungspflicht entgegensteht. Für Kontakt per E-Mail gilt dasselbe für das Postfach der Anbieterin.</p>'
        . '<h2 id="kommentare">Kommentare</h2>'
        . '<p>Unter Meldungen können Sie ohne Konto kommentieren. Gespeichert werden der angegebene Name, die E-Mail-Adresse, der Text und der Zeitpunkt. Name und Text werden nach Freigabe öffentlich angezeigt; die E-Mail-Adresse wird nicht veröffentlicht und nur für Rückfragen der Redaktion sowie für eine einmalige Nachricht verwendet, sobald Ihr Kommentar freigegeben ist. Jeder Kommentar erscheint erst nach Prüfung durch die Redaktion. Die IP-Adresse wird beim Kommentieren nicht gespeichert. Das Kommentarformular setzt keine Cookies. Rechtsgrundlage ist Artikel 6 Absatz 1 Buchstabe b DSGVO (Bereitstellung der Kommentarfunktion) und Buchstabe f DSGVO (Schutz vor Missbrauch). Kommentare können Sie jederzeit über die Redaktion löschen lassen.</p>'
        . '<h2 id="angemeldete-nutzer">Redaktion, Vereins- und Partnerzugänge</h2>'
        . '<p>Wer mit einem Zugang arbeitet (Redaktion, Vereinsredakteure, Unternehmenspartner), meldet sich über WordPress an. Dabei setzt WordPress die für die Anmeldung nötigen Cookies. Zu jedem Zugang speichern wir Benutzername, E-Mail-Adresse, Rolle und den Zeitpunkt der letzten Aktivität; an Beiträgen wird festgehalten, wer sie wann angelegt, bearbeitet, eingereicht oder freigegeben hat (Verlauf). Diese Angaben dienen dem redaktionellen Ablauf und der Nachvollziehbarkeit von Veröffentlichungen (Artikel 6 Absatz 1 Buchstabe b und f DSGVO).</p>'
        . '<h2 id="lokale-speicherung">Cookies und lokale Speicherung</h2>'
        . '<p>Für Leserinnen und Leser setzt die Seite keine Cookies. Ihr Browser speichert lokal, ohne Übertragung an uns oder Dritte: Ihre Auswahl im Datenschutz-Hinweis, das zuletzt empfangene Wetter und, wenn Sie Live-Meldungen zugestimmt haben, den Stand der zuletzt gesehenen Meldung. Sie können diese Einträge jederzeit über die Browser-Einstellungen löschen; die Auswahl ändern Sie über „Datenschutz-Einstellungen“ am Ende jeder Seite.</p>'
        . '<h2 id="live-meldungen">Live-Meldungen und Benachrichtigungen</h2>'
        . '<p>Wenn Sie „Live-Meldungen“ zugestimmt haben, fragt Ihr Browser, solange die Seite geöffnet ist, alle fünf Minuten die Meldungsliste bei unserem Server ab; daran sind keine Dritten beteiligt.</p>'
        . '<p>Erlauben Sie zusätzlich Benachrichtigungen Ihres Browsers, richtet Ihr Browser ein Push-Abonnement ein, damit neue Meldungen Sie auch bei geschlossener Seite erreichen. Dafür erzeugt Ihr Browser eine Abo-Adresse beim Push-Dienst Ihres Browser-Herstellers (Google für Chrome und Edge, Mozilla für Firefox, Apple für Safari) und zwei Schlüssel. Wir speichern diese Abo-Adresse und die beiden Schlüssel, keine IP-Adresse und keine Gerätekennung, und übergeben jede Meldung verschlüsselt an den Push-Dienst; dieser leitet sie an Ihr Gerät weiter und kann die Nachricht nicht lesen. Rechtsgrundlage ist Ihre Einwilligung (Art. 6 Abs. 1 lit. a DSGVO). Sie widerrufen sie jederzeit in den Datenschutz-Einstellungen oder in den Benachrichtigungs-Einstellungen Ihres Browsers; das Abonnement wird dann gelöscht, ebenso, wenn der Push-Dienst es als beendet meldet.</p>'
        . '<h2 id="externe-inhalte">Externe Links und Teilen</h2>'
        . '<p>Schriften, Skripte und Bilder liefert diese Website selbst aus. Die Teilen-Schaltflächen unter Meldungen sind einfache Links, die erst beim Anklicken WhatsApp, Facebook oder Ihr E-Mail-Programm öffnen; vorher wird nichts an diese Anbieter übertragen. Links auf fremde Websites, etwa Originalquellen, Vereins- oder Behördenseiten, führen Sie auf das Angebot des jeweiligen Anbieters; dort gelten dessen Datenschutzhinweise.</p>'
        . '<h2 id="schnittstelle">Technische Schnittstelle</h2>'
        . '<p>Unter /wp-json/ stellt WordPress eine Schnittstelle bereit, über die die Seite selbst Aufrufe, Besuche und Anzeigenkontakte zählt, das Wetter ausliefert und Push-Abonnements entgegennimmt oder löscht. Sie gibt nur veröffentlichte Inhalte aus.</p>'
        . '<h2 id="ihre-rechte">Ihre Rechte</h2>'
        . '<p>Im Rahmen der gesetzlichen Voraussetzungen bestehen Rechte auf Auskunft, Berichtigung, Löschung, Einschränkung, Datenübertragbarkeit und Widerspruch. Eine Einwilligung können Sie für die Zukunft widerrufen. Sie können sich bei einer Datenschutzaufsichtsbehörde beschweren, insbesondere der <a href="https://www.ldi.nrw.de/" target="_blank" rel="noopener">Landesbeauftragten für Datenschutz und Informationsfreiheit NRW</a>.</p>'
        . '<p>Stand: 2. Oktober 2026.</p>';
}

/* ---------------------------------------------------------- Seiten anlegen und aktualisieren */

function ma_rechtstext_html(string $slug): string {
    $f = ma_rechtstexte()[$slug]['html'] ?? '';
    return $f !== '' && function_exists($f) ? $f() : '';
}

function ma_rechtstext_seite(string $slug): ?WP_Post {
    $p = get_page_by_path($slug, OBJECT, 'page');
    return $p instanceof WP_Post && $p->post_status !== 'trash' ? $p : null;
}

/** Stand je Seite: Fassung und Hash des Textes, den das Plugin zuletzt geschrieben hat. */
function ma_rechtstexte_stand(): array {
    $s = get_option('ma_rechtstexte_stand', []);
    return is_array($s) ? $s : [];
}

/** Status einer Seite: fehlt | aktuell | veraltet | manuell. */
function ma_rechtstext_status(string $slug): string {
    $p = ma_rechtstext_seite($slug);
    if (!$p) return 'fehlt';
    $stand = ma_rechtstexte_stand()[$slug] ?? null;
    if (!$stand || md5((string) $p->post_content) !== ($stand['hash'] ?? '')) return 'manuell';
    return ($stand['version'] ?? '') === MA_RECHTSTEXTE_VERSION ? 'aktuell' : 'veraltet';
}

/** Seite schreiben (neu oder Fassung einspielen). */
function ma_rechtstext_schreiben(string $slug): int {
    $t = ma_rechtstexte()[$slug] ?? null;
    if (!$t) return 0;
    $html = ma_rechtstext_html($slug);
    $p = ma_rechtstext_seite($slug);
    $daten = ['post_type' => 'page', 'post_status' => 'publish', 'post_title' => $t['titel'], 'post_name' => $slug, 'post_excerpt' => $t['anriss'], 'post_content' => $html, 'comment_status' => 'closed', 'ping_status' => 'closed'];
    if ($p) { $daten['ID'] = $p->ID; $id = wp_update_post($daten, true); }
    else $id = wp_insert_post($daten, true);
    if ($id instanceof WP_Error || !$id) return 0;
    update_post_meta($id, 'ma_eyebrow', 'Rechtliches');
    $stand = ma_rechtstexte_stand();
    $stand[$slug] = ['version' => MA_RECHTSTEXTE_VERSION, 'hash' => md5($html), 't' => time()];
    update_option('ma_rechtstexte_stand', $stand, false);
    return (int) $id;
}

/** Beim Admin-Aufruf: fehlende Seiten anlegen, unveränderte Seiten auf die neue Fassung heben. Manuell geänderte bleiben. */
function ma_rechtstexte_abgleichen(): void {
    if (get_option('ma_rechtstexte_geprueft') === MA_RECHTSTEXTE_VERSION) return;
    foreach (array_keys(ma_rechtstexte()) as $slug) {
        $status = ma_rechtstext_status($slug);
        if ($status === 'fehlt' || $status === 'veraltet') ma_rechtstext_schreiben($slug);
    }
    update_option('ma_rechtstexte_geprueft', MA_RECHTSTEXTE_VERSION, false);
}
add_action('admin_init', 'ma_rechtstexte_abgleichen');

add_action('admin_menu', function (): void {
    add_submenu_page('merzenich-aktuell', 'Rechtstexte', 'Rechtstexte', 'manage_options', 'ma-rechtstexte', 'ma_rechtstexte_seite_admin');
}, 30);

add_action('admin_post_ma_rechtstext_uebernehmen', function (): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.', 403);
    check_admin_referer('ma_rechtstext_uebernehmen');
    $slug = sanitize_key(wp_unslash($_POST['slug'] ?? ''));
    $id = isset(ma_rechtstexte()[$slug]) ? ma_rechtstext_schreiben($slug) : 0;
    wp_safe_redirect(admin_url('admin.php?page=ma-rechtstexte&uebernommen=' . ($id ? $slug : '0'))); exit;
});

add_action('admin_notices', function (): void {
    if (!current_user_can('manage_options') || (sanitize_key(wp_unslash($_GET['page'] ?? '')) === 'ma-rechtstexte')) return;
    $manuell = array_filter(array_keys(ma_rechtstexte()), fn($s) => ma_rechtstext_status($s) === 'manuell' && (ma_rechtstexte_stand()[$s]['version'] ?? '') !== MA_RECHTSTEXTE_VERSION);
    if (!$manuell) return;
    printf('<div class="notice notice-warning"><p>Rechtstexte: %s wurde von Hand geändert; die Fassung %s des Plugins ist nicht eingespielt. <a href="%s">Rechtstexte prüfen</a></p></div>',
        esc_html(implode(', ', array_map(fn($s) => ma_rechtstexte()[$s]['titel'], $manuell))), esc_html(MA_RECHTSTEXTE_VERSION), esc_url(admin_url('admin.php?page=ma-rechtstexte')));
});

function ma_rechtstexte_seite_admin(): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
    $ue = sanitize_key(wp_unslash($_GET['uebernommen'] ?? ''));
    echo '<div class="wrap"><h1>Rechtstexte</h1>';
    if ($ue !== '') echo '<div class="notice notice-' . ($ue === '0' ? 'error' : 'success') . ' is-dismissible"><p>' . ($ue === '0' ? 'Nicht übernommen.' : 'Fassung ' . esc_html(MA_RECHTSTEXTE_VERSION) . ' für „' . esc_html(ma_rechtstexte()[$ue]['titel'] ?? $ue) . '“ eingespielt.') . '</p></div>';
    echo '<p>Impressum und Datenschutzerklärung pflegt das Plugin als normale Seiten (Fassung ' . esc_html(MA_RECHTSTEXTE_VERSION) . '). Eine neue Fassung wird nur eingespielt, wenn der Text nicht von Hand geändert wurde; sonst entscheiden Sie hier.</p>';
    echo '<table class="widefat striped" style="max-width:900px"><thead><tr><th>Seite</th><th>Status</th><th></th></tr></thead><tbody>';
    $namen = ['fehlt' => 'fehlt (wird beim nächsten Admin-Aufruf angelegt)', 'aktuell' => 'aktuell, Fassung ' . MA_RECHTSTEXTE_VERSION, 'veraltet' => 'ältere Fassung', 'manuell' => 'von Hand geändert'];
    foreach (ma_rechtstexte() as $slug => $t) {
        $p = ma_rechtstext_seite($slug); $status = ma_rechtstext_status($slug);
        echo '<tr><td><strong>' . esc_html($t['titel']) . '</strong><br><code>/' . esc_html($slug) . '/</code></td><td>' . esc_html($namen[$status]) . ($p ? '<br><span class="description">zuletzt geändert ' . esc_html(get_the_modified_date('d.m.Y H:i', $p)) . '</span>' : '') . '</td><td>';
        if ($p) echo '<a class="button" href="' . esc_url(get_permalink($p)) . '" target="_blank">Ansehen</a> <a class="button" href="' . esc_url(get_edit_post_link($p->ID)) . '">Bearbeiten</a> ';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline" onsubmit="return confirm(\'Den Text der Seite durch die Fassung des Plugins ersetzen? Eigene Änderungen gehen verloren (Revision bleibt).\')"><input type="hidden" name="action" value="ma_rechtstext_uebernehmen"><input type="hidden" name="slug" value="' . esc_attr($slug) . '">';
        wp_nonce_field('ma_rechtstext_uebernehmen');
        echo '<button class="button' . ($status === 'aktuell' ? '' : ' button-primary') . '">Fassung ' . esc_html(MA_RECHTSTEXTE_VERSION) . ' einspielen</button></form></td></tr>';
    }
    echo '</tbody></table>';
    echo '<h2>Offene Pflichtangaben</h2><p>Diese Punkte kann nur der Betreiber liefern. Sie stehen nicht auf den öffentlichen Seiten; das Impressum nennt die § 18 MStV-Person als „wird benannt, sobald bestätigt“.</p><ol>';
    foreach (ma_rechtstexte_offen() as $o) echo '<li>' . esc_html($o) . '</li>';
    echo '</ol><h2>Was die Datenschutzerklärung beschreibt</h2><p class="description">Aufrufzählung (statistik.php), Besucherzählung mit Tages-Hash und Löschung nach 40 Tagen (live.php), Anzeigenzählung (werbung-stat.php), Wetter über den eigenen Server (Open-Meteo), Formulare mit Eingang und Mail (forms.php, eingang.php), Kommentare ohne IP-Speicherung und mit Vorabmoderation (comments.php), Zugänge und Verlauf (redaktion.php), lokale Speicherung im Browser (einwilligung.js, v20.js). Ändert sich eine dieser Funktionen, gehört die Erklärung angepasst (MA_RECHTSTEXTE_VERSION hochzählen).</p></div>';
}
