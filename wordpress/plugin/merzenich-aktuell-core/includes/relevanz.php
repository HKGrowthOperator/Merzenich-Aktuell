<?php
/**
 * Relevanz 1–10 und Freigabe mit Startseiten-Wahl (30.09.2026, zehn Stufen
 * seit 01.10.2026 nach Vorgabe des Betreibers).
 *
 * Wer im Backend eine Einreichung annimmt, wählt gleich mit, ob sie auf die
 * Startseite darf, und ordnet sie mit einer Zahl von 1 bis 10 ein. Die
 * Startseiten-Freigabe ist die übergeordnete Bedingung: ohne sie erscheint
 * eine Meldung nie auf der Startseite, auch mit Relevanz 10. Mit ihr bestimmt
 * die Relevanz die Position (ma_relevanz_stufen()):
 *
 *   10  Kandidat für den Aufmacher (Haupt-Hero)
 *    9  Aufmacher / oberster Nachrichtenbereich
 *    8  Prominente Nachrichtenfläche (Bühne)
 *    7  Oberer Nachrichtenbereich
 *    6  Rubrikflächen, obere Position
 *  4–5  Rubrikflächen
 *    3  Unterer Teil der Rubrikflächen
 *  1–2  Nachrangig (Zeilen am Ende)
 *
 * Sortierung (ma_relevanz_wert()): Relevanz minus Alter in Tagen mal
 * Abklingfaktor, auf volle Stunden gerundet. Bei gleicher Relevanz steht die
 * neuere Meldung vorn, und eine ältere 10 verdrängt nicht dauerhaft alles
 * Neue. Aufmacher und Bühne verlangen zusätzlich „frisch“ (jünger als
 * frisch_stunden). Die Werte stehen zentral in ma_relevanz_einstellung() und
 * sind unter Merzenich Aktuell → Freigaben einstellbar; das Theme liest sie
 * über dieselben Funktionen (ma21_startseite_belegung()).
 * Ein fester Startplatz (Box „Startseite“) geht der Relevanz immer vor.
 * Ohne Angabe gilt 5.
 *
 * Zwei getrennte Freigaben (03.10.2026, KBS 01.10.): Veröffentlichen heißt
 * nicht Startseite. Die Startseiten-Freigabe ist ein eigenes Feld
 * (ma_startseite_freigabe ja/nein, wer und wann in ma_startseite_freigabe_von),
 * das nur Redaktion und Administration setzen. Ohne Entscheidung steht eine
 * Meldung nur in ihrer Rubrik. Was vor der Umstellung veröffentlicht war (oder
 * mit älterem Datum importiert wird), behält die bisherige Regel: Startseite,
 * solange nicht „Nur in der Rubrik“ gewählt ist. Dazu Priorität 1–10
 * (ma_prioritaet): ordnet innerhalb derselben Relevanz, höchstens ±0,45 Punkte.
 *
 * Nur für Redaktion und Administration (edit_others_posts), nie für Partner.
 */
if (!defined('ABSPATH')) { exit; }

const MA_RELEVANZ_STANDARD = 5;

/** Zentrale Einstellungen der Platzierung. */
function ma_relevanz_einstellung(): array {
    $e = function_exists('get_option') ? get_option('ma_relevanz_einstellung', []) : [];
    $e = is_array($e) ? $e : [];
    return [
        'frisch_stunden' => max(6, min(336, (int) ($e['frisch_stunden'] ?? 72))),
        'abklingen' => max(0.0, min(5.0, (float) ($e['abklingen'] ?? 1.0))),
        'hero_ab' => max(7, min(10, (int) ($e['hero_ab'] ?? 9))),
        'buehne_ab' => max(6, min(10, (int) ($e['buehne_ab'] ?? 8))),
    ];
}
if (!defined('MA_RELEVANZ_FRISCH_STUNDEN')) define('MA_RELEVANZ_FRISCH_STUNDEN', 72);

/** Die zehn Stufen: Bedeutung und Platz auf der Startseite. */
function ma_relevanz_stufen(): array {
    $t = [
        10 => ['Höchste Relevanz', 'Kandidat für den Aufmacher (Haupt-Hero), solange frisch'],
        9 => ['Sehr hohe Relevanz', 'Aufmacher oder oberster Nachrichtenbereich, solange frisch'],
        8 => ['Hohe Relevanz', 'Prominente Nachrichtenfläche (Bühne), solange frisch'],
        7 => ['Hohe lokale Bedeutung', 'Oberer Nachrichtenbereich der Startseite'],
        6 => ['Relevante Nachricht', 'Rubrikflächen der Startseite, obere Position'],
        5 => ['Normale Nachricht', 'Rubrikflächen der Startseite'],
        4 => ['Lokales Thema', 'Rubrikflächen der Startseite'],
        3 => ['Kleinere Nachricht', 'Unterer Teil der Rubrikflächen'],
        2 => ['Vereins- oder Spezialmeldung', 'Nachrangig, Zeilen am Ende der Rubrikflächen'],
        1 => ['Geringe allgemeine Relevanz', 'Nachrangig, Zeilen am Ende der Rubrikflächen'],
    ];
    $raus = [];
    foreach ($t as $r => [$name, $wo]) $raus[] = ['von' => $r, 'bis' => $r, 'name' => $name, 'wo' => $wo];
    return $raus;
}

function ma_relevanz_stufe(int $r): array {
    foreach (ma_relevanz_stufen() as $s) if ($r >= $s['von'] && $r <= $s['bis']) return $s;
    return ma_relevanz_stufe(MA_RELEVANZ_STANDARD);
}

/** Gruppe für die Farbe der Auswahlknöpfe. */
function ma_relevanz_gruppe(int $r): string {
    return $r >= 9 ? 'top-thema' : ($r >= 7 ? 'wichtig' : ($r >= 4 ? 'normal' : 'nur-rubrik'));
}

function ma_relevanz_saeubern($v): int {
    $r = (int) $v;
    return $r >= 1 && $r <= 10 ? $r : 0;
}

