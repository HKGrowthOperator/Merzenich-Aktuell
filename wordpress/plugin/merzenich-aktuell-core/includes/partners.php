<?php
/**
 * Begrenzte Redaktionspartner: Polizei/Feuerwehr, Sport, Rathaus, Vereine,
 * Unternehmen/Werbung und Immobilien.
 *
 * Partner duerfen eigene Inhalte erstellen und zur Freigabe einreichen.
 * Sie duerfen niemals selbst veroeffentlichen, fremde Inhalte bearbeiten oder
 * redaktionelle Freigaben setzen.
 */
if (!defined('ABSPATH')) { exit; }

// Hochzaehlen, wenn Rollen dazukommen: add_role laeuft dann beim naechsten Aufruf.
if (!defined('MA_PARTNER_ROLES_VERSION')) define('MA_PARTNER_ROLES_VERSION', '2');

function ma_partner_policies(): array {
    return [
        'ma_polizei_partner' => [
            'label' => 'Polizei-Partner',
            'description' => 'Polizei / Kreispolizeibehörde',
            'post_types' => ['post'],
            'categories' => ['blaulicht'],
        ],
        'ma_feuerwehr_partner' => [
            'label' => 'Feuerwehr-Partner',
            'description' => 'Feuerwehr / Löschgruppe',
            'post_types' => ['post'],
            'categories' => ['blaulicht'],
        ],
        // Bis 1.3.0 teilten sich Polizei und Feuerwehr diese Rolle. Sie bleibt,
        // damit bestehende Zugaenge weiter funktionieren; neue Zugaenge bekommen
        // eine der beiden Rollen oben.
        'ma_blaulicht_partner' => [
            'label' => 'Blaulicht-Partner (alt)',
            'description' => 'Polizei / Feuerwehr (bisherige gemeinsame Rolle)',
            'post_types' => ['post'],
            'categories' => ['blaulicht'],
            'veraltet' => true,
        ],
        'ma_sport_partner' => [
            'label' => 'Sport-Partner',
            'description' => 'Sportverein / Mannschaft',
            'post_types' => ['post'],
            'categories' => ['sport'],
        ],
        'ma_rathaus_partner' => [
            'label' => 'Rathaus-Partner',
            'description' => 'Gemeinde / Rathaus',
            'post_types' => ['post'],
            'categories' => ['rathaus'],
        ],
        'ma_vereine_partner' => [
            'label' => 'Vereins-Partner',
            'description' => 'Verein / Initiative',
            'post_types' => ['post'],
            'categories' => ['vereine'],
        ],
        'ma_wirtschaft_partner' => [
            'label' => 'Unternehmens-Partner',
            'description' => 'Unternehmen / Sponsor / Werbekunde',
            'post_types' => ['post','ma_business','ma_tip','ma_ad'],
            'categories' => ['wirtschaft'],
        ],
        'ma_immobilien_partner' => [
            'label' => 'Immobilien-Partner',
            'description' => 'Makler / Immobilien-Verantwortliche',
            'post_types' => ['ma_property'],
            'categories' => [],
        ],
    ];
}

function ma_register_partner_roles(): void {
    foreach (ma_partner_policies() as $role => $policy) {
        $obj = get_role($role);
        if (!$obj) {
            add_role($role, $policy['label'], [
                'read' => true,
                'edit_posts' => true,
                'upload_files' => true,
                'publish_posts' => false,
                'edit_published_posts' => false,
                'delete_posts' => false,
                'delete_published_posts' => false,
            ]);
            continue;
        }
        $obj->add_cap('read');
        $obj->add_cap('edit_posts');
        $obj->add_cap('upload_files');
        $obj->remove_cap('publish_posts');
        $obj->remove_cap('edit_published_posts');
        $obj->remove_cap('delete_posts');
        $obj->remove_cap('delete_published_posts');
    }
    update_option('ma_partner_roles_version', MA_PARTNER_ROLES_VERSION);
}

