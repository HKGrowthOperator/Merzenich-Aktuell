<?php
/**
 * Relevanz in drei Stufen und Freigabe mit Startseiten-Wahl (30.09.2026; zehn
 * Stufen 01.10.2026, drei Stufen seit 24.10.2026 nach Vorgabe des Betreibers:
 * „nur 3 Stufen, nicht zu viele“).
 *
 * Wer im Backend eine Einreichung annimmt, wählt gleich mit, ob sie auf die
 * Startseite darf, und ordnet sie in eine von drei Stufen ein. Die
 * Startseiten-Freigabe ist die übergeordnete Bedingung: ohne sie erscheint
 * eine Meldung nie auf der Startseite, auch mit Stufe 1. Mit ihr bestimmt die
 * Stufe die Position (ma_relevanz_stufen()):
 *
 *   1  Hoch: Aufmacher oder Bühne, solange frisch; danach oben in den Blöcken
 *   2  Mitte: Blöcke der Startseite (Standard ohne Angabe)
 *   3  Niedrig: weiter unten in den Blöcken
 *
 * Gespeichert wird die Stufe (ma_relevanz 1–3, 1 = wichtig). Gerechnet wird
 * mit Punkten (ma_relevanz_punkte(): 9, 6, 2), damit Aktualität weiter zählt:
 * Sortierwert = Punkte minus Alter in Tagen mal Abklingfaktor, auf volle
 * Stunden gerundet. Bei gleichem Wert steht die neuere Meldung vorn; eine
 * ältere Stufe 1 verdrängt nach rund drei Tagen keine frische Stufe 2 mehr.
 * Aufmacher und Bühne verlangen Stufe 1 und „frisch“ (jünger als
 * frisch_stunden). Die Werte stehen zentral in ma_relevanz_einstellung() und
 * sind unter Merzenich Aktuell → Freigaben einstellbar; das Theme liest sie
 * über dieselben Funktionen (ma21_startseite_belegung()).
 * Ein fester Startplatz (Box „Startseite“) geht der Relevanz immer vor.
 *
 * Alte Werte 1–10 rechnet ma_relevanz_stufen_umstellen() einmalig um
 * (8–10 → 1, 4–7 → 2, 1–3 → 3). Die frühere Priorität 1–10 (ma_prioritaet)
 * wird nicht mehr gelesen; der gespeicherte Wert bleibt unangetastet.
 *
 * Zwei getrennte Freigaben (03.10.2026, KBS 01.10.): Veröffentlichen heißt
 * nicht Startseite. Die Startseiten-Freigabe ist ein eigenes Feld
 * (ma_startseite_freigabe ja/nein, wer und wann in ma_startseite_freigabe_von),
 * das nur Redaktion und Administration setzen. Ohne Entscheidung steht eine
 * Meldung nur in ihrer Rubrik. Was vor der Umstellung veröffentlicht war (oder
 * mit älterem Datum importiert wird), behält die bisherige Regel: Startseite,
 * solange nicht „Nur in der Rubrik“ gewählt ist.
 *
 * Nur für Redaktion und Administration (edit_others_posts), nie für Partner.
 */
if (!defined('ABSPATH')) { exit; }

const MA_RELEVANZ_STANDARD = 2;

/** Zentrale Einstellungen der Platzierung. */
function ma_relevanz_einstellung(): array {
    $e = function_exists('get_option') ? get_option('ma_relevanz_einstellung', []) : [];
    $e = is_array($e) ? $e : [];
    return [
        'frisch_stunden' => max(6, min(336, (int) ($e['frisch_stunden'] ?? 72))),
        'abklingen' => max(0.0, min(5.0, (float) ($e['abklingen'] ?? 1.0))),
        // Feste Plätze ohne „Hervorhebung bis“ enden nach so vielen Tagen (1.27.2); 0 = unbefristet.
        'fest_tage' => max(0, min(60, (int) ($e['fest_tage'] ?? 7))),
    ];
}
if (!defined('MA_RELEVANZ_FRISCH_STUNDEN')) define('MA_RELEVANZ_FRISCH_STUNDEN', 72);