/** Relevanz eines Beitrags; ohne Angabe MA_RELEVANZ_STANDARD. */
function ma_relevanz($post): int {
    $id = $post instanceof WP_Post ? $post->ID : (int) $post;
    $r = ma_relevanz_saeubern(get_post_meta($id, 'ma_relevanz', true));
    return $r ?: MA_RELEVANZ_STANDARD;
}

/** Zählt die Relevanz für Aufmacher und Bühne noch? */
function ma_relevanz_frisch($post, ?int $jetzt = null): bool {
    $p = $post instanceof WP_Post ? $post : get_post((int) $post);
    if (!$p) return false;
    $zeit = (int) get_post_time('U', true, $p);
    return ($jetzt ?? time()) - $zeit <= ma_relevanz_einstellung()['frisch_stunden'] * HOUR_IN_SECONDS;
}

/** Priorität 1–10 (ohne Angabe 5): Feinsortierung innerhalb derselben Relevanz. */
function ma_prioritaet($post): int {
    $id = $post instanceof WP_Post ? $post->ID : (int) $post;
    $r = ma_relevanz_saeubern(get_post_meta($id, 'ma_prioritaet', true));
    return $r ?: 5;
}

/**
 * Sortierwert: Relevanz (plus Priorität) minus Alter (Tage, auf volle Stunden) mal Abklingen.
 * Stabil zwischen zwei Aufrufen derselben Stunde, ändert sich nur mit der Zeit
 * oder einer neuen Relevanz.
 */
function ma_relevanz_wert($post, ?int $jetzt = null): float {
    $p = $post instanceof WP_Post ? $post : get_post((int) $post);
    if (!$p) return 0.0;
    $stunden = intdiv(max(0, ($jetzt ?? time()) - (int) get_post_time('U', true, $p)), HOUR_IN_SECONDS);
    return ma_relevanz($p) + (ma_prioritaet($p) - 5) * 0.09 - ($stunden / 24) * ma_relevanz_einstellung()['abklingen'];
}

/** Ab wann gilt die ausdrückliche Startseiten-Freigabe (erster Lauf dieser Version)? */
function ma_startseite_freigabe_seit(): int {
    $t = (int) get_option('ma_startseite_freigabe_seit', 0);
    if (!$t) { $t = time(); update_option('ma_startseite_freigabe_seit', $t, false); }
    return $t;
}

/** Ausdrückliche Entscheidung: 'ja', 'nein' oder '' (noch keine). */
function ma_startseite_freigabe($post): string {
    $id = $post instanceof WP_Post ? $post->ID : (int) $post;
    $v = (string) get_post_meta($id, 'ma_startseite_freigabe', true);
    return in_array($v, ['ja', 'nein'], true) ? $v : '';
}

/** Darf die Meldung auf die Startseite? Nur mit Startseiten-Freigabe (Altbestand: bisherige Regel). */
function ma_relevanz_startseite($post): bool {
    $p = $post instanceof WP_Post ? $post : get_post((int) $post);
    if (!$p) return false;
    if ((string) get_post_meta($p->ID, 'ma_startplatz', true) === 'aus') return false;
    $f = ma_startseite_freigabe($p);
    if ($f !== '') return $f === 'ja';
    return (int) get_post_time('U', true, $p) < ma_startseite_freigabe_seit();
}

/**
 * Startseiten-Freigabe setzen (nur Redaktion): Feld, wer und wann, Verlauf.
 * Nein nimmt die Meldung von allen festen Plätzen der Startseite („Nur in der
 * Rubrik“), Ja hebt „Nur in der Rubrik“ auf (Platz dann nach Relevanz).
 */
