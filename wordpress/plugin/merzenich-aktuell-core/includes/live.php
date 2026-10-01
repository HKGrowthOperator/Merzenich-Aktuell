<?php
/**
 * Live-Tracker im Backend (01.10.2026): Wer ist gerade auf der Seite, wie
 * viele waren es heute und an den letzten 30 Tagen. Dashboard-Widget direkt
 * nach dem Anmelden und ein Live-Bereich oben auf Merzenich Aktuell → Statistik,
 * beide aktualisieren sich alle 10 Sekunden.
 *
 * Datenschutz, bewusst so gebaut:
 * - Kein Cookie, nichts im Browser gespeichert, kein Dienst eines Dritten.
 * - Ein Besucher wird nur innerhalb eines Tages wiedererkannt: Kennung =
 *   Hash aus IP-Adresse, Browserkennung und einem Zufallswert, der jeden Tag neu
 *   entsteht und den alten ersetzt. Die IP-Adresse selbst wird nie gespeichert;
 *   nach dem Tageswechsel lässt sich niemand mehr zuordnen.
 * - Gespeichert je Besuch: Kennung, erste und letzte Aktivität, Zahl der
 *   Seiten, Gerätetyp (Handy/Tablet/Desktop), Herkunft als Name (Google,
 *   Facebook, direkt …), zuletzt gelesene Seite. Nach 40 Tagen gelöscht.
 * - „Wer“ heißt deshalb: welche Seite, welches Gerät, woher, seit wann. Namen
 *   gibt es nur für angemeldete Redakteure und Partner („Team online“).
 * - Bots und angemeldete Redakteure zählen nicht als Besucher.
 *
 * Erfassung: Seitenaufruf (/ma/v1/aufruf, statistik.php) und ein Puls alle
 * 30 Sekunden, solange der Tab sichtbar ist (/ma/v1/puls). Als Besucher zählt
 * nur, wer mindestens eine Seite aufgerufen hat; der Puls hält einen Besuch
 * nur aktiv. „Gerade online“ = letzte Aktivität in den letzten 2 Minuten.
 */
if (!defined('ABSPATH')) { exit; }

const MA_LIVE_DB_VERSION = '1';
const MA_LIVE_FENSTER = 120; // Sekunden ohne Puls, bis jemand nicht mehr „gerade online“ ist
const MA_LIVE_AUFBEWAHREN_TAGE = 40;

function ma_live_tabelle(): string { global $wpdb; return $wpdb->prefix . 'ma_besuch'; }

add_action('plugins_loaded', function (): void {
    if (get_option('ma_live_db') === MA_LIVE_DB_VERSION) return;
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta('CREATE TABLE ' . ma_live_tabelle() . " (
  tag date NOT NULL,
  kennung char(16) NOT NULL,
  erst datetime NOT NULL,
  zuletzt datetime NOT NULL,
  seiten int(10) unsigned NOT NULL DEFAULT 0,
  geraet varchar(10) NOT NULL DEFAULT '',
  quelle varchar(40) NOT NULL DEFAULT '',
  pfad varchar(190) NOT NULL DEFAULT '',
  titel varchar(190) NOT NULL DEFAULT '',
  PRIMARY KEY  (tag,kennung),
  KEY zuletzt (zuletzt)
) " . $wpdb->get_charset_collate() . ';');
    update_option('ma_live_db', MA_LIVE_DB_VERSION, false);
});

/** Tageszufallswert: entsteht am ersten Aufruf des Tages neu, der alte ist weg. */
function ma_live_salz(): string {
    $tag = current_time('Y-m-d');
    $s = get_option('ma_live_salz');
    if (!is_array($s) || ($s['tag'] ?? '') !== $tag) {
        $s = ['tag' => $tag, 'salz' => bin2hex(random_bytes(16))];
        update_option('ma_live_salz', $s, false);
    }
    return (string) $s['salz'];
}

