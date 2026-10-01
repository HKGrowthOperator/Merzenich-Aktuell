<?php
/**
 * WordPress-Pflegeoberfläche für das bestehende Sportbildsystem.
 * Nutzt die vorhandenen Medienfelder ma_credit, ma_quelle, ma_nutzung aus
 * bildrechte.php und ergänzt nur Sportpool + redaktionelle Verifikation.
 */
if (!defined('ABSPATH')) { exit; }

function ma_sport_bild_keys(): array {
    return ['tischtennis','tennis','badminton','pickleball','volleyball','billard','boule',
        'schach','ju-jutsu','breitensport','wasser','fitness','wandern',
        'schiesssport','tanzsport','luftsport','american-football'];
}

/** Aus dem vorhandenen Medien-Editor, kein zweites Upload-/Rechtesystem. */
add_filter('attachment_fields_to_edit', static function (array $f, WP_Post $a): array {
    if (!current_user_can('manage_options') || !wp_attachment_is_image($a->ID)) return $f;
    $jetzt = (string) get_post_meta($a->ID, 'ma_sportpool', true);
    $html = '<select name="attachments[' . (int) $a->ID . '][ma_sportpool]"><option value="">– kein Sportpool –</option>';
    foreach (ma_sport_bild_keys() as $k) $html .= '<option value="' . esc_attr($k) . '"' . selected($jetzt, $k, false) . '>' . esc_html(ma_sportart_titel($k)) . '</option>';
    $html .= '</select><p class="help">Nur sportartspezifische, gesichtete Motive. Das Bild darf nicht fälschlich einen lokalen Verein oder ein konkretes Ereignis behaupten.</p>';
    $f['ma_sportpool'] = ['label'=>'Sport: Motivpool', 'input'=>'html', 'html'=>$html];
    $ok = (string) get_post_meta($a->ID, 'ma_image_rights_verified', true);
    $f['ma_sport_verified'] = [
        'label'=>'Sport: redaktionelle Freigabe',
        'input'=>'html',
        'html'=>'<select name="attachments[' . (int) $a->ID . '][ma_sport_verified]"><option value="0"' . selected($ok,'0',false) . '>Nicht freigegeben</option><option value="1"' . selected($ok,'1',false) . '>Bildrechte geprüft / Nutzung freigegeben</option></select><p class="help">Nur nach Prüfung von Fotograf / Urheber, Quelle, Nutzungsgrundlage und den gegebenenfalls notwendigen Einwilligungen bestätigen. Die drei vorhandenen Medienfelder oben vollständig ausfüllen.</p>'
    ];
    return $f;
}, 20, 2);

add_filter('attachment_fields_to_save', static function (array $post, array $daten): array {
    $id = (int) ($post['ID'] ?? 0);
    if (!$id || !current_user_can('manage_options') || !wp_attachment_is_image($id)) return $post;
    if (isset($daten['ma_sportpool'])) {
        $pool = sanitize_key((string) $daten['ma_sportpool']);
        if ($pool === '' || !in_array($pool, ma_sport_bild_keys(), true)) delete_post_meta($id, 'ma_sportpool');
        else update_post_meta($id, 'ma_sportpool', $pool);
    }
    // Die bisherigen zentralen Bildrechte-Felder bleiben führend;
    // für die Sportausgabe werden deren normierte Spiegel genutzt.
    $credit = trim((string) get_post_meta($id, 'ma_credit', true));
    $nutzung = trim((string) get_post_meta($id, 'ma_nutzung', true));
    $quelle = trim((string) get_post_meta($id, 'ma_quelle', true));
    if ($credit !== '') update_post_meta($id, 'ma_image_credit', $credit);
    else delete_post_meta($id, 'ma_image_credit');
    if ($nutzung !== '') update_post_meta($id, 'ma_image_license', $nutzung);
    else delete_post_meta($id, 'ma_image_license');
    if (filter_var($quelle, FILTER_VALIDATE_URL)) update_post_meta($id, 'ma_image_original_url', esc_url_raw($quelle));
    else delete_post_meta($id, 'ma_image_original_url');
    if (isset($daten['ma_sport_verified'])) {
        if ((string) $daten['ma_sport_verified'] === '1' && $credit !== '' && $nutzung !== '' && $quelle !== '') update_post_meta($id, 'ma_image_rights_verified', '1');
        else delete_post_meta($id, 'ma_image_rights_verified');
    } elseif (!$credit || !$nutzung || !$quelle) {
        delete_post_meta($id, 'ma_image_rights_verified');
    }
    return $post;
}, 20, 2);

