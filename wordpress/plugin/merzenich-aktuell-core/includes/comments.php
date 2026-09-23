<?php
/**
 * Kommentar-Moderation fuer Merzenich Aktuell.
 *
 * Jeder neue Kommentar wartet auf Freigabe. Die Redaktion kann in einer
 * kompakten Liste alle unkritischen Kommentare vorausgewaehlt lassen,
 * problematische abwaehlen und den Rest in einem Schritt genehmigen.
 * Nach Genehmigung erhaelt der Verfasser eine E-Mail, sofern eine gueltige
 * E-Mail-Adresse angegeben wurde.
 */
if (!defined('ABSPATH')) { exit; }

function ma_register_comment_moderation_hooks(): void {
    add_filter('pre_comment_approved', 'ma_comments_force_moderation', 20, 2);
    add_action('transition_comment_status', 'ma_comment_approval_mail', 10, 3);
    add_action('admin_menu', 'ma_comment_moderation_menu');
    add_filter('comment_row_actions', 'ma_comment_row_actions', 10, 2);
}

function ma_comments_force_moderation($approved, array $commentdata) {
    if ($approved === 'spam' || $approved === 'trash') return $approved;
    if (current_user_can('moderate_comments')) return $approved;
    return 0;
}

function ma_comment_approval_mail(string $new_status, string $old_status, WP_Comment $comment): void {
    if ($new_status !== 'approved' || $old_status === 'approved') return;
    $email = sanitize_email((string)$comment->comment_author_email);
    if (!$email || !is_email($email)) return;

    $title = get_the_title((int)$comment->comment_post_ID);
    $link = get_comment_link($comment);
    $subject = '[Merzenich Aktuell] Ihr Kommentar wurde freigeschaltet';
    $body = "Guten Tag";
    if (trim((string)$comment->comment_author) !== '') $body .= ' '.trim((string)$comment->comment_author);
    $body .= ",\n\nIhr Kommentar";
    if ($title !== '') $body .= ' zum Beitrag „'.$title.'“';
    $body .= " wurde von der Redaktion freigegeben und ist jetzt sichtbar.\n\n";
    $body .= $link."\n\nMerzenich Aktuell";
    wp_mail($email, $subject, $body);
}

function ma_comment_moderation_menu(): void {
    add_comments_page(
        'Kommentar-Freigabe',
        'Sammelfreigabe',
        'moderate_comments',
        'ma-kommentar-freigabe',
        'ma_comment_moderation_page'
    );
}

function ma_comment_moderation_process(): string {
    if (!current_user_can('moderate_comments')) return '';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return '';
    check_admin_referer('ma_comment_bulk');

    $ids = isset($_POST['comment_ids']) && is_array($_POST['comment_ids'])
        ? array_values(array_unique(array_filter(array_map('intval', $_POST['comment_ids']))))
        : [];
    if (!$ids) return 'Keine Kommentare ausgewählt.';

    if (isset($_POST['approve_selected'])) {
        $done = 0;
        foreach ($ids as $id) {
            $comment = get_comment($id);
            if (!$comment || (string)$comment->comment_approved !== '0') continue;
            if (wp_set_comment_status($id, 'approve', true)) $done++;
        }
        return $done.' Kommentar'.($done===1?'':'e').' genehmigt.';
    }

    if (isset($_POST['trash_selected'])) {
        $done = 0;
        foreach ($ids as $id) {
            $comment = get_comment($id);
            if (!$comment || (string)$comment->comment_approved !== '0') continue;
            if (wp_trash_comment($id)) $done++;
        }
        return $done.' Kommentar'.($done===1?'':'e').' abgelehnt und in den Papierkorb verschoben.';
    }

    return '';
}

function ma_comment_moderation_page(): void {
    if (!current_user_can('moderate_comments')) return;
    $meldung = ma_comment_moderation_process();
    $comments = get_comments([
        'status'=>'hold',
        'number'=>100,
        'orderby'=>'comment_date_gmt',
        'order'=>'ASC',
    ]);

    echo '<div class="wrap"><h1>Kommentar-Freigabe</h1>';
    echo '<p>Alle offenen Kommentare sind zunächst ausgewählt. Entfernen Sie den Haken bei Kommentaren, die <strong>nicht</strong> freigegeben werden sollen, und klicken Sie anschließend auf „Alle ausgewählten genehmigen“.</p>';
    if ($meldung !== '') echo '<div class="notice notice-success is-dismissible"><p>'.esc_html($meldung).'</p></div>';

    if (!$comments) {
        echo '<div class="notice notice-info"><p>Aktuell warten keine Kommentare auf Freigabe.</p></div></div>';
        return;
    }

    echo '<form method="post">'; wp_nonce_field('ma_comment_bulk');
    echo '<p><label><input type="checkbox" id="ma-comment-all" checked> Alle auswählen / abwählen</label></p>';
    echo '<table class="widefat striped"><thead><tr><th style="width:36px"></th><th>Kommentar</th><th>Verfasser</th><th>Beitrag</th><th>Eingang</th></tr></thead><tbody>';
    foreach ($comments as $comment) {
        $id=(int)$comment->comment_ID;
        $post_id=(int)$comment->comment_post_ID;
        echo '<tr>';
        echo '<td><input class="ma-comment-check" type="checkbox" name="comment_ids[]" value="'.esc_attr((string)$id).'" checked aria-label="Kommentar '.esc_attr((string)$id).' auswählen"></td>';
        echo '<td><div style="max-width:620px;white-space:pre-wrap">'.esc_html(wp_trim_words((string)$comment->comment_content,80,' …')).'</div></td>';
        echo '<td><strong>'.esc_html((string)$comment->comment_author).'</strong>';
        if ($comment->comment_author_email) echo '<br><small>'.esc_html((string)$comment->comment_author_email).'</small>';
        echo '</td>';
        echo '<td><a href="'.esc_url(get_edit_post_link($post_id)).'">'.esc_html(get_the_title($post_id)).'</a></td>';
        echo '<td>'.esc_html(get_comment_date('d.m.Y H:i',$comment)).'</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '<p style="display:flex;gap:10px;align-items:center"><button class="button button-primary button-hero" name="approve_selected" value="1">Alle ausgewählten genehmigen</button>';
    echo '<button class="button" name="trash_selected" value="1" onclick="return confirm(\'Ausgewählte Kommentare wirklich ablehnen?\')">Ausgewählte ablehnen</button></p>';
    echo '</form>';
    echo '<script>document.getElementById("ma-comment-all")?.addEventListener("change",function(){document.querySelectorAll(".ma-comment-check").forEach((c)=>c.checked=this.checked);});</script>';
    echo '</div>';
}

function ma_comment_row_actions(array $actions, WP_Comment $comment): array {
    if ((string)$comment->comment_approved === '0' && current_user_can('moderate_comments')) {
        $actions['ma_bulk_hint'] = '<a href="'.esc_url(admin_url('edit-comments.php?page=ma-kommentar-freigabe')).'">Zur Sammelfreigabe</a>';
    }
    return $actions;
}
