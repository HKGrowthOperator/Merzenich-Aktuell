<?php
/**
 * Werbeanfrage mit Fotos und Video (29.10.2026, includes/werbeanfrage.php):
 * Felder im Formular ohne Preis, Prüfung (Firma, Website, Format, Motive,
 * Einwilligung, Dateigrößen, gesamt unter 7,5 MB), Rate-Limit, Dateien mit Rolle,
 * Zusammenfassung (Preis nur für die Redaktion), Anlegen im Eingang: Firmenprofil
 * zur Prüfung mit Einwilligung und Logo, je Motiv eine Anzeige zur Prüfung mit
 * Website als Ziel, Plätzen und Laufzeit des Formats, Foto und Video, nicht aktiv;
 * vorhandenes Profil wird verknüpft; zweiter Aufruf legt nichts doppelt an.
 * Aufruf: php qa/wordpress/werbeanfrage-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('DAY_IN_SECONDS', 86400); define('HOUR_IN_SECONDS', 3600);
define('MA_CORE_URL', '/plugin/'); define('MA_CORE_VERSION', 'test');
$W = dirname(__DIR__, 2) . '/wordpress/plugin/merzenich-aktuell-core';
foreach (['opt' => [], 'meta' => [], 'multi' => [], 'posts' => [], 'naechste' => 100, 'transient' => [], 'mails' => [], 'uploads' => [], 'medien' => []] as $k => $v) $GLOBALS[$k] = $v;
function add_action(...$a) {} function add_shortcode(...$a) {} function add_submenu_page(...$a) {} function add_meta_box(...$a) {}
function get_option($k, $d = false) { return $GLOBALS['opt'][$k] ?? $d; }
function update_option($k, $v, $a = null) { $GLOBALS['opt'][$k] = $v; return true; }
function sanitize_text_field($s) { return trim(preg_replace('/\s+/', ' ', strip_tags((string) $s))); }
function sanitize_textarea_field($s) { return trim(strip_tags((string) $s)); }
function sanitize_key($k) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $k)); }
function sanitize_title($s) { return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower((string) $s)), '-'); }
function sanitize_file_name($s) { return basename((string) $s); }
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url_raw($s, $p = null) { return preg_match('#^https?://[^\s<>"]+$#i', (string) $s) ? (string) $s : ''; }
function wp_parse_url($u, $c = -1) { return parse_url((string) $u, $c); }
function wp_date($f, $t = null) { return date($f, $t ?? strtotime('2026-10-29 12:00:00')); }
function wp_salt($s = '') { return 'salz'; }
function wp_generate_password($n = 12, $s = true) { return str_repeat('z', $n); }
function delete_transient($k) { unset($GLOBALS['transient'][$k]); return true; }
function home_url($p = '') { return 'https://merzenich-aktuell.de' . $p; }
function current_user_can($c) { return true; }
function get_current_user_id() { return 1; }
function wp_slash($v) { return $v; }
function get_transient($k) { return $GLOBALS['transient'][$k] ?? false; }
function set_transient($k, $v, $t) { $GLOBALS['transient'][$k] = $v; return true; }
function wp_mail($an, $betreff, $text) { $GLOBALS['mails'][] = [$an, $betreff, $text]; return true; }
function wp_handle_upload($f, $o) { if (!empty($f['kaputt'])) return ['error' => 'Dateityp nicht erlaubt.']; $GLOBALS['uploads'][] = $f['name']; return ['file' => '/uploads/' . $f['name']]; }
// Echte kleine Dateien für die Inhaltsprüfung (Video-Kopf, Bild).
$tmp = sys_get_temp_dir() . '/ma-wa-test-' . getmypid(); @mkdir($tmp);
file_put_contents("$tmp/mp4", "\0\0\0\x18ftypmp42" . str_repeat("\0", 64));
file_put_contents("$tmp/webm", "\x1A\x45\xDF\xA3" . str_repeat("\0", 64));
file_put_contents("$tmp/zip", "PK\x03\x04" . str_repeat("\0", 64));
$bild = imagecreatetruecolor(40, 30); imagejpeg($bild, "$tmp/jpg");
function get_post_meta($id, $k, $s = false) { return $s ? ($GLOBALS['meta'][$id][$k] ?? '') : ($GLOBALS['multi'][$id][$k] ?? []); }
function update_post_meta($id, $k, $v) { $GLOBALS['meta'][$id][$k] = $v; return true; }
function delete_post_meta($id, $k) { unset($GLOBALS['meta'][$id][$k], $GLOBALS['multi'][$id][$k]); return true; }
function add_post_meta($id, $k, $v) { $GLOBALS['multi'][$id][$k][] = $v; return true; }
class WP_Error { function get_error_message() { return 'x'; } }
function is_wp_error($x) { return $x instanceof WP_Error; }
function wp_insert_post($a, $e = false) { $id = $GLOBALS['naechste']++; $GLOBALS['posts'][$id] = $a; foreach ((array) ($a['meta_input'] ?? []) as $k => $v) $GLOBALS['meta'][$id][$k] = $v; return $id; }
function wp_update_post($a) { $GLOBALS['posts'][$a['ID']] = array_merge($GLOBALS['posts'][$a['ID']] ?? [], $a); return $a['ID']; }
function get_post($id) { return isset($GLOBALS['posts'][$id]) ? (object) ($GLOBALS['posts'][$id] + ['ID' => $id]) : null; }
function get_post_status($id) { return $GLOBALS['posts'][$id]['post_status'] ?? false; }
function get_the_title($id) { return $GLOBALS['posts'][$id]['post_title'] ?? ''; }
function get_the_date($f, $id) { return '29.10.2026'; }
function set_post_thumbnail($id, $m) { $GLOBALS['meta'][$id]['_thumbnail_id'] = $m; return true; }
function get_post_thumbnail_id($id) { return (int) ($GLOBALS['meta'][$id]['_thumbnail_id'] ?? 0); }
function get_attached_media($t, $id) { return array_map(fn($m) => (object) ['ID' => $m], array_keys(array_filter($GLOBALS['medien'], fn($p) => $p === $id))); }
function get_posts($q) {
    return array_keys(array_filter($GLOBALS['posts'], function ($p, $id) use ($q) {
        if (($p['post_type'] ?? '') !== $q['post_type']) return false;
        if (isset($q['meta_query'])) return str_contains((string) ($GLOBALS['meta'][$id]['ma_business_website'] ?? ''), $q['meta_query'][0]['value']);
        if (isset($q['s'])) return stripos($p['post_title'] ?? '', $q['s']) !== false;
        return true;
    }, ARRAY_FILTER_USE_BOTH));
}
$GLOBALS['verlauf'] = [];
function ma_verlauf_eintragen($id, $was, $text = '') { $GLOBALS['verlauf'][] = [$id, $was]; }
require $W . '/includes/werbeformate.php';
require $W . '/includes/werbeanfrage.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-78s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
$datei = fn(string $name, float $mb = 1) => ['name' => $name, 'size' => (int) ($mb * 1048576), 'error' => 0, 'tmp_name' => "$tmp/" . (str_ends_with($name, '.mp4') ? 'mp4' : 'jpg')];
$gut = ['wa_firma' => 'Bäckerei Beispiel', 'wa_website' => 'www.baeckerei-beispiel.de', 'wa_branche' => 'Bäckerei', 'wa_einwilligung' => '1',
    'wa_titel' => [1 => 'Frische Brötchen ab 6 Uhr', 2 => 'Torten auf Bestellung'], 'wa_text' => [1 => 'Am Markt in Merzenich.', 2 => ''], 'wa_start' => '2026-11-02', 'wa_video_link' => ''];
$dateien = ['wa_foto_1' => $datei('foto1.jpg'), 'wa_foto_2' => $datei('foto2.jpg'), 'wa_video' => $datei('clip.mp4', 4), 'wa_logo' => $datei('logo.png', 0.2)];

echo "Formular\n";
$h = ma_werbeanfrage_html();
pruefe('Felder: Firma, Website, Logo, Format, Start, 2 Motive mit Foto, Video, Link, Einwilligung', [str_contains($h, 'name="wa_firma"'), str_contains($h, 'name="wa_website"'), str_contains($h, 'name="wa_logo"'), str_contains($h, 'name="wa_format" value="rundum-paket"'), str_contains($h, 'name="wa_start"'), substr_count($h, 'name="wa_foto_'), str_contains($h, 'name="wa_video"'), str_contains($h, 'name="wa_video_link"'), str_contains($h, 'name="wa_einwilligung"')], [true, true, true, true, true, 2, true, true, true]);
pruefe('Format öffentlich ohne Preis: „Preis auf Anfrage“', [str_contains($h, 'Preis auf Anfrage'), str_contains($h, '€')], [true, false]);
pruefe('Fotos werden verkleinert, Größen geprüft, Grenze 7,5 MB im Skript', [str_contains($h, 'data-ma-verkleinern="1600"'), str_contains($h, 'GESAMT=7864320'), str_contains($h, 'toBlob')], [true, true, true]);
pruefe('Pflichtfelder nur je Art (data-pflicht), Video höchstens 6 MB', [str_contains($h, 'data-pflicht="banner unternehmen"'), str_contains($h, 'data-max="6291456"')], [true, true]);

echo "\nPrüfung\n";
$r = ma_werbeanfrage_lesen('banner', $gut, $dateien);
pruefe('gültige Anfrage: kein Fehler, Website mit https, Format gesetzt, Start bleibt', [$r['fehler'], $r['daten']['website'], $r['daten']['format'], $r['daten']['start']], ['', 'https://www.baeckerei-beispiel.de', 'rundum-paket', '2026-11-02']);
pruefe('Motive und Dateien', [count($r['daten']['motive']), $r['daten']['dateien']], [2, ['wa_logo', 'wa_video', 'wa_foto_1', 'wa_foto_2']]);
$f = fn(array $aend, array $dat = null) => ma_werbeanfrage_lesen('banner', array_merge($gut, $aend), $dat ?? $dateien)['fehler'];
pruefe('ohne Firma', $f(['wa_firma' => '']), 'Bitte geben Sie den Namen Ihrer Firma an.');
pruefe('ohne Website (Werbepaket)', $f(['wa_website' => '']), 'Bitte geben Sie Ihre Website an; dorthin führt ein Klick auf die Anzeige.');
pruefe('ungültige Website', $f(['wa_website' => 'keine adresse']), 'Die Website-Adresse ist nicht gültig, bitte so: https://www.ihre-firma.de/');
pruefe('ohne Einwilligung', $f(['wa_einwilligung' => '']), 'Bitte willigen Sie in die Veröffentlichung nach Prüfung ein.');
pruefe('Motiv 1 ohne Foto', $f([], array_diff_key($dateien, ['wa_foto_1' => 1])), 'Bitte laden Sie für Motiv 1 ein Foto hoch.');
pruefe('Motiv 2 mit Foto, aber ohne Überschrift', $f(['wa_titel' => [1 => 'A', 2 => '']]), 'Bitte geben Sie für Motiv 2 eine Überschrift an.');
pruefe('Motiv 2 leer: nur ein Motiv', count(ma_werbeanfrage_lesen('banner', array_merge($gut, ['wa_titel' => [1 => 'A']]), array_diff_key($dateien, ['wa_foto_2' => 1]))['daten']['motive']), 1);
pruefe('Video über 6 MB', $f([], ['wa_video' => $datei('gross.mp4', 6.5)] + $dateien), 'Die Datei „gross.mp4“ ist größer als 6 MB.');
pruefe('zusammen über 7,5 MB', $f([], ['wa_video' => $datei('clip.mp4', 5.5), 'wa_foto_1' => $datei('a.jpg', 1.5), 'wa_foto_2' => $datei('b.jpg', 1)] + $dateien), 'Alle Dateien zusammen sind größer als 7,5 MB. Bitte das Video kleiner speichern oder als Link angeben.');
pruefe('Start in der Vergangenheit: heute', ma_werbeanfrage_lesen('banner', array_merge($gut, ['wa_start' => '2026-10-01']), $dateien)['daten']['start'], '2026-10-29');
$u = ma_werbeanfrage_lesen('unternehmen', ['wa_firma' => 'Nur Profil GmbH', 'wa_einwilligung' => '1'], []);
pruefe('Unternehmensprofil: ohne Website und ohne Motive gültig', [$u['fehler'], $u['daten']['motive'], $u['daten']['dateien']], ['', [], []]);

echo "\nRate-Limit, Dateien, Text\n";
$_SERVER['REMOTE_ADDR'] = '203.0.113.5';
$erlaubt = array_map(function () { $ok = ma_werbeanfrage_limit_ok(); if ($ok) ma_werbeanfrage_zaehlen(); return $ok; }, range(1, 6));
pruefe('höchstens fünf angenommene Anfragen je Stunde und Absender', $erlaubt, [true, true, true, true, true, false]);
$_SERVER['REMOTE_ADDR'] = '203.0.113.6';
pruefe('abgelehnte Anfragen zählen nicht (nur prüfen)', [ma_werbeanfrage_limit_ok(), ma_werbeanfrage_limit_ok(), ma_werbeanfrage_limit_ok(), ma_werbeanfrage_limit_ok(), ma_werbeanfrage_limit_ok(), ma_werbeanfrage_limit_ok()], [true, true, true, true, true, true]);
pruefe('IP nicht im Klartext gespeichert', str_contains(implode(' ', array_keys($GLOBALS['transient'])), '203.0.113.5'), false);
$fehlerText = '';
$rollen = ma_werbeanfrage_dateien($r['daten']['dateien'], $dateien, $fehlerText);
pruefe('Dateien mit Rolle: logo, video, foto-1, foto-2', array_values($rollen), ['logo', 'video', 'foto-1', 'foto-2']);
pruefe('zufällige Dateinamen (keine erratbare Adresse)', $GLOBALS['uploads'], ['werbeanfrage-logo-zzzzzzzzzzzz.png', 'werbeanfrage-video-zzzzzzzzzzzz.mp4', 'werbeanfrage-foto-1-zzzzzzzzzzzz.jpg', 'werbeanfrage-foto-2-zzzzzzzzzzzz.jpg']);
pruefe('Inhalt: MP4 und WebM erkannt, ZIP als Video abgelehnt, Bild erkannt, ZIP als Bild abgelehnt', [ma_werbeanfrage_datei_pruefen("$tmp/mp4", true), ma_werbeanfrage_datei_pruefen("$tmp/webm", true), ma_werbeanfrage_datei_pruefen("$tmp/zip", true), ma_werbeanfrage_datei_pruefen("$tmp/jpg", false), ma_werbeanfrage_datei_pruefen("$tmp/zip", false)], ['', '', 'ist kein MP4- oder WebM-Video.', '', 'ist kein Bild (JPG, PNG oder WebP).']);
$riesig = imagecreatetruecolor(8100, 10); imagepng($riesig, "$tmp/png");
pruefe('Bild über 8000 Pixel je Seite abgelehnt', ma_werbeanfrage_datei_pruefen("$tmp/png", false), 'ist zu groß in Pixeln (höchstens 8000 je Seite).');
$falsch = $dateien; $falsch['wa_video']['tmp_name'] = "$tmp/zip"; $GLOBALS['uploads'] = [];
pruefe('ZIP als .mp4: abgelehnt, nichts hochgeladen', [ma_werbeanfrage_dateien(['wa_video'], $falsch, $fehlerText), str_contains($fehlerText, 'kein MP4'), $GLOBALS['uploads']], [[], true, []]);
$kaputt = $dateien; $kaputt['wa_video']['kaputt'] = true;
pruefe('falscher Dateityp: Fehlertext, nichts übernommen', [ma_werbeanfrage_dateien(['wa_video'], $kaputt, $fehlerText), str_contains($fehlerText, 'MP4 oder WebM')], [[], true]);
update_option('ma_werbeformate', ['rundum-paket' => ['name' => 'Rundum-Paket', 'plaetze' => ma_werbeformate_plaetze(), 'motive' => 2, 'video' => true, 'profil' => true, 'tage' => 30, 'preis_cent' => 49000, 'aktiv' => true]]);
$t = ma_werbeanfrage_text($r['daten']); $tr = ma_werbeanfrage_text($r['daten'], true);
pruefe('Zusammenfassung ohne Preis, mit Preis nur auf Wunsch (Eingang)', [str_contains($t, '€'), str_contains($tr, '490,00 € netto'), str_contains($t, 'Website (Klickziel): https://www.baeckerei-beispiel.de')], [false, true, true]);
pruefe('Eingangsbestätigung an den Absender: fester Text, ohne Preis und ohne Angaben aus dem Formular', [ma_werbeanfrage_bestaetigen('kunde@example.org'), $GLOBALS['mails'][0][0], str_contains($GLOBALS['mails'][0][2], '€'), str_contains($GLOBALS['mails'][0][2], 'Angebot und Zeitraum'), str_contains($GLOBALS['mails'][0][2], 'baeckerei'), str_contains($GLOBALS['mails'][0][1], 'Bäckerei')], [true, 'kunde@example.org', false, true, false, false]);
pruefe('je Empfänger höchstens eine Bestätigung am Tag', [ma_werbeanfrage_bestaetigen('KUNDE@example.org'), count($GLOBALS['mails'])], [false, 1]);
$fm = (string) file_get_contents($W . '/includes/forms.php');
pruefe('Redaktionsmail ohne Preis und ohne Dateianhänge; gezählt erst nach dem Speichern', [str_contains($fm, 'ma_werbeanfrage_text($wa,true)'), str_contains($fm, "\$wa ? [] : \$attachments"), str_contains($fm, 'if($wa && $eingang){ ma_werbeanfrage_zaehlen();')], [false, true, true]);

echo "\nIm Eingang anlegen\n";
$GLOBALS['posts'][50] = ['post_type' => 'ma_eingang', 'post_status' => 'ma_neu', 'post_title' => 'Werbung'];
$GLOBALS['meta'][50] = ['_ma_eingang_werbung' => $r['daten'], '_ma_eingang_bildrechte' => '1', '_ma_eingang_name' => 'Erika Muster', '_ma_eingang_email' => 'kunde@example.org'];
foreach (['foto-1' => 61, 'foto-2' => 62, 'video' => 63, 'logo' => 64] as $rolle => $mid) { $GLOBALS['medien'][$mid] = 50; $GLOBALS['meta'][$mid]['_ma_eingang_rolle'] = $rolle; }
$e = ma_werbeanfrage_anlegen(50);
$pid = $e['profil']; [$a1, $a2] = $e['anzeigen'];
$pm = fn(string $k) => $GLOBALS['meta'][$pid][$k] ?? '';
pruefe('Firmenprofil zur Prüfung: Name, Website, Branche, Kanal', [$GLOBALS['posts'][$pid]['post_type'], $GLOBALS['posts'][$pid]['post_status'], $GLOBALS['posts'][$pid]['post_title'], $pm('ma_business_website'), $pm('ma_business_branche'), $pm('ma_business_kanal')], ['ma_business', 'pending', 'Bäckerei Beispiel', 'https://www.baeckerei-beispiel.de', 'Bäckerei', '1']);
pruefe('Einwilligung aus der Anfrage mit Quelle, Logo als Bild', [$pm('ma_business_consent'), $pm('ma_business_consent_quelle'), $pm('_thumbnail_id'), $pm('ma_business_logo_rechte')], ['1', 'Werbeanfrage vom 29.10.2026 über das Formular', 64, 'geklaert']);
$am = fn(int $id, string $k) => $GLOBALS['meta'][$id][$k] ?? '';
pruefe('zwei Anzeigen zur Prüfung mit Überschrift und Satz', [$GLOBALS['posts'][$a1]['post_status'], $GLOBALS['posts'][$a1]['post_title'], $GLOBALS['posts'][$a1]['post_excerpt'], $GLOBALS['posts'][$a2]['post_title']], ['pending', 'Frische Brötchen ab 6 Uhr', 'Am Markt in Merzenich.', 'Torten auf Bestellung']);
pruefe('Ziel ist die Website, Knopf „Zur Website“, Zeile oben, Firma, Format', [$am($a1, 'ma_ad_url'), $am($a1, 'ma_ad_knopf'), $am($a1, 'ma_ad_sponsor'), $am($a1, 'ma_business_id'), $am($a1, 'ma_ad_format')], ['https://www.baeckerei-beispiel.de', 'Zur Website', 'Bäckerei Beispiel · Bäckerei', (string) $pid, 'rundum-paket']);
pruefe('Plätze aus dem Format, Start = Wunschtermin, Ende erst beim Freischalten', [1 + count($GLOBALS['multi'][$a1]['ma_ad_slot_weitere'] ?? []), $am($a1, 'ma_ad_start'), $am($a1, 'ma_ad_end')], [11, '2026-11-02 00:00:00', '']);
pruefe('Foto je Motiv, Video bei beiden, nicht aktiv', [$am($a1, '_thumbnail_id'), $am($a2, '_thumbnail_id'), $am($a1, 'ma_ad_video'), $am($a2, 'ma_ad_video'), $am($a1, 'ma_ad_active')], [61, 62, '63', '63', '']);
pruefe('Eingang erledigt, angelegte Inhalte gemerkt', [$GLOBALS['posts'][50]['post_status'], $GLOBALS['meta'][50]['_ma_eingang_angelegt']], ['ma_erledigt', ['profil' => $pid, 'anzeigen' => [$a1, $a2]]]);
$n = count($GLOBALS['posts']);
$zweit = ma_werbeanfrage_anlegen(50);
pruefe('zweiter Aufruf: nichts doppelt', [count($GLOBALS['posts']), $zweit['profil'], $zweit['log']], [$n, $pid, ['Schon angelegt.']]);

$GLOBALS['posts'][70] = ['post_type' => 'ma_eingang', 'post_status' => 'ma_neu'];
$d2 = $r['daten']; $d2['website'] = 'https://baeckerei-beispiel.de/kontakt';
$GLOBALS['meta'][70] = ['_ma_eingang_werbung' => $d2, '_ma_eingang_bildrechte' => ''];
$GLOBALS['medien'][71] = 70; $GLOBALS['meta'][71]['_ma_eingang_rolle'] = 'foto-1';
$e2 = ma_werbeanfrage_anlegen(70);
pruefe('gleiche Website: vorhandenes Profil verknüpft, kein zweites', [$e2['profil'], count(array_filter($GLOBALS['posts'], fn($p) => ($p['post_type'] ?? '') === 'ma_business'))], [$pid, 1]);
$GLOBALS['posts'][80] = ['post_type' => 'ma_business', 'post_status' => 'draft', 'post_title' => 'Almera Clean']; $GLOBALS['meta'][80] = ['ma_business_website' => 'https://www.almera-clean.de/'];
$GLOBALS['posts'][81] = ['post_type' => 'ma_eingang', 'post_status' => 'ma_neu'];
$d3 = $r['daten']; $d3['firma'] = 'Almera Clean'; $d3['website'] = 'https://fremde-seite.example';
$GLOBALS['meta'][81] = ['_ma_eingang_werbung' => $d3, '_ma_eingang_bildrechte' => '1', '_ma_eingang_name' => 'Fremd'];
$GLOBALS['medien'][82] = 81; $GLOBALS['meta'][82]['_ma_eingang_rolle'] = 'logo';
$e3 = ma_werbeanfrage_anlegen(81);
pruefe('gleicher Name, andere Website: verknüpft, aber keine Einwilligung, kein Logo, Warnung', [$e3['profil'], $GLOBALS['meta'][80]['ma_business_consent'] ?? '', $GLOBALS['meta'][80]['_thumbnail_id'] ?? 0, str_contains(implode(' ', $e3['log']), 'weicht von der Website im Profil')], [80, '', 0, true]);
pruefe('ohne Bildrechte: Anzeige ohne Foto und Video, im Protokoll genannt', [$am($e2['anzeigen'][0], '_thumbnail_id'), $am($e2['anzeigen'][0], 'ma_ad_video'), str_contains(implode(' ', $e2['log']), 'ohne Foto')], ['', '', true]);

array_map('unlink', glob("$tmp/*")); @rmdir($tmp);
echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