/** Die drei Stufen: Bedeutung und Platz auf der Startseite. */
function ma_relevanz_stufen(): array {
    return [
        ['von' => 1, 'bis' => 1, 'name' => 'Hoch', 'wo' => 'Aufmacher oder Bühne, solange frisch; danach oben in den Blöcken der Startseite'],
        ['von' => 2, 'bis' => 2, 'name' => 'Mitte', 'wo' => 'Blöcke der Startseite'],
        ['von' => 3, 'bis' => 3, 'name' => 'Niedrig', 'wo' => 'Weiter unten in den Blöcken der Startseite'],
    ];
}

function ma_relevanz_stufe(int $r): array {
    foreach (ma_relevanz_stufen() as $s) if ($r >= $s['von'] && $r <= $s['bis']) return $s;
    return ma_relevanz_stufe(MA_RELEVANZ_STANDARD);
}

/** Gruppe für die Farbe der Auswahlknöpfe. */
function ma_relevanz_gruppe(int $r): string {
    return $r === 1 ? 'hoch' : ($r === 3 ? 'niedrig' : 'mitte');
}

/** Stufe 1–3; alles andere ist „ohne Angabe“ (0). */
function ma_relevanz_saeubern($v): int {
    $r = (int) $v;
    return $r >= 1 && $r <= 3 ? $r : 0;
}

/** Rechenpunkte einer Stufe (Sortierwert, Abklingen). */
function ma_relevanz_punkte(int $stufe): float {
    return [1 => 9.0, 2 => 6.0, 3 => 2.0][$stufe] ?? 6.0;
}

/** Alte Relevanz 1–10 als Stufe: 8–10 → 1, 4–7 → 2, 1–3 → 3 (0 = ohne Angabe). */
function ma_relevanz_alt_zu_stufe(int $alt): int {
    if ($alt >= 8 && $alt <= 10) return 1;
    if ($alt >= 4 && $alt <= 7) return 2;
    if ($alt >= 1 && $alt <= 3) return 3;
    return 0;
}

/** Stufe eines Beitrags; ohne Angabe MA_RELEVANZ_STANDARD (Mitte). */
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

/**
 * Sortierwert: Punkte der Stufe minus Alter (Tage, auf volle Stunden) mal Abklingen.
 * Stabil zwischen zwei Aufrufen derselben Stunde, ändert sich nur mit der Zeit
 * oder einer neuen Stufe.
 */
