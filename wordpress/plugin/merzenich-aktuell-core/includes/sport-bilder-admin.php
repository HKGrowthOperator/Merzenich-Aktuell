<?php
/**
 * WordPress-Pflegeoberfläche für das bestehende Sportbildsystem.
 * Nutzt die vorhandenen Medienfelder ma_credit, ma_quelle, ma_nutzung aus
 * bildrechte.php und ergänzt nur Sportpool + redaktionelle Verifikation.
 */
if (!defined('ABSPATH')) { exit; }

function ma_sport_bild_keys(): array {
    return ['fussball','tischtennis','tennis','badminton','pickleball','volleyball','billard','boule',
        'schach','ju-jutsu','breitensport','wasser','fitness','wandern',
        'schiesssport','tanzsport','luftsport','american-football'];
}

/* Redaktionelle Rechteprüfung je Medium (03.10.2026): für jedes Bild, durch die
   Redaktion (edit_others_posts), drei Stufen. „Geprüft“ setzt wie bisher
   ma_image_rights_verified, das Theme und Sportausgabe lesen. */
function ma_rechtepruefung_stufen(): array {
    return ['offen' => 'Offen – noch nicht geprüft', 'geprueft' => 'Geprüft – Nutzung freigegeben', 'abgelehnt' => 'Abgelehnt – nicht verwenden'];
}

function ma_rechtepruefung(int $id): string {
    $s = (string) get_post_meta($id, 'ma_rechtepruefung', true);
    if (isset(ma_rechtepruefung_stufen()[$s])) return $s;
    return (string) get_post_meta($id, 'ma_image_rights_verified', true) === '1' ? 'geprueft' : 'offen';
}

/** Fotograf, Nutzungsgrundlage und Quelle eines Mediums (neue Felder vor den importierten). */
function ma_medium_nachweis(int $id): array {
    $w = static fn(string $neu, string $alt): string => trim((string) get_post_meta($id, $neu, true)) ?: trim((string) get_post_meta($id, $alt, true));
    return ['credit' => $w('ma_credit', 'ma_image_credit'), 'nutzung' => $w('ma_nutzung', 'ma_image_license'), 'quelle' => $w('ma_quelle', 'ma_image_original_url')];
}

/** Aus dem vorhandenen Medien-Editor, kein zweites Upload-/Rechtesystem. */
add_filter('attachment_fields_to_edit', static function (array $f, WP_Post $a): array {
    if (!wp_attachment_is_image($a->ID)) return $f;
    if (current_user_can('manage_options')) {
        $jetzt = (string) get_post_meta($a->ID, 'ma_sportpool', true);
        $html = '<select name="attachments[' . (int) $a->ID . '][ma_sportpool]"><option value="">– kein Sportpool –</option>';
        foreach (ma_sport_bild_keys() as $k) $html .= '<option value="' . esc_attr($k) . '"' . selected($jetzt, $k, false) . '>' . esc_html(ma_sportart_titel($k)) . '</option>';
        $html .= '</select><p class="help">Nur sportartspezifische, gesichtete Motive. Das Bild darf nicht fälschlich einen lokalen Verein oder ein konkretes Ereignis behaupten.</p>';
        $f['ma_sportpool'] = ['label'=>'Sport: Motivpool', 'input'=>'html', 'html'=>$html];
    }
    if (current_user_can('edit_others_posts')) {
        $stufe = ma_rechtepruefung($a->ID);
        $html = '<select name="attachments[' . (int) $a->ID . '][ma_rechtepruefung]">';
        foreach (ma_rechtepruefung_stufen() as $k => $label) $html .= '<option value="' . esc_attr($k) . '"' . selected($stufe, $k, false) . '>' . esc_html($label) . '</option>';
        $html .= '</select>';
        $von = get_post_meta($a->ID, 'ma_rechtepruefung_von', true);
        if (is_array($von) && !empty($von['t'])) { $u = get_userdata((int) ($von['u'] ?? 0)); $html .= '<p class="help">Zuletzt: ' . esc_html(($u ? $u->display_name : 'unbekannt') . ', ' . wp_date('d.m.Y H:i', (int) $von['t'])) . '</p>'; }
        $html .= '<p class="help">„Geprüft“ erst nach Prüfung von Fotograf / Urheber, Quelle, Nutzungsgrundlage und den gegebenenfalls notwendigen Einwilligungen; die drei Felder oben müssen ausgefüllt sein. Kein erfundenes oder falsch zugeordnetes Ereignisbild.</p>';
        $f['ma_rechtepruefung'] = ['label'=>'Bildrechte: redaktionelle Prüfung', 'input'=>'html', 'html'=>$html];
    }
    return $f;
}, 20, 2);

