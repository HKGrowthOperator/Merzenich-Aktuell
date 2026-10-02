<?php
/**
 * Web Push aufs Handy ohne Fremddienst (02.10.2026).
 *
 * Leser, die „Live-Meldungen“ erlauben, bekommen jede neu veröffentlichte
 * Meldung als Benachrichtigung, auch bei geschlossenem Browser. Das Abo
 * (Endpoint des Push-Dienstes des Browsers, zwei Schlüssel) liegt in der
 * Tabelle {prefix}ma_push; keine IP, kein User-Agent. Der Versand läuft über
 * den Push-Dienst des jeweiligen Browsers (Google, Mozilla, Apple), die
 * Nachricht ist Ende-zu-Ende verschlüsselt (RFC 8291, aes128gcm) und mit dem
 * VAPID-Schlüssel der Seite signiert (RFC 8292). Alles mit PHP-Bordmitteln
 * (openssl, hash_hkdf), kein externer Dienst, keine Bibliothek.
 *
 * Reine Funktionen (ohne WordPress testbar, qa/wordpress/push-test.php):
 *   ma_push_b64url(), ma_push_b64url_dec(), ma_push_schluessel_erzeugen(),
 *   ma_push_vapid_jwt(), ma_push_der_zu_rs(), ma_push_verschluesseln(),
 *   ma_push_kopfzeilen(), ma_push_audience(), ma_push_nutzlast().
 */
if (!defined('ABSPATH')) { exit; }

const MA_PUSH_KONTAKT = 'mailto:info@kbs-management.tv';
const MA_PUSH_TTL = 86400;
const MA_PUSH_PAKET = 50;
const MA_PUSH_MAX_FEHLER = 5;
const MA_PUSH_RATE = 10; // Abo-Anfragen je Endpoint und Stunde

/* ------------------------------------------------------------ Reine Funktionen */

function ma_push_b64url(string $bin): string { return rtrim(strtr(base64_encode($bin), '+/', '-_'), '='); }
function ma_push_b64url_dec(string $s): string { $s = strtr($s, '-_', '+/'); return (string) base64_decode($s . str_repeat('=', (4 - strlen($s) % 4) % 4), true); }

/** Kann diese PHP-Installation Web Push (ECDH, HKDF, AES-128-GCM, P-256)? */
function ma_push_moeglich(): bool {
    return function_exists('openssl_pkey_derive') && function_exists('hash_hkdf') && function_exists('openssl_pkey_new')
        && in_array('aes-128-gcm', openssl_get_cipher_methods(), true) && in_array('prime256v1', openssl_get_curve_names() ?: [], true);
}

/** Roher P-256-Punkt (65 Byte, unkomprimiert) eines openssl-Schlüssels. */
function ma_push_punkt($schluessel): string {
    $d = openssl_pkey_get_details($schluessel);
    if (!$d || empty($d['ec']['x']) || empty($d['ec']['y'])) return '';
    return "\x04" . str_pad($d['ec']['x'], 32, "\0", STR_PAD_LEFT) . str_pad($d['ec']['y'], 32, "\0", STR_PAD_LEFT);
}

/** Öffentlicher Schlüssel aus einem rohen P-256-Punkt als PEM (SubjectPublicKeyInfo). */
function ma_push_punkt_pem(string $punkt): string {
    $der = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200') . $punkt;
    return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
}

/**
 * Neuer P-256-Schlüssel. PHP-Installationen ohne openssl.cnf (Playground,
 * manche Hoster) scheitern an openssl_pkey_new („BIO_new_file: no such
 * file“); dann bekommt openssl eine minimale Konfigurationsdatei mit.
 */
