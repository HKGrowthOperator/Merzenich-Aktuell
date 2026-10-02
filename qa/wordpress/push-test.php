<?php
/**
 * Web Push (includes/push.php) ohne WordPress: Base64url, VAPID-Schlüssel,
 * JWT mit Signaturprüfung, Verschlüsselung nach RFC 8291 mit Rückweg,
 * Kopfzeilen, Nutzlast.
 * Aufruf: php qa/wordpress/push-test.php
 */
define('ABSPATH', __DIR__);
function add_action(...$a) {} function add_filter(...$a) {}
require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/push.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-70s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Voraussetzungen\n";
pruefe('PHP kann Web Push (openssl_pkey_derive, hash_hkdf, AES-128-GCM, P-256)', ma_push_moeglich(), true);

echo "\nBase64url\n";
pruefe('Kodieren ohne Padding, URL-sicher', ma_push_b64url("\xfb\xff\xbf"), '-_-_');
pruefe('Rückweg', ma_push_b64url_dec(ma_push_b64url(random_bytes(33))) !== '' && ma_push_b64url_dec('AQID') === "\x01\x02\x03", true);

echo "\nVAPID-Schlüssel und JWT\n";
$k = ma_push_schluessel_erzeugen();
pruefe('Schlüsselpaar: PEM privat, 65-Byte-Punkt öffentlich (Base64url, 87 Zeichen)', [$k && str_starts_with($k['privat'], '-----BEGIN'), $k ? strlen(ma_push_b64url_dec($k['oeffentlich'])) : 0, $k ? strlen($k['oeffentlich']) : 0], [true, 65, 87]);
$jwt = ma_push_vapid_jwt('https://fcm.googleapis.com', MA_PUSH_KONTAKT, $k['privat'], time() + 43200);
$teile = explode('.', $jwt);
pruefe('JWT: drei Teile, Kopf ES256, Nutzlast aud/exp/sub, Signatur 64 Byte', [count($teile), json_decode(ma_push_b64url_dec($teile[0]), true), array_keys(json_decode(ma_push_b64url_dec($teile[1]), true)), strlen(ma_push_b64url_dec($teile[2]))], [3, ['typ' => 'JWT', 'alg' => 'ES256'], ['aud', 'exp', 'sub'], 64]);
// Signaturprüfung: r||s zurück nach DER und mit dem öffentlichen Schlüssel prüfen.
$rs = ma_push_b64url_dec($teile[2]);
$int = function (string $b): string { $b = ltrim($b, "\0"); if ((ord($b[0]) & 0x80) !== 0) $b = "\0" . $b; return "\x02" . chr(strlen($b)) . $b; };
$der = $int(substr($rs, 0, 32)) . $int(substr($rs, 32)); $der = "\x30" . chr(strlen($der)) . $der;
$pub = openssl_pkey_get_public(ma_push_punkt_pem(ma_push_b64url_dec($k['oeffentlich'])));
pruefe('Signatur gültig (openssl_verify gegen den öffentlichen Schlüssel)', openssl_verify("$teile[0].$teile[1]", $der, $pub, OPENSSL_ALGO_SHA256), 1);
pruefe('Manipulierte Nutzlast fällt durch', openssl_verify("$teile[0].x$teile[1]", $der, $pub, OPENSSL_ALGO_SHA256), 0);
pruefe('Audience aus Endpoint', [ma_push_audience('https://updates.push.services.mozilla.com/wpush/v2/abc'), ma_push_audience('kaputt')], ['https://updates.push.services.mozilla.com', '']);