add_filter('attachment_fields_to_save', static function (array $post, array $daten): array {
    $id = (int) ($post['ID'] ?? 0);
    if (!$id || !wp_attachment_is_image($id)) return $post;
    if (isset($daten['ma_sportpool']) && current_user_can('manage_options')) {
        $pool = sanitize_key((string) $daten['ma_sportpool']);
        if ($pool === '' || !in_array($pool, ma_sport_bild_keys(), true)) delete_post_meta($id, 'ma_sportpool');
        else update_post_meta($id, 'ma_sportpool', $pool);
    }
    // Spiegel für Theme und Sportausgabe: nur ausgefüllte Felder übernehmen. Leere
    // neue Felder löschen keine vorhandenen (importierten) Angaben mehr.
    $credit = trim((string) get_post_meta($id, 'ma_credit', true));
    $nutzung = trim((string) get_post_meta($id, 'ma_nutzung', true));
    $quelle = trim((string) get_post_meta($id, 'ma_quelle', true));
    if ($credit !== '') update_post_meta($id, 'ma_image_credit', $credit);
    if ($nutzung !== '') update_post_meta($id, 'ma_image_license', $nutzung);
    if (filter_var($quelle, FILTER_VALIDATE_URL)) update_post_meta($id, 'ma_image_original_url', esc_url_raw($quelle));
    // Älteres Formularfeld (bis 1.18): 1 = geprüft, 0 = offen.
    $stufe = isset($daten['ma_rechtepruefung']) ? sanitize_key((string) $daten['ma_rechtepruefung']) : (isset($daten['ma_sport_verified']) ? ((string) $daten['ma_sport_verified'] === '1' ? 'geprueft' : 'offen') : '');
    if ($stufe !== '' && current_user_can('edit_others_posts') && isset(ma_rechtepruefung_stufen()[$stufe]) && $stufe !== ma_rechtepruefung($id)) {
        $n = ma_medium_nachweis($id);
        if ($stufe === 'geprueft' && ($n['credit'] === '' || $n['nutzung'] === '' || $n['quelle'] === '')) {
            $post['errors']['ma_rechtepruefung']['errors'][] = 'Für „Geprüft“ bitte Fotograf / Urheber, Quelle und Nutzungsgrundlage ausfüllen.';
        } else {
            ma_rechtepruefung_setzen($id, $stufe);
        }
    }
    return $post;
}, 20, 2);

/** Stufe setzen, protokollieren und an Beiträge mit diesem Beitragsbild weitergeben. */
function ma_rechtepruefung_setzen(int $id, string $stufe): void {
    update_post_meta($id, 'ma_rechtepruefung', $stufe);
    update_post_meta($id, 'ma_rechtepruefung_von', ['u' => get_current_user_id(), 't' => time()]);
    if ($stufe === 'geprueft') update_post_meta($id, 'ma_image_rights_verified', '1');
    else delete_post_meta($id, 'ma_image_rights_verified');
    if ($stufe !== 'geprueft') return;
    foreach (get_posts(['post_type' => 'any', 'post_status' => 'any', 'numberposts' => 50, 'fields' => 'ids', 'meta_key' => '_thumbnail_id', 'meta_value' => (string) $id]) as $pid) {
        ma_medium_nachweis_uebernehmen((int) $pid, $id);
    }
}

/**
 * Bildnachweis vom Medium an den Beitrag (03.10.2026). Das Theme liest Credit,
 * Lizenz und „Bildrechte geprüft“ am Beitrag. Fotograf wird übernommen, wenn der
 * Beitrag noch keinen hat; Lizenz, Quelle und „geprüft“ nur, wenn die Redaktion
 * das Medium geprüft hat. Vorhandene Angaben am Beitrag bleiben.
 */
function ma_medium_nachweis_uebernehmen(int $post_id, int $att_id): void {
    if (!$post_id || !$att_id || get_post_type($att_id) !== 'attachment') return;
    $n = ma_medium_nachweis($att_id);
    if ($n['credit'] !== '' && trim((string) get_post_meta($post_id, 'ma_image_credit', true)) === '') update_post_meta($post_id, 'ma_image_credit', $n['credit']);
    if (ma_rechtepruefung($att_id) !== 'geprueft') return;
    if ($n['nutzung'] !== '' && trim((string) get_post_meta($post_id, 'ma_image_license', true)) === '') update_post_meta($post_id, 'ma_image_license', $n['nutzung']);
    if (filter_var($n['quelle'], FILTER_VALIDATE_URL) && trim((string) get_post_meta($post_id, 'ma_image_original_url', true)) === '') update_post_meta($post_id, 'ma_image_original_url', esc_url_raw($n['quelle']));
    if (current_user_can('edit_others_posts')) update_post_meta($post_id, 'ma_image_rights_verified', '1');
}

add_action('added_post_meta', 'ma_medium_beitragsbild_gesetzt', 10, 4);
add_action('updated_post_meta', 'ma_medium_beitragsbild_gesetzt', 10, 4);
function ma_medium_beitragsbild_gesetzt($meta_id, $post_id, $key, $wert): void {
    if ($key === '_thumbnail_id' && get_post_type((int) $post_id) !== 'attachment') ma_medium_nachweis_uebernehmen((int) $post_id, (int) $wert);
}

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
        echo '<tr><th><label for="ma_sportfoto_' . esc_attr($k) . '">' . esc_html(ma_sportart_titel($k)) . '</label></th><td><select id="ma_sportfoto_' . esc_attr($k) . '" name="ma_sportfoto[' . esc_attr($k) . ']" style="max-width:100%;width:100%"><option value="0">Automatischer Sportbildpool</option>';
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
