<?php
/**
 * Relevanz 1–10 und Freigabe mit Startseiten-Wahl (30.09.2026).
 *
 * Wunsch Betreiber und Ordin: Wer im Backend eine Einreichung annimmt, wählt
 * gleich mit, ob sie auf die Startseite darf, und ordnet sie schnell mit einer
 * Zahl von 1 bis 10 ein. Die Zahl entscheidet nach festen Stufen, wo die
 * Meldung erscheint (ma_relevanz_stufen()). Das Theme liest dieselbe Tabelle
 * (ma21_startseite_belegung()).
 *
 *   9–10  Top-Thema   Aufmacher oben, solange frisch
 *   7–8   Wichtig     Bühne neben und unter dem Aufmacher, solange frisch
 *   4–6   Normal      Startseite in der Fläche ihrer Rubrik
 *   1–3   Nur Rubrik  nicht auf der Startseite
 *
 * „Frisch“ heißt: jünger als MA_RELEVANZ_FRISCH_STUNDEN. Danach zählt eine
 * Meldung wie „Normal“ und rückt aus Aufmacher und Bühne. Ohne Angabe gilt 5.
 * Ein fester Startplatz (Box „Startseite“) geht der Relevanz immer vor.
 *
 * Bedienung:
 *  - Merzenich Aktuell → Freigaben: alle wartenden Einreichungen mit Bild,
 *    Absender, Relevanz, Haken „Auf die Startseite“ und Knopf „Freigeben“.
 *  - Beiträge → Alle Beiträge: Spalte „Relevanz“ mit Auswahl, speichert sofort.
 *  - Im Beitrag: Box „Startseite“ mit Relevanz und Platz.
 * Nur für Redaktion und Administration (edit_others_posts), nie für Partner.
 */
if (!defined('ABSPATH')) { exit; }

if (!defined('MA_RELEVANZ_FRISCH_STUNDEN')) define('MA_RELEVANZ_FRISCH_STUNDEN', 72);
const MA_RELEVANZ_STANDARD = 5;

/** Die vordefinierten Stufen: von, bis, Name, wohin. */
function ma_relevanz_stufen(): array {
    return [
        ['von' => 9, 'bis' => 10, 'name' => 'Top-Thema', 'wo' => 'Aufmacher oben auf der Startseite (solange frisch)'],
        ['von' => 7, 'bis' => 8, 'name' => 'Wichtig', 'wo' => 'Bühne neben und unter dem Aufmacher (solange frisch)'],
        ['von' => 4, 'bis' => 6, 'name' => 'Normal', 'wo' => 'Startseite in der Fläche der eigenen Rubrik'],
        ['von' => 1, 'bis' => 3, 'name' => 'Nur Rubrik', 'wo' => 'nur in der eigenen Rubrik, nicht auf der Startseite'],
    ];
}

function ma_relevanz_stufe(int $r): array {
    foreach (ma_relevanz_stufen() as $s) if ($r >= $s['von'] && $r <= $s['bis']) return $s;
    return ma_relevanz_stufen()[2];
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
    return ($jetzt ?? time()) - $zeit <= MA_RELEVANZ_FRISCH_STUNDEN * HOUR_IN_SECONDS;
}

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
});

