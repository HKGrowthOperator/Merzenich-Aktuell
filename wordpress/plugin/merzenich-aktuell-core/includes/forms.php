<?php
if (!defined('ABSPATH')) { exit; }
function ma_register_form_hooks(): void {
    add_shortcode('ma_formular','ma_form_shortcode');
    add_action('admin_post_nopriv_ma_form_submit','ma_form_submit');
    add_action('admin_post_ma_form_submit','ma_form_submit');
}
function ma_form_shortcode($atts): string {
    $a=shortcode_atts(['typ'=>'kontakt'],$atts);
    $type=sanitize_key($a['typ']);
    $allowed=['kontakt','meldung','termin','verein','werbung','immobilie','stelle','trauer','familie','korrektur','foto']; if(!in_array($type,$allowed,true)) $type='kontakt';
    $foto=$type==='foto';
    ob_start(); ?>
    <?php if(sanitize_key($_GET['gesendet']??'')===$type): ?><p class="ma-form-danke" role="status" id="formular-<?php echo esc_attr($type); ?>-danke"><strong>Danke, Ihre Einsendung ist angekommen.</strong> Die Redaktion prüft sie und meldet sich bei Rückfragen.</p><?php endif; ?>
    <form class="ma-public-form form" id="formular-<?php echo esc_attr($type); ?>" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
      <input type="hidden" name="action" value="ma_form_submit"><input type="hidden" name="type" value="<?php echo esc_attr($type); ?>"><input type="hidden" name="started" value="<?php echo esc_attr(time()); ?>"><?php wp_nonce_field('ma_form_submit_'.$type,'ma_form_nonce'); ?>
      <p style="position:absolute;left:-9999px"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></p>
      <?php
      $arten=[
        'immobilie'=>['verkauf'=>'Immobilie verkaufen','vermietung'=>'Immobilie vermieten','gesuch'=>'Immobilie suchen','gewerbe'=>'Gewerbeobjekt'],
        'stelle'=>['ausbildung'=>'Ausbildungsplatz','vollzeit'=>'Vollzeitstelle','teilzeit'=>'Teilzeitstelle','minijob'=>'Minijob / Aushilfe','praktikum'=>'Praktikum'],
        'trauer'=>['traueranzeige'=>'Traueranzeige','danksagung'=>'Danksagung','jahrgedaechtnis'=>'Jahrgedächtnis'],
        'familie'=>['geburt'=>'Geburt','hochzeit'=>'Hochzeit','jubilaeum'=>'Jubiläum','glueckwunsch'=>'Glückwunsch'],
        'werbung'=>['banner'=>'Werbebanner','tipp'=>'Tipp / Sponsoring','unternehmen'=>'Unternehmensprofil'],
      ];
      $ziel=ma_form_ziel_aus_link();
      $vorauswahl=$ziel['typ']===$type ? $ziel['art'] : sanitize_key($_GET['art']??'');
      if(isset($arten[$type])):
      ?>
        <p><label>Was möchten Sie veröffentlichen?<br><select name="listing_kind" required>
          <option value="">Bitte wählen</option>
          <?php foreach($arten[$type] as $key=>$label): ?><option value="<?php echo esc_attr($key); ?>" <?php selected($vorauswahl,$key); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?>
        </select></label></p>
      <?php endif; ?>
      <?php if($foto): ?><p><label>Ortsteil<br><select name="ortsteil" required><option value="">Bitte wählen</option><?php foreach(ma_form_ortsteile() as $k=>$l): ?><option value="<?php echo esc_attr($k); ?>"><?php echo esc_html($l); ?></option><?php endforeach; ?></select></label></p><?php endif; ?>
      <p><label>Name<br><input name="name" required maxlength="120"></label></p><p><label>E-Mail<br><input name="email" type="email" required maxlength="190"></label></p>
      <p><label>Telefon<?php echo $type==='trauer' ? ' (Pflicht, für Rückfragen)' : ' (optional, für Rückfragen)'; ?><br><input name="telefon" type="tel" maxlength="40" pattern="[0-9 +()/-]{6,}" title="Bitte eine Telefonnummer mit mindestens sechs Ziffern angeben." autocomplete="tel"<?php echo $type==='trauer' ? ' required' : ''; ?>></label></p><p><label><?php echo $foto ? 'Was ist zu sehen? (Motiv, Ort)' : 'Betreff'; ?><br><input name="subject" required maxlength="180" value="<?php echo esc_attr($ziel['typ']===$type ? $ziel['betreff'] : ''); ?>"></label></p><p><label><?php echo $foto ? 'Wann und wo haben Sie das Foto aufgenommen?' : 'Nachricht'; ?><br><textarea name="message" rows="<?php echo $foto ? 3 : 7; ?>" required maxlength="8000"></textarea></label></p>
      <?php if($foto): ?><p><label>Name im Bildnachweis (optional, sonst Ihr Name)<br><input name="fotograf" maxlength="120"></label></p>
      <p><label>Foto (JPG oder PNG, max. 5 MB, möglichst im Querformat)<br><input type="file" name="attachment" accept="image/jpeg,image/png" required></label></p>
      <p class="ma-bildrechte"><label><input type="checkbox" name="bildrechte" value="1" required> <?php echo esc_html(ma_form_bildrechte_text()); ?></label></p>
      <p class="ma-bildrechte"><label><input type="checkbox" name="zustimmung" value="1" required> <?php echo esc_html(ma_form_foto_zustimmung()); ?></label></p><?php endif; ?>
      <?php if(in_array($type,['meldung','termin','verein','werbung','immobilie','stelle','trauer','familie'],true)): ?><p><label>Datei / Bild (optional, JPG/PNG/PDF, max. 5 MB)<br><input type="file" name="attachment" accept="image/jpeg,image/png,application/pdf" data-ma-datei></label></p>
      <p class="ma-bildrechte"><label><input type="checkbox" name="bildrechte" value="1" data-ma-bildrechte> <?php echo esc_html(ma_form_bildrechte_text()); ?></label></p>
      <script>(function(f){var d=f.querySelector('[data-ma-datei]'),c=f.querySelector('[data-ma-bildrechte]');if(!d||!c)return;function p(){c.required=!!(d.files&&d.files.length);}d.addEventListener('change',p);p();})(document.currentScript.closest('form'));</script><?php endif; ?>
      <p><button type="submit" class="btn"><?php echo $foto ? 'Foto einsenden' : 'Absenden'; ?></button></p><p class="ma-form-note">Einsendungen werden redaktionell geprüft und nicht automatisch veröffentlicht. Ihre Angaben nutzen wir nur zur Bearbeitung und für Rückfragen, Details in der <a href="<?php echo esc_url(home_url('/datenschutz/')); ?>">Datenschutzerklärung</a>.</p>
    </form><?php if($ziel['typ']===$type): ?><script>(function(f){if(!f||location.hash)return;var h=f;while((h=h.previousElementSibling)&&!/^H[23]$/.test(h.tagName)&&h.tagName!=='FORM');requestAnimationFrame(function(){((h&&h.tagName!=='FORM')?h:f).scrollIntoView({block:'start'});window.scrollBy(0,-110);});})(document.getElementById('formular-<?php echo esc_js($type); ?>'));</script><?php endif; ?><?php return (string)ob_get_clean();
}