function ma_current_partner_policy(?WP_User $user=null): ?array {
    if (!$user) $user = wp_get_current_user();
    if (!$user || !$user->exists()) return null;
    $policies = ma_partner_policies();
    foreach ((array)$user->roles as $role) {
        if (isset($policies[$role])) return ['role'=>$role] + $policies[$role];
    }
    return null;
}

function ma_is_partner_user(?WP_User $user=null): bool {
    return ma_current_partner_policy($user) !== null;
}

function ma_register_partner_hooks(): void {
    add_action('init', function () {
        if (get_option('ma_partner_roles_version') !== MA_PARTNER_ROLES_VERSION) ma_register_partner_roles();
    }, 30);

    add_filter('map_meta_cap', 'ma_partner_map_meta_cap', 20, 4);
    add_filter('wp_insert_post_data', 'ma_partner_force_pending', 5, 2);
    add_action('save_post', 'ma_partner_enforce_assignment', 100, 3);
    add_action('save_post', 'ma_partner_save_rights', 20);
    add_action('transition_post_status', 'ma_partner_submission_notification', 20, 3);
    add_action('admin_menu', 'ma_partner_admin_menu', 100);
    add_action('admin_init', 'ma_partner_admin_route_guard', 5);
    add_action('pre_get_posts', 'ma_partner_admin_own_content');
    add_action('admin_notices', 'ma_partner_admin_notice');

    foreach (['post','ma_event','ma_property','ma_job','ma_obituary','ma_family_notice','ma_club','ma_business','ma_tip','ma_ad'] as $type) {
        add_filter('rest_pre_insert_'.$type, 'ma_partner_rest_guard', 10, 2);
    }
}

function ma_partner_map_meta_cap(array $caps, string $cap, int $user_id, array $args): array {
    if (!in_array($cap, ['edit_post','delete_post','read_post'], true) || empty($args[0])) return $caps;
    $user = get_userdata($user_id);
    $policy = $user ? ma_current_partner_policy($user) : null;
    if (!$policy) return $caps;

    $post = get_post((int)$args[0]);
    if (!$post) return $caps;

    if (!in_array($post->post_type, $policy['post_types'], true)) return ['do_not_allow'];
    if ((int)$post->post_author !== $user_id) return ['do_not_allow'];
    if ($post->post_status === 'publish' && $cap !== 'read_post') return ['do_not_allow'];
    return $caps;
}

/*
 * Fotoerlaubnis: Partner (Feuerwehr, Polizei, Makler, Vereine ...) laden eigene
 * Fotos hoch. Bevor ein Beitrag mit Bild zur Freigabe geht, bestaetigen sie,
 * dass sie die Fotos veroeffentlichen duerfen und dass nichts Identifizierendes
 * zu sehen ist. Die Redaktion sieht die Erklaerung (wer, wann) und setzt danach
 * selbst "Bildrechte geprueft". Ohne Erklaerung bleibt der Beitrag Entwurf.
 */
const MA_PARTNER_RIGHTS_META = 'ma_partner_rights_declared';
const MA_PARTNER_RIGHTS_MISSING_META = '_ma_partner_rights_missing';

function ma_partner_rights_text(): string {
    return 'Wir haben die Fotos selbst aufgenommen oder dürfen sie veröffentlichen. Kennzeichen, erkennbare Gesichter und Hausnummern sind nicht zu sehen oder unkenntlich gemacht.';
}

/** Hat der Beitrag ein Bild? Formularwert vor gespeichertem Stand. */
function ma_partner_has_image(int $post_id, array $postarr): bool {
    if (array_key_exists('_thumbnail_id', $postarr)) return (int)$postarr['_thumbnail_id'] > 0;
    return $post_id > 0 && function_exists('has_post_thumbnail') && has_post_thumbnail($post_id);
}

/**
 * Liegt die Erklaerung vor? Kommt das Redaktionsformular mit, zaehlt nur der
 * Haken darin (abgewaehlt heisst abgewaehlt); sonst der gespeicherte Stand.
 */
function ma_partner_rights_declared(int $post_id): bool {
    if (isset($_POST['ma_editorial_nonce'])) return isset($_POST[MA_PARTNER_RIGHTS_META]);
    return $post_id > 0 && (string)get_post_meta($post_id, MA_PARTNER_RIGHTS_META, true) === '1';
}

