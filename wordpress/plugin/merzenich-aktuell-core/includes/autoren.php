<?php
/**
 * Echte Autoren (Plugin 1.26.0, Wunsch Betreiber 07.10.2026): Meldungen tragen
 * den Namen dessen, der sie schreibt oder freigibt, statt nur „Redaktion
 * Merzenich Aktuell“. Google bewertet Meldungen mit Personen als Autor und
 * eigener Autorenseite höher.
 *
 * - Person: Konto mit dem Häkchen „Auf der Seite als Autor zeigen“ (setzen nur
 *   Administratoren; gedacht für die Redaktion, z. B. Tivi und Ordin).
 * - Organisation: Partner- und Vereinskonten (Polizei, Feuerwehr, Gemeinde,
 *   Vereine, Unternehmen). Name = Anzeigename ohne „(Partner)“.
 * - Sonst „Redaktion Merzenich Aktuell“: Sammelkonten (HK Growth, KBS, admin)
 *   und damit alle Meldungen vor dem 07.10.2026 (Entscheidung des Betreibers).
 *
 * Gibt eine Person einen Entwurf frei, der einem Sammelkonto gehört (Abgleich),
 * wird sie Autorin. Partner-Meldungen behalten ihren Partner. Veröffentlichte
 * Meldungen werden nie umgeschrieben.
 *
 * Autorenseiten liegen unter /autor/<name>/ (Adresse aus dem Anzeigenamen, nie
 * aus dem Anmeldenamen); die WordPress-Autorenseiten /author/<login>/ leiten
 * weiter auf /redaktion/.
 */
if (!defined('ABSPATH')) { exit; }

const MA_AUTOR_REDAKTION = 'Redaktion Merzenich Aktuell';

/** Bezeichnung je Partnerrolle (Zeile unter dem Namen). */
const MA_AUTOR_ROLLEN = [
    'ma_polizei_partner' => 'Polizei', 'ma_feuerwehr_partner' => 'Feuerwehr', 'ma_blaulicht_partner' => 'Blaulicht',
    'ma_rathaus_partner' => 'Gemeinde', 'ma_sport_partner' => 'Verein', 'ma_vereine_partner' => 'Verein',
    'ma_wirtschaft_partner' => 'Unternehmen', 'ma_immobilien_partner' => 'Anbieter', 'ma_werbe_partner' => 'Anzeige',
];

/** Anzeigename ohne Zusätze wie „(Partner)“. */
function ma_autor_name_bereinigen(string $name): string {
    return trim((string) preg_replace('/\s*\((?:Partner|Partnerin|Zugang)\)\s*$/iu', '', trim($name)));
}

/** Initialen für das runde Namensfeld: „Polizei Düren“ → „PD“, „Tivi“ → „T“. */
function ma_autor_initialen(string $name): string {
    $w = preg_split('/[\s\-]+/u', trim((string) preg_replace('/[^\p{L}\s\-]/u', '', $name)), -1, PREG_SPLIT_NO_EMPTY);
    $i = '';
    foreach (array_slice($w ?: [], 0, 2) as $t) $i .= mb_strtoupper(mb_substr($t, 0, 1));
    return $i !== '' ? $i : 'MA';
}

/**
 * Pure Entscheidung, wie ein Konto auf der Seite erscheint.
 * $d: person (bool), partner (bool), rolle (string), name, funktion, slug.
 * @return array{art:string,name:string,funktion:string,slug:string}
 */
function ma_autor_art(array $d): array {
    $name = ma_autor_name_bereinigen((string) ($d['name'] ?? ''));
    $funktion = trim((string) ($d['funktion'] ?? ''));
    $slug = (string) ($d['slug'] ?? '');
    if ($name !== '' && !empty($d['person'])) return ['art' => 'person', 'name' => $name, 'funktion' => $funktion !== '' ? $funktion : 'Redaktion', 'slug' => $slug];
    if ($name !== '' && !empty($d['partner'])) return ['art' => 'organisation', 'name' => $name, 'funktion' => $funktion !== '' ? $funktion : (MA_AUTOR_ROLLEN[(string) ($d['rolle'] ?? '')] ?? 'Partner'), 'slug' => $slug];
    return ['art' => 'redaktion', 'name' => MA_AUTOR_REDAKTION, 'funktion' => 'Lokalredaktion', 'slug' => ''];
}