function ma_relevanz_wert($post, ?int $jetzt = null): float {
    $p = $post instanceof WP_Post ? $post : get_post((int) $post);
    if (!$p) return 0.0;
    $stunden = intdiv(max(0, ($jetzt ?? time()) - (int) get_post_time('U', true, $p)), HOUR_IN_SECONDS);
    return ma_relevanz_punkte(ma_relevanz($p)) - ($stunden / 24) * ma_relevanz_einstellung()['abklingen'];
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

/** Platz-Zone: Stufe 1 und frisch = Aufmacher- und Bühnen-Kandidat (hero), sonst nach Stufe. */
function ma_relevanz_zone($post, ?int $jetzt = null): string {
    if (!ma_relevanz_startseite($post)) return 'aus';
    $r = ma_relevanz($post);
    if ($r === 1) return ma_relevanz_frisch($post, $jetzt) ? 'hero' : 'oben';
    return $r === 3 ? 'unten' : 'feed';
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
 * Einmalige Umstellung auf drei Stufen (Plugin 1.32.0): rechnet jede
 * gespeicherte Relevanz 1–10 um (8–10 → 1, 4–7 → 2, 1–3 → 3). Läuft genau
 * einmal (Option ma_relevanz_stufen = '3'), danach bedeuten 1–3 die neuen
 * Stufen und werden nie wieder angefasst. Ersetzt die frühere Umstellung
 * von 1.11, die Relevanz 1–3 auf „Nur in der Rubrik“ setzte: nach der neuen
 * Skala wäre 1 die wichtigste Stufe.
 * Gibt die Zahl je neuer Stufe zurück (Protokoll in ma_relevanz_stufen_log).
 */
function ma_relevanz_stufen_umstellen(): array {
    $zahl = [1 => 0, 2 => 0, 3 => 0, 'ohne' => 0, 'fehler' => 0];
    if (get_option('ma_relevanz_stufen') === '3') return $zahl;
    // Nie im Namen eines Partners: Für Partner sperrt redaktion.php das Schreiben von
    // ma_relevanz still (Review 24.10.2026). Dann übernimmt der nächste Aufruf eines
    // Besuchers oder der Redaktion.
    if (function_exists('ma_current_partner_policy') && ma_current_partner_policy()) return $zahl;
    // Je Beitrag einmal: Erst den neuen Wert schreiben und nachlesen, dann den alten in
    // _ma_relevanz_alt sichern. Wer die Sicherung hat, ist umgerechnet und wird
    // übersprungen; so rechnet auch ein abgebrochener oder doppelt gestarteter Lauf
    // keinen Wert zweimal um. Alle Status, auch Papierkorb und eigene Status.
    $status = function_exists('get_post_stati') ? array_values(array_diff(get_post_stati(), ['auto-draft', 'inherit'])) : 'any';
    $ids = get_posts(['post_type' => 'post', 'post_status' => $status, 'posts_per_page' => -1, 'fields' => 'ids',
        'meta_query' => [['key' => 'ma_relevanz', 'compare' => 'EXISTS'], ['key' => '_ma_relevanz_alt', 'compare' => 'NOT EXISTS']]]);
    foreach ($ids as $id) {
        if (get_post_meta($id, '_ma_relevanz_alt', true) !== '') continue;
        $alt = (int) get_post_meta($id, 'ma_relevanz', true);
        $neu = ma_relevanz_alt_zu_stufe($alt);
        if ($neu) update_post_meta($id, 'ma_relevanz', $neu); else delete_post_meta($id, 'ma_relevanz');
        if ((int) get_post_meta($id, 'ma_relevanz', true) !== $neu) { $zahl['fehler']++; continue; }
        update_post_meta($id, '_ma_relevanz_alt', (string) $alt);
        $neu ? $zahl[$neu]++ : $zahl['ohne']++;
    }
    // Erst fertig, wenn jeder Beitrag umgerechnet ist; sonst versucht es der nächste Aufruf.
    if (!$zahl['fehler']) update_option('ma_relevanz_stufen', '3', true);
    update_option('ma_relevanz_stufen_log', ['t' => time(), 'zahl' => $zahl], false);
    return $zahl;
}
// Früh im Aufruf, damit auch die Startseite nie alte Werte als Stufen liest.
add_action('init', 'ma_relevanz_stufen_umstellen', 5);

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
    // Die frühere Priorität 1–10 (ma_prioritaet) ist seit 28.10.2026 nicht mehr registriert:
    // nirgends mehr auswählbar, auch nicht über die REST-Schnittstelle. Gespeicherte Werte bleiben unbenutzt liegen.
});

/** Auswahl der drei Stufen als Knopfreihe (Box im Beitrag und Freigabeseite). */
function ma_relevanz_auswahl(string $name, int $wert): string {
    $h = '<fieldset class="ma-relevanz" data-ma-relevanz><legend class="screen-reader-text">Relevanz</legend><div class="ma-relevanz__reihe">';
    foreach (ma_relevanz_stufen() as $s) {
        $i = $s['von'];
        $h .= sprintf('<label class="ma-relevanz__knopf ma-relevanz__knopf--%s" title="%s"><input type="radio" name="%s" value="%d"%s><span>%d · %s</span></label>',
            esc_attr(ma_relevanz_gruppe($i)), esc_attr($s['name'] . ': ' . $s['wo']),
            esc_attr($name), $i, checked($wert, $i, false), $i, esc_html($s['name']));
    }
    $s = ma_relevanz_stufe($wert ?: MA_RELEVANZ_STANDARD);
    $h .= '</div><p class="ma-relevanz__wo" data-ma-relevanz-wo><strong>' . esc_html($s['name']) . ':</strong> ' . esc_html($s['wo']) . '</p></fieldset>';
    return $h;
}

