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
    $allowed=['kontakt','meldung','termin','verein','werbung','immobilie','trauer','familie']; if(!in_array($type,$allowed,true)) $type='kontakt';
    ob_start(); ?>
    <form class="ma-public-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
      <input type="hidden" name="action" value="ma_form_submit"><input type="hidden" name="type" value="<?php echo esc_attr($type); ?>"><input type="hidden" name="started" value="<?php echo esc_attr(time()); ?>"><?php wp_nonce_field('ma_form_submit_'.$type,'ma_form_nonce'); ?>
      <p style="position:absolute;left:-9999px"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></p>
      <?php
      $arten=[
        'immobilie'=>['verkauf'=>'Immobilie verkaufen','vermietung'=>'Immobilie vermieten','gesuch'=>'Immobilie suchen','gewerbe'=>'Gewerbeobjekt'],
        'trauer'=>['traueranzeige'=>'Traueranzeige','danksagung'=>'Danksagung','jahrgedaechtnis'=>'Jahrgedächtnis'],
        'familie'=>['geburt'=>'Geburt','hochzeit'=>'Hochzeit','jubilaeum'=>'Jubiläum','glueckwunsch'=>'Glückwunsch'],
        'werbung'=>['banner'=>'Werbebanner','tipp'=>'Tipp / Sponsoring','unternehmen'=>'Unternehmensprofil'],
      ];
      if(isset($arten[$type])):
        $vorauswahl=sanitize_key($_GET['art']??'');
      ?>
        <p><label>Was möchten Sie veröffentlichen?<br><select name="listing_kind" required>
          <option value="">Bitte wählen</option>
          <?php foreach($arten[$type] as $key=>$label): ?><option value="<?php echo esc_attr($key); ?>" <?php selected($vorauswahl,$key); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?>
        </select></label></p>
      <?php endif; ?>
      <p><label>Name<br><input name="name" required maxlength="120"></label></p><p><label>E-Mail<br><input name="email" type="email" required maxlength="190"></label></p>
      <p><label>Telefon<?php echo $type==='trauer' ? ' (Pflicht, für Rückfragen)' : ' (optional, für Rückfragen)'; ?><br><input name="telefon" type="tel" maxlength="40" pattern="[0-9 +()/-]{6,}" title="Bitte eine Telefonnummer mit mindestens sechs Ziffern angeben." autocomplete="tel"<?php echo $type==='trauer' ? ' required' : ''; ?>></label></p><p><label>Betreff<br><input name="subject" required maxlength="180"></label></p><p><label>Nachricht<br><textarea name="message" rows="7" required maxlength="8000"></textarea></label></p>
      <?php if(in_array($type,['meldung','termin','verein','werbung','immobilie','trauer','familie'],true)): ?><p><label>Datei / Bild (optional, JPG/PNG/PDF, max. 5 MB)<br><input type="file" name="attachment" accept="image/jpeg,image/png,application/pdf" data-ma-datei></label></p>
      <p class="ma-bildrechte"><label><input type="checkbox" name="bildrechte" value="1" data-ma-bildrechte> <?php echo esc_html(ma_form_bildrechte_text()); ?></label></p>
      <script>(function(f){var d=f.querySelector('[data-ma-datei]'),c=f.querySelector('[data-ma-bildrechte]');if(!d||!c)return;function p(){c.required=!!(d.files&&d.files.length);}d.addEventListener('change',p);p();})(document.currentScript.closest('form'));</script><?php endif; ?>
      <p><button type="submit">Absenden</button></p><p class="ma-form-note">Einsendungen werden redaktionell geprüft und nicht automatisch veröffentlicht.</p>
    </form><?php return (string)ob_get_clean();
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
    $to=sanitize_email((string)get_option('ma_editorial_email',get_option('admin_email'))); $attachments=[];
    if(!empty($_FILES['attachment']['name'])){
        if((int)$_FILES['attachment']['size']>5*1024*1024) wp_die('Datei ist zu groß.',400);
        require_once ABSPATH.'wp-admin/includes/file.php';
        $upload=wp_handle_upload($_FILES['attachment'],['test_form'=>false,'mimes'=>['jpg|jpeg'=>'image/jpeg','png'=>'image/png','pdf'=>'application/pdf']]);
        if(isset($upload['file'])) $attachments[]=$upload['file']; else wp_die('Datei konnte nicht verarbeitet werden.',400);
    }
    $kind=sanitize_text_field(wp_unslash($_POST['listing_kind']??''));
    $body="Typ: {$type}\n".($kind!==''?"Art: {$kind}\n":'')."Name: {$name}\nE-Mail: {$email}\n".($telefon!==''?"Telefon: {$telefon}\n":'').($attachments?'Bildrechte: bestätigt'."\n":'')."\n{$message}";
    // Eingang im Backend ablegen, damit nichts verloren geht, auch ohne Mailversand.
    $eingang=function_exists('ma_eingang_speichern') ? ma_eingang_speichern(['typ'=>$type,'art'=>$kind,'name'=>$name,'email'=>$email,'telefon'=>$telefon,'betreff'=>$subject,'text'=>$message,'bildrechte'=>$bildrechte?'1':''],$attachments) : 0;
    if($eingang) $body.="\n\nIm Backend: ".admin_url('post.php?post='.$eingang.'&action=edit');
    wp_mail($to,'[Merzenich Aktuell] '.$subject,$body,['Reply-To: '.$name.' <'.$email.'>'],$attachments);
    if(!$eingang) foreach($attachments as $f) @unlink($f);
    wp_safe_redirect(add_query_arg('gesendet','1',wp_get_referer()?:home_url('/'))); exit;
}

/** Bildrechte-Erklaerung, gleicher Wortlaut wie auf der Website (30.09.2026). */
function ma_form_bildrechte_text(): string {
    return 'Bildrechte (Pflicht, wenn Sie eine Datei hochladen): Ich versichere, dass ich die hochgeladenen Bilder selbst aufgenommen habe oder alle nötigen Nutzungs- und Lizenzrechte daran besitze. '
        . 'Erkennbar abgebildete Personen sind mit der Veröffentlichung einverstanden. Ich erlaube Merzenich Aktuell, die Bilder im Zusammenhang mit dieser Einsendung unentgeltlich online zu veröffentlichen, mit meinem Namen als Bildnachweis. '
        . 'Für die Rechte an den Bildern bin ich selbst verantwortlich: Macht ein Dritter Ansprüche wegen fehlender Rechte geltend, stelle ich Merzenich Aktuell davon frei.';
}