function ma_startseite_freigabe_setzen(int $id, bool $ja, string $anlass = ''): void {
    $vorher = ma_startseite_freigabe($id);
    update_post_meta($id, 'ma_startseite_freigabe', $ja ? 'ja' : 'nein');
    update_post_meta($id, 'ma_startseite_freigabe_von', ['u' => get_current_user_id(), 't' => time()]);
    $platz = (string) get_post_meta($id, 'ma_startplatz', true);
    if (!$ja && $platz !== 'aus') { update_post_meta($id, 'ma_startplatz', 'aus'); if (function_exists('ma_layout_post_entfernen')) ma_layout_post_entfernen($id, 'startseite', true); }
    elseif ($ja && ($platz === '' || $platz === 'aus')) update_post_meta($id, 'ma_startplatz', 'auto');
    if ($vorher !== ($ja ? 'ja' : 'nein') && function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($id, 'Startseiten-Freigabe: ' . ($ja ? 'ja' : 'nein') . ($anlass !== '' ? ' (' . $anlass . ')' : ''));
}

/** Kurztext für Listen und Freigabeseite. */
function ma_startseite_freigabe_text($post): string {
    $f = ma_startseite_freigabe($post);
    if ($f === 'ja') return 'freigegeben';
    if ($f === 'nein') return 'nicht freigegeben';
    return ma_relevanz_startseite($post) ? 'ja (Altbestand)' : 'noch nicht entschieden';
}

/** Qualifiziert für Aufmacher bzw. Bühne (Startseite erlaubt, frisch, Schwelle erreicht)? */
function ma_relevanz_zone($post, ?int $jetzt = null): string {
    if (!ma_relevanz_startseite($post)) return 'aus';
    $r = ma_relevanz($post); $e = ma_relevanz_einstellung(); $frisch = ma_relevanz_frisch($post, $jetzt);
    if ($frisch && $r >= $e['hero_ab']) return 'hero';
    if ($frisch && $r >= $e['buehne_ab']) return 'buehne';
    if ($r >= 7) return 'oben';
    if ($r >= 4) return 'feed';
    if ($r === 3) return 'unten';
    return 'nachrangig';
}

/** Sortiert Beiträge für die Startseite: höherer Wert zuerst, sonst der neuere. */
function ma_relevanz_sortieren(array $posts, ?int $jetzt = null): array {
    $jetzt = $jetzt ?? time();
    $w = [];
    foreach ($posts as $p) $w[$p->ID] = ma_relevanz_wert($p, $jetzt);
    usort($posts, function ($a, $b) use ($w) {
        if ($w[$a->ID] !== $w[$b->ID]) return $w[$b->ID] <=> $w[$a->ID];
        return strcmp((string) $b->post_date_gmt, (string) $a->post_date_gmt);
    });
    return $posts;
}

/**
 * Einmalige, sichere Umstellung: Bis 1.11 hieß Relevanz 1–3 „nur in der
 * Rubrik“. Damit solche Meldungen nicht plötzlich auf die Startseite rücken,
 * bekommen sie ausdrücklich „Nur in der Rubrik“. Es wird nichts gelöscht.
 */
add_action('admin_init', function (): void {
    if (get_option('ma_relevanz_umstellung') === '2') return;
    $ids = get_posts(['post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids',
        'meta_query' => [['key' => 'ma_relevanz', 'value' => [1, 2, 3], 'compare' => 'IN', 'type' => 'NUMERIC']]]);
    foreach ($ids as $id) {
        $platz = (string) get_post_meta($id, 'ma_startplatz', true);
        if ($platz === '' || $platz === 'auto') { update_post_meta($id, 'ma_startplatz', 'aus'); if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($id, 'Umstellung Relevanz: „Nur in der Rubrik“ übernommen', 'Bisherige Bedeutung von Relevanz 1–3', 0); }
    }
    update_option('ma_relevanz_umstellung', '2', false);
});

function ma_relevanz_darf(): bool {
    if (function_exists('ma_current_partner_policy') && ma_current_partner_policy()) return false;
    return current_user_can('edit_others_posts');
}

add_action('init', function (): void {
    register_post_meta('post', 'ma_relevanz', [
        'type' => 'integer', 'single' => true, 'show_in_rest' => true, 'default' => 0,
        'sanitize_callback' => 'ma_relevanz_saeubern',
        'auth_callback' => fn() => ma_relevanz_darf(),
    ]);
    register_post_meta('post', 'ma_prioritaet', [
        'type' => 'integer', 'single' => true, 'show_in_rest' => true, 'default' => 0,
        'sanitize_callback' => 'ma_relevanz_saeubern',
        'auth_callback' => fn() => ma_relevanz_darf(),
    ]);
});

/** Auswahl 1–10 als Knopfreihe (Box im Beitrag und Freigabeseite). */
function ma_relevanz_auswahl(string $name, int $wert): string {
    $h = '<fieldset class="ma-relevanz" data-ma-relevanz><legend class="screen-reader-text">Relevanz</legend><div class="ma-relevanz__reihe">';
    for ($i = 1; $i <= 10; $i++) {
        $h .= sprintf('<label class="ma-relevanz__knopf ma-relevanz__knopf--%s" title="%s"><input type="radio" name="%s" value="%d"%s><span>%d</span></label>',
            esc_attr(ma_relevanz_gruppe($i)), esc_attr(ma_relevanz_stufe($i)['name'] . ': ' . ma_relevanz_stufe($i)['wo']),
            esc_attr($name), $i, checked($wert, $i, false), $i);
    }
    $s = ma_relevanz_stufe($wert ?: MA_RELEVANZ_STANDARD);
    $h .= '</div><p class="ma-relevanz__wo" data-ma-relevanz-wo><strong>' . esc_html($s['name']) . ':</strong> ' . esc_html($s['wo']) . '</p></fieldset>';
    return $h;
}

/** Priorität 1–10 als Auswahlliste (Box im Beitrag, Freigabeseite). */
function ma_prioritaet_auswahl(string $name, int $wert, string $attr = ''): string {
    $h = '<select name="' . esc_attr($name) . '"' . $attr . ' aria-label="Priorität 1 bis 10"><option value="0"' . selected($wert, 0, false) . '>Priorität: normal (5)</option>';
    for ($i = 10; $i >= 1; $i--) $h .= '<option value="' . $i . '"' . selected($wert, $i, false) . '>Priorität ' . $i . ($i >= 8 ? ' · hoch' : ($i <= 3 ? ' · niedrig' : '')) . '</option>';
    return $h . '</select>';
}

/** Stufen als JSON für die Live-Anzeige „landet in …“. */
function ma_relevanz_js_daten(): array {
    $m = [];
    for ($i = 1; $i <= 10; $i++) { $s = ma_relevanz_stufe($i); $m[$i] = ['name' => $s['name'], 'wo' => $s['wo']]; }
    return $m;
}

add_action('admin_enqueue_scripts', function (string $hook): void {
    if (!ma_relevanz_darf()) return;
    $seite = sanitize_key(wp_unslash($_GET['page'] ?? ''));
    $typ = function_exists('get_current_screen') && get_current_screen() ? get_current_screen()->post_type : '';
    if (!in_array($hook, ['post.php', 'post-new.php', 'edit.php'], true) && $seite !== 'ma-freigaben') return;
    if (in_array($hook, ['post.php', 'post-new.php', 'edit.php'], true) && $typ !== 'post') return;
    wp_register_style('ma-relevanz', false, [], MA_CORE_VERSION);
    wp_enqueue_style('ma-relevanz');
    wp_add_inline_style('ma-relevanz', ma_relevanz_css());
    wp_register_script('ma-relevanz', false, [], MA_CORE_VERSION, true);
    wp_enqueue_script('ma-relevanz');
    wp_add_inline_script('ma-relevanz', 'window.maRelevanz=' . wp_json_encode(['stufen' => ma_relevanz_js_daten(), 'ajax' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('ma_relevanz')]) . ';' . ma_relevanz_js());
});

function ma_relevanz_css(): string {
    return '.ma-relevanz{border:0;margin:0 0 8px;padding:0}.ma-relevanz__reihe{display:grid;grid-template-columns:repeat(10,minmax(0,1fr));gap:3px}'
        . '.ma-relevanz__knopf{position:relative;display:block;cursor:pointer}.ma-relevanz__knopf input{position:absolute;opacity:0;inset:0;margin:0;cursor:pointer}'
        . '.ma-relevanz__knopf span{display:block;text-align:center;padding:6px 0;border:1px solid #c3c4c7;border-radius:3px;font-weight:600;font-variant-numeric:tabular-nums;background:#fff}'
        . '.ma-relevanz__knopf--nur-rubrik span{background:#f6f7f7}.ma-relevanz__knopf--normal span{background:#fff}.ma-relevanz__knopf--wichtig span{background:#fcf0e3}.ma-relevanz__knopf--top-thema span{background:#fbe3e4}'
        . '.ma-relevanz__knopf input:checked+span{background:#8c1c22;border-color:#8c1c22;color:#fff}.ma-relevanz__knopf input:focus-visible+span{outline:2px solid #2271b1;outline-offset:1px}'
        . '.ma-relevanz__wo{margin:6px 0 0;font-size:12px;color:#50575e}'
        . '.column-ma_relevanz{width:88px}.ma-relevanz-liste{max-width:84px}.ma-relevanz-ok{color:#008a20;margin-left:4px}'
        . '.ma-freigaben td{vertical-align:top}.ma-freigaben__bild{width:120px;height:68px;object-fit:cover;background:#f0f0f1;display:block}'
        . '.ma-freigaben__aktion{min-width:320px}.ma-freigaben__zeile{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:8px}'
        . '.ma-freigaben__status{font-weight:600}.ma-freigaben__status.ist-fehler{color:#b32d2e}.ma-freigaben__status.ist-ok{color:#008a20}'
        . '.ma-relevanz-stufen{border-collapse:collapse;margin:8px 0 18px}.ma-freigaben__typ{display:inline-block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#8c1c22}.ma-freigaben__aenderung{font-size:12px;color:#50575e}.ma-freigaben__planen{display:inline-flex;gap:4px;align-items:center}.ma-freigaben__ablehnen{color:#b32d2e!important}.ma-freigaben [data-ma-notiz]{width:100%;margin-top:8px}.ma-freigaben .button-link{margin-right:12px}.ma-freigaben__verlauf{margin-top:6px}.ma-freigaben__verlauf summary{cursor:pointer;color:#2271b1}.ma-relevanz-einstellung{display:flex;flex-wrap:wrap;gap:12px 20px;align-items:flex-end}.ma-relevanz-einstellung label{display:flex;flex-direction:column;gap:4px}.ma-relevanz-einstellung input{width:120px}.ma-verlauf{margin:6px 0 0;padding:0;list-style:none}.ma-verlauf li{padding:4px 0;border-top:1px solid #f0f0f1;font-size:12px}.ma-verlauf__zeit,.ma-verlauf__wer{color:#646970}.ma-verlauf__notiz{display:block;white-space:pre-line}.ma-relevanz-stufen td,.ma-relevanz-stufen th{padding:4px 12px 4px 0;text-align:left}';
}

function ma_relevanz_js(): string {
    return <<<'JS'
(function(){
  var d=window.maRelevanz||{};
  function wo(fs){var c=fs.querySelector('input:checked'),z=fs.querySelector('[data-ma-relevanz-wo]');if(!c||!z||!d.stufen)return;var s=d.stufen[c.value];z.innerHTML='';var b=document.createElement('strong');b.textContent=s.name+': ';z.append(b,document.createTextNode(s.wo));}
  document.querySelectorAll('[data-ma-relevanz]').forEach(function(fs){fs.addEventListener('change',function(){wo(fs);});});
  function post(daten){daten.nonce=d.nonce;return fetch(d.ajax,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams(daten)}).then(function(r){return r.json();});}
  // Spalte in der Beitragsliste: Auswahl speichert sofort.
  document.querySelectorAll('select.ma-relevanz-liste').forEach(function(sel){sel.addEventListener('change',function(){
    var ok=sel.parentNode.querySelector('.ma-relevanz-ok');if(ok)ok.remove();
    post({action:'ma_relevanz_setzen',post:sel.dataset.post,relevanz:sel.value}).then(function(j){var s=document.createElement('span');s.className='ma-relevanz-ok';s.textContent=j&&j.success?'✓':'Fehler';s.title=j&&j.success?j.data.wo:'Nicht gespeichert';sel.after(s);});
  });});
  // Freigabeseite: Freigeben, Planen und die übrigen Entscheidungen.
  document.querySelectorAll('[data-ma-freigabe]').forEach(function(box){
    var status=box.querySelector('.ma-freigaben__status'),notiz=box.querySelector('[data-ma-notiz]');
    function zeige(t,art){status.textContent=t;status.className='ma-freigaben__status'+(art?' ist-'+art:'');}
    function knoepfe(an){box.querySelectorAll('button').forEach(function(b){b.disabled=!an;});}
    function fertig(j){if(j&&j.success){zeige(j.data.meldung,'ok');box.closest('tr').style.opacity='.55';}else{zeige((j&&j.data&&j.data.meldung)||'Nicht gespeichert.','fehler');knoepfe(true);}}
    function fehler(){zeige('Keine Verbindung. Bitte erneut versuchen.','fehler');knoepfe(true);}
    function freigeben(zeit){
      var r=box.querySelector('input[type=radio]:checked');
      if(box.dataset.meldung==='1'&&!r){zeige('Bitte zuerst eine Relevanz von 1 bis 10 wählen.','fehler');return;}
      knoepfe(false);zeige(zeit?'Wird geplant …':'Wird freigegeben …');
      var st=box.querySelector('[data-ma-startseite]'),br=box.querySelector('[data-ma-bildrechte]'),pr=box.querySelector('[data-ma-prioritaet]');
      post({action:'ma_schnellfreigabe',post:box.dataset.post,relevanz:r?r.value:'0',prioritaet:pr?pr.value:'0',startseite:st&&st.checked?'1':'0',bildrechte:br&&br.checked?'1':'0',zeit:zeit||''}).then(fertig).catch(fehler);
    }
    box.querySelector('[data-ma-freigeben]').addEventListener('click',function(){freigeben('');});
    var pl=box.querySelector('[data-ma-planen]');if(pl)pl.addEventListener('click',function(){var z=box.querySelector('[data-ma-zeit]').value;if(!z){zeige('Bitte Datum und Uhrzeit wählen.','fehler');return;}freigeben(z);});
    box.querySelectorAll('[data-ma-aktion]').forEach(function(k){k.addEventListener('click',function(){
      var a=k.dataset.maAktion;
      if((a==='aenderung'||a==='ablehnen')&&notiz.hidden){notiz.hidden=false;notiz.focus();zeige(a==='aenderung'?'Bitte notieren, was geändert werden soll, dann erneut „Änderungen anfordern“.':'Begründung eintragen (optional), dann erneut „Ablehnen“.');return;}
      if(a==='aenderung'&&!notiz.value.trim()){zeige('Bitte kurz schreiben, was geändert werden soll.','fehler');notiz.focus();return;}
      knoepfe(false);zeige('Wird gespeichert …');
      post({action:'ma_redaktion_aktion',post:box.dataset.post,aktion:a,notiz:notiz.value}).then(fertig).catch(fehler);
    });});
  });
})();
JS;
}

