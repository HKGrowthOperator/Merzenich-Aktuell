<?php
/**
 * Hilfe für alle Zugänge (24.10.2026, Auftrag Betreiber): Wer sich einloggt,
 * soll ohne Rückfrage schreiben, Bilder einfügen und einreichen können.
 *
 * - Anleitung als PDF (assets/hilfe/anleitung-zugang.pdf, Quelle docs/anleitung/).
 * - Erklärvideo in der Mediathek (Option ma_hilfe_video: Medien-ID oder Adresse);
 *   ohne Video zeigt die Seite nur das PDF.
 * - Dashboard der Partner: Kasten „So geht's“ ganz oben (vor „Ihr Bereich“).
 * - Menüpunkt „Hilfe“ für alle Angemeldeten: Video, PDF, Schritte, häufige Fragen.
 * - Geführte Tour im Editor (nur Partner): markiert nacheinander Titel, Text,
 *   Beitragsbild, Bildrechte und „Zur Überprüfung einreichen“ (Meldung im
 *   Block-Editor; Termin, Anzeige und Profil im klassischen Formular).
 *   „Nicht mehr zeigen“ gilt je Konto (User-Meta ma_hilfe_tour_aus); die
 *   Hilfe-Seite startet sie wieder. Fehlt ein Element, erscheint der Hinweis
 *   ohne Markierung in der Mitte.
 *
 * Oben in der Datei nur add_action/add_filter: qa/wordpress/hilfe-test.php lädt sie ohne WordPress.
 */
if (!defined('ABSPATH')) { exit; }

const MA_HILFE_PDF = 'assets/hilfe/anleitung-zugang.pdf';
const MA_HILFE_TOUR_META = 'ma_hilfe_tour_aus';

/** Ist das aktuelle Konto ein Partnerzugang (Firma, Verein, Polizei …)? */
function ma_hilfe_ist_partner(): bool {
    return function_exists('ma_current_partner_policy') && (bool) ma_current_partner_policy();
}

/** Adresse des PDFs, '' wenn die Datei fehlt. */
function ma_hilfe_pdf_url(): string {
    return is_file(MA_CORE_PATH . MA_HILFE_PDF) ? MA_CORE_URL . MA_HILFE_PDF . '?v=' . rawurlencode((string) filemtime(MA_CORE_PATH . MA_HILFE_PDF)) : '';
}

/** Adresse des Erklärvideos aus der Option (Medien-ID oder https-Adresse), sonst ''. */
function ma_hilfe_video_url(): string {
    $v = trim((string) get_option('ma_hilfe_video', ''));
    if ($v === '') return '';
    if (ctype_digit($v)) return (string) (wp_get_attachment_url((int) $v) ?: '');
    return preg_match('#^https://#', $v) ? $v : '';
}

/** Die Schritte, die überall gleich lauten (Widget, Hilfe-Seite, PDF). */
function ma_hilfe_schritte(): array {
    return [
        ['Schreiben', 'Im Dashboard „Neue Meldung schreiben“ wählen, Titel und Text eingeben.'],
        ['Bild einfügen', 'Rechts unter „Beitrag“ auf „Beitragsbild festlegen“ klicken, Foto hochladen, Bildnachweis eintragen.'],
        ['Rechte bestätigen', 'Im Kasten „Bild- und Nutzungsrechte“ den Haken setzen. Ohne Haken lässt sich nichts einreichen.'],
        ['Einreichen', '„Zur Überprüfung einreichen“ klicken. Die Redaktion prüft und veröffentlicht; Sie bekommen eine E-Mail.'],
    ];
}