/** Haken fuer Partner, Nachweis fuer die Redaktion (im Feld "Bildtyp"). */
function ma_partner_rights_field(int $post_id): void {
    if (ma_current_partner_policy()) {
        $v = (string)get_post_meta($post_id, MA_PARTNER_RIGHTS_META, true);
        echo '<p><label><input type="checkbox" name="'.esc_attr(MA_PARTNER_RIGHTS_META).'" value="1" '.checked($v, '1', false).'> <strong>Fotoerlaubnis:</strong> '.esc_html(ma_partner_rights_text()).'</label>';
        echo '<br><span class="description">Pflicht, wenn der Beitrag ein Bild hat. Ohne diesen Haken bleibt er Entwurf.</span></p>';
        return;
    }
    if ((string)get_post_meta($post_id, '_ma_partner_submission', true) !== '1') return;
    if ((string)get_post_meta($post_id, MA_PARTNER_RIGHTS_META, true) === '1') {
        $wer = (string)get_post_meta($post_id, MA_PARTNER_RIGHTS_META.'_by', true);
        $wann = (string)get_post_meta($post_id, MA_PARTNER_RIGHTS_META.'_at', true);
        echo '<p><strong>Fotoerlaubnis des Partners:</strong> erteilt'.($wer !== '' ? ' von '.esc_html($wer) : '').($wann !== '' ? ' am '.esc_html($wann) : '').'.<br><span class="description">'.esc_html(ma_partner_rights_text()).' Das Foto trotzdem ansehen, dann „Bildrechte geprüft“ setzen.</span></p>';
    } else {
        echo '<p><strong>Fotoerlaubnis des Partners:</strong> nicht erteilt. Bild nicht verwenden, bis sie vorliegt.</p>';
    }
}

/** Speichern: nur ein Partner selbst kann die Erklaerung abgeben oder zuruecknehmen. */
function ma_partner_save_rights(int $post_id): void {
    if (!ma_current_partner_policy() || !isset($_POST['ma_editorial_nonce'])) return;
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ma_editorial_nonce'])), 'ma_editorial_save')) return;
    if (wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) return;
    if (isset($_POST[MA_PARTNER_RIGHTS_META])) {
        if ((string)get_post_meta($post_id, MA_PARTNER_RIGHTS_META, true) !== '1') {
            $user = wp_get_current_user();
            update_post_meta($post_id, MA_PARTNER_RIGHTS_META, '1');
            update_post_meta($post_id, MA_PARTNER_RIGHTS_META.'_by', (string)($user->display_name ?? '') ?: (string)($user->user_login ?? ''));
            update_post_meta($post_id, MA_PARTNER_RIGHTS_META.'_at', function_exists('current_time') ? current_time('d.m.Y H:i') : date('d.m.Y H:i'));
        }
    } else {
        delete_post_meta($post_id, MA_PARTNER_RIGHTS_META);
        delete_post_meta($post_id, MA_PARTNER_RIGHTS_META.'_by');
        delete_post_meta($post_id, MA_PARTNER_RIGHTS_META.'_at');
    }
}

function ma_partner_force_pending(array $data, array $postarr): array {
    $policy = ma_current_partner_policy();
    if (!$policy) return $data;

    $type = (string)($data['post_type'] ?? 'post');
    if (!in_array($type, $policy['post_types'], true)) {
        $data['post_status'] = 'draft';
        return $data;
    }

    if (!in_array((string)($data['post_status'] ?? ''), ['draft','auto-draft','inherit'], true)) {
        $data['post_status'] = 'pending';
    }

    // Mit Bild nur nach Fotoerlaubnis zur Freigabe.
    $post_id = (int)($postarr['ID'] ?? 0);
    if ($data['post_status'] === 'pending') {
        if (ma_partner_has_image($post_id, $postarr) && !ma_partner_rights_declared($post_id)) {
            $data['post_status'] = 'draft';
            if ($post_id) update_post_meta($post_id, MA_PARTNER_RIGHTS_MISSING_META, '1');
        } elseif ($post_id) {
            delete_post_meta($post_id, MA_PARTNER_RIGHTS_MISSING_META);
        }
    }
    return $data;
}

