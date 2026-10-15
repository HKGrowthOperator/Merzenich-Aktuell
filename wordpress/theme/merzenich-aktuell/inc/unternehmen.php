<?php
/**
 * Unternehmenskanäle (Theme 21.18.0, Auftrag Betreiber 13.10.2026, Vorbild
 * Oberberg Aktuell): /unternehmen/<firma>/ zeigt das Profil einer Firma und
 * ihre Beiträge, /unternehmen/<muster>/<beitrag>/ einen Musterbeitrag.
 *
 * Zwei Quellen, eine Ansicht (ma21_firma_ansicht):
 * - Echte Firmen: veröffentlichtes Unternehmensprofil (ma_business) mit
 *   verknüpftem Zugang oder zugeordneten Beiträgen (Plugin,
 *   includes/unternehmenskanal.php). Ihre Beiträge sind normale Beiträge mit
 *   Freigabe durch die Redaktion; deren Seiten liefert single.php.
 * - Musterfirmen aus inc/unternehmen-muster.json, solange weniger als drei echte
 *   Kanäle Beiträge haben. Ausgedacht und überall gekennzeichnet
 *   (Beispielfirma, Musterbeitrag, Testanzeige); ihre Seiten sind für
 *   Suchmaschinen gesperrt und stehen in keiner Sitemap und keinem Feed.
 *
 * Oben in der Datei nur add_action/add_filter: qa/wordpress/*-test.php laden sie
 * mit Ersatzfunktionen ohne WordPress.
 */
if (!defined('ABSPATH')) { exit; }

/** Ab so vielen echten Kanälen mit Beiträgen entfallen die Musterfirmen. */
const MA21_MUSTER_BIS = 3;

/* ------------------------------------------------------------ Daten */

/**
 * Musterfirmen (inc/unternehmen-muster.json): die drei Beispielfirmen, mit dem
 * Schalter „Fünf Musterfirmen statt drei Beispielfirmen“ (inc/ausbau.php) die
 * gespeicherten fünf Musterfirmen bis zum 14.10.2026 („frueher“).
 */
function ma21_muster_firmen(): array {
    static $f = [];
    $satz = function_exists('ma21_ausbau') && ma21_ausbau('muster_alt') ? 'frueher' : 'firmen';
    if (isset($f[$satz])) return $f[$satz];
    $d = json_decode((string) @file_get_contents(__DIR__ . '/unternehmen-muster.json'), true);
    return $f[$satz] = (is_array($d) && is_array($d[$satz] ?? null)) ? array_values($d[$satz]) : [];
}

/**
 * Musterfirmen für Menü, Übersicht und Adressen: ohne die, deren Adresse ein
 * veröffentlichtes echtes Profil belegt (das echte Profil geht vor, die
 * Musterfirma verschwindet ganz statt auf die echte Firma zu zeigen).
 */
function ma21_muster_liste(): array {
    static $l = [];
    $satz = function_exists('ma21_ausbau') && ma21_ausbau('muster_alt') ? 'frueher' : 'firmen';
    if (isset($l[$satz])) return $l[$satz];
    $slugs = array_column(ma21_muster_firmen(), 'slug');
    // Ein echtes Profil mit demselben Namen bekommt die Adresse <slug>-firma (Filter unten):
    // auch dann verschwindet die Beispielfirma, damit nie beide nebeneinander stehen.
    $suche = array_merge($slugs, array_map(fn($s) => $s . '-firma', $slugs));
    $belegt = $slugs ? array_map(fn($p) => (string) preg_replace('/-firma$/', '', (string) $p->post_name), (array) get_posts(['post_type' => 'ma_business', 'post_status' => 'publish', 'post_name__in' => $suche, 'posts_per_page' => count($suche)])) : [];
    return $l[$satz] = array_values(array_filter(ma21_muster_firmen(), fn($m) => !in_array((string) ($m['slug'] ?? ''), $belegt, true)));
}

// Echte Profile bekommen keine Adresse einer Musterfirma (sonst zeigt sie auf die echte Firma).
add_filter('wp_unique_post_slug', function ($slug, $id, $status, $typ) {
    return $typ === 'ma_business' && in_array($slug, array_column(ma21_muster_firmen(), 'slug'), true) ? $slug . '-firma' : $slug;
}, 10, 4);

