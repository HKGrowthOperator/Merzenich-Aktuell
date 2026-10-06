<?php
/**
 * Nur verschlüsselt (Theme 21.13.0, inc/ma21.php) ohne WordPress: wann http://
 * auf https:// umgeleitet wird (keine Schleife hinter einem Proxy) und dass der
 * .htaccess-Block die WordPress-Infodateien sperrt.
 * Aufruf: php qa/wordpress/sicherheit-test.php
 */
define('ABSPATH', '/var/www/html/');
$GLOBALS['filter'] = [];
function add_action(...$a) {} function remove_action(...$a) {} function add_image_size(...$a) {}
function add_filter($name, $fn, ...$a) { $GLOBALS['filter'][$name][] = $fn; }
function trailingslashit($s) { return rtrim((string) $s, '/') . '/'; }
function get_template_directory() { return '/var/www/html/wp-content/themes/merzenich-aktuell'; }
function wp_upload_dir(...$a) { return ['basedir' => '/var/www/html/wp-content/uploads']; }
function home_url($p = '') { return 'https://merzenich-aktuell.de' . $p; }
class WP_Term {} class WP_Query {} class WP_Post {}
require __DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/ma21.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-70s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Umleitung auf https://\n";
pruefe('http:// auf Port 80 wird umgeleitet', ma21_unverschluesselt(['SERVER_PORT' => '80']), true);
pruefe('HTTPS=on bleibt', ma21_unverschluesselt(['HTTPS' => 'on', 'SERVER_PORT' => '443']), false);
pruefe('HTTPS=1 bleibt', ma21_unverschluesselt(['HTTPS' => '1', 'SERVER_PORT' => '80']), false);
pruefe('HTTPS=off auf Port 80 wird umgeleitet', ma21_unverschluesselt(['HTTPS' => 'off', 'SERVER_PORT' => '80']), true);
pruefe('Proxy meldet https (X-Forwarded-Proto): keine Schleife', ma21_unverschluesselt(['HTTP_X_FORWARDED_PROTO' => 'https', 'SERVER_PORT' => '80']), false);
pruefe('REQUEST_SCHEME https: keine Schleife', ma21_unverschluesselt(['REQUEST_SCHEME' => 'https', 'SERVER_PORT' => '80']), false);
pruefe('Port 443 ohne Kennzeichen bleibt (wie is_ssl())', ma21_unverschluesselt(['SERVER_PORT' => '443']), false);
pruefe('Ohne Angaben keine Umleitung', ma21_unverschluesselt([]), false);

echo "\n.htaccess-Block\n";
$block = '';
foreach ($GLOBALS['filter']['mod_rewrite_rules'] ?? [] as $fn) $block = $fn('# WordPress');
pruefe('readme.html, license.txt, wp-config-sample.php gesperrt', str_contains($block, 'RewriteRule ^(readme\.html|license\.txt|wp-config-sample\.php)$ - [F,L]'), true);
pruefe('Sperre steht vor den Asset-Regeln und vor WordPress', strpos($block, '[F,L]') < strpos($block, 'ressort-menue') && strpos($block, '[F,L]') < strpos($block, '# WordPress'), true);

echo "\nLinks auf nicht veröffentlichte Beiträge (Theme 21.13.0)\n";
$status = fn(string $u): ?string => ['/blaulicht/entwurf/' => 'entwurf', '/blaulicht/fertig/' => 'publish'][(string) parse_url($u, PHP_URL_PATH)] ?? null;
$html = '<p>Mehr: <a href="https://merzenich-aktuell.de/blaulicht/entwurf/">Zur Meldung</a>, <a href="/blaulicht/fertig/#k">Bericht</a>, <a href="https://example.org/x/">extern</a>, <a href="/termine/">Termine</a>.</p>';
pruefe('Link auf Entwurf wird zu Text, die übrigen bleiben', ma21_links_ohne_entwuerfe($html, $status), '<p>Mehr: Zur Meldung, <a href="/blaulicht/fertig/#k">Bericht</a>, <a href="https://example.org/x/">extern</a>, <a href="/termine/">Termine</a>.</p>');
pruefe('Ohne Links unverändert', ma21_links_ohne_entwuerfe('<p>Text</p>', $status), '<p>Text</p>');

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
