<?php
/**
 * Werbung (Rotation, Kontingent), Unternehmen, sportfreie Startseite,
 * Partner-Benachrichtigung und Zugangsantrag ohne WordPress-Laufzeit pruefen.
 * Aufruf: php qa/wordpress/werbung-business-test.php
 *
 * Wie die anderen Tests hier: nachgebaute WordPress-Funktionen mit kleinem
 * Speicher fuer Beitraege, Metadaten, Benutzer und E-Mails. Geprueft wird die
 * Entscheidungslogik, nicht die Darstellung im Editor.
 */
define('ABSPATH', __DIR__);
define('MA_CORE_URL', '');
define('MA_CORE_VERSION', 'test');

class WP_User {
    public int $ID; public array $roles; public string $user_email; public string $display_name; public string $user_login;
    function __construct(int $id = 0, array $roles = [], string $email = '', string $name = '') {
        $this->ID = $id; $this->roles = $roles; $this->user_email = $email; $this->display_name = $name; $this->user_login = 'u'.$id;
    }
    function exists(): bool { return $this->ID > 0; }
}
class WP_Post {
    public $ID = 0; public $post_type = 'post'; public $post_author = 0; public $post_status = 'draft';
    public $post_title = ''; public $post_excerpt = ''; public $post_date = '2026-09-01 10:00:00';
    function __construct(array $a = []) { foreach ($a as $k => $v) $this->$k = $v; }
}
class WP_Term { public $term_id = 0; public $slug = ''; public $name = ''; public $parent = 0;
    function __construct(int $id, string $slug) { $this->term_id = $id; $this->slug = $slug; $this->name = ucfirst($slug); } }
class WP_Query {
    public $posts = []; public $found_posts = 0;
    function __construct($a = []) {
        $types = (array)($a['post_type'] ?? 'post');
        $status = (array)($a['post_status'] ?? 'publish');
        foreach ($GLOBALS['posts'] as $p) {
            if (!in_array($p->post_type, $types, true) || !in_array($p->post_status, $status, true)) continue;
            if (isset($a['author']) && (int)$p->post_author !== (int)$a['author']) continue;
            $this->posts[] = $p;
        }
        $this->found_posts = count($this->posts);
    }
}
class WP_Error { function __construct(...$a) {} }

$GLOBALS['posts'] = []; $GLOBALS['meta'] = []; $GLOBALS['umeta'] = []; $GLOBALS['opt'] = [];
$GLOBALS['users'] = []; $GLOBALS['mail'] = []; $GLOBALS['cats'] = []; $GLOBALS['tags'] = [];
$GLOBALS['user'] = new WP_User(0); $GLOBALS['caps'] = [];

function add_post(array $a, array $meta = []): WP_Post { $p = new WP_Post($a); $GLOBALS['posts'][$p->ID] = $p; foreach ($meta as $k => $v) $GLOBALS['meta'][$p->ID][$k] = $v; return $p; }
function add_user(WP_User $u): WP_User { $GLOBALS['users'][$u->ID] = $u; return $u; }
function login(WP_User $u, array $caps = []): void { $GLOBALS['user'] = $u; $GLOBALS['caps'] = $caps; }