/** Bildadresse absolut: Musterbilder sind relativ (/assets/…), echte Bilder schon absolut (Uploads). */
function ma21_abs(string $u): string {
    return preg_match('#^(https?:)?//#i', $u) ? $u : home_url($u);
}

/** Bild fürs Menü-JSON: kleine Fassung, Alternativtext und Nachweis (Urheber, Lizenz, Lizenzlink). */
function ma21_menue_bildnachweis(?array $b): ?array {
    return $b ? ['src' => ma21_abs($b['klein']), 'alt' => $b['alt'], 'foto' => (string) $b['foto'], 'lizenz' => (string) $b['lizenz'], 'lizenz_url' => (string) $b['lizenz_url']] : null;
}

/** Abfragewert nur als Text (Arrays aus ?ma_firma[]=… gelten als leer). */
function ma21_qv(string $k, ?WP_Query $q = null): string {
    $v = $q ? $q->get($k) : get_query_var($k);
    return is_string($v) ? $v : '';
}

/**
 * Bildnachweise der Werbefotos: im Theme-Paket static/werben/credits.json,
 * im Repository chatgpt-site/assets/werben/credits.json (Tests).
 */
function ma21_werben_bilder(): array {
    static $b = null;
    if ($b !== null) return $b;
    $b = [];
    foreach ([trailingslashit(get_template_directory()) . 'static/werben/credits.json', dirname(__DIR__, 4) . '/chatgpt-site/assets/werben/credits.json'] as $datei) {
        if (!is_readable($datei)) continue;
        $d = json_decode((string) file_get_contents($datei), true);
        foreach ((array) ($d['images'] ?? []) as $i) if (is_array($i) && !empty($i['id'])) $b[(string) $i['id']] = $i;
        if ($b) break;
    }
    return $b;
}

/** Bild eines Musterbeitrags: Adresse groß und 720 px, Alternativtext, Nachweis. null = unbekannt. */
function ma21_muster_bild(string $id): ?array {
    $i = ma21_werben_bilder()[$id] ?? null;
    if (!$i || empty($i['src'])) return null;
    return ['src' => (string) $i['src'], 'klein' => ma21_muster_klein((string) $i['src']), 'alt' => (string) ($i['alt'] ?? ''),
        'foto' => ma21_kurzname((string) ($i['author'] ?? '')), 'lizenz' => (string) ($i['license'] ?? ''), 'lizenz_url' => (string) ($i['licenseUrl'] ?? ''), 'quelle' => (string) ($i['sourceUrl'] ?? '')];
}

/** Urheberangabe aus Commons kurz halten („Dimitrios Savva (Photography), https://…“ → „Dimitrios Savva“). */
function ma21_kurzname(string $s): string {
    $s = trim((string) preg_replace('/\s*(\(|https?:|,).*$/u', '', $s));
    return $s !== '' ? $s : 'Wikimedia Commons';
}

/**
 * IDs der echten Unternehmenskanäle: veröffentlichte Profile mit verknüpftem
 * Zugang oder veröffentlichten, zugeordneten Beiträgen.
 */
function ma21_kanal_ids(): array {
    static $ids = null;
    if ($ids !== null) return $ids;
    $roh = [];
    foreach ((array) get_users(['meta_key' => 'ma_business_id', 'fields' => 'ID']) as $u) $roh[] = (int) get_user_meta((int) $u, 'ma_business_id', true);
    foreach ((array) get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 300, 'fields' => 'ids',
        'meta_query' => [['key' => 'ma_business_id', 'compare' => 'EXISTS'], ['key' => 'ma_gesponsert', 'value' => '1']]]) as $p) $roh[] = (int) get_post_meta((int) $p, 'ma_business_id', true);
    $ids = [];
    foreach (array_unique(array_filter($roh)) as $id) if (get_post_type($id) === 'ma_business' && get_post_status($id) === 'publish') $ids[] = (int) $id;
    return $ids;
}