function ma_partner_enforce_assignment(int $post_id, WP_Post $post, bool $update): void {
    $policy = ma_current_partner_policy();
    if (!$policy || wp_is_post_revision($post_id)) return;
    if ((int)$post->post_author !== get_current_user_id()) return;

    if (!in_array($post->post_type, $policy['post_types'], true)) return;

    if ($post->post_type === 'post' && !empty($policy['categories'])) {
        $ids = [];
        foreach ($policy['categories'] as $slug) {
            $term = get_term_by('slug', $slug, 'category');
            if ($term && !is_wp_error($term)) $ids[] = (int)$term->term_id;
        }
        if ($ids) wp_set_post_categories($post_id, $ids, false);
    }

    // Externe Partner koennen nie die redaktionelle Freigabe dokumentieren.
    foreach ([
        'ma_source_verified','ma_date_verified','ma_place_verified','ma_human_reviewed',
        'ma_image_rights_verified','ma_top_pinned','ma_release_confirmed','ma_ad_active'
    ] as $key) {
        delete_post_meta($post_id, $key);
    }

    update_post_meta($post_id, '_ma_partner_submission', '1');
    update_post_meta($post_id, '_ma_partner_role', $policy['role']);
    update_post_meta($post_id, '_ma_partner_submitted_by', (string)get_current_user_id());
}

function ma_partner_submission_notification(string $new_status, string $old_status, WP_Post $post): void {
    if ($new_status !== 'pending' || $old_status === 'pending') return;
    $author = get_userdata((int)$post->post_author);
    $policy = $author ? ma_current_partner_policy($author) : null;
    if (!$policy) return;

    $to = sanitize_email((string)get_option('ma_editorial_email', get_option('admin_email')));
    if (!$to || !is_email($to)) return;

    $subject = '[Merzenich Aktuell] Neue Partner-Einreichung zur Freigabe';
    $body = "Neue Einreichung wartet auf redaktionelle Freigabe.\n\n";
    $body .= 'Partner: '.($author->display_name ?: $author->user_login)."\n";
    $body .= 'Rolle: '.$policy['label']."\n";
    $body .= 'Inhaltstyp: '.$post->post_type."\n";
    $body .= 'Titel: '.$post->post_title."\n\n";
    $body .= 'Prüfen: '.admin_url('post.php?post='.$post->ID.'&action=edit')."\n";
    wp_mail($to, $subject, $body);
}

function ma_partner_admin_own_content(WP_Query $query): void {
    if (!is_admin() || !$query->is_main_query()) return;
    $policy = ma_current_partner_policy();
    if (!$policy) return;

    global $pagenow;
    if ($pagenow !== 'edit.php') return;
    $query->set('author', get_current_user_id());
}

function ma_partner_admin_menu(): void {
    $policy = ma_current_partner_policy();
    if (!$policy) {
        add_users_page('Partner-Zugänge', 'Partner-Zugänge', 'manage_options', 'ma-partner-zugaenge', 'ma_partner_admin_page');
        return;
    }

    $allowed = $policy['post_types'];
    $all = ['post','ma_event','ma_property','ma_job','ma_obituary','ma_family_notice','ma_club','ma_business','ma_tip','ma_ad'];
    foreach ($all as $type) {
        if (in_array($type, $allowed, true)) continue;
        remove_menu_page($type === 'post' ? 'edit.php' : 'edit.php?post_type='.$type);
    }
    remove_menu_page('edit-comments.php');
    remove_menu_page('tools.php');
}

function ma_partner_admin_route_guard(): void {
    $policy = ma_current_partner_policy();
    if (!$policy) return;

    global $pagenow;
    if (!in_array($pagenow, ['edit.php','post-new.php'], true)) return;

    $type = isset($_GET['post_type']) ? sanitize_key(wp_unslash($_GET['post_type'])) : 'post';
    if (!in_array($type, $policy['post_types'], true)) {
        wp_die('Dieser Partner-Zugang darf diesen Inhaltstyp nicht bearbeiten.', 'Keine Berechtigung', ['response'=>403]);
    }
}