/* ---------------------------------------------------------- Spalte in der Beitragsliste */
add_filter('manage_post_posts_columns', function (array $c): array {
    if (!ma_relevanz_darf()) return $c;
    $neu = [];
    foreach ($c as $k => $v) { $neu[$k] = $v; if ($k === 'title') $neu['ma_relevanz'] = 'Relevanz'; }
    return $neu;
}, 5);
add_action('manage_post_posts_custom_column', function (string $spalte, int $id): void {
    if ($spalte !== 'ma_relevanz') return;
    $r = ma_relevanz_saeubern(get_post_meta($id, 'ma_relevanz', true));
    printf('<select class="ma-relevanz-liste" data-post="%d" aria-label="Relevanz 1 bis 10"><option value="0"%s>–</option>', $id, selected($r, 0, false));
    for ($i = 10; $i >= 1; $i--) printf('<option value="%d"%s>%d · %s</option>', $i, selected($r, $i, false), $i, esc_html(ma_relevanz_stufe($i)['name']));
    echo '</select>';
}, 10, 2);
add_filter('manage_edit-post_sortable_columns', function (array $c): array { $c['ma_relevanz'] = 'ma_relevanz'; return $c; });
add_action('pre_get_posts', function (WP_Query $q): void {
    if (!is_admin() || !$q->is_main_query() || $q->get('orderby') !== 'ma_relevanz') return;
    $q->set('meta_key', 'ma_relevanz'); $q->set('orderby', 'meta_value_num');
});

