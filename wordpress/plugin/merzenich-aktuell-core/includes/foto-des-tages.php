<?php
/**
 * Foto des Tages aus Leser-Einsendungen (1.21.0).
 *
 * Leserinnen und Leser senden über „Meldung senden“ (Formular typ="foto") ein
 * Foto mit Ortsteil, Bildrechte-Bestätigung und Zustimmung zur Veröffentlichung.
 * Die Redaktion plant es im Eingang für ein Datum ein; das Theme zeigt an diesem
 * Tag dieses Foto vor der Reihe der Ortsansichten (ma21_foto_des_tages).
 * Gespeichert in der Option ma_foto_des_tages: Datum (JJJJ-MM-TT) => Eintrag.
 */
if (!defined('ABSPATH')) { exit; }

/** Alle eingeplanten Fotos, nach Datum sortiert. */
function ma_foto_des_tages_alle(): array {
    $l = get_option('ma_foto_des_tages', []);
    $l = is_array($l) ? array_filter($l, fn($e, $d) => is_array($e) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $d) && !empty($e['bild']), ARRAY_FILTER_USE_BOTH) : [];
    ksort($l);
    return $l;
}

/** Eingeplantes Leserfoto für ein Datum, im Format der Theme-Reihe (src, srcset, alt, ort, credit), sonst null. */
function ma_foto_des_tages_eingeplant(string $datum): ?array {
    $e = ma_foto_des_tages_alle()[$datum] ?? null;
    if (!$e) return null;
    $id = (int) $e['bild'];
    $src = $id ? (string) wp_get_attachment_image_url($id, 'full') : '';
    if ($src === '') return null;
    return ['src' => $src, 'srcset' => (string) wp_get_attachment_image_srcset($id, 'full'), 'alt' => (string) $e['alt'], 'ort' => (string) $e['ort'], 'credit' => (string) $e['credit'], 'leser' => true];
}

/** Einplanen: prüft Art, Bildrechte, Zustimmung, Datum und Bild. Gibt '' oder eine Fehlermeldung zurück. */
function ma_foto_des_tages_einplanen(int $eingang, string $datum): string {
    $p = get_post($eingang);
    if (!$p || $p->post_type !== 'ma_eingang') return 'Einsendung nicht gefunden.';
    $m = fn(string $k): string => (string) get_post_meta($eingang, '_ma_eingang_' . $k, true);
    if ($m('typ') !== 'foto') return 'Das ist keine Einsendung für das Foto des Tages.';
    if ($m('bildrechte') !== '1' || $m('zustimmung') !== '1') return 'Bildrechte und Zustimmung zur Veröffentlichung fehlen. Bitte beim Einsender nachfragen.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datum) || $datum < (string) wp_date('Y-m-d')) return 'Bitte ein Datum ab heute wählen.';
    $bild = 0;
    foreach (get_attached_media('image', $eingang) as $a) { $bild = (int) $a->ID; break; }
    if (!$bild) return 'Die Einsendung hat kein Bild.';
    $alle = ma_foto_des_tages_alle();
    if (isset($alle[$datum]) && (int) ($alle[$datum]['eingang'] ?? 0) !== $eingang) return 'Für diesen Tag ist schon ein Foto eingeplant.';
    $orte = function_exists('ma_form_ortsteile') ? ma_form_ortsteile() : [];
    $ort = $orte[$m('art')] ?? 'Gemeinde Merzenich';
    $alt = trim($m('betreff')) !== '' ? $m('betreff') : 'Leserfoto aus ' . $ort;
    $fotograf = $m('fotograf') !== '' ? $m('fotograf') : $m('name');
    // Ein Foto nur an einem Tag: alte Einplanung derselben Einsendung entfernen.
    foreach ($alle as $d => $e) if ((int) ($e['eingang'] ?? 0) === $eingang) unset($alle[$d]);
    $alle[$datum] = ['bild' => $bild, 'eingang' => $eingang, 'ort' => $ort, 'alt' => $alt, 'credit' => $fotograf . ' · Leserfoto', 'geplant_von' => get_current_user_id(), 'geplant_am' => current_time('mysql')];
    update_option('ma_foto_des_tages', $alle, false);
    update_post_meta($bild, '_wp_attachment_image_alt', $alt);
    update_post_meta($bild, 'ma_image_credit', $fotograf . ' · Leserfoto');
    update_post_meta($eingang, '_ma_foto_des_tages', $datum);
    wp_update_post(['ID' => $eingang, 'post_status' => 'ma_erledigt']);
    return '';
}

