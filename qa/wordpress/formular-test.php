<?php
/**
 * Vorauswahl der Anzeigen-Formulare aus Links (Plugin forms.php, 1.21.0):
 * /anzeigen/aufgeben/?art=Werbung&format=Unternehmenskanal usw. wählen das
 * richtige Formular und die richtige Art. Seit 1.24.0 auch die Wahlkarten
 * auf /anzeigen/aufgeben/ und der einheitliche Betreff. Ohne WordPress.
 * Aufruf: php qa/wordpress/formular-test.php
 */
define('ABSPATH', __DIR__ . '/');
function sanitize_key($k) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $k)); }
function sanitize_text_field($s) { return trim(strip_tags((string) $s)); }
function wp_unslash($s) { return $s; }
function remove_accents($s) { return strtr((string) $s, ['ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'Ä' => 'A', 'Ö' => 'O', 'Ü' => 'U', 'ß' => 'ss']); }
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return (string) $s; }
function remove_query_arg($k) { return '/anzeigen/aufgeben/'; }
function add_query_arg($k, $v, $u) { return $u . '?' . $k . '=' . rawurlencode($v); }
function ma_form_shortcode($a) { return '<form id="formular-' . $a['typ'] . '"></form>'; } // eigentliches Formular hier nicht nötig
define('MA_EINGANG_TYPEN', ['werbung' => 'Werbung', 'immobilie' => 'Immobilie', 'stelle' => 'Stellenanzeige', 'trauer' => 'Traueranzeige', 'familie' => 'Familienanzeige', 'meldung' => 'Meldung']);
$quelle = (string) file_get_contents(__DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/forms.php');
// forms.php ohne ma_form_shortcode laden (die echte Fassung braucht WordPress).
$quelle = preg_replace('/function ma_form_shortcode\(\$atts\): string \{.*?\n\}\n/s', '', $quelle, 1);
eval('?>' . $quelle);
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-62s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
$ziel = function (array $get): array { $_GET = $get; return ma_form_ziel_aus_link(); };

echo "Vorauswahl aus Links\n";
pruefe('Unternehmenskanal → Werbung, Unternehmensprofil', $ziel(['art' => 'Werbung', 'format' => 'Unternehmenskanal']), ['typ' => 'werbung', 'art' => 'unternehmen', 'betreff' => 'Anfrage: Unternehmenskanal']);
pruefe('Werbebanner → banner', $ziel(['art' => 'Werbung', 'format' => 'Werbebanner'])['art'], 'banner');
pruefe('Tipp (bezahlter Beitrag) → tipp', $ziel(['art' => 'Werbung', 'format' => 'Tipp (bezahlter Beitrag)'])['art'], 'tipp');
pruefe('Immobilie zum Verkauf', $ziel(['art' => 'Immobilie', 'angebot' => 'Verkauf']), ['typ' => 'immobilie', 'art' => 'verkauf', 'betreff' => '']);
pruefe('Stellenanzeige → Stellen-Formular', $ziel(['art' => 'Stellenanzeige'])['typ'], 'stelle');
pruefe('Jahrgedächtnis (mit Umlaut)', $ziel(['art' => 'Traueranzeige', 'trauerform' => 'Jahrgedächtnis']), ['typ' => 'trauer', 'art' => 'jahrgedaechtnis', 'betreff' => '']);
pruefe('Jubiläum', $ziel(['art' => 'Familienanzeige', 'anlass' => 'Jubiläum'])['art'], 'jubilaeum');
pruefe('Unbekannte Art: nichts vorgewählt', $ziel(['art' => 'Gewinnspiel'])['typ'], '');
pruefe('Ohne Parameter: nichts vorgewählt', $ziel([])['typ'], '');
pruefe('Sponsoring einer Rubrik → sponsoring', $ziel(['art' => 'Werbung', 'format' => 'Sponsoring einer Rubrik'])['art'], 'sponsoring');
pruefe('Noch offen, bitte beraten → beratung', $ziel(['art' => 'Werbung', 'format' => 'Noch offen, bitte beraten'])['art'], 'beratung');

echo "\nEinheitlicher Betreff\n";
pruefe('Fünf Anzeigenformulare mit eigener Frage', [array_keys(ma_form_arten()), array_keys(ma_form_arten_frage())], [['werbung', 'immobilie', 'stelle', 'trauer', 'familie'], ['werbung', 'immobilie', 'stelle', 'trauer', 'familie']]);
pruefe('Mail-Betreff: Art und Wahl, dann Name', ma_form_mail_betreff('werbung', ma_form_arten()['werbung']['banner'], 'Erika Muster'), '[Merzenich Aktuell] Werbung: Werbebanner – Erika Muster');
pruefe('Mail-Betreff ohne Namen', ma_form_mail_betreff('meldung', 'Laternenumzug', ''), '[Merzenich Aktuell] Meldung: Laternenumzug');

echo "\nWahlkarten auf /anzeigen/aufgeben/\n";
$_GET = []; $h = ma_aufgeben_shortcode();
pruefe('Ohne Wahl: fünf Karten, alle Formulare versteckt, Hinweis sichtbar', [substr_count($h, 'class="ma-aufgeben__karte"'), substr_count($h, '<section class="ma-aufgeben__formular"'), substr_count($h, '" hidden>'), str_contains($h, 'class="ma-aufgeben__leer"')], [5, 5, 5, true]);
pruefe('Frage steht genau einmal da', substr_count($h, 'Was möchten Sie veröffentlichen?'), 1);
$_GET = ['art' => 'Immobilie']; $h = ma_aufgeben_shortcode();
pruefe('?art=Immobilie: nur das Immobilien-Formular sichtbar, Karte markiert', [str_contains($h, 'data-ma-art-formular="immobilie">'), substr_count($h, '" hidden>'), str_contains($h, 'data-ma-art="immobilie" aria-current="true"'), str_contains($h, 'class="ma-aufgeben__leer"')], [true, 4, true, false]);
$_GET = ['gesendet' => 'trauer']; $h = ma_aufgeben_shortcode();
pruefe('Nach dem Absenden bleibt das Formular offen', str_contains($h, 'data-ma-art-formular="trauer">'), true);
pruefe('Karten sind echte Links mit ?art=', str_contains($h, 'href="/anzeigen/aufgeben/?art=Stellenanzeige#formular"'), true);

pruefe('Ortsteile für das Foto des Tages', array_keys(ma_form_ortsteile()), ['merzenich', 'golzheim', 'girbelsrath', 'morschenich', 'buergewald']);
pruefe('Zustimmungstext nennt Namen im Bildnachweis', str_contains(ma_form_foto_zustimmung(), 'Bildnachweis'), true);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