/** An ma_club: pro Sportangebot kann ein individuell freigegebenes Foto gewählt werden. */
add_action('add_meta_boxes_ma_club', static function (): void {
    if (!current_user_can('edit_others_posts')) return;
    add_meta_box('ma_sport_fotobox', 'Sport: Originalbilder nach Abteilung',
        'ma_sport_fotobox_anzeigen', 'ma_club', 'normal', 'default');
});
function ma_sport_fotobox_anzeigen(WP_Post $p): void {
    wp_nonce_field('ma_sport_fotobox_speichern', 'ma_sport_fotobox_nonce');
    $fotos = get_posts([
        'post_type'=>'attachment','post_status'=>'inherit','post_mime_type'=>'image',
        'posts_per_page'=>300,'orderby'=>'date','order'=>'DESC',
        'meta_query'=>[['key'=>'ma_image_rights_verified','value'=>'1','compare'=>'=']],
    ]);
    $fotos = array_values(array_filter($fotos, static function ($a): bool {
        return trim((string) get_post_meta($a->ID, 'ma_image_credit', true)) !== ''
            && trim((string) get_post_meta($a->ID, 'ma_image_license', true)) !== '';
    }));
    echo '<p>Pro Sportart das konkrete Foto der Vereinsabteilung auswählen. Nur freigegebene Bilder erscheinen in der Auswahl. Ein Foto eines Fußballspiels gehört nicht als Tennisfoto in den Sportbereich. Bildquelle und Nutzung in der Mediathek dokumentieren.</p>';
    echo '<table class="widefat striped"><thead><tr><th>Sportart</th><th>Freigegebenes Originalfoto</th></tr></thead><tbody>';
    foreach (ma_sport_bild_keys() as $k) {
        $jetzt = (int) get_post_meta($p->ID, 'ma_sportfoto_' . $k, true);
        echo '<tr><th><label for="ma_sportfoto_' . esc_attr($k) . '">' . esc_html(ma_sportart_titel($k)) . '</label></th><td><select id="ma_sportfoto_' . esc_attr($k) . '" name="ma_sportfoto[' . esc_attr($k) . ']" style="max-width:100%;width:100%"><option value="0">Automatischer Sportbildpool / Motivgrafik</option>';
        foreach ($fotos as $a) {
            echo '<option value="' . (int) $a->ID . '"' . selected($jetzt, (int) $a->ID, false) . '>' . esc_html(get_the_title($a) . ' – #' . $a->ID) . '</option>';
        }
        echo '</select></td></tr>';
    }
    echo '</tbody></table><p class="description">Die Zuordnung betrifft ausschließlich die Sportkarte des jeweiligen Hauptvereins. Bereits veröffentlichte Vereinsinhalte werden nicht überschrieben.</p>';
}

add_action('save_post_ma_club', static function (int $id): void {
    if (!isset($_POST['ma_sport_fotobox_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_sport_fotobox_nonce'])), 'ma_sport_fotobox_speichern')) return;
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($id) || !current_user_can('edit_post', $id) || !current_user_can('edit_others_posts')) return;
    $daten = isset($_POST['ma_sportfoto']) && is_array($_POST['ma_sportfoto']) ? wp_unslash($_POST['ma_sportfoto']) : [];
    foreach (ma_sport_bild_keys() as $k) {
        if (!array_key_exists($k, $daten)) continue;
        $idBild = absint($daten[$k]);
        $meta = 'ma_sportfoto_' . $k;
        if ($idBild && wp_attachment_is_image($idBild)
            && (string) get_post_meta($idBild, 'ma_image_rights_verified', true) === '1'
            && trim((string) get_post_meta($idBild, 'ma_image_credit', true)) !== ''
            && trim((string) get_post_meta($idBild, 'ma_image_license', true)) !== '') {
            update_post_meta($id, $meta, $idBild);
        } else delete_post_meta($id, $meta);
    }
}, 20);