/**
 * Vorauswahl aus Links wie /anzeigen/aufgeben/?art=Werbung&format=Unternehmenskanal,
 * ?art=Immobilie&angebot=Verkauf, ?art=Traueranzeige&trauerform=Danksagung oder
 * ?art=Familienanzeige&anlass=Geburt (Anzeigen-Assistent der früheren Seite, Theme).
 * Liefert Formular-Typ, Art im Auswahlfeld und einen vorgeschlagenen Betreff.
 */
function ma_form_ziel_aus_link(): array {
    $k = fn(string $v): string => sanitize_key(remove_accents(sanitize_text_field(wp_unslash($v))));
    $art = $k((string)($_GET['art'] ?? ''));
    $typ = ['werbung' => 'werbung', 'immobilie' => 'immobilie', 'traueranzeige' => 'trauer', 'trauer' => 'trauer', 'familienanzeige' => 'familie', 'familie' => 'familie', 'stellenanzeige' => 'stelle', 'stelle' => 'stelle'][$art] ?? '';
    if ($typ === '') return ['typ' => '', 'art' => '', 'betreff' => ''];
    $format = sanitize_text_field(wp_unslash((string)($_GET['format'] ?? '')));
    $f = $k($format); $wahl = '';
    if ($typ === 'werbung') $wahl = str_contains($f, 'banner') ? 'banner' : (str_contains($f, 'tipp') ? 'tipp' : (str_contains($f, 'unternehmen') ? 'unternehmen' : ''));
    if ($typ === 'immobilie') $wahl = in_array($a = $k((string)($_GET['angebot'] ?? '')), ['verkauf', 'vermietung', 'gesuch', 'gewerbe'], true) ? $a : '';
    if ($typ === 'trauer') { $t = $k((string)($_GET['trauerform'] ?? '')); $wahl = str_starts_with($t, 'jahr') ? 'jahrgedaechtnis' : (in_array($t, ['traueranzeige', 'danksagung'], true) ? $t : ''); }
    if ($typ === 'familie') { $t = $k((string)($_GET['anlass'] ?? '')); $wahl = str_starts_with($t, 'jubil') ? 'jubilaeum' : (in_array($t, ['geburt', 'hochzeit', 'glueckwunsch'], true) ? $t : ''); }
    return ['typ' => $typ, 'art' => $wahl, 'betreff' => $format !== '' ? 'Anfrage: ' . $format : ''];
}
function ma_form_submit(): void {
    $type=sanitize_key($_POST['type']??'kontakt');
    if(!isset($_POST['ma_form_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_form_nonce'])),'ma_form_submit_'.$type)) wp_die('Ungültige Anfrage.',403);
    if(!empty($_POST['website'])) wp_die('Ungültige Anfrage.',400);
    $started=(int)($_POST['started']??0); if(!$started || time()-$started<3) wp_die('Bitte versuchen Sie es erneut.',400);
    $name=sanitize_text_field(wp_unslash($_POST['name']??'')); $email=sanitize_email(wp_unslash($_POST['email']??'')); $subject=sanitize_text_field(wp_unslash($_POST['subject']??'')); $message=sanitize_textarea_field(wp_unslash($_POST['message']??''));
    if(!$name||!is_email($email)||!$subject||!$message) wp_die('Bitte alle Pflichtfelder korrekt ausfüllen.',400);
    $telefon=sanitize_text_field(wp_unslash($_POST['telefon']??''));
    // Traueranzeige: Telefon und E-Mail sind Pflicht (Betreiber 30.09.2026).
    if($type==='trauer' && strlen(preg_replace('/\D/','',$telefon))<6) wp_die('Für Traueranzeigen brauchen wir eine Telefonnummer für Rückfragen.',400);
    $bildrechte=!empty($_POST['bildrechte']);
    if(!empty($_FILES['attachment']['name']) && !$bildrechte) wp_die('Sie haben eine Datei angehängt: Bitte bestätigen Sie die Bildrechte.',400);
    // Foto des Tages: Foto, Ortsteil und Zustimmung zur Veröffentlichung sind Pflicht.
    $ortsteil=sanitize_key($_POST['ortsteil']??''); $zustimmung=!empty($_POST['zustimmung']); $fotograf=sanitize_text_field(wp_unslash($_POST['fotograf']??''));
    if($type==='foto'){
        if(empty($_FILES['attachment']['name']) || !preg_match('#^image/(jpeg|png)$#',(string)($_FILES['attachment']['type']??''))) wp_die('Bitte ein Foto (JPG oder PNG) anhängen.',400);
        if(!$zustimmung || !isset(ma_form_ortsteile()[$ortsteil])) wp_die('Bitte Ortsteil wählen und der Veröffentlichung zustimmen.',400);
    }
    $to=sanitize_email((string)get_option('ma_editorial_email',get_option('admin_email'))); $attachments=[];
    if(!empty($_FILES['attachment']['name'])){
        if((int)$_FILES['attachment']['size']>5*1024*1024) wp_die('Datei ist zu groß.',400);
        require_once ABSPATH.'wp-admin/includes/file.php';
        $upload=wp_handle_upload($_FILES['attachment'],['test_form'=>false,'mimes'=>$type==='foto'?['jpg|jpeg'=>'image/jpeg','png'=>'image/png']:['jpg|jpeg'=>'image/jpeg','png'=>'image/png','pdf'=>'application/pdf']]);
        if(isset($upload['file'])) $attachments[]=$upload['file']; else wp_die('Datei konnte nicht verarbeitet werden.',400);
    }
    $kind=sanitize_text_field(wp_unslash($_POST['listing_kind']??''));
    if($type==='foto') $kind=$ortsteil;
    $body="Typ: {$type}\n".($kind!==''?"Art: {$kind}\n":'')."Name: {$name}\nE-Mail: {$email}\n".($telefon!==''?"Telefon: {$telefon}\n":'').($attachments?'Bildrechte: bestätigt'."\n":'')."\n{$message}";
    // Eingang im Backend ablegen, damit nichts verloren geht, auch ohne Mailversand.
    $eingang=function_exists('ma_eingang_speichern') ? ma_eingang_speichern(['typ'=>$type,'art'=>$kind,'name'=>$name,'email'=>$email,'telefon'=>$telefon,'betreff'=>$subject,'text'=>$message,'bildrechte'=>$bildrechte?'1':'','fotograf'=>$fotograf,'zustimmung'=>$zustimmung?'1':''],$attachments) : 0;
    if($eingang) $body.="\n\nIm Backend: ".admin_url('post.php?post='.$eingang.'&action=edit');
    wp_mail($to,'[Merzenich Aktuell] '.$subject,$body,['Reply-To: '.$name.' <'.$email.'>'],$attachments);
    if(!$eingang) foreach($attachments as $f) @unlink($f);
    // Zurück zum Formular, mit Bestätigung über dem Formular (vorher stand dort nichts).
    wp_safe_redirect(add_query_arg('gesendet',$type,remove_query_arg('gesendet',wp_get_referer()?:home_url('/'))).'#formular-'.$type); exit;
}