add_action('wp_ajax_ma_relevanz_setzen', function (): void {
    check_ajax_referer('ma_relevanz', 'nonce');
    $id = (int) ($_POST['post'] ?? 0);
    if (!ma_relevanz_darf() || !$id || get_post_type($id) !== 'post' || !current_user_can('edit_post', $id)) wp_send_json_error(['meldung' => 'Keine Berechtigung.'], 403);
    $r = ma_relevanz_saeubern(wp_unslash($_POST['relevanz'] ?? 0));
    $vorher = (int) get_post_meta($id, 'ma_relevanz', true);
    if ($r) update_post_meta($id, 'ma_relevanz', $r); else delete_post_meta($id, 'ma_relevanz');
    if ($vorher !== $r && function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($id, 'Relevanz ' . ($r ?: 'ohne Angabe') . ($vorher ? ' (vorher ' . $vorher . ')' : ''));
    $s = ma_relevanz_stufe($r ?: MA_RELEVANZ_STANDARD);
    wp_send_json_success(['relevanz' => $r, 'wo' => $s['name'] . ': ' . $s['wo']]);
});

/* ---------------------------------------------------------- Freigeben mit Relevanz und Startseite */

/**
 * Nimmt eine Einreichung an: redaktionelle Prüfhaken mit Prüfer und Zeit,
 * Relevanz, Startseite ja/nein, veröffentlichen oder (mit $zeit) planen.
 * Änderungsvorschläge zu Veröffentlichtem werden ins Original übernommen.
 * Termine und Vereinsprofile brauchen keine Relevanz. Die Sperren
 * (Bildrechte, Pflichtangaben) bleiben wirksam; dann bleibt der Beitrag
 * Entwurf und die Antwort nennt den Grund.
 */
function ma_schnellfreigabe(int $id, int $relevanz, bool $startseite, bool $bildrechte = false, string $zeit = '', int $prioritaet = 0): array {
    $typ = (string) get_post_type($id);
    if (!in_array($typ, ma_freigabe_typen(), true)) return ['ok' => false, 'meldung' => 'Dieser Inhalt lässt sich hier nicht freigeben.'];
    $wer = wp_get_current_user()->display_name;
    // Änderungsvorschlag: ins Original übernehmen, die Kopie verschwindet. Relevanz
    // und Startseite des Originals bleiben, wie sie sind.
    if (function_exists('ma_aenderung_uebernehmen') && get_post_meta($id, '_ma_aenderung_von', true)) {
        $orig = ma_aenderung_uebernehmen($id);
        if ($orig && $bildrechte) update_post_meta($orig, 'ma_image_rights_verified', '1');
        return $orig ? ['ok' => true, 'meldung' => 'Änderung übernommen. Die veröffentlichte Fassung ist aktualisiert.'] : ['ok' => false, 'meldung' => 'Original nicht gefunden.'];
    }
    if ($typ === 'post' && !$relevanz) return ['ok' => false, 'meldung' => 'Bitte eine Relevanz von 1 bis 10 wählen.'];
    // Bildrechte bestaetigt die Redaktion nur ausdruecklich (Haken in der Zeile).
    if ($bildrechte) update_post_meta($id, 'ma_image_rights_verified', '1');
    if ($typ === 'post') {
        foreach (['ma_source_verified', 'ma_date_verified', 'ma_place_verified', 'ma_human_reviewed'] as $k) update_post_meta($id, $k, '1');
        update_post_meta($id, 'ma_reviewed_by', $wer);
        update_post_meta($id, 'ma_reviewed_at', current_time('mysql'));
        $vorher = (int) get_post_meta($id, 'ma_relevanz', true);
        update_post_meta($id, 'ma_relevanz', $relevanz);
        if ($prioritaet) update_post_meta($id, 'ma_prioritaet', $prioritaet); else delete_post_meta($id, 'ma_prioritaet');
        // Zweite, eigene Entscheidung: Startseite ja = nach Relevanz (ein fester Platz bleibt), nein = nur Rubrik.
        ma_startseite_freigabe_setzen($id, $startseite, 'Freigabe');
        if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($id, 'Relevanz ' . $relevanz . ($vorher && $vorher !== $relevanz ? ' (vorher ' . $vorher . ')' : '') . ($prioritaet ? ', Priorität ' . $prioritaet : ''));
    }
    if ($typ === 'ma_ad') {
        // Anzeige schalten: veröffentlicht + „Schaltung aktiv“ (beides setzt nur die Redaktion).
        if ((string) get_post_meta($id, 'ma_ad_slot', true) === '') return ['ok' => false, 'meldung' => 'Die Anzeige hat noch keinen Werbeplatz. Bitte im Formular wählen.'];
        update_post_meta($id, 'ma_ad_active', '1');
        if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($id, 'Anzeige freigegeben: ' . (function_exists('ma_ad_slot_label') ? ma_ad_slot_label((string) get_post_meta($id, 'ma_ad_slot', true)) : ''));
    }
    $ziel = ['ID' => $id, 'post_status' => 'publish'];
    if ($zeit !== '') {
        $t = strtotime($zeit . ' ' . wp_timezone_string());
        if (!$t || $t <= time() + 60) return ['ok' => false, 'meldung' => 'Der geplante Zeitpunkt muss in der Zukunft liegen.'];
        $ziel = ['ID' => $id, 'post_status' => 'future', 'post_date' => wp_date('Y-m-d H:i:s', $t), 'post_date_gmt' => gmdate('Y-m-d H:i:s', $t), 'edit_date' => true];
    }
    if (get_post_status($id) !== $ziel['post_status'] || $zeit !== '') wp_update_post($ziel);
    if (!in_array(get_post_status($id), ['publish', 'future'], true)) {
        $grund = (string) get_post_meta($id, '_ma_gate_reason', true);
        return ['ok' => false, 'meldung' => 'Nicht freigegeben: ' . ($grund !== '' ? str_replace('|', ', ', $grund) : 'eine Pflichtangabe fehlt') . '. Bitte im Beitrag prüfen.'];
    }
    $plan = get_post_status($id) === 'future' ? 'Geplant für ' . get_post_time('d.m.Y H:i', false, $id) . ' Uhr. ' : 'Veröffentlicht. ';
    if ($typ === 'ma_ad') {
        $von = (string) get_post_meta($id, 'ma_ad_start', true); $bis = (string) get_post_meta($id, 'ma_ad_end', true);
        return ['ok' => true, 'meldung' => $plan . 'Anzeige läuft auf „' . (function_exists('ma_ad_slot_label') ? ma_ad_slot_label((string) get_post_meta($id, 'ma_ad_slot', true)) : '') . '“' . ($von !== '' || $bis !== '' ? ' (' . ($von !== '' ? 'ab ' . mysql2date('d.m.Y H:i', $von) : '') . ($von !== '' && $bis !== '' ? ', ' : '') . ($bis !== '' ? 'bis ' . mysql2date('d.m.Y H:i', $bis) : '') . ')' : '') . '.' . (get_option('ma_ads_enabled') ? '' : ' Hinweis: Werbung ist unter Werbung → Werbeplätze global ausgeschaltet.')];
    }
    if ($typ !== 'post') return ['ok' => true, 'meldung' => $plan];
    $wo = $startseite ? ma_relevanz_stufe($relevanz)['wo'] : 'nur in der Rubrik, nicht auf der Startseite';
    return ['ok' => true, 'meldung' => $plan . 'Steht: ' . $wo . '.'];
}

add_action('wp_ajax_ma_schnellfreigabe', function (): void {
    check_ajax_referer('ma_relevanz', 'nonce');
    $id = (int) ($_POST['post'] ?? 0);
    if (!ma_relevanz_darf() || !$id || !current_user_can('edit_post', $id) || !current_user_can('publish_posts')) wp_send_json_error(['meldung' => 'Keine Berechtigung.'], 403);
    $zeit = sanitize_text_field(wp_unslash($_POST['zeit'] ?? ''));
    if ($zeit !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $zeit)) $zeit = '';
    $e = ma_schnellfreigabe($id, ma_relevanz_saeubern(wp_unslash($_POST['relevanz'] ?? 0)), ($_POST['startseite'] ?? '') === '1', ($_POST['bildrechte'] ?? '') === '1', str_replace('T', ' ', $zeit), ma_relevanz_saeubern(wp_unslash($_POST['prioritaet'] ?? 0)));
    $e['ok'] ? wp_send_json_success($e) : wp_send_json_error($e);
});