function ma_live_ip(): string {
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    // Hinter einem Proxy des Hosters steht die echte Adresse vorne in X-Forwarded-For.
    if ($ip !== '' && !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $erste = trim(explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        if (filter_var($erste, FILTER_VALIDATE_IP)) $ip = $erste;
    }
    return $ip;
}

function ma_live_kennung(string $ua): string {
    return substr(hash('sha256', ma_live_salz() . '|' . ma_live_ip() . '|' . $ua), 0, 16);
}

function ma_live_geraet(string $ua): string {
    if (preg_match('/iPad|Tablet|Android(?!.*Mobile)/i', $ua)) return 'Tablet';
    if (preg_match('/Mobile|iPhone|Android|Windows Phone/i', $ua)) return 'Handy';
    return 'Desktop';
}

/** Herkunft als Name, nur aus dem Hostnamen des Verweises. */
function ma_live_quelle(string $host): string {
    $host = strtolower(preg_replace('/^www\./', '', $host));
    if ($host === '') return 'direkt';
    $eigen = strtolower(preg_replace('/^www\./', '', (string) wp_parse_url(home_url(), PHP_URL_HOST)));
    if ($host === $eigen || $host === 'merzenichaktuell.hk-growthoperator.de') return 'intern';
    foreach (['google' => 'Google', 'bing' => 'Bing', 'duckduckgo' => 'DuckDuckGo', 'ecosia' => 'Ecosia', 'facebook' => 'Facebook', 'fb.' => 'Facebook', 'instagram' => 'Instagram',
        'whatsapp' => 'WhatsApp', 'x.com' => 'X', 't.co' => 'X', 'twitter' => 'X', 'linkedin' => 'LinkedIn', 'yahoo' => 'Yahoo', 'gemeinde-merzenich' => 'Gemeinde Merzenich', 'heimat-info' => 'Heimat-Info'] as $teil => $name) {
        if (str_contains($host, $teil)) return $name;
    }
    return substr($host, 0, 40);
}

/**
 * Besuch erfassen. $seite = 1 bei einem Seitenaufruf, 0 beim Puls.
 * Herkunft zählt nur beim ersten Aufruf des Tages (spätere sind „intern“).
 */
function ma_live_erfassen(WP_REST_Request $r, array $d, int $seite): void {
    global $wpdb;
    $ua = (string) $r->get_header('user_agent');
    $jetzt = current_time('mysql', true);
    $pfad = substr(sanitize_text_field((string) ($d['pfad'] ?? '')), 0, 190);
    if ($pfad !== '' && $pfad[0] !== '/') $pfad = '';
    $titel = substr(sanitize_text_field((string) ($d['titel'] ?? '')), 0, 190);
    $kennung = ma_live_kennung($ua);
    if ($seite === 0) {
        // Puls hält nur einen bestehenden Besuch aktiv und legt keinen neuen an
        // (sonst würde ein Gerät mit wechselnder IP-Adresse doppelt gezählt).
        $wpdb->query($wpdb->prepare('UPDATE ' . ma_live_tabelle() . " SET zuletzt = %s, pfad = IF(%s = '', pfad, %s) WHERE tag = %s AND kennung = %s", $jetzt, $pfad, $pfad, current_time('Y-m-d'), $kennung));
        return;
    }
    $quelle = ma_live_quelle(sanitize_text_field((string) ($d['ref'] ?? '')));
    $wpdb->query($wpdb->prepare('INSERT INTO ' . ma_live_tabelle() . ' (tag, kennung, erst, zuletzt, seiten, geraet, quelle, pfad, titel) VALUES (%s, %s, %s, %s, %d, %s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE zuletzt = VALUES(zuletzt), seiten = seiten + VALUES(seiten), pfad = IF(VALUES(pfad) = \'\', pfad, VALUES(pfad)), titel = IF(VALUES(titel) = \'\', titel, VALUES(titel))',
        current_time('Y-m-d'), $kennung, $jetzt, $jetzt, $seite, ma_live_geraet($ua), $quelle === 'intern' ? 'direkt' : $quelle, $pfad, $titel));
}

add_action('rest_api_init', function (): void {
    register_rest_route('ma/v1', '/puls', [
        'methods' => 'POST', 'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $r) {
            if (function_exists('ma_statistik_zaehlt') && !ma_statistik_zaehlt((string) $r->get_header('user_agent'))) return new WP_REST_Response(null, 204);
            ma_live_erfassen($r, json_decode((string) $r->get_body(), true) ?: [], 0);
            return new WP_REST_Response(null, 204);
        },
    ]);
    register_rest_route('ma/v1', '/live', [
        'methods' => 'GET',
        'permission_callback' => fn() => current_user_can('edit_others_posts') && !(function_exists('ma_current_partner_policy') && ma_current_partner_policy()),
        'callback' => fn() => new WP_REST_Response(ma_live_daten(), 200, ['Cache-Control' => 'no-store']),
    ]);
});