function ma_partner_admin_notice(): void {
    $policy = ma_current_partner_policy();
    if (!$policy) return;
    echo '<div class="notice notice-info"><p><strong>'.esc_html($policy['label']).':</strong> ';
    echo 'Sie können eigene Inhalte erstellen und zur Freigabe einreichen. Veröffentlichung und redaktionelle Prüfhaken übernimmt ausschließlich die Redaktion.</p></div>';
    $post_id = (int)(($GLOBALS['post']->ID ?? 0));
    if ($post_id && (string)get_post_meta($post_id, MA_PARTNER_RIGHTS_MISSING_META, true) === '1') {
        echo '<div class="notice notice-warning"><p><strong>Noch nicht eingereicht.</strong> Der Beitrag hat ein Bild, aber die Fotoerlaubnis fehlt. ';
        echo 'Setzen Sie im Kasten „Bildherkunft“ bzw. „Redaktion &amp; Quelle“ den Haken „Fotoerlaubnis“ und reichen Sie erneut ein.</p></div>';
    }
}

function ma_partner_rest_guard($prepared_post, WP_REST_Request $request) {
    $policy = ma_current_partner_policy();
    if (!$policy) return $prepared_post;

    $type = isset($prepared_post->post_type) ? (string)$prepared_post->post_type : '';
    if ($type === '' && isset($request['type'])) $type = sanitize_key((string)$request['type']);
    if ($type !== '' && !in_array($type, $policy['post_types'], true)) {
        return new WP_Error('ma_partner_forbidden_type', 'Dieser Zugang darf diesen Inhaltstyp nicht bearbeiten.', ['status'=>403]);
    }
    $prepared_post->post_author = get_current_user_id();
    if (!in_array((string)$prepared_post->post_status, ['draft','auto-draft'], true)) $prepared_post->post_status = 'pending';
    return $prepared_post;
}

function ma_partner_create_account(): string {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['ma_create_partner'])) return '';
    if (!current_user_can('manage_options')) return '';
    check_admin_referer('ma_create_partner');

    $name = sanitize_text_field(wp_unslash($_POST['partner_name'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['partner_email'] ?? ''));
    $role = sanitize_key(wp_unslash($_POST['partner_role'] ?? ''));
    $policies = ma_partner_policies();

    if ($name === '' || !$email || !is_email($email) || !isset($policies[$role]) || !empty($policies[$role]['veraltet'])) return 'Bitte Name, gültige E-Mail und Rolle vollständig angeben.';
    if (email_exists($email)) return 'Für diese E-Mail-Adresse existiert bereits ein WordPress-Zugang.';

    $base = sanitize_user((string)strstr($email, '@', true), true);
    if ($base === '') $base = 'partner';
    $login = $base; $i = 2;
    while (username_exists($login)) { $login = $base.'-'.$i; $i++; }

    $user_id = wp_insert_user([
        'user_login'=>$login,
        'user_email'=>$email,
        'display_name'=>$name,
        'user_pass'=>wp_generate_password(24, true, true),
        'role'=>$role,
    ]);
    if (is_wp_error($user_id)) return 'Zugang konnte nicht angelegt werden: '.$user_id->get_error_message();

    if (function_exists('wp_send_new_user_notifications')) wp_send_new_user_notifications((int)$user_id, 'user');
    else wp_new_user_notification((int)$user_id, null, 'user');

    return 'Partner-Zugang für '.$name.' angelegt. Die Einladung wurde an '.$email.' gesendet.';
}

