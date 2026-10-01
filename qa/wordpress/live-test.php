<?php
/**
 * Live-Tracker (includes/live.php) ohne WordPress: Besucherkennung, Gerät, Herkunft.
 * Aufruf: php qa/wordpress/live-test.php
 */
define('ABSPATH', __DIR__);
define('HOUR_IN_SECONDS', 3600);
$GLOBALS['opt'] = []; $GLOBALS['tag'] = '2026-10-01';
function add_action(...$a) {} function add_filter(...$a) {}
function get_option($k, $d = false) { return $GLOBALS['opt'][$k] ?? $d; }
function update_option($k, $v, $a = null) { $GLOBALS['opt'][$k] = $v; return true; }
function current_time($f) { return $f === 'Y-m-d' ? $GLOBALS['tag'] : $GLOBALS['tag'] . ' 12:00:00'; }
function home_url() { return 'https://merzenich-aktuell.de'; }
function wp_parse_url($u, $c = -1) { return parse_url($u, $c); }

require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/live.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-62s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Besucherkennung\n";
$_SERVER['REMOTE_ADDR'] = '203.0.113.7';
$a = ma_live_kennung('UA-A');
pruefe('Kennung hat 16 Zeichen', strlen($a), 16);
pruefe('gleicher Besucher am selben Tag = gleiche Kennung', ma_live_kennung('UA-A'), $a);
pruefe('anderer Browser = andere Kennung', ma_live_kennung('UA-B') !== $a, true);
pruefe('IP-Adresse steckt nicht in der Kennung', str_contains($a, '203'), false);
$GLOBALS['tag'] = '2026-10-02';
pruefe('am nächsten Tag nicht mehr zuzuordnen', ma_live_kennung('UA-A') !== $a, true);
pruefe('nur ein Tageswert gespeichert (alter verworfen)', $GLOBALS['opt']['ma_live_salz']['tag'], '2026-10-02');
$_SERVER['REMOTE_ADDR'] = '10.0.0.5'; $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.4, 10.0.0.1';
pruefe('hinter Proxy zählt die erste Adresse', ma_live_ip(), '198.51.100.4');
$_SERVER['REMOTE_ADDR'] = '203.0.113.9';
pruefe('öffentliche Adresse wird nicht überschrieben', ma_live_ip(), '203.0.113.9');

echo "Gerät\n";
pruefe('iPhone ist Handy', ma_live_geraet('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) Mobile/15E148'), 'Handy');
pruefe('Android-Handy ist Handy', ma_live_geraet('Mozilla/5.0 (Linux; Android 14; Pixel 8) Chrome/128 Mobile Safari'), 'Handy');
pruefe('iPad ist Tablet', ma_live_geraet('Mozilla/5.0 (iPad; CPU OS 17_0)'), 'Tablet');
pruefe('Android ohne Mobile ist Tablet', ma_live_geraet('Mozilla/5.0 (Linux; Android 14; SM-X710) Chrome/128 Safari'), 'Tablet');
pruefe('Windows ist Desktop', ma_live_geraet('Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/128'), 'Desktop');

echo "Herkunft\n";
pruefe('kein Verweis = direkt', ma_live_quelle(''), 'direkt');
pruefe('www.google.de = Google', ma_live_quelle('www.google.de'), 'Google');
pruefe('l.facebook.com = Facebook', ma_live_quelle('l.facebook.com'), 'Facebook');
pruefe('l.instagram.com = Instagram', ma_live_quelle('l.instagram.com'), 'Instagram');
pruefe('eigene Domain = intern', ma_live_quelle('www.merzenich-aktuell.de'), 'intern');
pruefe('fremde Seite = Hostname', ma_live_quelle('www.aachener-zeitung.de'), 'aachener-zeitung.de');

echo $fehler ? "\n$fehler Fehler\n" : "\nalles ok\n";
exit($fehler ? 1 : 0);