/* ---------------------------------------------------------- Team online (angemeldete Benutzer) */
add_action('init', function (): void {
    if (!is_user_logged_in()) return;
    $id = get_current_user_id();
    $zuletzt = (int) get_user_meta($id, 'ma_zuletzt_aktiv', true);
    if (time() - $zuletzt >= 60) update_user_meta($id, 'ma_zuletzt_aktiv', time());
});

/* ---------------------------------------------------------- Aufräumen */
add_action('init', function (): void {
    if (!wp_next_scheduled('ma_live_aufraeumen')) wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'ma_live_aufraeumen');
});
add_action('ma_live_aufraeumen', function (): void {
    global $wpdb;
    $wpdb->query($wpdb->prepare('DELETE FROM ' . ma_live_tabelle() . ' WHERE tag < %s', gmdate('Y-m-d', strtotime(current_time('Y-m-d') . ' -' . MA_LIVE_AUFBEWAHREN_TAGE . ' days'))));
});

/* ---------------------------------------------------------- Auswertung */
function ma_live_daten(): array {
    global $wpdb;
    $t = ma_live_tabelle();
    $heute = current_time('Y-m-d');
    $grenze = gmdate('Y-m-d H:i:s', time() - MA_LIVE_FENSTER);
    $jetztUtc = time();
    $aktiv = $wpdb->get_results($wpdb->prepare("SELECT kennung, erst, zuletzt, seiten, geraet, quelle, pfad, titel FROM {$t} WHERE zuletzt >= %s AND seiten > 0 ORDER BY zuletzt DESC LIMIT 60", $grenze));
    $besucher = [];
    $seitenJetzt = [];
    foreach ($aktiv as $i => $b) {
        $titel = $b->titel !== '' ? preg_replace('/\s*[|·–-]\s*(?:Merzenich Aktuell|' . preg_quote(get_bloginfo('name'), '/') . ')\s*$/u', '', $b->titel) : $b->pfad;
        if ($b->pfad === '/') $titel = 'Startseite';
        $besucher[] = ['nr' => $i + 1, 'pfad' => $b->pfad, 'titel' => $titel, 'geraet' => $b->geraet, 'quelle' => $b->quelle,
            'seit_min' => max(0, (int) floor(($jetztUtc - strtotime($b->erst . ' UTC')) / 60)), 'seiten' => (int) $b->seiten];
        $k = $b->pfad ?: '/';
        if (!isset($seitenJetzt[$k])) $seitenJetzt[$k] = ['pfad' => $k, 'titel' => $titel, 'n' => 0];
        $seitenJetzt[$k]['n']++;
    }
    usort($seitenJetzt, fn($a, $b) => $b['n'] <=> $a['n']);
    // Heute je Stunde (Berliner Zeit): Besucher, die in dieser Stunde aktiv wurden.
    $stunden = array_fill(0, 24, 0);
    $quellen = []; $geraete = ['Handy' => 0, 'Tablet' => 0, 'Desktop' => 0];
    $heuteRows = $wpdb->get_results($wpdb->prepare("SELECT erst, quelle, geraet FROM {$t} WHERE tag = %s AND seiten > 0", $heute));
    foreach ($heuteRows as $z) {
        $stunden[(int) wp_date('G', strtotime($z->erst . ' UTC'))]++;
        $quellen[$z->quelle ?: 'direkt'] = ($quellen[$z->quelle ?: 'direkt'] ?? 0) + 1;
        if (isset($geraete[$z->geraet])) $geraete[$z->geraet]++;
    }
    arsort($quellen);
    // 30 Tage: eindeutige Besucher je Tag.
    $ab = gmdate('Y-m-d', strtotime($heute . ' -29 days'));
    $jeTag = $wpdb->get_results($wpdb->prepare("SELECT tag, COUNT(*) n, SUM(seiten) s FROM {$t} WHERE tag >= %s AND seiten > 0 GROUP BY tag", $ab), OBJECT_K);
    $tage = [];
    for ($i = 0; $i < 30; $i++) { $d = gmdate('Y-m-d', strtotime($ab . ' +' . $i . ' days')); $tage[] = ['tag' => $d, 'besucher' => (int) ($jeTag[$d]->n ?? 0)]; }
    $gestern = gmdate('Y-m-d', strtotime($heute . ' -1 day'));
    // Team online: angemeldet und in den letzten 5 Minuten aktiv.
    $team = [];
    foreach (get_users(['meta_key' => 'ma_zuletzt_aktiv', 'meta_value' => time() - 300, 'meta_compare' => '>=', 'meta_type' => 'NUMERIC', 'number' => 20]) as $u) {
        $rolle = function_exists('ma_current_partner_policy') && ma_current_partner_policy($u) ? ma_current_partner_policy($u)['label'] : (in_array('administrator', (array) $u->roles, true) ? 'Administration' : 'Redaktion');
        $team[] = ['name' => $u->display_name ?: $u->user_login, 'rolle' => $rolle, 'vor_min' => (int) floor((time() - (int) get_user_meta($u->ID, 'ma_zuletzt_aktiv', true)) / 60)];
    }
    $aufrufeHeute = function_exists('ma_statistik_summe') ? ma_statistik_summe(1) : 0;
    return [
        'stand' => wp_date('H:i:s'),
        'jetzt' => count($besucher),
        'besucher' => array_slice($besucher, 0, 30),
        'seiten_jetzt' => array_slice($seitenJetzt, 0, 8),
        'heute' => ['besucher' => count($heuteRows), 'aufrufe' => $aufrufeHeute, 'gestern_besucher' => (int) ($jeTag[$gestern]->n ?? 0)],
        'stunden' => $stunden,
        'stunde_jetzt' => (int) wp_date('G'),
        'quellen' => array_slice(array_map(fn($k, $v) => ['quelle' => $k, 'n' => $v], array_keys($quellen), $quellen), 0, 6),
        'geraete' => $geraete,
        'tage' => $tage,
        'team' => $team,
    ];
}