function ma_partner_admin_page(): void {
    if (!current_user_can('manage_options')) return;
    $policies = ma_partner_policies();
    $meldung = ma_partner_create_account();

    echo '<div class="wrap"><h1>Partner-Zugänge & Freigaben</h1>';
    echo '<p>Polizei, Feuerwehr, Vereine, Rathaus, Unternehmen und Immobilienpartner erhalten nur ihren eigenen Arbeitsbereich. Veröffentlichung bleibt immer bei der Redaktion.</p>';
    if ($meldung !== '') echo '<div class="notice notice-info"><p>'.esc_html($meldung).'</p></div>';

    echo '<div style="display:grid;grid-template-columns:minmax(320px,520px) minmax(0,1fr);gap:28px;align-items:start">';
    echo '<section style="padding:20px;background:#fff;border:1px solid #dcdcde"><h2 style="margin-top:0">Partner-Zugang anlegen</h2><form method="post">';
    wp_nonce_field('ma_create_partner');
    echo '<p><label><strong>Name / Organisation</strong><br><input class="regular-text" name="partner_name" required></label></p>';
    echo '<p><label><strong>E-Mail</strong><br><input class="regular-text" type="email" name="partner_email" required></label></p>';
    echo '<p><label><strong>Rolle</strong><br><select name="partner_role" required><option value="">Bitte wählen</option>';
    foreach($policies as $role=>$p) if (empty($p['veraltet'])) echo '<option value="'.esc_attr($role).'">'.esc_html($p['label'].' – '.$p['description']).'</option>';
    echo '</select></label></p><p><button class="button button-primary" name="ma_create_partner" value="1">Zugang anlegen & Einladung senden</button></p></form></section>';

    echo '<section><h2 style="margin-top:0">Rollen</h2><table class="widefat striped"><thead><tr><th>Rolle</th><th>Für</th><th>Offen</th></tr></thead><tbody>';
    foreach ($policies as $role=>$p) {
        $users = get_users(['role'=>$role,'fields'=>'ID']);
        $pending = 0;
        if ($users) {
            $q = new WP_Query([
                'post_type'=>$p['post_types'],'post_status'=>'pending',
                'author__in'=>array_map('intval',$users),'posts_per_page'=>1,'fields'=>'ids'
            ]);
            $pending = (int)$q->found_posts;
        }
        echo '<tr><td><strong>'.esc_html($p['label']).'</strong><br><small>'.esc_html(implode(', ',$p['post_types'])).'</small></td><td>'.esc_html($p['description']).'</td><td>'.esc_html((string)$pending).'</td></tr>';
    }
    echo '</tbody></table></section></div>';

    $role_names=array_keys($policies);
    $partner_users=get_users(['role__in'=>$role_names,'fields'=>'ID']);
    echo '<h2>Offene Partner-Einreichungen</h2>';
    if($partner_users){
        $pending_q=new WP_Query([
            'post_type'=>['post','ma_property','ma_business','ma_tip','ma_ad'],
            'post_status'=>'pending','author__in'=>array_map('intval',$partner_users),
            'posts_per_page'=>50,'orderby'=>'date','order'=>'ASC'
        ]);
    } else $pending_q=null;

    if($pending_q && $pending_q->posts){
        echo '<table class="widefat striped"><thead><tr><th>Titel</th><th>Partner</th><th>Rolle</th><th>Typ</th><th>Eingang</th><th></th></tr></thead><tbody>';
        foreach($pending_q->posts as $item){
            $author=get_userdata((int)$item->post_author);
            $policy=$author?ma_current_partner_policy($author):null;
            echo '<tr><td><strong>'.esc_html($item->post_title?:'(ohne Titel)').'</strong></td><td>'.esc_html($author?($author->display_name?:$author->user_login):'').'</td><td>'.esc_html($policy['label']??'').'</td><td>'.esc_html($item->post_type).'</td><td>'.esc_html(get_the_date('d.m.Y H:i',$item)).'</td><td><a class="button button-small" href="'.esc_url(get_edit_post_link($item->ID)).'">Prüfen</a></td></tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="notice notice-info inline"><p>Aktuell warten keine Partner-Einreichungen auf Freigabe.</p></div>';
    }

    echo '<p><a class="button button-primary" href="'.esc_url(admin_url('edit.php?post_status=pending&post_type=post')).'">Alle offenen Nachrichten</a> ';
    echo '<a class="button" href="'.esc_url(admin_url('edit-comments.php?page=ma-kommentar-freigabe')).'">Kommentar-Sammelfreigabe</a> ';
    echo '<a class="button" href="'.esc_url(admin_url('users.php')).'">Alle Benutzer</a></p></div>';
}
