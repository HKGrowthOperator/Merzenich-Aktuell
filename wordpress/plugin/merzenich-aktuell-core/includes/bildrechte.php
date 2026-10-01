<?php
/**
 * Bestätigung der Bild- und Nutzungsrechte (Version 2, 01.10.2026).
 *
 * Wer als Partner (Verein, Polizei, Feuerwehr, Unternehmen …) einen Beitrag mit
 * Bildern zur Prüfung einreicht, bestätigt unmittelbar davor ausdrücklich die
 * Erklärung ma_bildrechte_text(). Die Bestätigung gilt nur für die Medien, die
 * in diesem Moment im Beitrag stecken. Kommt später ein neues Bild dazu, ist
 * beim erneuten Einreichen eine neue Bestätigung nötig; ein altes Häkchen gilt
 * nicht für künftige Uploads.
 *
 * Durchsetzung:
 *  - Block-Editor: Kasten „Bild- und Nutzungsrechte“ in der Seitenleiste und vor
 *    dem Einreichen. Ohne Haken bei unbestätigten Bildern ist „Zur Prüfung
 *    einreichen“ gesperrt (lockPostSaving). Der Haken ist nie vorausgewählt.
 *  - Klassischer Editor: Haken im Kasten „Bildherkunft“.
 *  - Server: nach jedem Speichern (wp_after_insert_post, auch über REST) wird
 *    geprüft, ob alle Medien bestätigt sind; sonst bleibt der Beitrag Entwurf.
 *
 * Nachweis je Bestätigung (_ma_bildrechte_log am Beitrag, _ma_bildrechte am
 * Medium): Person, Beitrag, Medien-IDs, Zeit, Version und Prüfsumme des Textes.
 * Die Bestätigung ersetzt nicht die redaktionelle Prüfung („Bildrechte geprüft“).
 */
if (!defined('ABSPATH')) { exit; }

const MA_BILDRECHTE_VERSION = '2';
const MA_BILDRECHTE_LOG = '_ma_bildrechte_log';

function ma_bildrechte_text(): string {
    return 'Ich bestätige, dass ich für sämtliche von mir mit diesem Beitrag hochgeladenen Bilder, Fotografien und sonstigen Medien über die erforderlichen Rechte beziehungsweise Nutzungserlaubnisse verfüge. '
        . 'Ich bin berechtigt, diese Inhalte Merzenich Aktuell zur Prüfung und im Falle einer redaktionellen Freigabe zur Veröffentlichung auf der Website zur Verfügung zu stellen. '
        . 'Soweit erforderlich, liegen mir die entsprechenden Einwilligungen abgebildeter Personen vor. Ich habe insbesondere darauf geachtet, dass keine Rechte Dritter, insbesondere Urheber-, Persönlichkeits- oder Markenrechte, verletzt werden. '
        . 'Mir ist bekannt, dass ich für die Richtigkeit meiner Angaben und die Berechtigung zur Einreichung der von mir bereitgestellten Inhalte verantwortlich bin.';
}

function ma_bildrechte_haken_text(): string { return 'Ich habe die Erklärung gelesen und bestätige sie ausdrücklich.'; }

/** Medien-IDs in einem Inhalt (Bild-, Galerie-, Medien-Text-, Cover-, Datei-Blöcke, klassische <img>). */
function ma_bildrechte_medien_aus_text(string $html): array {
    $ids = [];
    if (preg_match_all('/wp-image-(\d+)/', $html, $m)) $ids = array_merge($ids, $m[1]);
    if (preg_match_all('/<!-- wp:(?:image|media-text|cover|video|audio|file)\s+(\{[^}]*\})/', $html, $m)) {
        foreach ($m[1] as $json) { $a = json_decode($json, true); foreach (['id', 'mediaId'] as $k) if (!empty($a[$k])) $ids[] = $a[$k]; }
    }
    if (preg_match_all('/"ids":\[([\d,\s]+)\]/', $html, $m)) foreach ($m[1] as $l) $ids = array_merge($ids, explode(',', $l));
    if (preg_match_all('/data-id="(\d+)"/', $html, $m)) $ids = array_merge($ids, $m[1]);
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    sort($ids);
    return $ids;
}

/** Alle Medien eines Beitrags: Beitragsbild und Medien im Text. */
function ma_bildrechte_medien(int $post_id, ?string $inhalt = null, ?int $bild = null): array {
    $inhalt = $inhalt ?? (string) get_post_field('post_content', $post_id);
    $ids = ma_bildrechte_medien_aus_text($inhalt);
    $bild = $bild ?? (int) get_post_thumbnail_id($post_id);
    if ($bild > 0) $ids[] = $bild;
    $ids = array_values(array_unique(array_map('intval', $ids)));
    sort($ids);
    return $ids;
}