/* ---------------------------------------------------------- Anzeige */
function ma_live_html(bool $gross): string {
    $api = esc_url_raw(rest_url('ma/v1/live'));
    $nonce = wp_create_nonce('wp_rest');
    $id = 'ma-live-' . ($gross ? 'seite' : 'widget');
    ob_start(); ?>
<div class="ma-live<?php echo $gross ? ' ma-live--gross' : ''; ?>" id="<?php echo esc_attr($id); ?>" data-api="<?php echo esc_attr($api); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">
  <div class="ma-live__kopf">
    <div class="ma-live__jetzt"><span class="ma-live__punkt" aria-hidden="true"></span><strong data-l="jetzt">–</strong><span>Besucher gerade auf der Seite</span></div>
    <dl class="ma-live__heute">
      <div><dt>Besucher heute</dt><dd data-l="heute-besucher">–</dd></div>
      <div><dt>Aufrufe heute</dt><dd data-l="heute-aufrufe">–</dd></div>
      <div><dt>Besucher gestern</dt><dd data-l="gestern">–</dd></div>
    </dl>
  </div>
  <div class="ma-live__stunden"><p class="ma-live__titel">Besucher heute je Stunde</p><svg data-l="stunden" role="img" aria-label="Besucher je Stunde heute"></svg></div>
  <div class="ma-live__raster">
    <div><p class="ma-live__titel">Wird gerade gelesen</p><ol class="ma-live__liste" data-l="seiten"><li class="ma-live__leer">Gerade niemand.</li></ol></div>
<?php if ($gross): ?>
    <div class="ma-live__breit"><p class="ma-live__titel">Gerade online</p><div class="ma-live__tabwrap"><table class="ma-live__tabelle"><thead><tr><th>Besucher</th><th>liest gerade</th><th>Gerät</th><th>kommt von</th><th>seit</th><th>Seiten</th></tr></thead><tbody data-l="besucher"><tr><td colspan="6" class="ma-live__leer">Gerade niemand.</td></tr></tbody></table></div></div>
    <div><p class="ma-live__titel">Herkunft heute</p><ol class="ma-live__liste" data-l="quellen"></ol></div>
    <div><p class="ma-live__titel">Geräte heute</p><ol class="ma-live__liste" data-l="geraete"></ol></div>
    <div class="ma-live__breit"><p class="ma-live__titel">Besucher je Tag, letzte 30 Tage</p><svg data-l="tage" role="img" aria-label="Besucher je Tag, letzte 30 Tage"></svg></div>
<?php endif; ?>
    <div><p class="ma-live__titel">Team online</p><ol class="ma-live__liste" data-l="team"><li class="ma-live__leer">Niemand angemeldet.</li></ol></div>
  </div>
  <p class="ma-live__fuss"><span data-l="stand">lädt …</span> · aktualisiert sich alle 10 Sekunden · ohne Cookies, ohne gespeicherte IP-Adresse<?php if (!$gross): ?> · <a href="<?php echo esc_url(admin_url('admin.php?page=ma-statistik')); ?>">Ganze Statistik</a><?php endif; ?></p>
</div>
<?php
    return (string) ob_get_clean();
}