/**
 * Pure Regel für die Freigabe: Wird die freigebende Person Autorin?
 * Nur beim ersten Veröffentlichen aus einem Entwurfsstand, nur wenn der
 * bisherige Autor ein Sammelkonto ist (weder Person noch Partner).
 */
function ma_autor_freigabe_uebernimmt(string $alt, string $neu, bool $freigeberPerson, bool $autorPerson, bool $autorPartner): bool {
    return $neu === 'publish' && in_array($alt, ['draft', 'pending', 'auto-draft', 'ma_in_pruefung', 'ma_aenderung'], true)
        && $freigeberPerson && !$autorPerson && !$autorPartner;
}

function ma_autor_ist_person($u): bool {
    return $u instanceof WP_User && (string) get_user_meta($u->ID, 'ma_autor_zeigen', true) === '1';
}

function ma_autor_ist_partner($u): bool {
    return $u instanceof WP_User && function_exists('ma_partner_rollen') && array_intersect((array) $u->roles, ma_partner_rollen());
}

/** Feste Adresse /autor/<slug>/ (einmal aus dem Namen erzeugt, eindeutig). */
function ma_autor_slug(WP_User $u): string {
    $s = (string) get_user_meta($u->ID, 'ma_autor_slug', true);
    if ($s !== '') return $s;
    $basis = sanitize_title(ma_autor_name_bereinigen($u->display_name)) ?: 'autor-' . $u->ID;
    $s = $basis;
    for ($n = 2; get_users(['meta_key' => 'ma_autor_slug', 'meta_value' => $s, 'exclude' => [$u->ID], 'fields' => 'ID', 'number' => 1]); $n++) $s = $basis . '-' . $n;
    update_user_meta($u->ID, 'ma_autor_slug', $s);
    return $s;
}

/** Wie ein Konto auf der Seite erscheint, mit Adresse der Autorenseite ('' bei Redaktion). */
function ma_autor_anzeige($u): array {
    $u = $u instanceof WP_User ? $u : null;
    $person = ma_autor_ist_person($u); $partner = !$person && ma_autor_ist_partner($u);
    $a = ma_autor_art(['person' => $person, 'partner' => $partner, 'rolle' => $u ? (string) (array_values(array_intersect((array) $u->roles, array_keys(MA_AUTOR_ROLLEN)))[0] ?? '') : '',
        'name' => $u ? $u->display_name : '', 'funktion' => $u ? (string) get_user_meta($u->ID, 'ma_autor_funktion', true) : '', 'slug' => $u && ($person || $partner) ? ma_autor_slug($u) : '']);
    $a['url'] = $a['slug'] !== '' ? home_url('/autor/' . $a['slug'] . '/') : home_url('/redaktion/');
    $a['bio'] = $u && $a['art'] !== 'redaktion' ? trim((string) get_user_meta($u->ID, 'description', true)) : '';
    $a['id'] = $u && $a['art'] !== 'redaktion' ? (int) $u->ID : 0;
    $a['initialen'] = $a['art'] === 'redaktion' ? 'MA' : ma_autor_initialen($a['name']);
    return $a;
}

/** Autor einer Meldung (für Theme, SEO und Feed). */
function ma_autor_von($post = null): array {
    $p = get_post($post);
    return ma_autor_anzeige($p ? get_userdata((int) $p->post_author) : null);
}

/** Konto zu einer Autoren-Adresse; nur Personen und Partner. */
function ma_autor_konto(string $slug): ?WP_User {
    if ($slug === '') return null;
    $ids = get_users(['meta_key' => 'ma_autor_slug', 'meta_value' => sanitize_title($slug), 'fields' => 'ID', 'number' => 1]);
    $u = $ids ? get_userdata((int) $ids[0]) : null;
    return $u && (ma_autor_ist_person($u) || ma_autor_ist_partner($u)) ? $u : null;
}