function ma_bildrechte_log(int $post_id): array {
    return array_values(array_filter((array) get_post_meta($post_id, MA_BILDRECHTE_LOG), 'is_array'));
}

/** Medien, für die eine Bestätigung in der aktuellen Textversion vorliegt. */
function ma_bildrechte_bestaetigt(int $post_id): array {
    $ids = [];
    foreach (ma_bildrechte_log($post_id) as $e) if (($e['version'] ?? '') === MA_BILDRECHTE_VERSION) $ids = array_merge($ids, (array) ($e['medien'] ?? []));
    return array_values(array_unique(array_map('intval', $ids)));
}

/** Noch nicht bestätigte Medien. */
function ma_bildrechte_offen(int $post_id, ?array $medien = null): array {
    return array_values(array_diff($medien ?? ma_bildrechte_medien($post_id), ma_bildrechte_bestaetigt($post_id)));
}

/** Bestätigung festhalten. */
function ma_bildrechte_eintragen(int $post_id, array $medien, ?int $user_id = null): array {
    $u = get_userdata($user_id ?? get_current_user_id());
    $medien = array_values(array_unique(array_filter(array_map('intval', $medien), fn($id) => $id > 0 && get_post_type($id) === 'attachment')));
    $e = ['t' => time(), 'u' => $u ? (int) $u->ID : 0, 'name' => $u ? ($u->display_name ?: $u->user_login) : '', 'medien' => $medien,
        'version' => MA_BILDRECHTE_VERSION, 'text_sha256' => hash('sha256', ma_bildrechte_text())];
    add_post_meta($post_id, MA_BILDRECHTE_LOG, $e);
    foreach ($medien as $m) add_post_meta($m, '_ma_bildrechte', ['post' => $post_id, 't' => $e['t'], 'u' => $e['u'], 'version' => MA_BILDRECHTE_VERSION]);
    // Alter Schalter, den Freigabeseite und Redaktion bisher lesen.
    update_post_meta($post_id, 'ma_partner_rights_declared', '1');
    update_post_meta($post_id, 'ma_partner_rights_declared_by', $e['name']);
    update_post_meta($post_id, 'ma_partner_rights_declared_at', wp_date('d.m.Y H:i', $e['t']));
    if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($post_id, 'Bild- und Nutzungsrechte bestätigt', count($medien) . ' Medium/Medien, Erklärung Version ' . MA_BILDRECHTE_VERSION, $e['u']);
    return $e;
}

/** Stand für Redaktion und Freigabeseite. */
function ma_bildrechte_stand(int $post_id): array {
    $medien = ma_bildrechte_medien($post_id);
    if (!$medien) return ['noetig' => false, 'ok' => true, 'text' => ''];
    $offen = ma_bildrechte_offen($post_id, $medien);
    $log = ma_bildrechte_log($post_id);
    $letzte = end($log) ?: null;
    // Beiträge der Redaktion selbst brauchen keine Partner-Erklärung.
    $autor = get_userdata((int) get_post_field('post_author', $post_id));
    $partner = $autor && function_exists('ma_current_partner_policy') && ma_current_partner_policy($autor);
    if (!$partner && !$log) return ['noetig' => false, 'ok' => true, 'text' => ''];
    return ['noetig' => true, 'ok' => !$offen, 'text' => $letzte ? ($letzte['name'] . ', ' . wp_date('d.m.Y H:i', (int) $letzte['t']) . ($offen ? ', ' . count($offen) . ' neue(s) Medium/Medien unbestätigt' : '')) : ''];
}

/* ---------------------------------------------------------- Server: nach dem Speichern prüfen */

/**
 * Partner reicht ein (pending): alle Medien müssen bestätigt sein. Kommt der
 * Haken aus dem klassischen Formular mit, wird er hier für die gespeicherten
 * Medien eingetragen. Sonst zurück auf Entwurf, mit Hinweis.
 */
add_action('wp_after_insert_post', function (int $id, WP_Post $p): void {
    if (!function_exists('ma_current_partner_policy') || !ma_current_partner_policy()) return;
    if (wp_is_post_revision($id) || in_array($p->post_status, ['auto-draft', 'trash', 'inherit'], true)) return;
    $medien = ma_bildrechte_medien($id);
    if ($medien && isset($_POST['ma_bildrechte_haken'], $_POST['ma_editorial_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_editorial_nonce'])), 'ma_editorial_save')) {
        ma_bildrechte_eintragen($id, $medien);
    }
    $offen = $medien ? ma_bildrechte_offen($id, $medien) : [];
    if ($p->post_status === 'pending' && $offen) {
        update_post_meta($id, '_ma_partner_rights_missing', '1');
        // Im Verlauf steht die angehaltene Einreichung, nicht „eingereicht“ und „Entwurf“.
        $l = function_exists('ma_verlauf') ? ma_verlauf($id) : [];
        $letzte = end($l);
        if ($letzte && str_contains((string) $letzte['a'], 'eingereicht') && time() - (int) $letzte['t'] < 30) { array_pop($l); update_post_meta($id, MA_VERLAUF_META, $l); }
        $GLOBALS['ma_verlauf_still'] = true;
        wp_update_post(['ID' => $id, 'post_status' => 'draft']);
        $GLOBALS['ma_verlauf_still'] = false;
        if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($id, 'Einreichung angehalten: Bild- und Nutzungsrechte nicht bestätigt', count($offen) . ' Medium/Medien ohne Bestätigung');
        return;
    }
    if (!$offen) delete_post_meta($id, '_ma_partner_rights_missing');
}, 20, 2);