function ma_live_assets(): void {
    static $einmal = false; if ($einmal) return; $einmal = true;
    echo '<style>' . ma_live_css() . '</style><script>' . ma_live_js() . '</script>';
}

function ma_live_css(): string {
    return '.ma-live{--rot:#8c1c22;--gruen:#1f7a44;font-size:13px;color:#1d2327}'
        . '.ma-live__kopf{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:12px 24px;padding-bottom:12px;border-bottom:1px solid #dcdcde}'
        . '.ma-live__jetzt{display:grid;grid-template-columns:auto auto;justify-content:start;align-items:center;column-gap:10px}.ma-live__jetzt strong{font-size:44px;line-height:1;font-weight:700;font-variant-numeric:tabular-nums;color:#1d2327}'
        . '.ma-live__jetzt span:not(.ma-live__punkt){grid-column:1/-1;margin-top:4px;color:#50575e}'
        . '.ma-live__punkt{width:12px;height:12px;border-radius:50%;background:#a7aaad}.ma-live.ist-live .ma-live__punkt{background:var(--gruen);box-shadow:0 0 0 0 rgba(31,122,68,.45);animation:ma-live-puls 2s cubic-bezier(.22,1,.36,1) infinite}'
        . '@keyframes ma-live-puls{0%{box-shadow:0 0 0 0 rgba(31,122,68,.45)}100%{box-shadow:0 0 0 12px rgba(31,122,68,0)}}@media (prefers-reduced-motion:reduce){.ma-live.ist-live .ma-live__punkt{animation:none}}'
        . '.ma-live__heute{display:flex;gap:22px;margin:0}.ma-live__heute div{display:flex;flex-direction:column-reverse}.ma-live__heute dt{color:#50575e}.ma-live__heute dd{margin:0;font-size:20px;font-weight:600;font-variant-numeric:tabular-nums}'
        . '.ma-live__titel{margin:14px 0 6px;font-weight:600;color:#1d2327}'
        . '.ma-live svg[data-l]{display:block;width:100%;height:110px;overflow:visible}.ma-live svg[data-l="tage"]{height:130px}'
        . '.ma-live__raster{display:grid;grid-template-columns:1fr;gap:0 24px}.ma-live--gross .ma-live__raster{grid-template-columns:repeat(auto-fit,minmax(260px,1fr))}.ma-live__raster>div{min-width:0}.ma-live__breit{grid-column:1/-1}'
        . '.ma-live__liste{margin:0;padding:0;list-style:none}.ma-live__liste li{display:flex;justify-content:space-between;gap:12px;padding:5px 0;border-top:1px solid #f0f0f1}.ma-live__liste li b{font-variant-numeric:tabular-nums}'
        . '.ma-live__liste a{text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0}.ma-live__leer{color:#646970;justify-content:flex-start!important}'
        . '.ma-live__tabwrap{overflow-x:auto}.ma-live__tabelle{width:100%;border-collapse:collapse;white-space:nowrap}.ma-live__tabelle td:nth-child(2){white-space:normal;min-width:160px}.ma-live__tabelle th{text-align:left;font-weight:600;color:#50575e;padding:4px 8px 4px 0;border-bottom:1px solid #dcdcde}.ma-live__tabelle td{padding:6px 8px 6px 0;border-bottom:1px solid #f0f0f1;vertical-align:top}.ma-live__tabelle td:last-child,.ma-live__tabelle th:last-child{text-align:right}'
        . ''
        . '.ma-live__fuss{margin:12px 0 0;color:#646970;font-size:12px}'
        . '#ma_live_widget .inside{padding-top:8px}';
}