function get_option($k, $d = false) { return $GLOBALS['opt'][$k] ?? $d; }
function get_post_meta($id, $k, $s = false) { return $GLOBALS['meta'][$id][$k] ?? ''; }
function update_post_meta($id, $k, $v) { $GLOBALS['meta'][$id][$k] = $v; return true; }
function delete_post_meta($id, $k) { unset($GLOBALS['meta'][$id][$k]); return true; }
function get_user_meta($id, $k, $s = false) { return $GLOBALS['umeta'][$id][$k] ?? ''; }
function update_user_meta($id, $k, $v) { $GLOBALS['umeta'][$id][$k] = $v; return true; }
function wp_get_current_user() { return $GLOBALS['user']; }
function get_current_user_id() { return $GLOBALS['user']->ID; }
function get_userdata($id) { return $GLOBALS['users'][$id] ?? false; }
function current_user_can($cap, ...$a) { return in_array($cap, $GLOBALS['caps'], true); }
function get_post($p) { return $p instanceof WP_Post ? $p : ($GLOBALS['posts'][(int)$p] ?? null); }
function get_post_status($id) { return $GLOBALS['posts'][$id]->post_status ?? false; }
function get_the_category($id) { return array_map(fn($s) => new WP_Term(crc32($s), $s), $GLOBALS['cats'][$id] ?? []); }
function get_the_tags($id) { $t = $GLOBALS['tags'][$id] ?? []; return $t ? array_map(fn($s) => new WP_Term(crc32($s), $s), $t) : false; }
function get_term($id, $tax) { return null; }
function get_the_title($p) { $p = get_post($p); return $p ? $p->post_title : ''; }
function get_permalink($p) { $p = get_post($p); return 'https://merzenich.invalid/?p='.($p ? $p->ID : 0); }
function get_the_post_thumbnail_url($p, $s = '') { return ''; }
function current_time($f) { return $f === 'Y-m-d H:i:s' ? '2026-09-26 12:00:00' : '26.09.2026 12:00'; }
function mysql2date($f, $d) { return date($f, strtotime($d)); }
function wp_mail($to, $subject, $body, $headers = [], $att = []) { $GLOBALS['mail'][] = compact('to', 'subject', 'body', 'headers'); return true; }
function admin_url($p = '') { return 'https://merzenich.invalid/wp-admin/'.$p; }
function wp_verify_nonce($n, $a) { return $n === 'ok'; }
function wp_is_post_revision($id) { return false; }
function wp_enqueue_script(...$a) { $GLOBALS['enqueued'][] = $a[0]; }
function shortcode_atts($d, $a, $s = '') { return array_merge($d, (array)$a); }
function sanitize_email($v) { return trim((string)$v); }
function is_email($v) { return filter_var($v, FILTER_VALIDATE_EMAIL) !== false; }
function sanitize_text_field($s) { return trim(strip_tags((string)$s)); }
function sanitize_textarea_field($s) { return trim(strip_tags((string)$s)); }
function sanitize_key($s) { return strtolower(preg_replace('/[^a-z0-9_\-]/i', '', (string)$s)); }
function wp_unslash($s) { return $s; }
function esc_url_raw($s) { return (string)$s; }
function esc_url($s, $p = null) { return htmlspecialchars((string)$s, ENT_QUOTES); }
function esc_html($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
function esc_attr($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }

$plugin = __DIR__.'/../../wordpress/plugin/merzenich-aktuell-core/includes/';
foreach (['partners.php','ads.php','ad-quota.php','business.php','partner-notify.php','startseite.php','partner-antrag.php'] as $datei) require $plugin.$datei;

$fehler = 0;
function pruefe($name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-70s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: '.var_export($ist, true).')'); }
function ids(array $posts): array { return array_map(fn($p) => (int)$p->ID, $posts); }

// ------------------------------------------------------------------ Rotation
echo "Werbung: mehrere Anzeigen je Platz\n";
$GLOBALS['opt'] = ['ma_ads_enabled' => 1, 'ma_ad_slots' => ['homepage_band_1' => 1, 'homepage_band_2' => 1, 'homepage_band_6' => 1]];
$aktiv = ['ma_ad_slot' => 'homepage_band_1', 'ma_ad_active' => '1'];
add_post(['ID' => 101, 'post_type' => 'ma_ad', 'post_status' => 'publish', 'post_title' => 'Anzeige A', 'post_date' => '2026-09-10 10:00:00'], $aktiv + ['ma_ad_priority' => '5', 'ma_ad_url' => 'https://a.invalid/']);
add_post(['ID' => 102, 'post_type' => 'ma_ad', 'post_status' => 'publish', 'post_title' => 'Anzeige B', 'post_date' => '2026-09-01 10:00:00'], $aktiv + ['ma_ad_priority' => '10']);
add_post(['ID' => 103, 'post_type' => 'ma_ad', 'post_status' => 'publish', 'post_title' => 'Anzeige C ohne Prioritaet', 'post_date' => '2026-09-20 10:00:00'], $aktiv);
add_post(['ID' => 104, 'post_type' => 'ma_ad', 'post_status' => 'publish', 'post_title' => 'pausiert'], ['ma_ad_slot' => 'homepage_band_1', 'ma_ad_active' => '0']);
add_post(['ID' => 105, 'post_type' => 'ma_ad', 'post_status' => 'publish', 'post_title' => 'abgelaufen'], $aktiv + ['ma_ad_end' => '2026-09-20 00:00:00']);
add_post(['ID' => 106, 'post_type' => 'ma_ad', 'post_status' => 'publish', 'post_title' => 'startet spaeter'], $aktiv + ['ma_ad_start' => '2026-10-01 00:00:00']);
add_post(['ID' => 107, 'post_type' => 'ma_ad', 'post_status' => 'pending', 'post_title' => 'nicht freigegeben'], $aktiv);
add_post(['ID' => 108, 'post_type' => 'ma_ad', 'post_status' => 'publish', 'post_title' => 'anderer Platz'], ['ma_ad_slot' => 'homepage_band_2', 'ma_ad_active' => '1']);

pruefe('ma_active_ads liefert alle drei laufenden Anzeigen', count(ma_active_ads('homepage_band_1')), 3);
pruefe('Reihenfolge: Prioritaet, dann neueste', ids(ma_active_ads('homepage_band_1')), [102, 101, 103]);
pruefe('Limit wird beachtet', ids(ma_active_ads('homepage_band_1', 2)), [102, 101]);
pruefe('ma_active_ad bleibt kompatibel (erste Anzeige)', ma_active_ad('homepage_band_1')->ID ?? null, 102);
pruefe('Anderer Platz getrennt', ids(ma_active_ads('homepage_band_2')), [108]);
pruefe('Werbeband 6 existiert als Platz', in_array('homepage_band_6', ma_ad_slots(), true), true);
pruefe('Sechs Werbebaender', count(ma_ad_band_slots()), 6);
$html = ma_render_ads('homepage_band_1');
pruefe('Ausgabe enthaelt alle drei Anzeigen', substr_count($html, 'class="ma-ad__item"'), 3);
pruefe('Nur die erste sichtbar, zwei warten', substr_count($html, ' hidden>'), 2);
pruefe('Gekennzeichnet als Anzeige', str_contains($html, '>Anzeige</small>'), true);
pruefe('Rotation per data-Attribut', str_contains($html, 'data-ma-ad-rotate="8000"'), true);
pruefe('Link mit rel="sponsored noopener"', str_contains($html, 'rel="sponsored noopener"'), true);
pruefe('Rotationsskript eingereiht', in_array('ma-ad-rotation', $GLOBALS['enqueued'] ?? [], true), true);
pruefe('Rotation deterministisch je Startwert', ids(ma_ads_rotate(ma_active_ads('homepage_band_1'), 'homepage_band_1', 7)), ids(ma_ads_rotate(ma_active_ads('homepage_band_1'), 'homepage_band_1', 7)));
pruefe('Rotation verliert keine Anzeige', count(array_unique(ids(ma_ads_rotate(ma_active_ads('homepage_band_1'), 'homepage_band_1', 3)))), 3);
pruefe('Einzelne Anzeige ohne Rotation', str_contains(ma_render_ads('homepage_band_2'), 'data-ma-ad-rotate'), false);
ma_active_ads_reset();
$GLOBALS['opt']['ma_ad_slots']['homepage_band_1'] = 0;
pruefe('Abgeschalteter Platz: keine Anzeige', ma_active_ads('homepage_band_1'), []);
pruefe('Abgeschalteter Platz: ma_active_ad null', ma_active_ad('homepage_band_1'), null);
pruefe('Abgeschalteter Platz: keine Ausgabe', ma_render_ads('homepage_band_1'), '');
$GLOBALS['opt']['ma_ads_enabled'] = 0; ma_active_ads_reset();
pruefe('Werbung global aus: nichts', ma_active_ads('homepage_band_2'), []);
$GLOBALS['opt']['ma_ads_enabled'] = 1; $GLOBALS['opt']['ma_ad_slots']['homepage_band_1'] = 1; ma_active_ads_reset();

// ---------------------------------------------------------------- Kontingent
echo "\nWerbekontingent der Unternehmen\n";
$firma = add_user(new WP_User(20, ['ma_wirtschaft_partner'], 'firma@example.de', 'Firma'));
pruefe('Voreinstellung: 1 Anzeige', ma_ad_quota_for_user(20)['max'], 1);
pruefe('Voreinstellung: alle Werbebaender', ma_ad_quota_for_user(20)['slots'], ma_ad_band_slots());
add_post(['ID' => 201, 'post_type' => 'ma_ad', 'post_status' => 'publish', 'post_author' => 20, 'post_title' => 'laufend'], ['ma_ad_slot' => 'homepage_band_2', 'ma_ad_active' => '1']);
add_post(['ID' => 202, 'post_type' => 'ma_ad', 'post_status' => 'publish', 'post_author' => 20, 'post_title' => 'abgelaufen'], ['ma_ad_slot' => 'homepage_band_2', 'ma_ad_active' => '1', 'ma_ad_end' => '2026-08-01 00:00:00']);
add_post(['ID' => 203, 'post_type' => 'ma_ad', 'post_status' => 'draft', 'post_author' => 20, 'post_title' => 'neu']);
login($firma, ['edit_posts']);
pruefe('Belegt: nur die laufende zaehlt', ma_ad_quota_used(20, 203), 1);

$_POST = ['ma_content_admin_nonce' => 'ok', 'ma_ad_slot' => 'homepage_band_3'];
$d = ma_partner_force_pending(['post_type' => 'ma_ad', 'post_status' => 'publish'], ['ID' => 203]);
pruefe('Partner-Einreichung wird zunaechst ausstehend', $d['post_status'], 'pending');
$d = ma_ad_quota_gate($d, ['ID' => 203]);
pruefe('Ueber Kontingent: bleibt Entwurf', $d['post_status'], 'draft');
pruefe('Grund fuer den Hinweis vermerkt', $GLOBALS['meta'][203][MA_AD_QUOTA_REASON_META] ?? '', 'quota:1/1');
pruefe('Hinweis erklaert das Kontingent', str_contains(ma_ad_quota_reason_text('quota:1/1', 20), 'Kontingent erlaubt 1'), true);

update_user_meta(20, MA_AD_QUOTA_MAX_META, '2');
$d = ma_ad_quota_gate(['post_type' => 'ma_ad', 'post_status' => 'pending'], ['ID' => 203]);
pruefe('Mit Kontingent 2: zur Freigabe', $d['post_status'], 'pending');
pruefe('Hinweis verschwindet', isset($GLOBALS['meta'][203][MA_AD_QUOTA_REASON_META]), false);

$_POST = ['ma_content_admin_nonce' => 'ok', 'ma_ad_slot' => 'header_billboard'];
$d = ma_ad_quota_gate(['post_type' => 'ma_ad', 'post_status' => 'pending'], ['ID' => 203]);
pruefe('Nicht freigeschalteter Platz: Entwurf', $d['post_status'], 'draft');
pruefe('Grund: Platz', $GLOBALS['meta'][203][MA_AD_QUOTA_REASON_META] ?? '', 'slot:header_billboard');
update_user_meta(20, MA_AD_QUOTA_SLOTS_META, 'homepage_band_1,header_billboard');
$d = ma_ad_quota_gate(['post_type' => 'ma_ad', 'post_status' => 'pending'], ['ID' => 203]);
pruefe('Freigeschalteter Sonderplatz: zur Freigabe', $d['post_status'], 'pending');
update_user_meta(20, MA_AD_QUOTA_SLOTS_META, '-');
pruefe('Bewusst kein Platz bleibt leer', ma_ad_quota_for_user(20)['slots'], []);
$_POST = [];

login(add_user(new WP_User(1, ['administrator'], 'redaktion@example.de', 'Redaktion')), ['manage_options', 'edit_others_posts', 'edit_post', 'edit_user']);
$d = ma_ad_quota_gate(['post_type' => 'ma_ad', 'post_status' => 'publish'], ['ID' => 203]);
pruefe('Redaktion ist nicht an Kontingente gebunden', $d['post_status'], 'publish');

// ---------------------------------------------------------------- Unternehmen
echo "\nUnternehmen: Einwilligung\n";
add_post(['ID' => 301, 'post_type' => 'ma_business', 'post_status' => 'draft', 'post_title' => 'Betrieb ohne Einwilligung']);
$d = ma_business_publication_gate(['post_type' => 'ma_business', 'post_status' => 'publish'], ['ID' => 301]);
pruefe('Ohne Einwilligung nicht veroeffentlicht', $d['post_status'] !== 'publish', true);
pruefe('Ohne Einwilligung: Entwurf', $d['post_status'], 'draft');
pruefe('Hinweis vermerkt', $GLOBALS['meta'][301][MA_BUSINESS_CONSENT_MISSING_META] ?? '', '1');
$GLOBALS['posts'][301]->post_status = 'pending';
$d = ma_business_publication_gate(['post_type' => 'ma_business', 'post_status' => 'publish'], ['ID' => 301]);
pruefe('Eingereicht ohne Einwilligung bleibt ausstehend', $d['post_status'], 'pending');
$_POST = ['ma_business_nonce' => 'ok', MA_BUSINESS_CONSENT_META => '1'];
$d = ma_business_publication_gate(['post_type' => 'ma_business', 'post_status' => 'publish'], ['ID' => 301]);
pruefe('Mit Einwilligung veroeffentlicht', $d['post_status'], 'publish');
pruefe('Hinweis verschwindet', isset($GLOBALS['meta'][301][MA_BUSINESS_CONSENT_MISSING_META]), false);
$_POST = ['ma_business_nonce' => 'ok'];
$GLOBALS['meta'][301][MA_BUSINESS_CONSENT_META] = '1';
$d = ma_business_publication_gate(['post_type' => 'ma_business', 'post_status' => 'publish'], ['ID' => 301]);
pruefe('Abgewaehlter Haken zaehlt, nicht der alte Stand', $d['post_status'] !== 'publish', true);
$_POST = [];
pruefe('Ohne Formular gilt der gespeicherte Stand', ma_business_publication_gate(['post_type' => 'ma_business', 'post_status' => 'publish'], ['ID' => 301])['post_status'], 'publish');
pruefe('Telefon wird bereinigt', ma_business_sanitize('tel', '02421 / 12<script>'), '02421 / 12');
pruefe('Unbekannter Ortsteil wird verworfen', ma_business_sanitize('ortsteil', 'koeln'), '');
pruefe('Ortsteil Buergewald', ma_business_sanitize('ortsteil', 'buergewald'), 'buergewald');

// ------------------------------------------------------------- Startseite
echo "\nStartseite ohne Sport\n";
$fanclub = add_post(['ID' => 401, 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Fanclub fährt zum Auswärtsspiel']);
$GLOBALS['cats'][401] = ['vereine'];
$blaulicht = add_post(['ID' => 402, 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Einbruch in Vereinsheim am Sportplatz']);
$GLOBALS['cats'][402] = ['blaulicht'];
$sport = add_post(['ID' => 403, 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Saisonbilanz']);
$GLOBALS['cats'][403] = ['sport'];
$markt = add_post(['ID' => 404, 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Martinsmarkt in Golzheim']);
$GLOBALS['cats'][404] = ['vereine'];
$tag = add_post(['ID' => 405, 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Ehrung im Bürgerhaus']);
$GLOBALS['cats'][405] = ['leben']; $GLOBALS['tags'][405] = ['sc-merzenich'];
$transport = add_post(['ID' => 406, 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Transport &amp; Verkehr: neue Buslinie']);
$GLOBALS['cats'][406] = ['nachrichten'];
pruefe('Vereine-Beitrag mit „Fanclub“ ist Sport', ma_is_sport_post($fanclub), true);
pruefe('Blaulicht-Beitrag ist nie Sport (trotz „Sportplatz“)', ma_is_sport_post($blaulicht), false);
pruefe('Rubrik Sport ist immer Sport', ma_is_sport_post($sport), true);
pruefe('Schlagwort „SC Merzenich“ zaehlt', ma_is_sport_post($tag), true);
pruefe('„Transport“ ist kein Sport (Wortgrenze)', ma_is_sport_post($transport), false);
pruefe('Startseite: Fanclub raus, Blaulicht bleibt', ids(ma_home_without_sport([$fanclub, $blaulicht, $sport, $markt, $tag, $transport])), [402, 404, 406]);

// -------------------------------------------------------- Benachrichtigung
echo "\nPartner-Benachrichtigung\n";
$verein = add_user(new WP_User(30, ['ma_vereine_partner'], 'verein@example.de', 'Musikverein'));
$beitrag = add_post(['ID' => 501, 'post_type' => 'post', 'post_status' => 'publish', 'post_author' => 30, 'post_title' => 'Konzert im Pfarrheim'], ['_ma_partner_submission' => '1']);
$GLOBALS['mail'] = [];
ma_partner_decision_mail('publish', 'pending', $beitrag);
pruefe('Freigabe: eine Mail', count($GLOBALS['mail']), 1);
pruefe('Mail geht an den Partner', $GLOBALS['mail'][0]['to'] ?? '', 'verein@example.de');
pruefe('Betreff: Ihr Beitrag ist freigegeben', str_contains($GLOBALS['mail'][0]['subject'] ?? '', 'Ihr Beitrag ist freigegeben'), true);
pruefe('Mail enthaelt den Link', str_contains($GLOBALS['mail'][0]['body'] ?? '', 'https://merzenich.invalid/?p=501'), true);
ma_partner_decision_mail('publish', 'publish', $beitrag);
pruefe('Kein zweites Mal ohne Statuswechsel', count($GLOBALS['mail']), 1);
ma_partner_decision_mail('publish', 'draft', $beitrag);
pruefe('Nur aus „ausstehend“ heraus', count($GLOBALS['mail']), 1);

$GLOBALS['mail'] = [];
update_post_meta(501, MA_REJECT_REASON_META, 'Bitte Quelle und Datum ergänzen.');
ma_partner_decision_mail(MA_REJECTED_STATUS, 'pending', $beitrag);
pruefe('Ablehnung: Mail mit Begruendung', str_contains($GLOBALS['mail'][0]['body'] ?? '', 'Bitte Quelle und Datum ergänzen.'), true);
pruefe('Ablehnung: Betreff', str_contains($GLOBALS['mail'][0]['subject'] ?? '', 'nicht freigegeben'), true);
ma_partner_decision_mail('trash', 'pending', $beitrag);
pruefe('Papierkorb: ebenfalls Mail', count($GLOBALS['mail']), 2);

$_POST = ['ma_partner_reject_nonce' => 'ok', 'ma_reject' => '1', 'ma_reject_reason' => 'Doppelt eingereicht.'];
$d = ma_partner_reject_apply(['post_type' => 'post', 'post_status' => 'pending'], ['ID' => 501]);
pruefe('Kasten „Ablehnen“ setzt Status abgelehnt', $d['post_status'], MA_REJECTED_STATUS);
pruefe('Begruendung gespeichert', $GLOBALS['meta'][501][MA_REJECT_REASON_META] ?? '', 'Doppelt eingereicht.');
$_POST = [];

$GLOBALS['mail'] = [];
login($verein, ['edit_posts']);
ma_partner_decision_mail('publish', 'pending', $beitrag);
pruefe('Handelt der Partner selbst: keine Mail', count($GLOBALS['mail']), 0);
$redaktion = add_post(['ID' => 502, 'post_type' => 'post', 'post_status' => 'publish', 'post_author' => 1, 'post_title' => 'Redaktionsbeitrag']);
login($GLOBALS['users'][1], ['manage_options', 'edit_others_posts']);
ma_partner_decision_mail('publish', 'pending', $redaktion);
pruefe('Redaktionsbeitrag: keine Partner-Mail', count($GLOBALS['mail']), 0);

// ------------------------------------------------------------ Zugangsantrag
echo "\nAntrag auf Partner-Zugang\n";
$GLOBALS['opt']['admin_email'] = 'admin@example.de';
$GLOBALS['mail'] = [];
$antrag = ['ma_partner_antrag_nonce' => 'ok', 'started' => time() - 30, 'organisation' => 'Löschgruppe', 'typ' => 'feuerwehr', 'name' => 'Ansprechperson', 'email' => 'lg@example.de', 'telefon' => '0000', 'nachricht' => 'Bitte Zugang.', 'einwilligung' => '1'];
$r = ma_partner_antrag_process($antrag);
pruefe('Gueltiger Antrag angenommen', $r['ok'], true);
pruefe('Mail an die Administration', $GLOBALS['mail'][0]['to'] ?? '', 'admin@example.de');
pruefe('Mail nennt Art und Rolle', str_contains($GLOBALS['mail'][0]['body'] ?? '', 'Art: Feuerwehr') && str_contains($GLOBALS['mail'][0]['body'] ?? '', 'Feuerwehr-Partner'), true);
pruefe('Kein Konto angelegt (Hinweis in der Mail)', str_contains($GLOBALS['mail'][0]['body'] ?? '', 'kein Konto angelegt'), true);
$r = ma_partner_antrag_process(['einwilligung' => ''] + $antrag);
pruefe('Ohne Einwilligung abgelehnt', $r['fehler'], 'einwilligung');
$r = ma_partner_antrag_process(['typ' => 'partei'] + $antrag);
pruefe('Unbekannte Art abgelehnt', $r['fehler'], 'felder');
$r = ma_partner_antrag_process(['website' => 'http://spam.invalid'] + $antrag);
pruefe('Honeypot: keine Mail', count($GLOBALS['mail']), 1);
$r = ma_partner_antrag_process(['ma_partner_antrag_nonce' => 'falsch'] + $antrag);
pruefe('Falsche Nonce abgelehnt', $r['fehler'], 'sitzung');

echo "\n".($fehler === 0 ? "Alle Pruefungen bestanden.\n" : "$fehler Pruefung(en) fehlgeschlagen.\n");
exit($fehler === 0 ? 0 : 1);
