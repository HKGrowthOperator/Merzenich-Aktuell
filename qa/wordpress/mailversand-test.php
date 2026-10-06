<?php
/**
 * Mailversand über SMTP (Plugin 1.23.0, includes/mailversand.php) ohne WordPress:
 * Passwort verschlüsselt und nie im HTML, leeres Feld überschreibt nichts,
 * SMTP nur bei vollständiger und eingeschalteter Einstellung.
 * Aufruf: php qa/wordpress/mailversand-test.php
 */
define('ABSPATH', __DIR__ . '/');
function add_action(...$a) {} function add_filter(...$a) {}
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return (string) $s; }
function admin_url($p = '') { return 'https://merzenich-aktuell.de/wp-admin/' . $p; }
function wp_nonce_field($a, $n = '_wpnonce', $r = true, $echo = true) { return '<input type="hidden" name="' . $n . '" value="nonce">'; }
function is_email($s) { return filter_var($s, FILTER_VALIDATE_EMAIL) ? $s : false; }
function sanitize_text_field($s) { return trim(strip_tags((string) $s)); }
require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/mailversand.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-70s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
$k = sodium_crypto_generichash('salz-der-installation', '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
$k2 = sodium_crypto_generichash('andere-salze', '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

echo "Passwort\n";
$g = ma_mail_verschluesseln('Geh€im!pass', $k);
pruefe('Verschlüsselt gespeichert, nicht im Klartext', str_contains($g, 'Geh') || str_contains(base64_decode($g), 'Geh'), false);
pruefe('Entschlüsseln liefert das Passwort zurück', ma_mail_entschluesseln($g, $k), 'Geh€im!pass');
pruefe('Falscher Schlüssel oder Unsinn: leer statt Fehler', [ma_mail_entschluesseln($g, $k2), ma_mail_entschluesseln('kaputt', $k), ma_mail_entschluesseln('', $k)], ['', '', '']);

echo "\nFormular\n";
$alt = ['aktiv' => true, 'host' => 'smtp.ionos.de', 'port' => 587, 'art' => 'tls', 'benutzer' => 'redaktion@merzenich-aktuell.de', 'passwort' => $g, 'absender' => '', 'name' => 'Merzenich Aktuell'];
$neu = ma_mail_aus_formular($alt, ['aktiv' => '1', 'host' => ' SMTP.IONOS.DE ', 'port' => '587', 'art' => 'tls', 'benutzer' => 'redaktion@merzenich-aktuell.de', 'passwort' => '', 'absender' => '', 'name' => ''], $k);
pruefe('Leeres Passwortfeld lässt das gespeicherte stehen', $neu['passwort'], $g);
pruefe('Server kleingeschrieben und getrimmt, Name mit Vorgabe', [$neu['host'], $neu['name']], ['smtp.ionos.de', 'Merzenich Aktuell']);
$neu2 = ma_mail_aus_formular($alt, ['aktiv' => '1', 'host' => 'smtp.ionos.de', 'port' => '465', 'art' => 'ssl', 'benutzer' => 'x@y.de', 'passwort' => 'neu123'], $k);
pruefe('Neues Passwort ersetzt das alte (verschlüsselt)', [$neu2['passwort'] !== $g, ma_mail_entschluesseln($neu2['passwort'], $k)], [true, 'neu123']);
pruefe('„Passwort löschen“ entfernt es', ma_mail_aus_formular($alt, ['passwort_loeschen' => '1', 'passwort' => ''], $k)['passwort'], '');
pruefe('Unsinn wird verworfen (Server, Port, Art, Absender)', array_intersect_key(ma_mail_aus_formular($alt, ['host' => 'smtp.ionos.de/<x>', 'port' => '99999', 'art' => 'ftp', 'absender' => 'kein-mail'], $k), array_flip(['host', 'port', 'art', 'absender', 'aktiv'])), ['aktiv' => false, 'host' => '', 'port' => 65535, 'art' => 'tls', 'absender' => '']);

echo "\nWann SMTP greift\n";
pruefe('Eingeschaltet und vollständig: bereit', ma_mail_bereit($alt), true);
pruefe('Ausgeschaltet: PHP-Standard bleibt', ma_mail_bereit(['aktiv' => false] + $alt), false);
pruefe('Ohne Passwort oder Benutzer: nicht bereit', [ma_mail_bereit(['passwort' => ''] + $alt), ma_mail_bereit(['benutzer' => ''] + $alt)], [false, false]);
pruefe('Absender: eigene Adresse, sonst Benutzer', [ma_mail_absender(['absender' => 'info@merzenich-aktuell.de'] + $alt), ma_mail_absender($alt)], ['info@merzenich-aktuell.de', 'redaktion@merzenich-aktuell.de']);
$m = new class { public $smtp = false; public $Host, $Port, $SMTPAuth, $Username, $Password, $SMTPSecure, $SMTPAutoTLS, $Timeout; public function isSMTP() { $this->smtp = true; } };
ma_mail_phpmailer_setzen($m, $alt, 'Geh€im!pass');
pruefe('PHPMailer: SMTP, IONOS, 587, STARTTLS, Anmeldung', [$m->smtp, $m->Host, $m->Port, $m->SMTPSecure, $m->SMTPAuth, $m->Username, $m->Password, $m->SMTPAutoTLS], [true, 'smtp.ionos.de', 587, 'tls', true, 'redaktion@merzenich-aktuell.de', 'Geh€im!pass', true]);
ma_mail_phpmailer_setzen($m, ['art' => 'ssl', 'port' => 465] + $alt, 'x');
pruefe('PHPMailer: SSL auf 465', [$m->SMTPSecure, $m->Port], ['ssl', 465]);

echo "\nSeite\n";
$html = ma_mail_seite_html($alt, 'redaktion@merzenich-aktuell.de', true, ['ok' => false, 'an' => 'redaktion@merzenich-aktuell.de', 'fehler' => 'SMTP connect() failed.']);
pruefe('Passwort steht nie im HTML, Feld bleibt leer', [str_contains($html, 'Geh€im'), str_contains($html, $g), str_contains($html, 'name="passwort" autocomplete="new-password" value=""')], [false, false, true]);
pruefe('Stand, gespeichert-Hinweis, Fehler der Test-Mail sichtbar', [str_contains($html, 'eingerichtet über smtp.ionos.de'), str_contains($html, 'Gespeichert.'), str_contains($html, 'SMTP connect() failed.')], [true, true, true]);
pruefe('Nicht eingerichtet: Hinweis auf Spam', str_contains(ma_mail_status_text(['aktiv' => false] + $alt), 'landen oft im Spam'), true);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
