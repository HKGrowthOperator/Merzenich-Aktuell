<?php
/**
 * Vorauswahl der Anzeigen-Formulare aus Links (Plugin forms.php, 1.21.0):
 * /anzeigen/aufgeben/?art=Werbung&format=Unternehmenskanal usw. wählen das
 * richtige Formular und die richtige Art. Ohne WordPress.
 * Aufruf: php qa/wordpress/formular-test.php
 */
define('ABSPATH', __DIR__ . '/');
function sanitize_key($k) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $k)); }
function sanitize_text_field($s) { return trim(strip_tags((string) $s)); }
function wp_unslash($s) { return $s; }
function remove_accents($s) { return strtr((string) $s, ['ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'Ä' => 'A', 'Ö' => 'O', 'Ü' => 'U', 'ß' => 'ss']); }
require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/forms.php';
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
pruefe('Ortsteile für das Foto des Tages', array_keys(ma_form_ortsteile()), ['merzenich', 'golzheim', 'girbelsrath', 'morschenich', 'buergewald']);
pruefe('Zustimmungstext nennt Namen im Bildnachweis', str_contains(ma_form_foto_zustimmung(), 'Bildnachweis'), true);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