/** Auswahl 1–10 als Knopfreihe (Box im Beitrag und Freigabeseite). */
function ma_relevanz_auswahl(string $name, int $wert): string {
    $h = '<fieldset class="ma-relevanz" data-ma-relevanz><legend class="screen-reader-text">Relevanz</legend><div class="ma-relevanz__reihe">';
    for ($i = 1; $i <= 10; $i++) {
        $h .= sprintf('<label class="ma-relevanz__knopf ma-relevanz__knopf--%s" title="%s"><input type="radio" name="%s" value="%d"%s><span>%d</span></label>',
            esc_attr(sanitize_title(ma_relevanz_stufe($i)['name'])), esc_attr(ma_relevanz_stufe($i)['name'] . ': ' . ma_relevanz_stufe($i)['wo']),
            esc_attr($name), $i, checked($wert, $i, false), $i);
    }
    $s = ma_relevanz_stufe($wert ?: MA_RELEVANZ_STANDARD);
    $h .= '</div><p class="ma-relevanz__wo" data-ma-relevanz-wo><strong>' . esc_html($s['name']) . ':</strong> ' . esc_html($s['wo']) . '</p></fieldset>';
    return $h;
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
        . '.ma-relevanz__knopf--nur-rubrik span{background:#f6f7f7}.ma-relevanz__knopf--wichtig span{background:#fcf0e3}.ma-relevanz__knopf--top-thema span{background:#fbe3e4}'
        . '.ma-relevanz__knopf input:checked+span{background:#8c1c22;border-color:#8c1c22;color:#fff}.ma-relevanz__knopf input:focus-visible+span{outline:2px solid #2271b1;outline-offset:1px}'
        . '.ma-relevanz__wo{margin:6px 0 0;font-size:12px;color:#50575e}'
        . '.column-ma_relevanz{width:88px}.ma-relevanz-liste{max-width:84px}.ma-relevanz-ok{color:#008a20;margin-left:4px}'
        . '.ma-freigaben td{vertical-align:top}.ma-freigaben__bild{width:120px;height:68px;object-fit:cover;background:#f0f0f1;display:block}'
        . '.ma-freigaben__aktion{min-width:320px}.ma-freigaben__zeile{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:8px}'
        . '.ma-freigaben__status{font-weight:600}.ma-freigaben__status.ist-fehler{color:#b32d2e}.ma-freigaben__status.ist-ok{color:#008a20}'
        . '.ma-relevanz-stufen{border-collapse:collapse;margin:8px 0 18px}.ma-relevanz-stufen td,.ma-relevanz-stufen th{padding:4px 12px 4px 0;text-align:left}';
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
  // Freigabeseite: Freigeben mit Relevanz und Startseiten-Wahl.
  document.querySelectorAll('[data-ma-freigabe]').forEach(function(box){
    var knopf=box.querySelector('[data-ma-freigeben]'),status=box.querySelector('.ma-freigaben__status');
    knopf.addEventListener('click',function(){
      var r=box.querySelector('input[type=radio]:checked');
      if(!r){status.textContent='Bitte zuerst eine Relevanz von 1 bis 10 wählen.';status.className='ma-freigaben__status ist-fehler';return;}
      knopf.disabled=true;status.textContent='Wird freigegeben …';status.className='ma-freigaben__status';
      var br=box.querySelector('[data-ma-bildrechte]');post({action:'ma_schnellfreigabe',post:box.dataset.post,relevanz:r.value,startseite:box.querySelector('[data-ma-startseite]').checked?'1':'0',bildrechte:br&&br.checked?'1':'0'}).then(function(j){
        if(j&&j.success){status.textContent=j.data.meldung;status.className='ma-freigaben__status ist-ok';box.closest('tr').style.opacity='.55';}
        else{status.textContent=(j&&j.data&&j.data.meldung)||'Freigabe fehlgeschlagen.';status.className='ma-freigaben__status ist-fehler';knopf.disabled=false;}
      }).catch(function(){status.textContent='Keine Verbindung. Bitte erneut versuchen.';status.className='ma-freigaben__status ist-fehler';knopf.disabled=false;});
    });
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
    if ($r) update_post_meta($id, 'ma_relevanz', $r); else delete_post_meta($id, 'ma_relevanz');
    $s = ma_relevanz_stufe($r ?: MA_RELEVANZ_STANDARD);
    wp_send_json_success(['relevanz' => $r, 'wo' => $s['name'] . ': ' . $s['wo']]);
});

/* ---------------------------------------------------------- Freigeben mit Relevanz und Startseite */

/**
 * Nimmt eine Einreichung an: redaktionelle Prüfhaken mit Prüfer und Zeit,
 * Relevanz, Startseite ja/nein, veröffentlichen. Die Sperren (Bildrechte,
 * Pflichtangaben) bleiben wirksam; dann bleibt der Beitrag Entwurf und die
 * Antwort nennt den Grund.
 */
function ma_schnellfreigabe(int $id, int $relevanz, bool $startseite, bool $bildrechte = false): array {
    if (get_post_type($id) !== 'post') return ['ok' => false, 'meldung' => 'Nur Beiträge lassen sich hier freigeben.'];
    if (!$relevanz) return ['ok' => false, 'meldung' => 'Bitte eine Relevanz von 1 bis 10 wählen.'];
    $wer = wp_get_current_user()->display_name;
    foreach (['ma_source_verified', 'ma_date_verified', 'ma_place_verified', 'ma_human_reviewed'] as $k) update_post_meta($id, $k, '1');
    update_post_meta($id, 'ma_reviewed_by', $wer);
    update_post_meta($id, 'ma_reviewed_at', current_time('mysql'));
    update_post_meta($id, 'ma_relevanz', $relevanz);
    // Bildrechte bestaetigt die Redaktion nur ausdruecklich (Haken in der Zeile).
    if ($bildrechte) update_post_meta($id, 'ma_image_rights_verified', '1');
    // Startseite nein = nur Rubrik. Ja = nach Relevanz, ein fester Platz bleibt.
    $platz = (string) get_post_meta($id, 'ma_startplatz', true);
    if (!$startseite) update_post_meta($id, 'ma_startplatz', 'aus');
    elseif ($platz === '' || $platz === 'aus') update_post_meta($id, 'ma_startplatz', 'auto');
    if (get_post_status($id) !== 'publish') wp_update_post(['ID' => $id, 'post_status' => 'publish']);
    if (!in_array(get_post_status($id), ['publish', 'future'], true)) {
        $grund = (string) get_post_meta($id, '_ma_gate_reason', true);
        return ['ok' => false, 'meldung' => 'Nicht veröffentlicht: ' . ($grund !== '' ? $grund : 'eine Pflichtangabe fehlt') . '. Bitte im Beitrag prüfen.'];
    }
    $s = ma_relevanz_stufe($relevanz);
    $wo = $startseite ? ($relevanz <= 3 ? 'nur in der Rubrik (Relevanz ' . $relevanz . ')' : $s['wo']) : 'nur in der Rubrik';
    return ['ok' => true, 'meldung' => 'Veröffentlicht. Steht: ' . $wo . '.'];
}

add_action('wp_ajax_ma_schnellfreigabe', function (): void {
    check_ajax_referer('ma_relevanz', 'nonce');
    $id = (int) ($_POST['post'] ?? 0);
    if (!ma_relevanz_darf() || !$id || !current_user_can('edit_post', $id) || !current_user_can('publish_posts')) wp_send_json_error(['meldung' => 'Keine Berechtigung.'], 403);
    $e = ma_schnellfreigabe($id, ma_relevanz_saeubern(wp_unslash($_POST['relevanz'] ?? 0)), ($_POST['startseite'] ?? '') === '1', ($_POST['bildrechte'] ?? '') === '1');
    $e['ok'] ? wp_send_json_success($e) : wp_send_json_error($e);
});

add_action('admin_menu', function (): void {
    $n = count(get_posts(['post_type' => 'post', 'post_status' => 'pending', 'posts_per_page' => 99, 'fields' => 'ids']));
    $titel = 'Freigaben' . ($n ? ' <span class="awaiting-mod count-' . $n . '"><span class="pending-count">' . $n . '</span></span>' : '');
    add_submenu_page('merzenich-aktuell', 'Freigaben', $titel, 'edit_others_posts', 'ma-freigaben', 'ma_freigaben_seite', 0);
}, 20);

function ma_freigaben_seite(): void {
    if (!ma_relevanz_darf()) wp_die('Keine Berechtigung.');
    $wartend = get_posts(['post_type' => 'post', 'post_status' => ['pending', 'draft'], 'posts_per_page' => 60, 'orderby' => 'modified', 'order' => 'DESC',
        'meta_query' => [['key' => '_ma_partner_submission', 'compare' => 'EXISTS']]]);
    $wartend = array_merge(get_posts(['post_type' => 'post', 'post_status' => 'pending', 'posts_per_page' => 60, 'orderby' => 'date', 'order' => 'DESC']),
        array_filter($wartend, fn($p) => $p->post_status === 'draft'));
    $gesehen = []; $wartend = array_values(array_filter($wartend, function ($p) use (&$gesehen) { if (isset($gesehen[$p->ID])) return false; return $gesehen[$p->ID] = true; }));
    echo '<div class="wrap"><h1>Freigaben</h1><p>Hier nimmst du Einreichungen an. Wähle die <strong>Relevanz von 1 bis 10</strong> und ob die Meldung <strong>auf die Startseite</strong> darf, dann „Freigeben“. Die Relevanz legt fest, wo sie steht:</p>';
    echo '<table class="ma-relevanz-stufen"><thead><tr><th>Relevanz</th><th>Stufe</th><th>Wo die Meldung steht</th></tr></thead><tbody>';
    foreach (ma_relevanz_stufen() as $s) printf('<tr><td>%d–%d</td><td><strong>%s</strong></td><td>%s</td></tr>', $s['von'], $s['bis'], esc_html($s['name']), esc_html($s['wo']));
    printf('</tbody></table><p class="description">„Frisch“ heißt jünger als %d Stunden; danach rückt eine Meldung aus Aufmacher und Bühne und steht wie „Normal“. Ohne Angabe gilt %d. Ein fester Platz unter <a href="%s">Startseite</a> geht immer vor.</p>', MA_RELEVANZ_FRISCH_STUNDEN, MA_RELEVANZ_STANDARD, esc_url(admin_url('admin.php?page=ma-startseite')));
    if (!$wartend) { echo '<p><strong>Keine Einreichungen warten.</strong></p></div>'; return; }
    echo '<table class="widefat striped ma-freigaben"><thead><tr><th style="width:130px">Bild</th><th>Meldung</th><th>Von</th><th class="ma-freigaben__aktion">Relevanz und Startseite</th></tr></thead><tbody>';
    foreach ($wartend as $p) {
        $autor = get_userdata((int) $p->post_author);
        $rolle = $autor && function_exists('ma_current_partner_policy') ? (ma_current_partner_policy($autor)['label'] ?? '') : '';
        $bild = get_the_post_thumbnail_url($p, 'medium');
        $rechte = (string) get_post_meta($p->ID, 'ma_partner_rights_declared', true) === '1';
        $kats = implode(', ', array_map(fn($t) => $t->name, get_the_category($p->ID)));
        $r = ma_relevanz_saeubern(get_post_meta($p->ID, 'ma_relevanz', true));
        $aus = (string) get_post_meta($p->ID, 'ma_startplatz', true) === 'aus';
        echo '<tr><td>' . ($bild ? '<img class="ma-freigaben__bild" src="' . esc_url($bild) . '" alt="">' : '<span class="description">kein Bild</span>') . '</td>';
        printf('<td><strong><a href="%s">%s</a></strong><br><span class="description">%s · %s · %s</span>%s</td>',
            esc_url(get_edit_post_link($p->ID)), esc_html(get_the_title($p) ?: '(ohne Titel)'), esc_html($kats ?: 'ohne Rubrik'),
            esc_html($p->post_status === 'pending' ? 'wartet auf Freigabe' : 'Entwurf'), esc_html(get_the_modified_date('d.m.Y H:i', $p)),
            $bild ? '<br><span class="description">Bildrechte des Einsenders: ' . ($rechte ? 'bestätigt' : '<strong>nicht bestätigt</strong>') . '</span>' : '');
        printf('<td>%s<br><span class="description">%s</span></td>', esc_html($autor ? ($autor->display_name ?: $autor->user_login) : '–'), esc_html($rolle ?: 'Redaktion'));
        echo '<td class="ma-freigaben__aktion"><div data-ma-freigabe data-post="' . (int) $p->ID . '">' . ma_relevanz_auswahl('ma_relevanz_' . $p->ID, $r)
            . '<div class="ma-freigaben__zeile"><label><input type="checkbox" data-ma-startseite' . ($aus ? '' : ' checked') . '> Auf die Startseite</label>'
            . ($bild ? '<label><input type="checkbox" data-ma-bildrechte' . ((string) get_post_meta($p->ID, 'ma_image_rights_verified', true) === '1' ? ' checked' : '') . '> Bildrechte geprüft</label>' : '')
            . '<button type="button" class="button button-primary" data-ma-freigeben>Freigeben</button> <a class="button" href="' . esc_url(get_edit_post_link($p->ID)) . '">Bearbeiten</a></div>'
            . '<p class="ma-freigaben__status" role="status" aria-live="polite"></p></div></td></tr>';
    }
    echo '</tbody></table></div>';
}