/** Alle Personen der Redaktion (für /redaktion/ und die Sitemap). */
function ma_autor_personen(): array {
    return get_users(['meta_key' => 'ma_autor_zeigen', 'meta_value' => '1', 'orderby' => 'display_name', 'order' => 'ASC']);
}

/* ------------------------------------------------------------ Freigabe setzt den Autor */

add_action('transition_post_status', function (string $neu, string $alt, WP_Post $p): void {
    if ($p->post_type !== 'post' || !is_user_logged_in()) return;
    $freigeber = wp_get_current_user(); $autor = get_userdata((int) $p->post_author);
    if (!ma_autor_freigabe_uebernimmt($alt, $neu, ma_autor_ist_person($freigeber), ma_autor_ist_person($autor), ma_autor_ist_partner($autor))) return;
    global $wpdb;
    $wpdb->update($wpdb->posts, ['post_author' => (int) $freigeber->ID], ['ID' => (int) $p->ID]);
    clean_post_cache((int) $p->ID);
    if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen((int) $p->ID, 'Autor: ' . ma_autor_name_bereinigen($freigeber->display_name) . ' (bei der Freigabe)');
}, 20, 3);

/* ------------------------------------------------------------ Profilfelder */

function ma_autor_profilfelder(WP_User $u): void {
    $admin = current_user_can('manage_options');
    $a = ma_autor_anzeige($u);
    echo '<h2>Autor auf Merzenich Aktuell</h2><table class="form-table" role="presentation">';
    echo '<tr><th>Als Autor zeigen</th><td><label><input type="checkbox" name="ma_autor_zeigen" value="1"' . checked(ma_autor_ist_person($u), true, false) . disabled(!$admin, true, false) . '> Meldungen dieses Kontos tragen seinen Namen (Person der Redaktion)</label>'
        . '<p class="description">Für Redakteurinnen und Redakteure. Partner- und Vereinskonten erscheinen ohnehin mit ihrem Namen. Nur Administratoren ändern das. Gezeigt wird der Name aus „Öffentlich anzeigen als“; bei Personen bitte Vor- und Nachname.</p></td></tr>';
    echo '<tr><th><label for="ma_autor_funktion">Funktion</label></th><td><input type="text" class="regular-text" id="ma_autor_funktion" name="ma_autor_funktion" value="' . esc_attr((string) get_user_meta($u->ID, 'ma_autor_funktion', true)) . '" placeholder="z. B. Redakteur, Lokalreporterin"><p class="description">Steht unter dem Namen. Die Vorstellung kommt aus „Biografische Angaben“ oben.</p></td></tr>';
    echo '<tr><th>Autorenseite</th><td>' . ($a['art'] === 'redaktion' ? 'keine (erscheint als „' . esc_html(MA_AUTOR_REDAKTION) . '“)' : '<a href="' . esc_url($a['url']) . '" target="_blank" rel="noopener">' . esc_html($a['url']) . '</a>') . '</td></tr></table>';
}
add_action('show_user_profile', 'ma_autor_profilfelder');
add_action('edit_user_profile', 'ma_autor_profilfelder');

function ma_autor_profil_speichern(int $id): void {
    if (!current_user_can('edit_user', $id)) return;
    if (isset($_POST['ma_autor_funktion'])) update_user_meta($id, 'ma_autor_funktion', sanitize_text_field(wp_unslash($_POST['ma_autor_funktion'])));
    if (current_user_can('manage_options')) {
        if (!empty($_POST['ma_autor_zeigen'])) update_user_meta($id, 'ma_autor_zeigen', '1');
        else delete_user_meta($id, 'ma_autor_zeigen');
    }
}
add_action('personal_options_update', 'ma_autor_profil_speichern');
add_action('edit_user_profile_update', 'ma_autor_profil_speichern');