function ma_push_ec_neu() {
    // Testhaken: der Playground (php-wasm) kann keine EC-Schlüssel erzeugen, wohl aber signieren und ableiten;
    // die Prüfskripte reichen hier einen fertigen Schlüssel herein. Im Betrieb liefert der Filter nichts.
    if (function_exists('apply_filters')) { $k = apply_filters('ma_push_ec_schluessel', null); if ($k) return $k; }
    $opt = ['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC];
    $k = @openssl_pkey_new($opt);
    if ($k) return $k;
    $cfg = ma_push_openssl_cnf();
    return $cfg !== '' ? (@openssl_pkey_new($opt + ['config' => $cfg]) ?: null) : null;
}
function ma_push_openssl_cnf(): string {
    static $pfad = null;
    if ($pfad !== null) return $pfad;
    $pfad = rtrim(sys_get_temp_dir(), '/') . '/ma-openssl.cnf';
    if (!file_exists($pfad) && @file_put_contents($pfad, "openssl_conf = default_conf\n[default_conf]\n[req]\ndistinguished_name = dn\n[dn]\n") === false) $pfad = '';
    return $pfad;
}

/** VAPID-Schlüsselpaar: privat als PEM, öffentlich als Base64url des rohen Punkts (für applicationServerKey). */
function ma_push_schluessel_erzeugen(): ?array {
    $k = ma_push_ec_neu();
    if (!$k) return null;
    if (!@openssl_pkey_export($k, $pem) && !(ma_push_openssl_cnf() !== '' && @openssl_pkey_export($k, $pem, null, ['config' => ma_push_openssl_cnf()]))) return null;
    $punkt = ma_push_punkt($k);
    return $punkt === '' ? null : ['privat' => $pem, 'oeffentlich' => ma_push_b64url($punkt)];
}

/** DER-kodierte ECDSA-Signatur (SEQUENCE von zwei INTEGER) in die rohe Form r||s (64 Byte) für JWS. */
function ma_push_der_zu_rs(string $der): string {
    $pos = 2;
    if ((ord($der[1]) & 0x80) !== 0) $pos += ord($der[1]) & 0x7f;
    $teile = [];
    for ($i = 0; $i < 2; $i++) {
        if (ord($der[$pos]) !== 0x02) return '';
        $len = ord($der[$pos + 1]); $wert = substr($der, $pos + 2, $len); $pos += 2 + $len;
        $wert = ltrim($wert, "\0");
        $teile[] = str_pad($wert, 32, "\0", STR_PAD_LEFT);
    }
    return $teile[0] . $teile[1];
}

/** Signiertes VAPID-JWT (ES256): aud = Ursprung des Push-Dienstes, sub = Kontakt, exp = Ablauf (höchstens 24 h). */
function ma_push_vapid_jwt(string $aud, string $sub, string $pemPrivat, int $ablauf): string {
    $kopf = ma_push_b64url((string) json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
    $nutz = ma_push_b64url((string) json_encode(['aud' => $aud, 'exp' => $ablauf, 'sub' => $sub]));
    $der = '';
    if (!openssl_sign("$kopf.$nutz", $der, $pemPrivat, OPENSSL_ALGO_SHA256)) return '';
    return "$kopf.$nutz." . ma_push_b64url(ma_push_der_zu_rs($der));
}

/** Ursprung des Push-Dienstes (Schema + Host) aus dem Abo-Endpoint; das ist das aud des JWT. */
function ma_push_audience(string $endpoint): string {
    $u = parse_url($endpoint);
    return isset($u['scheme'], $u['host']) ? $u['scheme'] . '://' . $u['host'] : '';
}

/**
 * Nutzlast nach RFC 8291 (aes128gcm) verschlüsseln. p256dh/auth sind die
 * Base64url-Werte aus PushSubscription.toJSON().keys. Liefert den fertigen
 * Request-Körper (Header: salt | rs | idlen | Absender-Punkt, dann Chiffrat +
 * Tag) oder null bei ungültigen Schlüsseln. $test (nur Tests): fester
 * Absenderschlüssel und Salt für reproduzierbare Ergebnisse.
 */
function ma_push_verschluesseln(string $nutzlast, string $p256dh, string $auth, ?array $test = null): ?string {
    $uaPunkt = ma_push_b64url_dec($p256dh); $authGeheim = ma_push_b64url_dec($auth);
    if (strlen($uaPunkt) !== 65 || $uaPunkt[0] !== "\x04" || strlen($authGeheim) !== 16 || strlen($nutzlast) > 3993) return null;
    $lokal = $test['lokal'] ?? ma_push_ec_neu();
    $uaSchluessel = openssl_pkey_get_public(ma_push_punkt_pem($uaPunkt));
    if (!$lokal || !$uaSchluessel) return null;
    $asPunkt = ma_push_punkt($lokal);
    $geteilt = openssl_pkey_derive($uaSchluessel, $lokal, 32);
    if (!is_string($geteilt) || strlen($geteilt) !== 32) return null;
    $salt = $test['salt'] ?? random_bytes(16);
    $ikm = hash_hkdf('sha256', $geteilt, 32, "WebPush: info\0" . $uaPunkt . $asPunkt, $authGeheim);
    $cek = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\0", $salt);
    $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\0", $salt);
    $tag = '';
    $chiffrat = openssl_encrypt($nutzlast . "\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);
    if ($chiffrat === false) return null;
    return $salt . pack('N', 4096) . chr(65) . $asPunkt . $chiffrat . $tag;
}

/** Gegenstück für Tests: entschlüsselt mit dem privaten Schlüssel des Empfängers (PEM) und seinem auth-Geheimnis. */
function ma_push_entschluesseln(string $koerper, string $pemPrivatEmpfaenger, string $auth): ?string {
    if (strlen($koerper) < 86) return null;
    $salt = substr($koerper, 0, 16); $idlen = ord($koerper[20]); $asPunkt = substr($koerper, 21, $idlen); $rest = substr($koerper, 21 + $idlen);
    $ua = openssl_pkey_get_private($pemPrivatEmpfaenger); if (!$ua) return null;
    $uaPunkt = ma_push_punkt($ua);
    $as = openssl_pkey_get_public(ma_push_punkt_pem($asPunkt)); if (!$as) return null;
    $geteilt = openssl_pkey_derive($as, $ua, 32);
    $ikm = hash_hkdf('sha256', $geteilt, 32, "WebPush: info\0" . $uaPunkt . $asPunkt, ma_push_b64url_dec($auth));
    $cek = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\0", $salt);
    $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\0", $salt);
    $klar = openssl_decrypt(substr($rest, 0, -16), 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, substr($rest, -16));
    if ($klar === false) return null;
    return rtrim(substr($klar, 0, strrpos($klar, "\x02")), "\0");
}

/** Kopfzeilen der Push-Anfrage (RFC 8292 vapid-Schema, RFC 8188 Encoding). */
function ma_push_kopfzeilen(string $jwt, string $oeffentlich, int $laenge, int $ttl = MA_PUSH_TTL): array {
    return ['Authorization' => 'vapid t=' . $jwt . ', k=' . $oeffentlich, 'Content-Type' => 'application/octet-stream', 'Content-Encoding' => 'aes128gcm',
        'Content-Length' => (string) $laenge, 'TTL' => (string) $ttl, 'Urgency' => 'normal'];
}

/** Nachricht als JSON: Titel, kurzer Text (≤ 120 Zeichen), Ziel, Bild, Tag (ersetzt eine ältere Meldung mit gleichem Tag). */
function ma_push_nutzlast(string $titel, string $text, string $url, string $icon = '', string $tag = ''): string {
    $titel = html_entity_decode(trim((string) preg_replace('/\s+/u', ' ', wp_strip_all_tags_falls($titel))), ENT_QUOTES, 'UTF-8');
    $text = html_entity_decode(trim((string) preg_replace('/\s+/u', ' ', wp_strip_all_tags_falls($text))), ENT_QUOTES, 'UTF-8');
    if (mb_strlen($text) > 120) $text = rtrim(mb_substr($text, 0, 119)) . '…';
    return (string) json_encode(['title' => $titel, 'body' => $text, 'url' => $url, 'icon' => $icon, 'tag' => $tag !== '' ? $tag : 'merzenich-aktuell'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
function wp_strip_all_tags_falls(string $s): string { return function_exists('wp_strip_all_tags') ? wp_strip_all_tags($s) : strip_tags($s); }

/* ------------------------------------------------------------ Tabelle und Schlüssel */

const MA_PUSH_DB_VERSION = '1';
function ma_push_tabelle(): string { global $wpdb; return $wpdb->prefix . 'ma_push'; }

add_action('plugins_loaded', function (): void {
    if (get_option('ma_push_db') === MA_PUSH_DB_VERSION) return;
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta('CREATE TABLE ' . ma_push_tabelle() . " (
  kennung char(64) NOT NULL,
  endpoint text NOT NULL,
  p256dh varchar(120) NOT NULL,
  auth varchar(40) NOT NULL,
  erstellt datetime NOT NULL,
  zuletzt datetime NOT NULL,
  fehler tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY  (kennung)
) " . $wpdb->get_charset_collate() . ';');
    update_option('ma_push_db', MA_PUSH_DB_VERSION, false);
});

/** VAPID-Schlüsselpaar der Seite, einmalig erzeugt (Option ohne Autoload; der private Schlüssel verlässt den Server nie). */
function ma_push_schluessel(): ?array {
    $k = get_option('ma_push_vapid');
    if (is_array($k) && !empty($k['privat']) && !empty($k['oeffentlich'])) return $k;
    if (!ma_push_moeglich()) return null;
    $k = ma_push_schluessel_erzeugen();
    if (!$k) return null;
    $k['erzeugt'] = time();
    update_option('ma_push_vapid', $k, false);
    return $k;
}
function ma_push_oeffentlich(): string { return (string) (ma_push_schluessel()['oeffentlich'] ?? ''); }
function ma_push_abonnenten(): int { global $wpdb; return (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . ma_push_tabelle()); }

/* ------------------------------------------------------------ REST: abonnieren, abbestellen */

/** Abo aus PushSubscription.toJSON() prüfen: https-Endpoint, 65-Byte-Punkt, 16-Byte-Geheimnis. Reine Funktion. */
function ma_push_abo_pruefen(array $d): ?array {
    $endpoint = trim((string) ($d['endpoint'] ?? '')); $p = (string) ($d['keys']['p256dh'] ?? ''); $a = (string) ($d['keys']['auth'] ?? '');
    if (!preg_match('#^https://[^\s"<>]{10,1900}$#', $endpoint)) return null;
    if (strlen(ma_push_b64url_dec($p)) !== 65 || strlen(ma_push_b64url_dec($a)) !== 16) return null;
    return ['kennung' => hash('sha256', $endpoint), 'endpoint' => $endpoint, 'p256dh' => $p, 'auth' => $a];
}

function ma_push_rate_ok(string $kennung): bool {
    $k = 'ma_push_r_' . substr($kennung, 0, 24); $n = (int) get_transient($k);
    if ($n >= MA_PUSH_RATE) return false;
    set_transient($k, $n + 1, HOUR_IN_SECONDS);
    return true;
}

add_action('rest_api_init', function (): void {
    register_rest_route('ma/v1', '/push', [
        ['methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => 'ma_push_rest_abonnieren'],
        ['methods' => 'DELETE', 'permission_callback' => '__return_true', 'callback' => 'ma_push_rest_abbestellen'],
    ]);
});

function ma_push_rest_abonnieren(WP_REST_Request $r) {
    if (!ma_push_moeglich() || ma_push_oeffentlich() === '') return new WP_Error('ma_push_aus', 'Push nicht verfügbar', ['status' => 503]);
    $abo = ma_push_abo_pruefen((array) $r->get_json_params());
    if (!$abo) return new WP_Error('ma_push_ungueltig', 'Ungültiges Abo', ['status' => 400]);
    if (!ma_push_rate_ok($abo['kennung'])) return new WP_Error('ma_push_rate', 'Zu viele Anfragen', ['status' => 429]);
    global $wpdb; $jetzt = current_time('mysql');
    $wpdb->query($wpdb->prepare('INSERT INTO ' . ma_push_tabelle() . ' (kennung, endpoint, p256dh, auth, erstellt, zuletzt, fehler) VALUES (%s, %s, %s, %s, %s, %s, 0)
        ON DUPLICATE KEY UPDATE p256dh = VALUES(p256dh), auth = VALUES(auth), zuletzt = VALUES(zuletzt), fehler = 0', $abo['kennung'], $abo['endpoint'], $abo['p256dh'], $abo['auth'], $jetzt, $jetzt));
    return new WP_REST_Response(null, 204);
}

function ma_push_rest_abbestellen(WP_REST_Request $r) {
    $endpoint = trim((string) (((array) $r->get_json_params())['endpoint'] ?? ''));
    if ($endpoint === '') return new WP_Error('ma_push_ungueltig', 'Endpoint fehlt', ['status' => 400]);
    global $wpdb;
    $wpdb->delete(ma_push_tabelle(), ['kennung' => hash('sha256', $endpoint)]);
    return new WP_REST_Response(null, 204);
}

/* ------------------------------------------------------------ Versand bei Erstveröffentlichung */

/** Nachricht zu einer Meldung: Titel, Anriss, Link, Beitragsbild (nur mit Rechten, ma_feed_bild) oder Site-Icon. */
function ma_push_nachricht_fuer(WP_Post $p): string {
    $bild = function_exists('ma_feed_bild') ? ma_feed_bild($p) : [];
    $icon = (string) ($bild['url'] ?? '');
    if ($icon === '' && function_exists('get_site_icon_url')) $icon = (string) get_site_icon_url(512);
    $text = trim($p->post_excerpt) !== '' ? $p->post_excerpt : $p->post_content;
    return ma_push_nutzlast(html_entity_decode(get_the_title($p), ENT_QUOTES, 'UTF-8'), $text, (string) get_permalink($p), $icon, 'meldung-' . $p->ID);
}

/* Nur die erste Veröffentlichung einer Meldung löst den Versand aus; Änderungen danach nicht (kein doppelter Hinweis). */
add_action('transition_post_status', function (string $neu, string $alt, WP_Post $p): void {
    if ($p->post_type !== 'post' || $neu !== 'publish' || $alt === 'publish') return;
    if (!ma_push_moeglich() || get_post_meta($p->ID, 'ma_push_gesendet', true) !== '') return;
    update_post_meta($p->ID, 'ma_push_gesendet', current_time('mysql'));
    if (!wp_next_scheduled('ma_push_senden', [$p->ID])) wp_schedule_single_event(time() - 1, 'ma_push_senden', [$p->ID]);
    if (function_exists('spawn_cron')) spawn_cron();
}, 10, 3);
add_action('ma_push_senden', 'ma_push_senden');

/** Versand in Paketen zu 50: 404/410 löscht das Abo, ab 5 Fehlern ebenfalls; Ergebnis in ma_push_letzter. */
function ma_push_senden(int $postId): void {
    $p = get_post($postId);
    if (!$p instanceof WP_Post || $p->post_status !== 'publish') return;
    $k = ma_push_schluessel();
    if (!$k) return;
    global $wpdb; $t = ma_push_tabelle();
    $nachricht = ma_push_nachricht_fuer($p);
    $gesendet = $geloescht = $fehler = 0; $jwts = []; $zuletzt = '';
    if (function_exists('set_time_limit')) @set_time_limit(120);
    while (true) {
        $abos = $wpdb->get_results($wpdb->prepare("SELECT kennung, endpoint, p256dh, auth, fehler FROM $t WHERE kennung > %s ORDER BY kennung LIMIT %d", $zuletzt, MA_PUSH_PAKET), ARRAY_A);
        if (!$abos) break;
        foreach ($abos as $a) {
            $zuletzt = $a['kennung'];
            $aud = ma_push_audience($a['endpoint']);
            if (!isset($jwts[$aud])) $jwts[$aud] = ma_push_vapid_jwt($aud, MA_PUSH_KONTAKT, $k['privat'], time() + 43200);
            $koerper = $aud !== '' && $jwts[$aud] !== '' ? ma_push_verschluesseln($nachricht, $a['p256dh'], $a['auth']) : null;
            if ($koerper === null) { $wpdb->delete($t, ['kennung' => $a['kennung']]); $geloescht++; continue; }
            $antwort = wp_remote_post($a['endpoint'], ['timeout' => 10, 'headers' => ma_push_kopfzeilen($jwts[$aud], $k['oeffentlich'], strlen($koerper)), 'body' => $koerper]);
            $code = is_wp_error($antwort) ? 0 : (int) wp_remote_retrieve_response_code($antwort);
            if ($code >= 200 && $code < 300) {
                $gesendet++;
                if ((int) $a['fehler'] > 0) $wpdb->update($t, ['fehler' => 0], ['kennung' => $a['kennung']]);
            } elseif (in_array($code, [404, 410], true) || (int) $a['fehler'] + 1 >= MA_PUSH_MAX_FEHLER) {
                $wpdb->delete($t, ['kennung' => $a['kennung']]); $geloescht++;
            } else {
                $fehler++;
                $wpdb->query($wpdb->prepare("UPDATE $t SET fehler = fehler + 1 WHERE kennung = %s", $a['kennung']));
            }
        }
        if (count($abos) < MA_PUSH_PAKET) break;
    }
    update_option('ma_push_letzter', ['zeit' => time(), 'post' => $postId, 'titel' => get_the_title($p), 'gesendet' => $gesendet, 'geloescht' => $geloescht, 'fehler' => $fehler], false);
}

/* ------------------------------------------------------------ /sw.js und Skript */

add_action('init', function (): void {
    add_rewrite_rule('^sw\.js$', 'index.php?ma_api=sw', 'top');
}, 6);

/** Service Worker für WordPress: nur Push und Klick, kein Seiten-Cache (sonst veralten WordPress-Seiten). */
function ma_push_sw_js(): string {
    return "/* Merzenich Aktuell: Benachrichtigungen (Web Push). Kein Seiten-Cache. */\n"
        . "self.addEventListener('install', function () { self.skipWaiting(); });\n"
        . "self.addEventListener('activate', function (e) { e.waitUntil(self.clients.claim()); });\n"
        . "self.addEventListener('push', function (e) {\n"
        . "  var d = {}; try { d = e.data ? e.data.json() : {}; } catch (x) { d = { title: 'Merzenich Aktuell', body: e.data ? e.data.text() : '' }; }\n"
        . "  var o = { body: d.body || '', icon: d.icon || '/assets/img/avatar-1024.png', badge: '/assets/img/favicon.svg', tag: d.tag || 'merzenich-aktuell', data: { url: d.url || '/' }, renotify: false };\n"
        . "  e.waitUntil(self.registration.showNotification(d.title || 'Merzenich Aktuell', o));\n"
        . "});\n"
        . "self.addEventListener('notificationclick', function (e) {\n"
        . "  e.notification.close();\n"
        . "  var url = (e.notification.data && e.notification.data.url) || '/';\n"
        . "  e.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (liste) {\n"
        . "    for (var i = 0; i < liste.length; i++) { if ('focus' in liste[i]) { liste[i].navigate(url); return liste[i].focus(); } }\n"
        . "    if (self.clients.openWindow) return self.clients.openWindow(url);\n"
        . "  }));\n"
        . "});\n";
}

add_action('template_redirect', function (): void {
    if (get_query_var('ma_api') !== 'sw') return;
    nocache_headers();
    header('Content-Type: application/javascript; charset=utf-8');
    header('Service-Worker-Allowed: /');
    echo ma_push_sw_js(); exit;
}, 0);

/* Skript auf jeder öffentlichen Seite: reagiert auf die Einwilligung (einwilligung.js) und meldet das Abo an REST. */
add_action('wp_enqueue_scripts', function (): void {
    if (is_admin() || !ma_push_moeglich()) return;
    $key = ma_push_oeffentlich();
    if ($key === '') return;
    wp_register_script('ma-push', MA_CORE_URL . 'assets/push.js', [], MA_CORE_VERSION, true);
    wp_add_inline_script('ma-push', 'window.maPush=' . wp_json_encode(['key' => $key, 'api' => rest_url('ma/v1/push'), 'sw' => home_url('/sw.js')]) . ';', 'before');
    wp_enqueue_script('ma-push');
}, 20);