/** Häufige Fragen (Hilfe-Seite und PDF). */
function ma_hilfe_fragen(): array {
    return [
        ['Wann erscheint mein Beitrag?', 'Nach der Prüfung durch die Redaktion, meist am selben oder am nächsten Werktag. Sie bekommen eine E-Mail, sobald er online ist.'],
        ['Die Redaktion hat „Änderungen angefordert“. Was nun?', 'Im Dashboard steht unter dem Beitrag, was geändert werden soll. Beitrag öffnen, ändern, erneut „Zur Überprüfung einreichen“.'],
        ['Ich möchte einen veröffentlichten Beitrag ändern.', 'Im Dashboard beim Beitrag „Änderung vorschlagen“ wählen. Die Redaktion übernimmt die Änderung nach Prüfung.'],
        ['Welche Bilder darf ich verwenden?', 'Nur eigene Fotos oder Bilder, für die Sie die Rechte haben. Keine erkennbaren Gesichter ohne Einverständnis, keine lesbaren Kennzeichen, keine Hausnummern. Am besten Querformat, möglichst 1600 Pixel breit; JPG, PNG oder WebP bis 10 MB.'],
        ['Ich habe mein Passwort vergessen.', 'Auf der Anmeldeseite „Passwort vergessen?“ wählen oder die Redaktion fragen. Das Passwort ändern Sie selbst unter „Profil“.'],
    ];
}

/* ---------------------------------------------------------- Dashboard */

// Nach vereine.php (Priorität 999, leert das Dashboard der Partner) und ganz nach vorn.
// Partner haben nur zwei Kästen: eine Spalte, keine leere Fläche „Boxen hier ablegen“.
add_filter('screen_layout_columns', function ($spalten, $id) { if ($id === 'dashboard' && ma_hilfe_ist_partner()) $spalten['dashboard'] = 1; return $spalten; }, 10, 2);
add_filter('get_user_option_screen_layout_dashboard', fn($w) => ma_hilfe_ist_partner() ? 1 : $w);
add_action('wp_dashboard_setup', 'ma_hilfe_dashboard_setup', 1000);
function ma_hilfe_dashboard_setup(): void {
    if (!ma_hilfe_ist_partner()) return;
    wp_add_dashboard_widget('ma_hilfe_sogehts', 'So geht\'s: in vier Schritten zum Beitrag', 'ma_hilfe_widget');
    global $wp_meta_boxes;
    $kern = $wp_meta_boxes['dashboard']['normal']['core'] ?? [];
    if (isset($kern['ma_hilfe_sogehts'])) $wp_meta_boxes['dashboard']['normal']['core'] = ['ma_hilfe_sogehts' => $kern['ma_hilfe_sogehts']] + $kern;
}

function ma_hilfe_widget(): void {
    echo ma_hilfe_widget_html(ma_hilfe_pdf_url(), ma_hilfe_video_url() !== '' ? admin_url('admin.php?page=ma-hilfe#video') : '', admin_url('admin.php?page=ma-hilfe'));
}

/** HTML des Kastens „So geht's“ (pure Funktion, testbar). */
function ma_hilfe_widget_html(string $pdf, string $video, string $hilfe): string {
    $h = '<ol class="ma-hilfe-schritte">';
    foreach (ma_hilfe_schritte() as $i => [$titel, $text]) $h .= '<li><strong>' . esc_html($titel) . '</strong> ' . esc_html($text) . '</li>';
    $h .= '</ol><p class="ma-hilfe-knoepfe">';
    if ($video !== '') $h .= '<a class="button button-primary" href="' . esc_url($video) . '">Erklärvideo ansehen</a> ';
    if ($pdf !== '') $h .= '<a class="button" href="' . esc_url($pdf) . '" target="_blank" rel="noopener">Anleitung als PDF</a> ';
    return $h . '<a class="button-link" href="' . esc_url($hilfe) . '">Alle Hilfen</a></p>';
}

/* ---------------------------------------------------------- Seite „Hilfe“ */

add_action('admin_menu', 'ma_hilfe_menue', 40);
function ma_hilfe_menue(): void {
    add_menu_page('Hilfe', 'Hilfe', 'read', 'ma-hilfe', 'ma_hilfe_seite', 'dashicons-sos', 3);
}