/* ------------------------------------------------------------ Autorenseiten /autor/<slug>/ */

add_action('init', function (): void {
    add_rewrite_rule('^autor/([^/]+)/?$', 'index.php?ma_autor=$matches[1]', 'top');
    add_rewrite_rule('^autor/([^/]+)/page/([0-9]+)/?$', 'index.php?ma_autor=$matches[1]&paged=$matches[2]', 'top');
});
add_filter('query_vars', function (array $v): array { $v[] = 'ma_autor'; return $v; });

add_action('pre_get_posts', function (WP_Query $q): void {
    if (is_admin() || !$q->is_main_query() || (string) $q->get('ma_autor') === '') return;
    $u = ma_autor_konto((string) $q->get('ma_autor'));
    $q->set('post_type', 'post'); $q->set('post_status', 'publish'); $q->set('posts_per_page', 13); $q->set('ignore_sticky_posts', true);
    if ($u) $q->set('author', (int) $u->ID);
    else $q->set('post__in', [0]);
    // Listen-Seite, nicht Startseite (sonst greifen Startseiten-Titel und -Daten).
    $q->is_home = false; $q->is_front_page = false; $q->is_archive = true; $q->is_author = true;
});
add_action('template_redirect', function (): void {
    if ((string) get_query_var('ma_autor') === '') return;
    if (!ma_autor_konto((string) get_query_var('ma_autor'))) { global $wp_query; $wp_query->set_404(); status_header(404); }
}, 1);
add_filter('template_include', function (string $t): string {
    if ((string) get_query_var('ma_autor') === '' || is_404()) return $t;
    return locate_template('autor.php') ?: $t;
});
add_filter('redirect_canonical', fn($ziel) => get_query_var('ma_autor') ? false : $ziel);

/* /redaktion/: Personen der Redaktion unter dem gepflegten Text. */
add_filter('the_content', function (string $html): string {
    if (!is_page('redaktion') || !in_the_loop()) return $html;
    $personen = ma_autor_personen();
    if (!$personen) return $html;
    $li = '';
    foreach ($personen as $u) {
        $a = ma_autor_anzeige($u);
        $li .= '<li><a href="' . esc_url($a['url']) . '"><b>' . esc_html($a['name']) . '</b></a> · ' . esc_html($a['funktion']) . ($a['bio'] !== '' ? '<br><span>' . esc_html(wp_trim_words($a['bio'], 30)) . '</span>' : '') . '</li>';
    }
    return $html . '<h2>Die Redaktion</h2><ul class="redaktion-liste">' . $li . '</ul>';
}, 20);

/* Sitemap: Autorenseiten der Personen und Partner mit Meldungen. */
add_action('init', function (): void {
    if (!class_exists('WP_Sitemaps_Provider') || !function_exists('wp_register_sitemap_provider')) return;
    if (!class_exists('MA_Sitemap_Autoren')) {
        class MA_Sitemap_Autoren extends WP_Sitemaps_Provider {
            public function __construct() { $this->name = 'autoren'; $this->object_type = 'autor'; }
            public function get_url_list($page_num, $object_subtype = '') {
                $liste = [];
                $ids = array_unique(array_merge(array_map(fn($u) => (int) $u->ID, ma_autor_personen()),
                    function_exists('ma_partner_rollen') ? array_map('intval', get_users(['role__in' => ma_partner_rollen(), 'fields' => 'ID'])) : []));
                foreach ($ids as $id) {
                    if (!count_user_posts($id, 'post', true)) continue;
                    $a = ma_autor_anzeige(get_userdata($id));
                    if ($a['slug'] !== '') $liste[] = ['loc' => $a['url']];
                }
                return $liste;
            }
            // Ohne Einträge nicht im Sitemap-Verzeichnis (WordPress gibt dann 404, Google meldet einen Fehler).
            public function get_max_num_pages($object_subtype = '') { return $this->get_url_list(1) ? 1 : 0; }
        }
    }
    wp_register_sitemap_provider('autoren', new MA_Sitemap_Autoren());
}, 20);