/* Bestätigung aus dem Block-Editor (Haken im Kasten). */
add_action('wp_ajax_ma_bildrechte_bestaetigen', function (): void {
    check_ajax_referer('ma_bildrechte', 'nonce');
    $id = (int) ($_POST['post'] ?? 0);
    if (!$id || !current_user_can('edit_post', $id)) wp_send_json_error(['meldung' => 'Keine Berechtigung.'], 403);
    $medien = array_map('intval', array_filter(explode(',', (string) ($_POST['medien'] ?? ''))));
    // Nur Medien, die man selbst hochgeladen hat oder die schon am Beitrag hängen.
    $medien = array_values(array_filter($medien, fn($m) => get_post_type($m) === 'attachment' && ((int) get_post_field('post_author', $m) === get_current_user_id() || in_array($m, ma_bildrechte_medien($id), true))));
    if (!$medien) wp_send_json_error(['meldung' => 'Keine Bilder zu bestätigen.']);
    $e = ma_bildrechte_eintragen($id, $medien);
    wp_send_json_success(['bestaetigt' => ma_bildrechte_bestaetigt($id), 'zeit' => wp_date('d.m.Y H:i', $e['t'])]);
});

/* ---------------------------------------------------------- Block-Editor */

add_action('enqueue_block_editor_assets', function (): void {
    if (!function_exists('ma_current_partner_policy') || !ma_current_partner_policy()) return;
    $id = (int) get_the_ID();
    wp_register_script('ma-bildrechte', false, ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data'], MA_CORE_VERSION, true);
    wp_enqueue_script('ma-bildrechte');
    wp_add_inline_script('ma-bildrechte', 'window.maBildrechte=' . wp_json_encode([
        'ajax' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('ma_bildrechte'), 'post' => $id,
        'bestaetigt' => $id ? ma_bildrechte_bestaetigt($id) : [], 'text' => ma_bildrechte_text(), 'haken' => ma_bildrechte_haken_text(),
    ]) . ';' . ma_bildrechte_js());
});

function ma_bildrechte_js(): string {
    return <<<'JS'
(function(wp){
  if(!wp||!wp.plugins||!wp.element||!wp.data)return;
  var d=window.maBildrechte||{},h=wp.element.createElement,useState=wp.element.useState,useEffect=wp.element.useEffect,useSelect=wp.data.useSelect;
  var C=wp.components,EP=wp.editPost||{},ED=wp.editor||{};
  var DocPanel=ED.PluginDocumentSettingPanel||EP.PluginDocumentSettingPanel,PrePanel=ED.PluginPrePublishPanel||EP.PluginPrePublishPanel;
  var bestaetigt=(d.bestaetigt||[]).map(Number);
  function medienSammeln(bl,ids){(bl||[]).forEach(function(b){var a=b.attributes||{};['id','mediaId'].forEach(function(k){if(a[k])ids.push(Number(a[k]));});(a.ids||[]).forEach(function(x){ids.push(Number(x&&x.id?x.id:x));});medienSammeln(b.innerBlocks,ids);});return ids;}
  function useMedien(){return useSelect(function(s){var ids=medienSammeln(s('core/block-editor').getBlocks(),[]);var f=s('core/editor').getEditedPostAttribute('featured_media');if(f)ids.push(Number(f));return ids.filter(function(x,i,a){return x>0&&a.indexOf(x)===i;});},[]);}
  function Kasten(p){
    var medien=useMedien(),st=useState(false),haken=st[0],setHaken=st[1],ms=useState(''),meldung=ms[0],setMeldung=ms[1],bs=useState(bestaetigt),best=bs[0],setBest=bs[1];
    var offen=medien.filter(function(m){return best.indexOf(m)<0;});
    useEffect(function(){var e=wp.data.dispatch('core/editor');if(!p.sperren)return;if(offen.length)e.lockPostSaving('ma-bildrechte');else e.unlockPostSaving('ma-bildrechte');return function(){e.unlockPostSaving('ma-bildrechte');};},[offen.length,p.sperren]);
    if(!medien.length)return h('p',{},'Dieser Beitrag enthält keine Bilder. Eine Bestätigung ist nicht nötig.');
    if(!offen.length)return h('p',{},'Bild- und Nutzungsrechte für alle '+medien.length+' Medien bestätigt.');
    function aendern(v){setHaken(v);if(!v)return;setMeldung('Wird gespeichert …');
      var b=new URLSearchParams({action:'ma_bildrechte_bestaetigen',nonce:d.nonce,post:String(d.post||wp.data.select('core/editor').getCurrentPostId()),medien:offen.join(',')});
      fetch(d.ajax,{method:'POST',credentials:'same-origin',body:b}).then(function(r){return r.json();}).then(function(j){if(j&&j.success){setBest(j.data.bestaetigt.map(Number));setMeldung('Bestätigt am '+j.data.zeit+'.');}else{setHaken(false);setMeldung((j&&j.data&&j.data.meldung)||'Nicht gespeichert. Bitte Beitrag einmal als Entwurf speichern und erneut bestätigen.');}}).catch(function(){setHaken(false);setMeldung('Keine Verbindung.');});}
    return h('div',{className:'ma-bildrechte'},
      h('p',{},h('strong',{},'Bestätigung der Bild- und Nutzungsrechte')),
      h('p',{style:{fontSize:'12px',lineHeight:1.5}},d.text),
      h(C.CheckboxControl,{label:d.haken,checked:haken,onChange:aendern}),
      h('p',{style:{color:'#8a2424',fontSize:'12px'}},offen.length+' Medium/Medien noch nicht bestätigt. Ohne Bestätigung kann der Beitrag nicht eingereicht werden.'),
      meldung?h('p',{style:{fontSize:'12px'}},meldung):null);
  }
  wp.plugins.registerPlugin('ma-bildrechte',{render:function(){return h(wp.element.Fragment,{},
    DocPanel?h(DocPanel,{name:'ma-bildrechte',title:'Bild- und Nutzungsrechte'},h(Kasten,{sperren:false})):null,
    PrePanel?h(PrePanel,{title:'Bild- und Nutzungsrechte',initialOpen:true},h(Kasten,{sperren:true})):null);}});
})(window.wp);
JS;
}

/* ---------------------------------------------------------- Bildnachweis je Medium */

/** Felder an jedem Medium: Fotograf, Quelle, Nutzungsgrundlage (Beschreibung und Alternativtext hat WordPress selbst). */
function ma_bildrechte_medienfelder(): array {
    return ['ma_credit' => 'Fotograf / Urheber', 'ma_quelle' => 'Quelle', 'ma_nutzung' => 'Nutzungsgrundlage (eigenes Foto, Lizenz, Freigabe …)'];
}

add_filter('attachment_fields_to_edit', function (array $f, WP_Post $a): array {
    foreach (ma_bildrechte_medienfelder() as $k => $label) {
        $f[$k] = ['label' => $label, 'input' => 'text', 'value' => (string) get_post_meta($a->ID, $k, true)];
    }
    $nachweis = array_filter((array) get_post_meta($a->ID, '_ma_bildrechte'), 'is_array');
    if ($nachweis) {
        $l = end($nachweis);
        $u = get_userdata((int) ($l['u'] ?? 0));
        $f['ma_bildrechte_nachweis'] = ['label' => 'Rechte bestätigt', 'input' => 'html', 'html' => esc_html(($u ? $u->display_name : 'unbekannt') . ', ' . wp_date('d.m.Y H:i', (int) $l['t']) . ', Erklärung Version ' . ($l['version'] ?? '?'))];
    }
    return $f;
}, 10, 2);

add_filter('attachment_fields_to_save', function (array $post, array $daten): array {
    foreach (array_keys(ma_bildrechte_medienfelder()) as $k) {
        if (!isset($daten[$k])) continue;
        $v = sanitize_text_field((string) $daten[$k]);
        $v === '' ? delete_post_meta((int) $post['ID'], $k) : update_post_meta((int) $post['ID'], $k, $v);
    }
    return $post;
}, 10, 2);

/* Uploads der Partner: nur Bilder und PDF, höchstens 10 MB. */
add_filter('wp_handle_upload_prefilter', function (array $datei): array {
    if (!function_exists('ma_current_partner_policy') || !ma_current_partner_policy()) return $datei;
    $typ = wp_check_filetype_and_ext($datei['tmp_name'] ?? '', $datei['name'] ?? '');
    if (!in_array($typ['type'] ?? '', ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'], true)) $datei['error'] = 'Erlaubt sind Bilder (JPG, PNG, WebP, GIF) und PDF.';
    elseif ((int) ($datei['size'] ?? 0) > 10 * 1024 * 1024) $datei['error'] = 'Die Datei ist größer als 10 MB.';
    return $datei;
});