// Partner sahen „Merzenich Aktuell“ (über den Unterpunkt „Quellen / News Radar“), dahinter nur eine
// Fehlerseite (Prüfung 24.10.2026). Für Partner ausblenden; die Redaktion behält das Menü.
add_action('admin_menu', 'ma_hilfe_menue_aufraeumen', 999);
function ma_hilfe_menue_aufraeumen(): void {
    if (ma_hilfe_ist_partner()) remove_menu_page('merzenich-aktuell');
}

add_action('admin_post_ma_hilfe_tour', 'ma_hilfe_tour_neu');
function ma_hilfe_tour_neu(): void {
    check_admin_referer('ma_hilfe_tour');
    delete_user_meta(get_current_user_id(), MA_HILFE_TOUR_META);
    // Erste Inhaltsart des Zugangs (Makler: Immobilie, Werbekunden: Anzeige, sonst Meldung).
    $typ = (string) ((function_exists('ma_current_partner_policy') ? (ma_current_partner_policy()['post_types'] ?? []) : [])[0] ?? 'post');
    wp_safe_redirect(admin_url('post-new.php' . ($typ !== 'post' ? '?post_type=' . rawurlencode($typ) : ''))); exit;
}

function ma_hilfe_seite(): void {
    $pdf = ma_hilfe_pdf_url(); $video = ma_hilfe_video_url();
    echo '<div class="wrap ma-hilfe"><h1>Hilfe: so arbeiten Sie mit Merzenich Aktuell</h1>';
    echo '<p class="ma-hilfe__einleitung">Sie schreiben, die Redaktion prüft und veröffentlicht. Alles Wichtige steht im Video und in der Anleitung; die Schritte unten sind die Kurzfassung.</p>';
    if ($video !== '') echo '<h2 id="video">Erklärvideo</h2><video class="ma-hilfe__video" controls preload="metadata" src="' . esc_url($video) . '"></video>';
    echo '<h2>In vier Schritten zum Beitrag</h2>' . ma_hilfe_widget_html($pdf, '', admin_url('admin.php?page=ma-hilfe'));
    if (ma_hilfe_ist_partner()) {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="ma_hilfe_tour">';
        wp_nonce_field('ma_hilfe_tour');
        echo '<button class="button">Geführte Tour im Editor starten</button> <span class="description">Öffnet eine neue Meldung und zeigt Schritt für Schritt, wo was hingehört. Gespeichert wird dabei nichts.</span></form>';
    }
    echo '<h2>Häufige Fragen</h2><div class="ma-hilfe__fragen">';
    foreach (ma_hilfe_fragen() as [$f, $a]) echo '<details><summary>' . esc_html($f) . '</summary><p>' . esc_html($a) . '</p></details>';
    echo '</div><h2>Kontakt</h2><p>Fragen beantwortet die Redaktion: <a href="mailto:info@kbs-management.tv">info@kbs-management.tv</a>.</p></div>';
}

add_action('admin_head', 'ma_hilfe_css');
function ma_hilfe_css(): void {
    echo '<style>.ma-hilfe-schritte{margin:0 0 12px 18px}.ma-hilfe-schritte li{margin:0 0 6px;line-height:1.45}.ma-hilfe-knoepfe{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:0}'
        . '.ma-hilfe{max-width:900px}.ma-hilfe__einleitung{font-size:14px}.ma-hilfe__video{width:100%;max-width:900px;aspect-ratio:16/9;background:#111;border-radius:4px}'
        . '.ma-hilfe__fragen details{border-top:1px solid #dcdcde;padding:8px 0}.ma-hilfe__fragen summary{cursor:pointer;font-weight:600}.ma-hilfe__fragen p{margin:6px 0 0}'
        // Partner: keine leere Ablagefläche „Boxen hier ablegen“ unter den beiden Kästen.
        . (ma_hilfe_ist_partner() ? '#dashboard-widgets .postbox-container .empty-container{display:none}' : '') . '</style>';
}

/* ---------------------------------------------------------- Geführte Tour im Editor */