function ma_live_js(): string {
    return <<<'JS'
(function(){
  function e(s){return String(s==null?'':s).replace(/[&<>"']/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
  function zahl(n){return Number(n||0).toLocaleString('de-DE');}
  function seit(m){return m<1?'gerade eben':m<60?m+' Min.':Math.floor(m/60)+' Std. '+(m%60)+' Min.';}
  function balken(svg,werte,hervor,beschrift,titel){if(!svg)return;var breite=Math.max(240,svg.clientWidth||480),hoehe=svg.clientHeight||110,unten=hoehe-16,max=Math.max(1,Math.max.apply(null,werte)),w=breite/werte.length,h='';svg.setAttribute('viewBox','0 0 '+breite+' '+hoehe);werte.forEach(function(v,i){var bh=Math.round(v/max*(unten-14)),x=i*w;h+='<rect x="'+(x+1).toFixed(1)+'" y="'+(unten-bh)+'" width="'+Math.max(1,w-2).toFixed(1)+'" height="'+bh+'" rx="1.5" fill="'+(i===hervor?'#8c1c22':'#c9a5a7')+'"><title>'+e(titel(i,v))+'</title></rect>';if(v>0&&w>=14)h+='<text x="'+(x+w/2).toFixed(1)+'" y="'+(unten-bh-3)+'" font-size="10" text-anchor="middle" fill="#50575e">'+v+'</text>';var b=beschrift(i);if(b)h+='<text x="'+(x+w/2).toFixed(1)+'" y="'+(hoehe-3)+'" font-size="10" text-anchor="middle" fill="#646970">'+e(b)+'</text>';});h+='<line x1="0" x2="'+breite+'" y1="'+(unten+.5)+'" y2="'+(unten+.5)+'" stroke="#dcdcde"/>';svg.innerHTML=h;}
  function start(box){
    var api=box.dataset.api,nonce=box.dataset.nonce,q=function(n){return box.querySelector('[data-l="'+n+'"]');};
    var letzte=null;function grafik(d){balken(q('stunden'),d.stunden,d.stunde_jetzt,function(i){return i%3===0?i+(i===0?' Uhr':''):'';},function(i,v){return i+' Uhr: '+v+' Besucher';});var tg=function(i){var t=d.tage[i].tag.split('-');return t[2]+'.'+t[1]+'.';};if(q('tage'))balken(q('tage'),d.tage.map(function(t){return t.besucher;}),d.tage.length-1,function(i){return (d.tage.length-1-i)%7===0?(i===d.tage.length-1?'heute':tg(i)):'';},function(i,v){return tg(i)+': '+v+' Besucher';});}
    var warte;window.addEventListener('resize',function(){clearTimeout(warte);warte=setTimeout(function(){if(letzte)grafik(letzte);},150);});
    function setze(n,v){var z=q(n);if(z)z.textContent=v;}
    function liste(n,items,leer){var z=q(n);if(!z)return;z.innerHTML=items.length?items.join(''):'<li class="ma-live__leer">'+leer+'</li>';}
    function laden(){
      if(document.hidden)return;
      return fetch(api,{credentials:'same-origin',headers:{'X-WP-Nonce':nonce},cache:'no-store'}).then(function(r){if(!r.ok)throw new Error(r.status);return r.json();}).then(function(d){
        box.classList.toggle('ist-live',d.jetzt>0);
        setze('jetzt',zahl(d.jetzt));
        setze('heute-besucher',zahl(d.heute.besucher));setze('heute-aufrufe',zahl(d.heute.aufrufe));setze('gestern',zahl(d.heute.gestern_besucher));
        liste('seiten',d.seiten_jetzt.map(function(s){return '<li><a href="'+e(s.pfad)+'" target="_blank" rel="noopener" title="'+e(s.pfad)+'">'+e(s.titel||s.pfad)+'</a><b>'+s.n+'</b></li>';}),'Gerade niemand.');
        var tb=q('besucher');if(tb){tb.innerHTML=d.besucher.length?d.besucher.map(function(b){return '<tr><td>Besucher '+b.nr+'</td><td>'+(b.pfad?'<a href="'+e(b.pfad)+'" target="_blank" rel="noopener">'+e(b.titel||b.pfad)+'</a>':'<span class="ma-live__leer">Seite unbekannt</span>')+'</td><td>'+e(b.geraet)+'</td><td>'+e(b.quelle)+'</td><td>'+seit(b.seit_min)+'</td><td>'+b.seiten+'</td></tr>';}).join(''):'<tr><td colspan="6" class="ma-live__leer">Gerade niemand.</td></tr>'}
        liste('quellen',d.quellen.map(function(s){return '<li><span>'+e(s.quelle)+'</span><b>'+zahl(s.n)+'</b></li>';}),'Noch keine Besucher heute.');
        liste('geraete',Object.keys(d.geraete).map(function(g){return '<li><span>'+e(g)+'</span><b>'+zahl(d.geraete[g])+'</b></li>';}),'');
        liste('team',d.team.map(function(t){return '<li><span>'+e(t.name)+' · '+e(t.rolle)+'</span><b>'+(t.vor_min<1?'jetzt':'vor '+t.vor_min+' Min.')+'</b></li>';}),'Niemand angemeldet.');
        letzte=d;grafik(d);
        setze('stand','Stand '+d.stand+' Uhr');
      }).catch(function(){setze('stand','Live-Daten gerade nicht erreichbar, neuer Versuch in 10 Sekunden');});
    }
    laden();setInterval(laden,10000);document.addEventListener('visibilitychange',function(){if(!document.hidden)laden();});
  }
  function los(){document.querySelectorAll('.ma-live[data-api]').forEach(start);}
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',los);else los();
})();
JS;
}

/* Dashboard-Widget direkt nach dem Anmelden, oben in der ersten Spalte. */
add_action('wp_dashboard_setup', function (): void {
    if (!current_user_can('edit_others_posts') || (function_exists('ma_current_partner_policy') && ma_current_partner_policy())) return;
    wp_add_dashboard_widget('ma_live_widget', 'Live: Merzenich Aktuell', function (): void { ma_live_assets(); echo ma_live_html(false); });
    // Nach oben schieben.
    global $wp_meta_boxes;
    $normal = $wp_meta_boxes['dashboard']['normal']['core'] ?? [];
    if (isset($normal['ma_live_widget'])) {
        $w = ['ma_live_widget' => $normal['ma_live_widget']]; unset($normal['ma_live_widget']);
        $wp_meta_boxes['dashboard']['normal']['core'] = array_merge($w, $normal);
    }
});

/*
 * Nach dem Anmelden direkt auf das Dashboard mit der Live-Anzeige. Der
 * Hoster leitet /wp-admin/ sonst auf seine eigene Übersichtsseite um.
 * Nur wenn kein bestimmtes Ziel angefordert war (redirect_to = Standard).
 */
add_filter('login_redirect', function ($ziel, $angefordert, $user) {
    if (!($user instanceof WP_User) || !user_can($user, 'edit_others_posts')) return $ziel;
    if (function_exists('ma_current_partner_policy') && ma_current_partner_policy($user)) return $ziel;
    $standard = ['', admin_url(), admin_url('/'), admin_url('index.php')];
    return in_array((string) $angefordert, $standard, true) ? admin_url('index.php') : $ziel;
}, 999, 3);