function ma_foto_des_tages_ausplanen(int $eingang): void {
    $alle = ma_foto_des_tages_alle();
    foreach ($alle as $d => $e) if ((int) ($e['eingang'] ?? 0) === $eingang) unset($alle[$d]);
    update_option('ma_foto_des_tages', $alle, false);
    delete_post_meta($eingang, '_ma_foto_des_tages');
}

/* Kasten im Eingang: einplanen, verschieben, entfernen. */
add_action('add_meta_boxes_ma_eingang', function (WP_Post $p): void {
    if ((string) get_post_meta($p->ID, '_ma_eingang_typ', true) !== 'foto') return;
    add_meta_box('ma-foto-des-tages', 'Foto des Tages', function (WP_Post $p): void {
        $m = fn(string $k): string => (string) get_post_meta($p->ID, '_ma_eingang_' . $k, true);
        $geplant = (string) get_post_meta($p->ID, '_ma_foto_des_tages', true);
        $url = wp_nonce_url(admin_url('admin-post.php?action=ma_foto_einplanen&eingang=' . $p->ID), 'ma_foto_einplanen_' . $p->ID);
        echo '<p>Bildrechte: <strong>' . ($m('bildrechte') === '1' ? 'bestätigt' : 'fehlt') . '</strong> · Zustimmung zur Veröffentlichung: <strong>' . ($m('zustimmung') === '1' ? 'ja' : 'fehlt') . '</strong></p>';
        if ($geplant !== '') echo '<p>Eingeplant für <strong>' . esc_html(wp_date('l, j. F Y', strtotime($geplant . ' 12:00'))) . '</strong>.</p>';
        echo '<p><label>Datum<br><input type="date" id="ma-foto-datum" min="' . esc_attr((string) wp_date('Y-m-d')) . '" value="' . esc_attr($geplant ?: (string) wp_date('Y-m-d', time() + DAY_IN_SECONDS)) . '"></label></p>';
        echo '<p><a class="button button-primary" href="#" data-url="' . esc_url($url) . '" onclick="var d=document.getElementById(\'ma-foto-datum\').value;if(!d){alert(\'Bitte Datum wählen.\');return false;}location.href=this.dataset.url+\'&datum=\'+encodeURIComponent(d);return false;">' . ($geplant !== '' ? 'Verschieben' : 'Als Foto des Tages einplanen') . '</a></p>';
        if ($geplant !== '') echo '<p><a href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=ma_foto_ausplanen&eingang=' . $p->ID), 'ma_foto_einplanen_' . $p->ID)) . '">Einplanung entfernen</a></p>';
        $naechste = array_slice(array_filter(ma_foto_des_tages_alle(), fn($d) => $d >= (string) wp_date('Y-m-d'), ARRAY_FILTER_USE_KEY), 0, 7, true);
        if ($naechste) { echo '<p class="description">Schon eingeplant:</p><ul>'; foreach ($naechste as $d => $e) echo '<li>' . esc_html(wp_date('d.m.', strtotime($d . ' 12:00')) . ' · ' . $e['ort'] . ' · ' . $e['credit']) . '</li>'; echo '</ul>'; }
        echo '<p class="description">Ohne eingeplantes Leserfoto zeigt die Startseite täglich eine Ortsansicht.</p>';
    }, 'ma_eingang', 'side', 'high');
});

add_action('admin_post_ma_foto_einplanen', function (): void {
    $id = (int) ($_GET['eingang'] ?? 0);
    check_admin_referer('ma_foto_einplanen_' . $id);
    if (!$id || !current_user_can('edit_others_posts') || (function_exists('ma_current_partner_policy') && ma_current_partner_policy())) wp_die('Keine Berechtigung.', 403);
    $fehler = ma_foto_des_tages_einplanen($id, sanitize_text_field(wp_unslash($_GET['datum'] ?? '')));
    if ($fehler !== '') wp_die(esc_html($fehler), 400);
    wp_safe_redirect(admin_url('post.php?post=' . $id . '&action=edit')); exit;
});
add_action('admin_post_ma_foto_ausplanen', function (): void {
    $id = (int) ($_GET['eingang'] ?? 0);
    check_admin_referer('ma_foto_einplanen_' . $id);
    if (!$id || !current_user_can('edit_others_posts')) wp_die('Keine Berechtigung.', 403);
    ma_foto_des_tages_ausplanen($id);
    wp_safe_redirect(admin_url('post.php?post=' . $id . '&action=edit')); exit;
});