/** Schritte der Tour je Bildschirm: [Selektoren, Titel, Text]. block = Meldung im Block-Editor. */
function ma_hilfe_tour_schritte(string $art): array {
    if ($art === 'block') return [
        [['.editor-post-title', '.wp-block-post-title'], 'Titel', 'Eine kurze, klare Überschrift: Wer, was, wo. Etwa „Herbstfest des TV Golzheim am 8. November“.'],
        [['.is-root-container .block-editor-default-block-appender', '.is-root-container > p', '.is-root-container'], 'Text', 'Hier schreiben Sie den Text. Mit Enter beginnt ein neuer Absatz. Ein Foto im Text fügen Sie über das Plus-Zeichen und „Bild“ ein.'],
        [['.editor-post-featured-image', '.editor-post-featured-image__toggle'], 'Beitragsbild', 'Das wichtigste Foto: „Beitragsbild festlegen“, dann „Dateien hochladen“. Querformat, möglichst 1600 Pixel breit, höchstens 10 MB. Rechts beim Bild Fotograf und Quelle eintragen.'],
        [['.ma-bildrechte', 'panel:Bild- und Nutzungsrechte'], 'Bild- und Nutzungsrechte', 'Sobald ein Bild drin ist: Erklärung lesen und den Haken setzen. Ohne Haken lässt sich der Beitrag nicht einreichen.'],
        [['panel:Orte', 'panel:Kategorien', '.editor-post-taxonomies__hierarchical-terms-list'], 'Ortsteil', 'Wählen Sie unter „Orte“, welcher Ortsteil betroffen ist. Die Rubrik setzt die Redaktion passend zu Ihrem Zugang.'],
        [['.editor-post-publish-panel__toggle', '.editor-post-publish-button__button', '.editor-post-publish-button'], 'Einreichen', '„Zur Überprüfung einreichen“: Die Redaktion prüft und veröffentlicht. Sie bekommen eine E-Mail. Zwischendurch speichern Sie mit „Speichern“.'],
    ];
    return [
        [['#title'], 'Titel', 'Eine kurze, klare Überschrift.'],
        [['#postdivrich', '#postexcerpt', '#postbox-container-2'], 'Angaben', 'Text und Felder ausfüllen. Pflichtfelder sind markiert.'],
        [['#postimagediv'], 'Bild', '„Bild festlegen“, Foto hochladen, Bildnachweis eintragen.'],
        [['.ma-bildrechte-klassisch'], 'Bild- und Nutzungsrechte', 'Erklärung lesen und den Haken setzen. Ohne Haken bleibt alles Entwurf.'],
        [['#publish', '#submitdiv'], 'Einreichen', '„Zur Überprüfung einreichen“: Die Redaktion prüft und veröffentlicht.'],
    ];
}

add_action('admin_enqueue_scripts', 'ma_hilfe_tour_laden');
function ma_hilfe_tour_laden(string $hook): void {
    if (!in_array($hook, ['post-new.php', 'post.php'], true) || !ma_hilfe_ist_partner()) return;
    if ((string) get_user_meta(get_current_user_id(), MA_HILFE_TOUR_META, true) === '1') return;
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $block = $screen && method_exists($screen, 'is_block_editor') && $screen->is_block_editor();
    wp_register_script('ma-hilfe-tour', false, [], MA_CORE_VERSION, true);
    wp_enqueue_script('ma-hilfe-tour');
    wp_add_inline_script('ma-hilfe-tour', 'window.maHilfeTour=' . wp_json_encode([
        'schritte' => array_map(fn($s) => ['sel' => $s[0], 'titel' => $s[1], 'text' => $s[2]], ma_hilfe_tour_schritte($block ? 'block' : 'klassisch')),
        'block' => $block, 'ajax' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('ma_hilfe_tour_aus'),
        'pdf' => ma_hilfe_pdf_url(), 'hilfe' => admin_url('admin.php?page=ma-hilfe'),
    ]) . ';' . ma_hilfe_tour_js());
    wp_register_style('ma-hilfe-tour', false, [], MA_CORE_VERSION);
    wp_enqueue_style('ma-hilfe-tour');
    wp_add_inline_style('ma-hilfe-tour', ma_hilfe_tour_css());
}