/** Echte Kanäle mit mindestens einem veröffentlichten Beitrag (Menü, Übersicht). */
function ma21_echte_firmen(): array {
    static $f = null;
    if ($f !== null) return $f;
    $f = [];
    foreach (ma21_kanal_ids() as $id) {
        $a = ma21_firma_ansicht(get_post($id));
        if ($a && $a['beitraege']) $f[] = $a;
    }
    // Neueste Beiträge zuerst (Oberberg Aktuell ordnet ebenso nach Aktualität).
    usort($f, fn($a, $b) => strcmp((string) ($b['beitraege'][0]['iso'] ?? ''), (string) ($a['beitraege'][0]['iso'] ?? '')));
    return $f;
}

/** Werden die Musterfirmen gezeigt? Bis drei echte Kanäle Beiträge haben. */
function ma21_muster_aktiv(): bool {
    return count(ma21_echte_firmen()) < MA21_MUSTER_BIS;
}

/** Alle Firmen für Menü und Übersicht: echte zuerst, dann Musterfirmen (solange aktiv). */
function ma21_alle_firmen(int $max = 8): array {
    $f = ma21_echte_firmen();
    if (ma21_muster_aktiv()) foreach (ma21_muster_liste() as $m) $f[] = ma21_firma_ansicht($m);
    return array_slice(array_values(array_filter($f)), 0, $max);
}

/** Kürzel aus dem Namen („Café Feldlerche“ → „CF“, ein altes „Muster-“ zählt nicht). */
function ma21_firma_kuerzel(string $name): string {
    $w = array_values(array_filter(preg_split('/[\s\-]+/u', (string) preg_replace('/^Muster-/u', '', $name)) ?: [], fn($x) => preg_match('/^\p{Lu}/u', $x)));
    $k = mb_substr($w[0] ?? $name, 0, 1) . (isset($w[1]) ? mb_substr($w[1], 0, 1) : '');
    return mb_strtoupper($k !== '' ? $k : '?');
}