/** Stufen als JSON für die Live-Anzeige „landet in …“. */
function ma_relevanz_js_daten(): array {
    $m = [];
    foreach (ma_relevanz_stufen() as $s) $m[$s['von']] = ['name' => $s['name'], 'wo' => $s['wo']];
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
    return '.ma-relevanz{border:0;margin:0 0 8px;padding:0}.ma-relevanz__reihe{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:4px}'
        . '.ma-relevanz__knopf{position:relative;display:block;cursor:pointer}.ma-relevanz__knopf input{position:absolute;opacity:0;inset:0;margin:0;cursor:pointer}'
        . '.ma-relevanz__knopf span{display:block;text-align:center;padding:8px 4px;border:1px solid #c3c4c7;border-radius:3px;font-weight:600;font-variant-numeric:tabular-nums;background:#fff}'
        . '.ma-relevanz__knopf--niedrig span{background:#f6f7f7}.ma-relevanz__knopf--mitte span{background:#fff}.ma-relevanz__knopf--hoch span{background:#fbe3e4}'
        . '.ma-relevanz__knopf input:checked+span{background:#8c1c22;border-color:#8c1c22;color:#fff}.ma-relevanz__knopf input:focus-visible+span{outline:2px solid #2271b1;outline-offset:1px}'
        . '.ma-relevanz__wo{margin:6px 0 0;font-size:12px;color:#50575e}'
        . '.column-ma_relevanz{width:150px}.edit-php.post-type-post .wp-list-table .column-title{width:28%}.wp-list-table .column-ma_startplatz{width:130px}.wp-core-ui .wp-list-table select.ma-relevanz-liste{width:100%;max-width:100%;min-width:0}.ma-relevanz-ok{color:#008a20;margin-left:4px}'
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
      if(box.dataset.meldung==='1'&&!r){zeige('Bitte zuerst Hoch, Mitte oder Niedrig wählen.','fehler');return;}
      knoepfe(false);zeige(zeit?'Wird geplant …':'Wird freigegeben …');
      var st=box.querySelector('[data-ma-startseite]'),br=box.querySelector('[data-ma-bildrechte]');
      post({action:'ma_schnellfreigabe',post:box.dataset.post,relevanz:r?r.value:'0',startseite:st&&st.checked?'1':'0',bildrechte:br&&br.checked?'1':'0',zeit:zeit||''}).then(fertig).catch(fehler);
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
    printf('<select class="ma-relevanz-liste" data-post="%d" aria-label="Relevanz: Hoch, Mitte oder Niedrig"><option value="0"%s>–</option>', $id, selected($r, 0, false));
    foreach (ma_relevanz_stufen() as $s) printf('<option value="%d"%s>%d · %s</option>', $s['von'], selected($r, $s['von'], false), $s['von'], esc_html($s['name']));
    echo '</select>';
}, 10, 2);
add_filter('manage_edit-post_sortable_columns', function (array $c): array { $c['ma_relevanz'] = 'ma_relevanz'; return $c; });
// Sortieren nach Relevanz: includes/beitragsliste.php (auch Beiträge ohne Relevanz, Datum als zweites Kriterium).