add_action('wp_ajax_ma_hilfe_tour_aus', 'ma_hilfe_tour_aus');
function ma_hilfe_tour_aus(): void {
    check_ajax_referer('ma_hilfe_tour_aus', 'nonce');
    update_user_meta(get_current_user_id(), MA_HILFE_TOUR_META, '1');
    wp_send_json_success();
}

function ma_hilfe_tour_css(): string {
    return '.ma-tour-rahmen{position:fixed;z-index:100000;border:3px solid #971725;border-radius:6px;box-shadow:0 0 0 9999px rgba(20,18,16,.38);pointer-events:none;transition:all .25s cubic-bezier(.22,1,.36,1)}'
        . '.ma-tour-karte{position:fixed;z-index:100001;width:320px;max-width:calc(100vw - 24px);background:#fff;color:#151412;border-radius:8px;box-shadow:0 12px 32px rgba(0,0,0,.28);padding:16px 18px 14px;font:14px/1.5 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}'
        . '.ma-tour-karte small{display:block;color:#971725;font-weight:700;letter-spacing:.06em;text-transform:uppercase;font-size:11px;margin-bottom:4px}'
        . '.ma-tour-karte h2{margin:0 0 6px;font-size:17px;line-height:1.25}.ma-tour-karte p{margin:0 0 12px}'
        . '.ma-tour-knoepfe{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.ma-tour-knoepfe .ma-tour-aus{margin-left:auto;font-size:12px}'
        . '@media (prefers-reduced-motion:reduce){.ma-tour-rahmen{transition:none}}';
}

