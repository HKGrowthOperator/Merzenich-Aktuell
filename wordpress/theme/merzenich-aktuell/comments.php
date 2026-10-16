<?php if (post_password_required()) return;
/* Formular im Stil der übrigen Formulare (.form, .btn) mit Hinweis auf Kommentarrichtlinien und Datenschutz (21.13.0). */
?><section id="comments" class="comments-area"><h2>Kommentare</h2><?php if (have_comments()): ?><ol><?php wp_list_comments(['style' => 'ol', 'short_ping' => true, 'avatar_size' => 0]); ?></ol><?php endif; ?><?php if (comments_open()) comment_form([
    'title_reply' => 'Kommentar schreiben',
    'class_form' => 'comment-form form',
    'class_submit' => 'btn',
    'comment_notes_before' => '<p class="ma-form-note">Ihre E-Mail-Adresse wird nicht veröffentlicht. Kommentare erscheinen erst nach Prüfung durch die Redaktion; es gelten die <a href="' . esc_url(home_url('/kommentarregeln/')) . '">Kommentarrichtlinien</a>. Wie wir Ihre Angaben verarbeiten, steht in der <a href="' . esc_url(home_url('/datenschutz/')) . '">Datenschutzerklärung</a>.</p>',
    'fields' => [
        'author' => '<p><label>Name<br><input name="author" required maxlength="120" autocomplete="name"></label></p>',
        'email' => '<p><label>E-Mail<br><input name="email" type="email" required maxlength="190" autocomplete="email"></label></p>',
        // Eigener Speichern-Hinweis (16.10.2026): Der Kern-Text nannte ein Adressfeld, das es hier nicht gibt.
        'cookies' => '<p class="comment-form-cookies-consent check"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . (defined('COOKIEHASH') && isset($_COOKIE['comment_author_email_' . COOKIEHASH]) ? ' checked' : '') . '> <label for="wp-comment-cookies-consent">Name und E-Mail-Adresse in diesem Browser für meinen nächsten Kommentar speichern.</label></p>',
    ],
    'comment_field' => '<p><label>Kommentar<br><textarea name="comment" rows="6" required maxlength="4000"></textarea></label></p>',
    'label_submit' => 'Kommentar absenden',
]); ?></section>