/** Feste Farbe je Name (für Firmen ohne Logo). */
function ma21_firma_farbe(string $name): string {
    $farben = ['#7a3b2e', '#2f5d62', '#5b4a8a', '#8a6d1f', '#3d6b35', '#8c2f39'];
    $h = 0;
    foreach (preg_split('//u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $c) $h = ($h * 31 + mb_ord($c)) % 2147483647;
    return $farben[$h % count($farben)];
}

/**
 * Einheitliche Ansicht einer Firma. $q: WP_Post (ma_business) oder Eintrag aus
 * unternehmen-muster.json. Beiträge: titel, anriss, url, bild, zeit, iso, muster.
 */
function ma21_firma_ansicht($q): ?array {
    $home = home_url('/');
    if (is_array($q)) {
        $slug = (string) ($q['slug'] ?? '');
        if ($slug === '' || empty($q['name'])) return null;
        $url = $home . 'unternehmen/' . $slug . '/';
        $ort = MA21_ORTE[(string) ($q['ort'] ?? '')] ?? 'Merzenich';
        $beitraege = [];
        foreach ((array) ($q['beitraege'] ?? []) as $b) {
            if (empty($b['slug']) || empty($b['titel'])) continue;
            $beitraege[] = ['slug' => (string) $b['slug'], 'titel' => (string) $b['titel'], 'anriss' => (string) ($b['anriss'] ?? ''), 'absaetze' => (array) ($b['absaetze'] ?? []),
                'url' => $url . $b['slug'] . '/', 'bild' => ma21_muster_bild((string) ($b['bild'] ?? '')), 'zeit' => '', 'iso' => '', 'muster' => true];
        }
        $anzeige = (array) ($q['anzeige'] ?? []);
        return ['slug' => $slug, 'name' => (string) $q['name'], 'branche' => (string) ($q['branche'] ?? ''), 'ort' => $ort, 'zeile' => trim(($q['branche'] ?? '') . ' · ' . $ort, ' ·'),
            'url' => $url, 'muster' => true, 'kuerzel' => (string) ($q['kuerzel'] ?? '') ?: ma21_firma_kuerzel((string) $q['name']), 'farbe' => (string) ($q['farbe'] ?? '') ?: ma21_firma_farbe((string) $q['name']),
            'logo' => '', 'text' => (string) ($q['text'] ?? ''), 'fakten' => array_filter(['Branche' => (string) ($q['branche'] ?? ''), 'Ort' => $ort, 'Öffnungszeiten' => (string) ($q['info'] ?? '')]),
            'bild' => ma21_muster_bild((string) ($q['bild'] ?? '')), 'profil' => '', 'beitraege' => $beitraege,
            'anzeige' => $anzeige ? ['titel' => (string) ($anzeige['titel'] ?? ''), 'text' => (string) ($anzeige['text'] ?? ''), 'knopf' => (string) ($anzeige['knopf'] ?? 'Mehr erfahren'), 'bild' => ma21_muster_bild((string) ($anzeige['bild'] ?? '')), 'url' => $url] : null];
    }
    if (!$q instanceof WP_Post || $q->post_type !== 'ma_business') return null;
    $name = html_entity_decode(get_the_title($q), ENT_QUOTES, 'UTF-8');
    $p = function_exists('ma_business_profile') ? (array) ma_business_profile($q->ID) : [];
    $ort = (string) ($p['ortsteil'] ?? '') ?: 'Gemeinde Merzenich';
    $beitraege = [];
    foreach (function_exists('ma_business_beitraege') ? ma_business_beitraege($q->ID, 24) : [] as $b) {
        $bild = ma21_bild($b);
        $beitraege[] = ['slug' => $b->post_name, 'titel' => html_entity_decode(get_the_title($b), ENT_QUOTES, 'UTF-8'), 'anriss' => ma21_teaser($b), 'absaetze' => [],
            'url' => get_permalink($b), 'bild' => $bild ? ['src' => $bild['src'], 'klein' => (string) (wp_get_attachment_image_url($bild['id'], 'ma-480') ?: $bild['src']), 'srcset' => $bild['srcset'], 'w' => $bild['w'], 'h' => $bild['h'],
                'alt' => $bild['alt'], 'foto' => $bild['credit'], 'lizenz' => '', 'lizenz_url' => '', 'quelle' => ''] : null,
            'zeit' => ma21_zeit_text($b, true, ', '), 'iso' => (string) get_post_time('c', false, $b), 'muster' => false];
    }
    $logo = (string) (get_the_post_thumbnail_url($q, 'thumbnail') ?: '');
    $text = trim(wp_strip_all_tags($q->post_excerpt !== '' ? $q->post_excerpt : wp_trim_words(wp_strip_all_tags($q->post_content), 40, ' …')));
    return ['slug' => $q->post_name, 'name' => $name, 'branche' => (string) ($p['branche'] ?? ''), 'ort' => $ort, 'zeile' => trim(($p['branche'] ?? '') . ' · ' . $ort, ' ·'),
        'url' => $home . 'unternehmen/' . $q->post_name . '/', 'muster' => false, 'kuerzel' => ma21_firma_kuerzel($name), 'farbe' => ma21_firma_farbe($name), 'logo' => $logo, 'text' => $text,
        'fakten' => array_filter(['Branche' => (string) ($p['branche'] ?? ''), 'Ort' => $ort, 'Adresse' => (string) ($p['adresse'] ?? ''), 'Telefon' => (string) ($p['telefon'] ?? ''), 'Öffnungszeiten' => (string) ($p['oeffnungszeiten'] ?? '')]),
        'telefon_href' => (string) ($p['telefon_href'] ?? ''), 'website' => (string) ($p['website'] ?? ''),
        'bild' => null, 'profil' => get_permalink($q), 'beitraege' => $beitraege, 'anzeige' => null, 'id' => (int) $q->ID];
}

/* ------------------------------------------------------------ Adressen */

add_action('init', function (): void {
    add_rewrite_rule('^unternehmen/([^/]+)/?$', 'index.php?ma_firma=$matches[1]', 'top');
    add_rewrite_rule('^unternehmen/([^/]+)/([^/]+)/?$', 'index.php?ma_firma=$matches[1]&ma_firmenbeitrag=$matches[2]', 'top');
});
add_filter('query_vars', function (array $v): array { $v[] = 'ma_firma'; $v[] = 'ma_firmenbeitrag'; return $v; });

/**
 * Was unter /unternehmen/<firma>/[<beitrag>/] steht. art: kanal, beitrag oder 404.
 * Ein echtes Profil geht einer Musterfirma mit gleichem Namen vor; Beiträge
 * echter Firmen haben ihre eigene Adresse (single.php), darunter gibt es nichts.
 */
function ma21_firmen_route(string $firma, string $beitrag = ''): array {
    $firma = sanitize_title($firma); $beitrag = sanitize_title($beitrag);
    if ($firma === '' || in_array($firma, ['page', 'feed', 'embed', 'amp', 'trackback'], true)) return ['art' => '404'];
    $echt = get_posts(['post_type' => 'ma_business', 'post_status' => 'publish', 'name' => $firma, 'posts_per_page' => 1]);
    if ($echt && in_array((int) $echt[0]->ID, ma21_kanal_ids(), true)) {
        return $beitrag === '' ? ['art' => 'kanal', 'firma' => ma21_firma_ansicht($echt[0])] : ['art' => '404'];
    }
    if (!ma21_muster_aktiv()) return ['art' => '404'];
    foreach (ma21_muster_liste() as $m) {
        if (($m['slug'] ?? '') !== $firma) continue;
        $f = ma21_firma_ansicht($m);
        if ($beitrag === '') return ['art' => 'kanal', 'firma' => $f];
        foreach ($f['beitraege'] as $i => $b) if ($b['slug'] === $beitrag) return ['art' => 'beitrag', 'firma' => $f, 'nr' => $i];
        return ['art' => '404'];
    }
    return ['art' => '404'];
}

/** Ergebnis für die laufende Anfrage (einmal ermittelt). */
function ma21_firmen_anfrage(): ?array {
    if (!array_key_exists('ma21_firmen_route', $GLOBALS)) {
        $f = ma21_qv('ma_firma');
        $GLOBALS['ma21_firmen_route'] = $f !== '' ? ma21_firmen_route($f, ma21_qv('ma_firmenbeitrag')) : null;
    }
    return $GLOBALS['ma21_firmen_route'];
}

add_action('pre_get_posts', function (WP_Query $q): void {
    if (is_admin() || !$q->is_main_query() || ma21_qv('ma_firma', $q) === '') return;
    // Keine Beitragsabfrage: Kanal und Musterbeitrag lesen ihre Daten selbst.
    $q->set('post_type', 'post'); $q->set('post__in', [0]); $q->set('posts_per_page', 1); $q->set('no_found_rows', true);
    $q->is_home = false; $q->is_archive = true;
});
// Gültige Adresse: Seite statt 404. Unbekannte Firma oder Beitrag: echte 404.
add_filter('pre_handle_404', function ($stop, $q) {
    if (!$q->is_main_query() || ma21_qv('ma_firma', $q) === '') return $stop;
    $r = ma21_firmen_anfrage();
    if ($r && $r['art'] !== '404') { status_header(200); return true; }
    $q->set_404(); status_header(404); nocache_headers();
    return true;
}, 10, 2);
// Kein Raten ähnlicher Adressen (redirect_guess_404_permalink) unter /unternehmen/<…>/.
add_filter('redirect_canonical', fn($ziel) => ma21_qv('ma_firma') !== '' ? false : $ziel);
add_filter('template_include', function ($t) {
    $r = ma21_qv('ma_firma') !== '' ? ma21_firmen_anfrage() : null;
    if (!$r || $r['art'] === '404') return $t;
    return locate_template($r['art'] === 'beitrag' ? 'unternehmen-beitrag.php' : 'unternehmen-kanal.php') ?: $t;
}, 20);
// Musterseiten: nicht in Suchmaschinen (zusätzlich zum robots-Meta über ma_seo_kontext).
add_action('template_redirect', function (): void {
    $r = ma21_qv('ma_firma') !== '' ? ma21_firmen_anfrage() : null;
    if ($r && $r['art'] !== '404' && !empty($r['firma']['muster']) && !headers_sent()) header('X-Robots-Tag: noindex, follow');
});

/** Titel, Adresse, Brotkrumen und Index für Kanal und Musterbeitrag (Plugin seo.php). */
add_filter('ma_seo_kontext', 'ma21_firmen_seo');
function ma21_firmen_seo(array $k): array {
    $r = ma21_qv('ma_firma') !== '' ? ma21_firmen_anfrage() : null;
    if (!$r || $r['art'] === '404') return $k;
    $f = $r['firma']; $home = home_url('/');
    $krumen = [['Start', $home], ['Unternehmen', $home . 'unternehmen/']];
    $bild = $f['bild'] ?? null;
    if ($r['art'] === 'kanal') {
        $k = array_merge($k, ['typ' => 'liste', 'titel' => $f['name'] . ($f['muster'] ? ' (Beispielfirma)' : '') . ' · Unternehmenskanal', 'url' => $f['url'], 'index' => !$f['muster'], 'og' => 'website',
            'beschreibung' => function_exists('ma_seo_kuerzen') ? ma_seo_kuerzen(($f['muster'] ? 'Beispielfirma, kein echtes Unternehmen. ' : '') . $f['text'] . ' Beiträge von ' . $f['name'] . ' aus ' . $f['ort'] . ' auf Merzenich Aktuell.') : $f['text'],
            'krumen' => array_merge($krumen, [[$f['name'], null]]), 'liste' => array_map(fn($b) => [$b['url'], $b['titel']], array_slice($f['beitraege'], 0, 20))]);
    } else {
        $b = $f['beitraege'][$r['nr']]; $bild = $b['bild'] ?: $bild;
        $k = array_merge($k, ['typ' => 'seite', 'seitenart' => 'WebPage', 'titel' => $b['titel'] . ' (Musterbeitrag)', 'url' => $b['url'], 'index' => false, 'og' => 'website',
            'beschreibung' => function_exists('ma_seo_kuerzen') ? ma_seo_kuerzen('Musterbeitrag, kein echtes Unternehmen. ' . $b['anriss']) : $b['anriss'],
            'krumen' => array_merge($krumen, [[$f['name'], $f['url']], [$b['titel'], null]]), 'liste' => []]);
    }
    if ($bild) $k['bild'] = ['url' => ma21_abs($bild['src']), 'w' => (int) ($bild['w'] ?? 0), 'h' => (int) ($bild['h'] ?? 0), 'alt' => $bild['alt']];
    return $k;
}

/* ------------------------------------------------------------ Bausteine */

/** Logo oder Kürzel einer Firma. */
function ma21_firma_logo_html(array $f, string $klasse = 'firma-logo'): string {
    if ($f['logo'] !== '') return '<span class="' . esc_attr($klasse) . '"><img src="' . esc_url($f['logo']) . '" alt="" width="48" height="48" loading="lazy" decoding="async"></span>';
    return '<span class="' . esc_attr($klasse) . '" style="background:' . esc_attr($f['farbe']) . '" aria-hidden="true">' . esc_html($f['kuerzel']) . '</span>';
}

/** Bildnachweis „Foto: Name, Lizenz“ (Lizenz verlinkt). */
function ma21_muster_nachweis(?array $b, string $vorne = 'Symbolfoto: '): string {
    if (!$b) return '';
    return esc_html($vorne . $b['foto']) . ($b['lizenz'] !== '' ? ', ' . ($b['lizenz_url'] !== '' ? '<a href="' . esc_url($b['lizenz_url']) . '" target="_blank" rel="noopener license">' . esc_html($b['lizenz']) . '</a>' : esc_html($b['lizenz'])) : '')
        . ($b['quelle'] !== '' ? ', <a href="' . esc_url($b['quelle']) . '" target="_blank" rel="noopener">Wikimedia Commons</a>' : '');
}

/** Bild einer Karte. Musterbild: 720er-Fassung, darüber die große. Echtes Bild: srcset und Maße aus der Mediathek. */
function ma21_firmen_bild_html(?array $b, string $sizes, bool $eager = false): string {
    if (!$b) return '';
    if (!empty($b['srcset'])) $srcset = ' srcset="' . esc_attr($b['srcset']) . '" sizes="' . esc_attr($sizes) . '"';
    else $srcset = $b['klein'] !== $b['src'] && empty($b['w']) ? ' srcset="' . esc_attr(ma21_abs($b['klein']) . ' 720w, ' . ma21_abs($b['src']) . ' 1080w') . '" sizes="' . esc_attr($sizes) . '"' : '';
    $w = (int) ($b['w'] ?? 0) ?: 720; $h = (int) ($b['h'] ?? 0) ?: 405;
    return '<img src="' . esc_url(ma21_abs($b['klein'])) . '"' . $srcset . ' alt="' . esc_attr($b['alt']) . '" width="' . $w . '" height="' . $h . '" loading="' . ($eager ? 'eager' : 'lazy') . '" decoding="async">';
}

/** Beitragskarte (Bildraster) eines Firmenbeitrags; Musterbeiträge mit Etikett und Nachweis. */
function ma21_firmen_karte_html(array $f, array $b, bool $eager = false): string {
    $u = esc_url($b['url']); $t = esc_html($b['titel']);
    return '<article class="bildraster-karte u-firmen-karte' . ($b['muster'] ? ' u-muster-karte' : '') . '">'
        . ($b['bild'] ? '<a class="bildraster-bild" href="' . $u . '" tabindex="-1" aria-hidden="true"><div class="media">' . ma21_firmen_bild_html($b['bild'], '(max-width: 520px) 92vw, (max-width: 1099px) 46vw, 420px', $eager) . ($b['muster'] ? '<span class="badge">Musterbeitrag</span>' : '') . '</div></a>' : '')
        . '<div class="bildraster-text"><span class="kicker">' . ($b['muster'] ? 'Musterbeitrag' : 'Anzeige') . ' · <a href="' . esc_url($f['url']) . '">' . esc_html($f['name']) . '</a> · ' . esc_html($f['ort']) . '</span>'
        . '<h3><a href="' . $u . '">' . $t . '</a></h3>'
        . ($b['anriss'] !== '' ? '<p class="dek">' . esc_html($b['anriss']) . '</p>' : '')
        . '<div class="meta">' . ($b['muster'] ? 'Musterbeitrag · ' . ma21_muster_nachweis($b['bild'], 'Foto: ') : esc_html($b['zeit'])) . '</div></div></article>';
}

/** Testanzeige einer Musterfirma bzw. laufende Anzeige einer echten Firma (Plugin ma_ad). '' = keine. */
function ma21_firmen_anzeige_html(array $f, string $format = 'gap'): string {
    if ($f['muster'] && !empty($f['anzeige']['titel'])) {
        // Eigene Karte statt der Werbeformate (Band und Spalte passen nicht in Text- und Seitenspalte):
        // breit Bild neben Text, schmal Bild über Text; immer als Testanzeige gekennzeichnet.
        $a = $f['anzeige']; $b = $a['bild'];
        return '<aside class="u-testanzeige u-testanzeige--' . ($format === 'gap' ? 'schmal' : 'breit') . '" aria-label="Testanzeige"><span class="u-testanzeige__label">Testanzeige</span>'
            . '<a class="u-testanzeige__karte" href="' . esc_url($a['url']) . '" aria-label="' . esc_attr('Testanzeige: ' . $f['name'] . ' – ' . $a['titel']) . '">'
            . ($b ? '<span class="u-testanzeige__bild"><img src="' . esc_url(ma21_abs($b['klein'])) . '" alt="' . esc_attr($b['alt']) . '" width="720" height="405" loading="lazy" decoding="async"></span>' : '')
            . '<span class="u-testanzeige__text"><span class="u-testanzeige__firma">' . esc_html($f['name'] . ' · ' . $f['ort']) . '</span><strong>' . esc_html($a['titel']) . '</strong>'
            . ($a['text'] !== '' ? '<span class="u-testanzeige__satz">' . esc_html($a['text']) . '</span>' : '') . '<span class="u-testanzeige__knopf">' . esc_html($a['knopf']) . '</span>'
            . '<small>Testanzeige · kein echtes Unternehmen</small></span></a>'
            . ($b ? '<span class="u-testanzeige__credit">' . ma21_muster_nachweis($b, 'Foto: ') . '</span>' : '') . '</aside>';
    }
    if ($f['muster'] || empty($f['id']) || !function_exists('ma_business_beitraege') || !function_exists('ma_ad_is_running')) return '';
    foreach (ma_business_beitraege((int) $f['id'], 6, 'ma_ad') as $ad) {
        if (!ma_ad_is_running($ad)) continue;
        $url = (string) get_post_meta($ad->ID, 'ma_ad_url', true); $img = (string) get_the_post_thumbnail_url($ad, 'large');
        $inhalt = ($img !== '' ? '<span class="ma-ad-art"><img src="' . esc_url($img) . '" alt="' . esc_attr(function_exists('ma_ad_alt') ? ma_ad_alt($ad) : '') . '" loading="lazy" decoding="async"></span>' : '')
            . '<span class="ma-ad-copy"><span class="ma-ad-eyebrow">' . esc_html($f['name']) . '</span><strong>' . esc_html(get_the_title($ad)) . '</strong>' . ($ad->post_excerpt !== '' ? '<span class="ma-ad-text">' . esc_html($ad->post_excerpt) . '</span>' : '') . '</span>';
        $attr = ' class="ma-ad-card ma-ad-card--' . esc_attr($format) . ' ma-ad__item" data-ma-anzeige="' . (int) $ad->ID . '" aria-label="' . esc_attr('Anzeige: ' . $f['name'] . ' – ' . get_the_title($ad)) . '"';
        return '<aside class="werbung werbung--' . esc_attr($format) . ' werbung--echt" aria-label="Anzeige"><span class="werbung-label">Anzeige</span><div class="werbung-flaeche ma-ad-rotator ma-ad-rotator--' . esc_attr($format) . '">'
            . ($url !== '' ? '<a' . $attr . ' href="' . esc_url($url) . '" rel="sponsored noopener" target="_blank">' . $inhalt . '</a>' : '<div' . $attr . '>' . $inhalt . '</div>') . '</div></aside>';
    }
    return '';
}

/** Kacheln der Unternehmenskanäle (Übersicht /unternehmen/, Seitenspalte im Kanal). */
function ma21_firmen_kacheln_html(array $firmen, string $ueberschrift = 'Unternehmenskanäle', string $ohne = ''): string {
    $firmen = array_values(array_filter($firmen, fn($f) => $f && $f['slug'] !== $ohne));
    if (!$firmen) return '';
    $h = '<section class="u-kanaele" aria-label="' . esc_attr($ueberschrift) . '"><p class="eyebrow">' . esc_html($ueberschrift) . '</p>';
    if (array_filter($firmen, fn($f) => $f['muster'])) $h .= '<p class="u-muster__hinweis">Beispielfirmen zeigen, wie Unternehmen hier erscheinen. Es sind keine echten Betriebe.</p>';
    $h .= '<ul class="u-kanaele__liste">';
    foreach ($firmen as $f) {
        $n = count($f['beitraege']);
        $h .= '<li><a class="u-kanal" href="' . esc_url($f['url']) . '">' . ma21_firma_logo_html($f, 'u-kanal__logo')
            . '<span class="u-kanal__text"><strong>' . esc_html($f['name']) . '</strong><small>' . esc_html($f['zeile']) . '</small>'
            . '<small class="u-kanal__zahl">' . ($f['muster'] ? '<em>Beispielfirma</em> · ' : '') . $n . ($n === 1 ? ' Beitrag' : ' Beiträge') . '</small></span></a></li>';
    }
    return $h . '</ul></section>';
}

/** Musterbeiträge für /unternehmen/: der Reihe nach je Firma einer, bis $max. */
function ma21_muster_beitraege_liste(int $max = 6): array {
    $firmen = array_map('ma21_firma_ansicht', ma21_muster_liste());
    $liste = [];
    for ($runde = 0; count($liste) < $max && $runde < 5; $runde++) {
        foreach ($firmen as $f) if ($f && isset($f['beitraege'][$runde]) && count($liste) < $max) $liste[] = [$f, $f['beitraege'][$runde]];
    }
    return $liste;
}