/** Inhaltsarten der Freigabeseite (03.10.2026: auch Immobilien, Stellen, Trauer- und Familienanzeigen). */
function ma_freigabe_typen(): array {
    return ['post', 'ma_event', 'ma_club', 'ma_business', 'ma_tip', 'ma_ad', 'ma_property', 'ma_job', 'ma_obituary', 'ma_family_notice'];
}

/** Wartende Inhalte: eingereicht oder in Prüfung, dazu Partner-Entwürfe mit fehlender Fotoerlaubnis. */
function ma_freigaben_wartend(): array {
    $typen = ma_freigabe_typen();
    $l = get_posts(['post_type' => $typen, 'post_status' => ['pending', 'ma_in_pruefung'], 'posts_per_page' => 100, 'orderby' => 'date', 'order' => 'ASC']);
    $entwuerfe = get_posts(['post_type' => 'post', 'post_status' => 'draft', 'posts_per_page' => 40, 'orderby' => 'modified', 'order' => 'DESC',
        'meta_query' => [['key' => '_ma_partner_rights_missing', 'value' => '1']]]);
    $gesehen = [];
    return array_values(array_filter(array_merge($l, $entwuerfe), function ($p) use (&$gesehen) { if (isset($gesehen[$p->ID])) return false; return $gesehen[$p->ID] = true; }));
}

