<?php
/**
 * Formate und Preise (29.10.2026, includes/werbeformate.php): Vorgabe Rundum-Paket
 * (alle Plätze, zwei Motive, Video, Profil, 30 Tage, ohne Preis), Preis lesen und
 * zeigen, Formular der Seite „Formate & Preise“, Format auf eine Anzeige anwenden,
 * öffentliche Karten ohne Preis, Preis nur für die Redaktion im Backend.
 * Aufruf: php qa/wordpress/werbeformate-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('DAY_IN_SECONDS', 86400);
define('MA_CORE_URL', '/plugin/'); define('MA_CORE_VERSION', 'test');
$W = dirname(__DIR__, 2) . '/wordpress/plugin/merzenich-aktuell-core';
$GLOBALS['opt'] = []; $GLOBALS['meta'] = []; $GLOBALS['multi'] = []; $GLOBALS['kann'] = true; $GLOBALS['partner'] = false;
function add_action(...$a) {} function add_shortcode(...$a) {} function add_submenu_page(...$a) {}
function get_option($k, $d = false) { return $GLOBALS['opt'][$k] ?? $d; }
function update_option($k, $v, $a = null) { $GLOBALS['opt'][$k] = $v; return true; }
function sanitize_text_field($s) { return trim(preg_replace('/\s+/', ' ', strip_tags((string) $s))); }
function sanitize_textarea_field($s) { return trim(strip_tags((string) $s)); }
function sanitize_key($k) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $k)); }
function sanitize_title($s) { return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(strtr((string) $s, ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss']))), '-'); }
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function add_query_arg($a, $u = '') { return $u . '?' . http_build_query($a); }
function current_user_can($c) { return $GLOBALS['kann']; }
function ma_current_partner_policy() { return $GLOBALS['partner'] ? ['rolle' => 'x'] : null; }
function get_post_meta($id, $k, $s = false) { return $s ? ($GLOBALS['meta'][$id][$k] ?? '') : ($GLOBALS['multi'][$id][$k] ?? []); }
function update_post_meta($id, $k, $v) { $GLOBALS['meta'][$id][$k] = $v; return true; }
function delete_post_meta($id, $k) { unset($GLOBALS['meta'][$id][$k], $GLOBALS['multi'][$id][$k]); return true; }
function add_post_meta($id, $k, $v) { $GLOBALS['multi'][$id][$k][] = $v; return true; }
require $W . '/includes/werbeformate.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-76s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Vorgabe\n";
$f = ma_werbeformate();
pruefe('ohne Speicherung: ein Format „Rundum-Paket“', [array_keys($f), $f['rundum-paket']['name']], [['rundum-paket'], 'Rundum-Paket']);
pruefe('alle elf Plätze, 2 Motive, Video, Firmenprofil, 30 Tage, noch kein Preis, buchbar', [count($f['rundum-paket']['plaetze']), $f['rundum-paket']['motive'], $f['rundum-paket']['video'], $f['rundum-paket']['profil'], $f['rundum-paket']['tage'], $f['rundum-paket']['preis_cent'], $f['rundum-paket']['aktiv']], [11, 2, true, true, 30, 0, true]);

echo "\nPreis\n";
pruefe('lesen: 490 / 490,50 / 1.290,00 € / 1290.5 / leer', array_map('ma_werbeformat_preis_lesen', ['490', '490,50', '1.290,00 €', '1290.5', '']), [49000, 49050, 129000, 129050, 0]);
pruefe('zeigen: 490,00 € und 1.290,00 €, ohne Preis leer', [ma_werbeformat_preis(49000), ma_werbeformat_preis(129000), ma_werbeformat_preis(0)], ['490,00 €', '1.290,00 €', '']);
pruefe('Zeile ohne Preis: „noch kein Preis hinterlegt“', ma_werbeformat_zeile('rundum-paket'), 'Rundum-Paket · 30 Tage · noch kein Preis hinterlegt');

echo "\nSeite „Formate & Preise“\n";
$neu = ma_werbeformate_aus_formular([
    'rundum-paket' => ['name' => 'Rundum-Paket', 'beschreibung' => 'Alles', 'plaetze' => ['homepage_band_1', 'homepage_buehne', 'gibt_es_nicht'], 'motive' => '9', 'video' => '1', 'profil' => '1', 'tage' => '30', 'preis' => '490', 'notiz' => 'intern', 'aktiv' => '1'],
    'alt' => ['name' => 'Altes Format', 'plaetze' => ['homepage_band_2'], 'loeschen' => '1'],
    'neu' => ['name' => 'Werbeband Startseite', 'plaetze' => ['homepage_band_1'], 'motive' => '1', 'tage' => '14', 'preis' => '149,50', 'aktiv' => '1'],
]);
pruefe('gelöscht fällt weg, neue Zeile bekommt einen Schlüssel aus dem Namen', array_keys($neu), ['rundum-paket', 'werbeband-startseite']);
pruefe('unbekannte Plätze fallen weg, Motive höchstens 4, Preis in Cent', [$neu['rundum-paket']['plaetze'], $neu['rundum-paket']['motive'], $neu['rundum-paket']['preis_cent'], $neu['werbeband-startseite']['preis_cent']], [['homepage_band_1', 'homepage_buehne'], 4, 49000, 14950]);
pruefe('neue Zeile ohne Namen zählt nicht', array_keys(ma_werbeformate_aus_formular(['neu' => ['name' => '', 'plaetze' => ['homepage_band_1']]])), []);
update_option('ma_werbeformate', $neu);
pruefe('buchbar nur mit Namen und Plätzen', array_keys(ma_werbeformate_aktiv()), ['rundum-paket', 'werbeband-startseite']);
pruefe('Zeile mit Preis', ma_werbeformat_zeile('rundum-paket'), 'Rundum-Paket · 30 Tage · 490,00 € netto');
pruefe('Zeile ohne Preis (für Firmen-Zugänge)', ma_werbeformat_zeile('rundum-paket', false), 'Rundum-Paket · 30 Tage');
$GLOBALS['partner'] = true;
pruefe('Preis sichtbar: Redaktion ja, Firmen-Zugang nein', [ma_werbeformat_preis_sichtbar()], [false]);
$GLOBALS['partner'] = false;
pruefe('Preis sichtbar für die Redaktion', ma_werbeformat_preis_sichtbar(), true);

echo "\nFormat auf eine Anzeige anwenden\n";
$GLOBALS['meta'][5] = ['ma_ad_start' => '2026-11-01 00:00:00'];
ma_werbeformat_anwenden(5, 'rundum-paket');
pruefe('Hauptplatz, weitere Plätze, Ende = Start + 30 Tage', [$GLOBALS['meta'][5]['ma_ad_slot'], $GLOBALS['multi'][5]['ma_ad_slot_weitere'] ?? [], $GLOBALS['meta'][5]['ma_ad_end']], ['homepage_band_1', ['homepage_buehne'], '2026-12-01 00:00:00']);
$GLOBALS['meta'][6] = ['ma_ad_start' => '2026-11-01 00:00:00', 'ma_ad_end' => '2026-11-10 00:00:00'];
ma_werbeformat_anwenden(6, 'rundum-paket', ['homepage_buehne']);
pruefe('Kontingent begrenzt die Plätze, vorhandenes Ende bleibt', [$GLOBALS['meta'][6]['ma_ad_slot'], $GLOBALS['multi'][6]['ma_ad_slot_weitere'] ?? [], $GLOBALS['meta'][6]['ma_ad_end']], ['homepage_buehne', [], '2026-11-10 00:00:00']);

echo "\nÖffentlich ohne Preis\n";
$h = ma_werbeformate_shortcode();
pruefe('Karten der Formate mit „Preis auf Anfrage“ und Anfrage-Knopf', [substr_count($h, 'class="ma-werbeformat"'), str_contains($h, 'Preis auf Anfrage'), str_contains($h, 'format=Paket')], [2, true, true]);
pruefe('kein Euro-Betrag und keine interne Notiz im Frontend', [str_contains($h, '€'), str_contains($h, '490'), str_contains($h, 'intern')], [false, false, false]);
$code = (string) file_get_contents($W . '/includes/content-admin.php') . file_get_contents($W . '/includes/relevanz.php');
pruefe('Werbemittel und Freigaben zeigen den Preis nur der Redaktion', [substr_count($code, 'ma_werbeformat_preis_sichtbar()') >= 2, str_contains($code, "'ma_ad_format'=>'format'")], [true, true]);
$seite = (string) file_get_contents($W . '/includes/seiten.php');
preg_match('/function ma_seite_werben\(\): string \{(.*?)\n\}/s', $seite, $w);
pruefe('Seite „Werben“: Formate, Schritte, Beispiel, Formular, kein Preis', [str_contains($w[1], '[ma_werbeformate]'), str_contains($w[1], 'ma-werben-schritte'), str_contains($w[1], '[ma_werbung_beispiel]'), str_contains($w[1], '[ma_formular typ="werbung"]'), str_contains($w[1], '€')], [true, true, true, true, false]);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