/** Ortsteile für das Foto des Tages. */
function ma_form_ortsteile(): array {
    return ['merzenich'=>'Merzenich','golzheim'=>'Golzheim','girbelsrath'=>'Girbelsrath','morschenich'=>'Morschenich','buergewald'=>'Bürgewald'];
}

/** Zustimmung zur Veröffentlichung als Foto des Tages (Pflicht). */
function ma_form_foto_zustimmung(): string {
    return 'Ich bin einverstanden, dass Merzenich Aktuell dieses Foto als „Foto des Tages“ auf merzenich-aktuell.de mit meinem Namen im Bildnachweis zeigt. Auf dem Foto sind keine Personen erkennbar, oder alle erkennbaren Personen sind einverstanden. (Pflicht)';
}

/** Bildrechte-Erklaerung: seit 01.10.2026 der verbindliche Wortlaut aus bildrechte.php (Version 2). */
function ma_form_bildrechte_text(): string {
    $text = function_exists('ma_bildrechte_text') ? ma_bildrechte_text() : 'Ich bestätige, dass ich für die hochgeladenen Bilder die erforderlichen Rechte besitze.';
    return 'Bestätigung der Bild- und Nutzungsrechte (Pflicht, wenn Sie eine Datei hochladen): ' . $text . ' ' . (function_exists('ma_bildrechte_haken_text') ? ma_bildrechte_haken_text() : '');
}