echo "\nVerschlüsselung (RFC 8291 aes128gcm)\n";
$ua = ma_push_schluessel_erzeugen(); $auth = ma_push_b64url(random_bytes(16));
$nachricht = ma_push_nutzlast('Feuerwehr Merzenich: <b>Einsatz</b> am Kamp', "Rauch   über Bürgewald,\nFeuerwehr vor Ort. " . str_repeat('Lange Beschreibung ', 20), 'https://merzenich-aktuell.de/blaulicht/x/', 'https://merzenich-aktuell.de/i.webp', 'x');
$j = json_decode($nachricht, true);
pruefe('Nutzlast: Tags raus, Weißraum eins, Text ≤ 120 Zeichen mit …, Felder', [strip_tags($j['title']) === $j['title'], mb_strlen($j['body']) <= 120, str_ends_with($j['body'], '…'), array_keys($j)], [true, true, true, ['title', 'body', 'url', 'icon', 'tag']]);
$koerper = ma_push_verschluesseln($nachricht, $ua['oeffentlich'], $auth);
pruefe('Körper: Salt 16, rs 4096, idlen 65, Absenderpunkt, Chiffrat + Tag', [$koerper !== null, $koerper ? unpack('N', substr($koerper, 16, 4))[1] : 0, $koerper ? ord($koerper[20]) : 0, $koerper ? strlen($koerper) : 0], [true, 4096, 65, 16 + 4 + 1 + 65 + strlen($nachricht) + 1 + 16]);
pruefe('Rückweg mit dem Empfängerschlüssel liefert die Nachricht', ma_push_entschluesseln($koerper, $ua['privat'], $auth), $nachricht);
pruefe('Falsches auth-Geheimnis scheitert', ma_push_entschluesseln($koerper, $ua['privat'], ma_push_b64url(random_bytes(16))), null);
pruefe('Zwei Sendungen derselben Nachricht sind verschieden (Salt, Absenderschlüssel)', ma_push_verschluesseln($nachricht, $ua['oeffentlich'], $auth) === $koerper, false);
// Reproduzierbar mit festem Absenderschlüssel und Salt (Test-Haken).
$lokal = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]); $salt = random_bytes(16);
pruefe('Fester Absenderschlüssel und Salt: identisches Ergebnis', ma_push_verschluesseln('a', $ua['oeffentlich'], $auth, ['lokal' => $lokal, 'salt' => $salt]) === ma_push_verschluesseln('a', $ua['oeffentlich'], $auth, ['lokal' => $lokal, 'salt' => $salt]), true);
pruefe('Ungültige Schlüssel (Länge) → null', [ma_push_verschluesseln('a', 'AQID', $auth), ma_push_verschluesseln('a', $ua['oeffentlich'], 'AQID')], [null, null]);
pruefe('Zu lange Nutzlast (> 3993 Byte) → null', ma_push_verschluesseln(str_repeat('x', 3994), $ua['oeffentlich'], $auth), null);

echo "\nAbo prüfen\n";
$ok = ['endpoint' => 'https://fcm.googleapis.com/fcm/send/abcdefghijklmnop', 'keys' => ['p256dh' => $ua['oeffentlich'], 'auth' => $auth]];
$g = ma_push_abo_pruefen($ok);
pruefe('Gültiges Abo: Kennung sha256 des Endpoints, Felder übernommen', [$g ? $g['kennung'] : '', $g ? array_keys($g) : []], [hash('sha256', $ok['endpoint']), ['kennung', 'endpoint', 'p256dh', 'auth']]);
pruefe('Abgelehnt: http, kurzer Schlüssel, fehlendes auth, leer', [ma_push_abo_pruefen(['endpoint' => 'http://x.example/abc', 'keys' => $ok['keys']]), ma_push_abo_pruefen(['endpoint' => $ok['endpoint'], 'keys' => ['p256dh' => 'AQID', 'auth' => $auth]]), ma_push_abo_pruefen(['endpoint' => $ok['endpoint'], 'keys' => ['p256dh' => $ua['oeffentlich']]]), ma_push_abo_pruefen([])], [null, null, null, null]);
pruefe('sw.js: push- und notificationclick-Listener, kein Cache', [str_contains(ma_push_sw_js(), "addEventListener('push'"), str_contains(ma_push_sw_js(), "addEventListener('notificationclick'"), str_contains(ma_push_sw_js(), 'caches.')], [true, true, false]);

echo "\nKopfzeilen\n";
$h = ma_push_kopfzeilen($jwt, $k['oeffentlich'], 123);
pruefe('vapid t=…, k=…; aes128gcm; TTL 86400; Urgency normal; Länge', [str_starts_with($h['Authorization'], 'vapid t=' . $jwt . ', k=' . $k['oeffentlich']), $h['Content-Encoding'], $h['TTL'], $h['Urgency'], $h['Content-Length'], $h['Content-Type']], [true, 'aes128gcm', '86400', 'normal', '123', 'application/octet-stream']);

echo "\n" . ($fehler ? "$fehler Fehler" : 'Alle Prüfungen bestanden') . "\n";
exit($fehler ? 1 : 0);