add_action('admin_menu', function (): void {
    $n = count(get_posts(['post_type' => ma_freigabe_typen(), 'post_status' => 'pending', 'posts_per_page' => 99, 'fields' => 'ids']));
    $titel = 'Freigaben' . ($n ? ' <span class="awaiting-mod count-' . $n . '"><span class="pending-count">' . $n . '</span></span>' : '');
    add_submenu_page('merzenich-aktuell', 'Freigaben', $titel, 'edit_others_posts', 'ma-freigaben', 'ma_freigaben_seite', 0);
}, 20);

add_action('admin_post_ma_relevanz_einstellung', function (): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.', 403);
    check_admin_referer('ma_relevanz_einstellung');
    $alt = ma_relevanz_einstellung();
    update_option('ma_relevanz_einstellung', [
        'frisch_stunden' => (int) ($_POST['frisch_stunden'] ?? 72), 'abklingen' => (float) str_replace(',', '.', (string) ($_POST['abklingen'] ?? '1')),
        'hero_ab' => (int) ($_POST['hero_ab'] ?? 9), 'buehne_ab' => (int) ($_POST['buehne_ab'] ?? 8),
    ], false);
    $neu = ma_relevanz_einstellung();
    update_option('ma_relevanz_einstellung_log', array_slice(array_merge((array) get_option('ma_relevanz_einstellung_log', []), [['t' => time(), 'u' => get_current_user_id(), 'alt' => $alt, 'neu' => $neu]]), -50), false);
    wp_safe_redirect(admin_url('admin.php?page=ma-freigaben&gespeichert=1#einstellung')); exit;
});

function ma_freigaben_seite(): void {
    if (!ma_relevanz_darf()) wp_die('Keine Berechtigung.');
    $wartend = ma_freigaben_wartend();
    $e = ma_relevanz_einstellung();
    echo '<div class="wrap"><h1>Freigaben</h1>';
    if (!empty($_GET['gespeichert'])) echo '<div class="notice notice-success is-dismissible"><p>Einstellungen der Platzierung gespeichert.</p></div>';
    echo '<p>Eingereichte Meldungen, Termine, Vereins- und Unternehmensprofile, Anzeigen, Immobilien, Stellen, Trauer- und Familienanzeigen sowie Änderungsvorschläge. Für Meldungen triffst du zwei getrennte Entscheidungen: <strong>veröffentlichen</strong> und, eigens, <strong>Startseite ja oder nein</strong>; dazu die <strong>Relevanz von 1 bis 10</strong> und auf Wunsch eine <strong>Priorität 1 bis 10</strong> (ordnet innerhalb derselben Relevanz). Dann <strong>Freigeben</strong> (sofort) oder <strong>Planen</strong> (Zeitpunkt). Alternativ: in Prüfung nehmen, Änderungen anfordern oder ablehnen; der Einsender bekommt jeweils eine E-Mail.</p>';
    if (!$wartend) echo '<p><strong>Keine Einreichungen warten.</strong></p>';
    else {
        echo '<table class="widefat striped ma-freigaben"><thead><tr><th style="width:130px">Bild</th><th>Einreichung</th><th>Von</th><th class="ma-freigaben__aktion">Entscheidung</th></tr></thead><tbody>';
        foreach ($wartend as $p) echo ma_freigaben_zeile($p);
        echo '</tbody></table>';
    }
    echo '<h2 id="stufen" style="margin-top:28px">Die zehn Stufen</h2><table class="ma-relevanz-stufen"><thead><tr><th>Relevanz</th><th>Bedeutung</th><th>Platz auf der Startseite (mit Startseiten-Freigabe)</th></tr></thead><tbody>';
    foreach (ma_relevanz_stufen() as $s) printf('<tr><td>%d</td><td><strong>%s</strong></td><td>%s</td></tr>', $s['von'], esc_html($s['name']), esc_html($s['wo']));
    printf('</tbody></table><p class="description">Ohne Startseiten-Freigabe steht eine Meldung nur in ihrer Rubrik, auch mit Relevanz 10. „Frisch“ heißt jünger als %d Stunden. Sortiert wird nach Relevanz minus %s Punkt(e) je Tag Alter; bei gleichem Wert steht die neuere Meldung vorn. Ein fester Platz unter <a href="%s">Startseite</a> geht immer vor.</p>',
        $e['frisch_stunden'], esc_html(number_format_i18n($e['abklingen'], 1)), esc_url(admin_url('admin.php?page=ma-startseite')));
    if (current_user_can('manage_options')) {
        echo '<h2 id="einstellung">Einstellungen der Platzierung</h2><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" class="ma-relevanz-einstellung"><input type="hidden" name="action" value="ma_relevanz_einstellung">';
        wp_nonce_field('ma_relevanz_einstellung');
        printf('<label>Frisch für Aufmacher und Bühne (Stunden) <input type="number" name="frisch_stunden" min="6" max="336" value="%d"></label>', $e['frisch_stunden']);
        printf('<label>Abklingen je Tag Alter (Punkte) <input type="number" name="abklingen" min="0" max="5" step="0.1" value="%s"></label>', esc_attr((string) $e['abklingen']));
        printf('<label>Aufmacher ab Relevanz <input type="number" name="hero_ab" min="7" max="10" value="%d"></label>', $e['hero_ab']);
        printf('<label>Bühne ab Relevanz <input type="number" name="buehne_ab" min="6" max="10" value="%d"></label>', $e['buehne_ab']);
        echo '<button class="button">Speichern</button></form>';
    }
    echo '</div>';
}

