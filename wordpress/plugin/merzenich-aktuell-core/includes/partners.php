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

function ma_partner_policies(): array {
    return [
        'ma_blaulicht_partner' => [
            'label' => 'Blaulicht-Partner',
            'description' => 'Polizei / Feuerwehr',
            'post_types' => ['post'],
            'categories' => ['blaulicht'],
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
            'post_types' => ['post','ma_business','ma_ad'],
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
    update_option('ma_partner_roles_version', '1');
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
        if (get_option('ma_partner_roles_version') !== '1') ma_register_partner_roles();
    }, 30);

    add_filter('map_meta_cap', 'ma_partner_map_meta_cap', 20, 4);
    add_filter('wp_insert_post_data', 'ma_partner_force_pending', 5, 2);
    add_action('save_post', 'ma_partner_enforce_assignment', 100, 3);
    add_action('admin_menu', 'ma_partner_admin_menu', 100);
    add_action('pre_get_posts', 'ma_partner_admin_own_content');
    add_action('admin_notices', 'ma_partner_admin_notice');

    foreach (['post','ma_event','ma_property','ma_job','ma_obituary','ma_family_notice','ma_club','ma_business','ma_ad'] as $type) {
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
    $all = ['post','ma_event','ma_property','ma_job','ma_obituary','ma_family_notice','ma_club','ma_business','ma_ad'];
    foreach ($all as $type) {
        if (in_array($type, $allowed, true)) continue;
        remove_menu_page($type === 'post' ? 'edit.php' : 'edit.php?post_type='.$type);
    }
    remove_menu_page('edit-comments.php');
    remove_menu_page('tools.php');
}

function ma_partner_admin_notice(): void {
    $policy = ma_current_partner_policy();
    if (!$policy) return;
    echo '<div class="notice notice-info"><p><strong>'.esc_html($policy['label']).':</strong> ';
    echo 'Sie können eigene Inhalte erstellen und zur Freigabe einreichen. Veröffentlichung und redaktionelle Prüfhaken übernimmt ausschließlich die Redaktion.</p></div>';
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

function ma_partner_admin_page(): void {
    if (!current_user_can('manage_options')) return;
    $policies = ma_partner_policies();
    echo '<div class="wrap"><h1>Partner-Zugänge</h1>';
    echo '<p>Neue Zugänge unter <a href="'.esc_url(admin_url('user-new.php')).'">Benutzer → Neu hinzufügen</a> anlegen und eine der Rollen unten zuweisen. Partner reichen ausschließlich Entwürfe zur Freigabe ein.</p>';
    echo '<table class="widefat striped"><thead><tr><th>Rolle</th><th>Für</th><th>Erlaubte Inhalte</th><th>Offene Einreichungen</th></tr></thead><tbody>';
    foreach ($policies as $role=>$p) {
        $types = implode(', ', array_map('esc_html', $p['post_types']));
        $users = get_users(['role'=>$role,'fields'=>'ID']);
        $pending = 0;
        if ($users) {
            $q = new WP_Query([
                'post_type'=>$p['post_types'],
                'post_status'=>'pending',
                'author__in'=>array_map('intval',$users),
                'posts_per_page'=>1,
                'fields'=>'ids',
            ]);
            $pending = (int)$q->found_posts;
        }
        echo '<tr><td><strong>'.esc_html($p['label']).'</strong></td><td>'.esc_html($p['description']).'</td><td>'.esc_html($types).'</td><td>'.esc_html((string)$pending).'</td></tr>';
    }
    echo '</tbody></table>';
    echo '<p><a class="button button-primary" href="'.esc_url(admin_url('edit.php?post_status=pending&post_type=post')).'">Offene Nachrichten prüfen</a> ';
    echo '<a class="button" href="'.esc_url(admin_url('edit-comments.php?page=ma-kommentar-freigabe')).'">Kommentar-Freigabe</a></p></div>';
}