function ma_hilfe_tour_js(): string {
    return <<<'JS'
(function(){
  var d=window.maHilfeTour||{},s=d.schritte||[],i=0,rahmen,karte;
  if(!s.length)return;
  // Element und seine Lage im Fenster: auch im Editor-Rahmen (iframe editor-canvas); „panel:Titel“ = Bereich der Seitenleiste, wird aufgeklappt.
  var neuMessen=false;
  function bereich(titel){var t=[].slice.call(document.querySelectorAll('.components-panel__body-toggle')).filter(function(b){return b.textContent.trim().indexOf(titel)===0;})[0];if(!t)return null;if(t.getAttribute('aria-expanded')==='false'){t.click();neuMessen=true;}return t.closest('.components-panel__body');}
  function finde(sel){
    var fr=document.querySelector('iframe[name="editor-canvas"]'),doc=null;try{doc=fr&&fr.contentDocument;}catch(e){}
    for(var k=0;k<sel.length;k++){
      if(sel[k].indexOf('panel:')===0){var b=bereich(sel[k].slice(6));if(b)return {el:b,dx:0,dy:0};continue;}
      var e=document.querySelector(sel[k]);if(e&&e.getBoundingClientRect().width>0)return {el:e,dx:0,dy:0};
      if(doc){e=doc.querySelector(sel[k]);if(e&&e.getBoundingClientRect().width>0){var r=fr.getBoundingClientRect();return {el:e,dx:r.left,dy:r.top};}}
    }
    return null;
  }
  function seitenleiste(){
    // Block-Editor: Einstellungen und Bereiche öffnen, damit Beitragsbild und Rubrik sichtbar sind.
    try{var wd=window.wp&&wp.data;if(!wd)return;var ep=wd.dispatch('core/edit-post');if(ep&&ep.openGeneralSidebar)ep.openGeneralSidebar('edit-post/document');
      var ed=wd.dispatch('core/editor'),es=wd.select('core/editor');['featured-image','taxonomy-panel-category','taxonomy-panel-ma_location'].forEach(function(p){if(es.isEditorPanelOpened&&!es.isEditorPanelOpened(p)&&ed.toggleEditorPanelOpened)ed.toggleEditorPanelOpened(p);});}catch(e){}
  }
  function aus(fuerImmer){if(rahmen)rahmen.remove();if(karte)karte.remove();window.removeEventListener('resize',zeige);
    if(fuerImmer)fetch(d.ajax,{method:'POST',credentials:'same-origin',body:new URLSearchParams({action:'ma_hilfe_tour_aus',nonce:d.nonce})});}
  function knopf(text,cls,fn){var b=document.createElement('button');b.type='button';b.className=cls;b.textContent=text;b.addEventListener('click',fn);return b;}
  function zeige(){
    var st=s[i],f=finde(st.sel),el=f&&f.el;
    if(!rahmen){rahmen=document.createElement('div');rahmen.className='ma-tour-rahmen';document.body.append(rahmen);}
    if(!karte){karte=document.createElement('div');karte.className='ma-tour-karte';karte.setAttribute('role','dialog');karte.setAttribute('aria-live','polite');document.body.append(karte);}
    karte.innerHTML='';
    var k=document.createElement('small');k.textContent='Schritt '+(i+1)+' von '+s.length;
    var h=document.createElement('h2');h.textContent=st.titel;var p=document.createElement('p');p.textContent=st.text;
    var kn=document.createElement('div');kn.className='ma-tour-knoepfe';
    if(i>0)kn.append(knopf('Zurück','button',function(){i--;zeige();}));
    kn.append(i<s.length-1?knopf('Weiter','button button-primary',function(){i++;zeige();}):knopf('Fertig','button button-primary',function(){aus(true);}));
    kn.append(knopf('Nicht mehr zeigen','button-link ma-tour-aus',function(){aus(true);}));
    karte.append(k,h,p,kn);
    if(el){el.scrollIntoView({block:'center',behavior:'instant'});var r0=el.getBoundingClientRect(),pad=6,r={left:r0.left+f.dx,top:r0.top+f.dy,right:r0.right+f.dx,bottom:r0.bottom+f.dy,width:r0.width,height:r0.height};
      rahmen.style.display='block';rahmen.style.left=(r.left-pad)+'px';rahmen.style.top=(r.top-pad)+'px';rahmen.style.width=(r.width+2*pad)+'px';rahmen.style.height=(r.height+2*pad)+'px';
      var kw=karte.offsetWidth,kh=karte.offsetHeight,x=r.right+16,y=Math.max(12,r.top);
      if(x+kw>innerWidth-12)x=r.left-kw-16;if(x<12){x=Math.min(Math.max(12,r.left),innerWidth-kw-12);y=r.bottom+16;if(y+kh>innerHeight-12)y=Math.max(12,r.top-kh-16);}
      karte.style.left=x+'px';karte.style.top=Math.min(y,innerHeight-kh-12)+'px';
      // Gerade aufgeklappter Bereich: nach dem Aufklappen noch einmal messen.
      if(neuMessen){neuMessen=false;setTimeout(zeige,350);}
    }else{rahmen.style.display='none';karte.style.left=Math.max(12,(innerWidth-karte.offsetWidth)/2)+'px';karte.style.top=Math.max(12,(innerHeight-karte.offsetHeight)/2)+'px';}
  }
  function start(){
    // Willkommens-Fenster des Block-Editors nicht über die Tour legen.
    try{if(d.block&&wp.data.dispatch('core/preferences'))wp.data.dispatch('core/preferences').set('core/edit-post','welcomeGuide',false);}catch(e){}
    if(d.block)seitenleiste();setTimeout(zeige,d.block?600:0);window.addEventListener('resize',zeige);}
  // Block-Editor: warten, bis der Titel da ist, auch im Editor-Rahmen (höchstens 10 Sekunden).
  function titelDa(){var q='.editor-post-title, .wp-block-post-title';if(document.querySelector(q))return true;var fr=document.querySelector('iframe[name="editor-canvas"]');try{return !!(fr&&fr.contentDocument&&fr.contentDocument.querySelector(q));}catch(e){return false;}}
  if(d.block){var n=0,t=setInterval(function(){if(titelDa()||++n>40){clearInterval(t);start();}},250);}
  else if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start);else start();
})();
JS;
}