function ma_freigaben_zeile(WP_Post $p): string {
    $autor = get_userdata((int) $p->post_author);
    $rolle = $autor && function_exists('ma_current_partner_policy') ? (ma_current_partner_policy($autor)['label'] ?? '') : '';
    $verein = $autor ? (string) get_user_meta($autor->ID, 'ma_verein_name', true) : '';
    $bild = get_the_post_thumbnail_url($p, 'medium');
    $von = (int) get_post_meta($p->ID, '_ma_aenderung_von', true);
    $typen = ['post' => 'Meldung', 'ma_event' => 'Termin', 'ma_club' => 'Vereinsprofil', 'ma_business' => 'Unternehmensprofil', 'ma_tip' => 'Tipp', 'ma_ad' => 'Anzeige', 'ma_property' => 'Immobilie', 'ma_job' => 'Stelle', 'ma_obituary' => 'Traueranzeige', 'ma_family_notice' => 'Familienanzeige'];
    $istMeldung = $p->post_type === 'post';
    $kats = $istMeldung ? implode(', ', array_map(fn($t) => $t->name, get_the_category($p->ID))) : '';
    if ($p->post_type === 'ma_ad') {
        $slot = (string) get_post_meta($p->ID, 'ma_ad_slot', true); $sponsor = (string) get_post_meta($p->ID, 'ma_ad_sponsor', true);
        $von = (string) get_post_meta($p->ID, 'ma_ad_start', true); $bis = (string) get_post_meta($p->ID, 'ma_ad_end', true);
        $kats = ($slot !== '' && function_exists('ma_ad_slot_label') ? ma_ad_slot_label($slot) : 'ohne Werbeplatz') . ($sponsor !== '' ? ' · ' . $sponsor : '') . ($von !== '' ? ' · ab ' . mysql2date('d.m.Y', $von) : '') . ($bis !== '' ? ' · bis ' . mysql2date('d.m.Y', $bis) : '');
    }
    $r = ma_relevanz_saeubern(get_post_meta($p->ID, 'ma_relevanz', true));
    $startJa = ma_startseite_freigabe($p) === 'ja';
    $rechte = function_exists('ma_bildrechte_stand') ? ma_bildrechte_stand($p->ID) : ['noetig' => (bool) $bild, 'ok' => (string) get_post_meta($p->ID, 'ma_partner_rights_declared', true) === '1', 'text' => ''];
    $h = '<tr><td>' . ($bild ? '<img class="ma-freigaben__bild" src="' . esc_url($bild) . '" alt="">' : '<span class="description">kein Bild</span>') . '</td>';
    $h .= sprintf('<td><span class="ma-freigaben__typ">%s</span>%s<br><strong><a href="%s">%s</a></strong><br><span class="description">%s%s · %s</span>%s%s<details class="ma-freigaben__verlauf"><summary>Verlauf</summary>%s</details></td>',
        esc_html($typen[$p->post_type] ?? $p->post_type), $von ? ' <span class="ma-freigaben__aenderung">Änderung an „<a href="' . esc_url(get_permalink($von)) . '" target="_blank">' . esc_html(get_the_title($von)) . '</a>“</span>' : '',
        esc_url(get_edit_post_link($p->ID)), esc_html(get_the_title($p) ?: '(ohne Titel)'), $kats !== '' ? esc_html($kats) . ' · ' : '',
        esc_html(function_exists('ma_status_name') ? ma_status_name($p->post_status) : $p->post_status), esc_html(get_the_modified_date('d.m.Y H:i', $p)),
        $rechte['noetig'] ? '<br><span class="description">Bildrechte des Einsenders: ' . ($rechte['ok'] ? 'bestätigt' . ($rechte['text'] !== '' ? ' (' . esc_html($rechte['text']) . ')' : '') : '<strong>nicht bestätigt</strong>') . '</span>' : '',
        $p->post_status === 'ma_in_pruefung' ? '<br><span class="description">In Prüfung bei ' . esc_html((get_userdata((int) get_post_meta($p->ID, '_ma_pruefer', true))->display_name ?? 'Redaktion')) . '</span>' : '',
        function_exists('ma_verlauf_html') ? ma_verlauf_html($p->ID, 8) : '');
    $h .= sprintf('<td>%s<br><span class="description">%s</span></td>', esc_html($autor ? ($autor->display_name ?: $autor->user_login) : '–'), esc_html(trim($rolle . ($verein !== '' && $verein !== ($autor->display_name ?? '') ? ' · ' . $verein : '')) ?: 'Redaktion'));
    $h .= '<td class="ma-freigaben__aktion"><div data-ma-freigabe data-post="' . (int) $p->ID . '" data-meldung="' . ($istMeldung && !$von ? '1' : '0') . '">';
    if ($istMeldung && !$von) $h .= ma_relevanz_auswahl('ma_relevanz_' . $p->ID, $r) . '<div class="ma-freigaben__zeile"><label><input type="checkbox" data-ma-startseite' . ($startJa ? ' checked' : '') . '> Startseiten-Freigabe: auf die Startseite</label>' . ma_prioritaet_auswahl('ma_prioritaet_' . $p->ID, ma_relevanz_saeubern(get_post_meta($p->ID, 'ma_prioritaet', true)), ' data-ma-prioritaet');
    else $h .= '<div class="ma-freigaben__zeile">';
    if ($bild) $h .= '<label><input type="checkbox" data-ma-bildrechte' . ((string) get_post_meta($p->ID, 'ma_image_rights_verified', true) === '1' ? ' checked' : '') . '> Bildrechte geprüft</label>';
    $h .= '</div><div class="ma-freigaben__zeile"><button type="button" class="button button-primary" data-ma-freigeben>' . ($von ? 'Änderung übernehmen' : 'Freigeben') . '</button>';
    if (!$von) $h .= '<span class="ma-freigaben__planen"><input type="datetime-local" data-ma-zeit aria-label="Veröffentlichung planen"><button type="button" class="button" data-ma-planen>Planen</button></span>';
    $h .= '</div><div class="ma-freigaben__zeile">'
        . ($p->post_status !== 'ma_in_pruefung' ? '<button type="button" class="button-link" data-ma-aktion="pruefen">In Prüfung nehmen</button>' : '')
        . '<button type="button" class="button-link" data-ma-aktion="aenderung">Änderungen anfordern</button>'
        . '<button type="button" class="button-link ma-freigaben__ablehnen" data-ma-aktion="ablehnen">Ablehnen</button>'
        . '<a class="button-link" href="' . esc_url(get_edit_post_link($p->ID)) . '">Bearbeiten</a></div>'
        . '<textarea data-ma-notiz rows="2" placeholder="Notiz an den Einsender (Pflicht bei „Änderungen anfordern“)" hidden></textarea>'
        . '<p class="ma-freigaben__status" role="status" aria-live="polite"></p></div></td></tr>';
    return $h;
}