add_action('wp_ajax_ma_relevanz_setzen', function (): void {
    check_ajax_referer('ma_relevanz', 'nonce');
    $id = (int) ($_POST['post'] ?? 0);
    if (!ma_relevanz_darf() || !$id || get_post_type($id) !== 'post' || !current_user_can('edit_post', $id)) wp_send_json_error(['meldung' => 'Keine Berechtigung.'], 403);
    $r = ma_relevanz_saeubern(wp_unslash($_POST['relevanz'] ?? 0));
    $vorher = ma_relevanz_saeubern(get_post_meta($id, 'ma_relevanz', true));
    if ($r) update_post_meta($id, 'ma_relevanz', $r); else delete_post_meta($id, 'ma_relevanz');
    if ($vorher !== $r && function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($id, 'Relevanz ' . ($r ? ma_relevanz_stufe($r)['name'] : 'ohne Angabe') . ($vorher ? ' (vorher ' . ma_relevanz_stufe($vorher)['name'] . ')' : ''));
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
function ma_schnellfreigabe(int $id, int $relevanz, bool $startseite, bool $bildrechte = false, string $zeit = ''): array {
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
    $relevanz = ma_relevanz_saeubern($relevanz);
    if ($typ === 'post' && !$relevanz) return ['ok' => false, 'meldung' => 'Bitte eine Relevanz wählen: Hoch, Mitte oder Niedrig.'];
    // Bildrechte bestaetigt die Redaktion nur ausdruecklich (Haken in der Zeile).
    if ($bildrechte) update_post_meta($id, 'ma_image_rights_verified', '1');
    if ($typ === 'post') {
        foreach (['ma_source_verified', 'ma_date_verified', 'ma_place_verified', 'ma_human_reviewed'] as $k) update_post_meta($id, $k, '1');
        update_post_meta($id, 'ma_reviewed_by', $wer);
        update_post_meta($id, 'ma_reviewed_at', current_time('mysql'));
        $vorher = ma_relevanz_saeubern(get_post_meta($id, 'ma_relevanz', true));
        update_post_meta($id, 'ma_relevanz', $relevanz);
        // Zweite, eigene Entscheidung: Startseite ja = nach Relevanz (ein fester Platz bleibt), nein = nur Rubrik.
        ma_startseite_freigabe_setzen($id, $startseite, 'Freigabe');
        if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($id, 'Relevanz ' . ma_relevanz_stufe($relevanz)['name'] . ($vorher && $vorher !== $relevanz ? ' (vorher ' . ma_relevanz_stufe($vorher)['name'] . ')' : ''));
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
        return ['ok' => true, 'meldung' => $plan . 'Anzeige läuft auf „' . (function_exists('ma_ad_slot_label') ? implode('“, „', array_map('ma_ad_slot_label', function_exists('ma_ad_plaetze') ? ma_ad_plaetze($id) : [(string) get_post_meta($id, 'ma_ad_slot', true)])) : '') . '“' . ($von !== '' || $bis !== '' ? ' (' . ($von !== '' ? 'ab ' . mysql2date('d.m.Y H:i', $von) : '') . ($von !== '' && $bis !== '' ? ', ' : '') . ($bis !== '' ? 'bis ' . mysql2date('d.m.Y H:i', $bis) : '') . ')' : '') . '.' . (get_option('ma_ads_enabled') ? '' : ' Hinweis: Werbung ist unter Werbung → Werbeplätze global ausgeschaltet.')];
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
    $e = ma_schnellfreigabe($id, ma_relevanz_saeubern(wp_unslash($_POST['relevanz'] ?? 0)), ($_POST['startseite'] ?? '') === '1', ($_POST['bildrechte'] ?? '') === '1', str_replace('T', ' ', $zeit));
    $e['ok'] ? wp_send_json_success($e) : wp_send_json_error($e);
});

/** Inhaltsarten der Freigabeseite (03.10.2026: auch Immobilien, Stellen, Trauer- und Familienanzeigen). */
function ma_freigabe_typen(): array {
    return ['post', 'ma_event', 'ma_club', 'ma_business', 'ma_tip', 'ma_ad', 'ma_property', 'ma_job', 'ma_obituary', 'ma_family_notice'];
}

/** Meldungen und (seit 1.21.0) recherchierte Termine aus dem Abgleich (abgleich.php), die als Entwurf auf die Freigabe warten. */
function ma_freigaben_abgleich(bool $nur_ids = false): array {
    return get_posts(['post_type' => ['post', 'ma_event'], 'post_status' => 'draft', 'posts_per_page' => 100, 'orderby' => 'date', 'order' => 'DESC', 'fields' => $nur_ids ? 'ids' : 'all',
        'meta_query' => [['key' => '_ma_abgleich_hash', 'compare' => 'EXISTS']]]);
}

/** Wartende Inhalte: eingereicht oder in Prüfung, Partner-Entwürfe mit fehlender Fotoerlaubnis und (seit 1.20.4) die Entwürfe aus dem Abgleich. */
function ma_freigaben_wartend(): array {
    $typen = ma_freigabe_typen();
    $l = get_posts(['post_type' => $typen, 'post_status' => ['pending', 'ma_in_pruefung'], 'posts_per_page' => 100, 'orderby' => 'date', 'order' => 'ASC']);
    $entwuerfe = get_posts(['post_type' => 'post', 'post_status' => 'draft', 'posts_per_page' => 40, 'orderby' => 'modified', 'order' => 'DESC',
        'meta_query' => [['key' => '_ma_partner_rights_missing', 'value' => '1']]]);
    $gesehen = [];
    return array_values(array_filter(array_merge($l, $entwuerfe, ma_freigaben_abgleich()), function ($p) use (&$gesehen) { if (isset($gesehen[$p->ID])) return false; return $gesehen[$p->ID] = true; }));
}

add_action('admin_menu', function (): void {
    $n = count(get_posts(['post_type' => ma_freigabe_typen(), 'post_status' => 'pending', 'posts_per_page' => 99, 'fields' => 'ids'])) + count(ma_freigaben_abgleich(true));
    $titel = 'Freigaben' . ($n ? ' <span class="awaiting-mod count-' . $n . '"><span class="pending-count">' . $n . '</span></span>' : '');
    add_submenu_page('merzenich-aktuell', 'Freigaben', $titel, 'edit_others_posts', 'ma-freigaben', 'ma_freigaben_seite', 0);
}, 20);

add_action('admin_post_ma_relevanz_einstellung', function (): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.', 403);
    check_admin_referer('ma_relevanz_einstellung');
    $alt = ma_relevanz_einstellung();
    update_option('ma_relevanz_einstellung', [
        'frisch_stunden' => (int) ($_POST['frisch_stunden'] ?? 72), 'abklingen' => (float) str_replace(',', '.', (string) ($_POST['abklingen'] ?? '1')),
        'fest_tage' => (int) ($_POST['fest_tage'] ?? 7),
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
    echo '<p>Eingereichte Meldungen, Termine, Vereins- und Unternehmensprofile, Anzeigen, Immobilien, Stellen, Trauer- und Familienanzeigen sowie Änderungsvorschläge. Für Meldungen triffst du zwei getrennte Entscheidungen: <strong>veröffentlichen</strong> und, eigens, <strong>Startseite ja oder nein</strong>; dazu die <strong>Relevanz: 1 Hoch, 2 Mitte oder 3 Niedrig</strong>. Dann <strong>Freigeben</strong> (sofort) oder <strong>Planen</strong> (Zeitpunkt). Alternativ: in Prüfung nehmen, Änderungen anfordern oder ablehnen; der Einsender bekommt jeweils eine E-Mail.</p>';
    $abgleich = count(ma_freigaben_abgleich(true));
    if ($abgleich) echo '<div class="notice notice-info inline"><p><strong>' . $abgleich . ($abgleich === 1 ? ' neue Meldung' : ' neue Meldungen') . ' aus dem <a href="' . esc_url(admin_url('admin.php?page=ma-abgleich')) . '">Abgleich</a></strong> warten unten als Entwurf. Relevanz (Hoch, Mitte, Niedrig) wählen, Startseite ja oder nein, „Freigeben“: dann ist die Meldung online und steht in der News-Sitemap für Google.</p></div>';
    if (!$wartend) echo '<p><strong>Keine Einreichungen warten.</strong></p>';
    else {
        echo '<table class="widefat striped ma-freigaben"><thead><tr><th style="width:130px">Bild</th><th>Einreichung</th><th>Von</th><th class="ma-freigaben__aktion">Entscheidung</th></tr></thead><tbody>';
        foreach ($wartend as $p) echo ma_freigaben_zeile($p);
        echo '</tbody></table>';
    }
    echo '<h2 id="stufen" style="margin-top:28px">Die drei Stufen</h2><table class="ma-relevanz-stufen"><thead><tr><th>Relevanz</th><th>Bedeutung</th><th>Platz auf der Startseite (mit Startseiten-Freigabe)</th></tr></thead><tbody>';
    foreach (ma_relevanz_stufen() as $s) printf('<tr><td>%d</td><td><strong>%s</strong></td><td>%s</td></tr>', $s['von'], esc_html($s['name']), esc_html($s['wo']));
    printf('</tbody></table><p class="description">Ohne Startseiten-Freigabe steht eine Meldung nur in ihrer Rubrik, auch mit Stufe 1. Ohne Angabe gilt Stufe 2 (Mitte). „Frisch“ heißt jünger als %d Stunden; Aufmacher und Bühne bekommen nur frische Meldungen der Stufe 1. Sortiert wird nach Stufe (Hoch 9, Mitte 6, Niedrig 2 Punkte) minus %s Punkt(e) je Tag Alter; bei gleichem Wert steht die neuere Meldung vorn. Ein fester Platz unter <a href="%s">Startseite</a> geht immer vor; ohne „Hervorhebung bis“ endet er %s, dann übernimmt wieder die Automatik.</p>',
        $e['frisch_stunden'], esc_html(number_format_i18n($e['abklingen'], 1)), esc_url(admin_url('admin.php?page=ma-startseite')),
        $e['fest_tage'] > 0 ? 'nach ' . $e['fest_tage'] . ' Tagen' : 'nie (unbefristet)');
    if (current_user_can('manage_options')) {
        echo '<h2 id="einstellung">Einstellungen der Platzierung</h2><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" class="ma-relevanz-einstellung"><input type="hidden" name="action" value="ma_relevanz_einstellung">';
        wp_nonce_field('ma_relevanz_einstellung');
        printf('<label>Frisch für Aufmacher und Bühne (Stunden) <input type="number" name="frisch_stunden" min="6" max="336" value="%d"></label>', $e['frisch_stunden']);
        printf('<label>Abklingen je Tag Alter (Punkte) <input type="number" name="abklingen" min="0" max="5" step="0.1" value="%s"></label>', esc_attr((string) $e['abklingen']));
        printf('<label>Feste Plätze enden nach (Tage, 0 = unbefristet) <input type="number" name="fest_tage" min="0" max="60" value="%d"></label>', $e['fest_tage']);
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
    if ($istMeldung && get_post_meta($p->ID, '_ma_abgleich_hash', true) !== '') $typen['post'] = 'Meldung aus dem Abgleich';
    $kats = $istMeldung ? implode(', ', array_map(fn($t) => $t->name, get_the_category($p->ID))) : '';
    if ($p->post_type === 'ma_ad') {
        $slot = (string) get_post_meta($p->ID, 'ma_ad_slot', true); $sponsor = (string) get_post_meta($p->ID, 'ma_ad_sponsor', true);
        $von = (string) get_post_meta($p->ID, 'ma_ad_start', true); $bis = (string) get_post_meta($p->ID, 'ma_ad_end', true);
        $kats = ($slot !== '' && function_exists('ma_ad_slot_label') ? implode(', ', array_map('ma_ad_slot_label', function_exists('ma_ad_plaetze') ? ma_ad_plaetze($p->ID) : [$slot])) : 'ohne Werbeplatz') . ($sponsor !== '' ? ' · ' . $sponsor : '') . ($von !== '' ? ' · ab ' . mysql2date('d.m.Y', $von) : '') . ($bis !== '' ? ' · bis ' . mysql2date('d.m.Y', $bis) : '');
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
    if ($istMeldung && !$von) $h .= ma_relevanz_auswahl('ma_relevanz_' . $p->ID, $r) . '<div class="ma-freigaben__zeile"><label><input type="checkbox" data-ma-startseite' . ($startJa ? ' checked' : '') . '> Startseiten-Freigabe: auf die Startseite</label>';
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
