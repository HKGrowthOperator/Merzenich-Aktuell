<?php
/**
 * Theme 21: WordPress sieht aus wie die statische Seite (30.09.2026).
 *
 * Kopf, Fuß und die Service-Blöcke der Startseite kommen als Vorlagen aus der
 * gebauten Seite (deploy/wp-theme.mjs -> vorlagen/*.html). Die Nachrichten
 * (Bühne, Rubrikflächen, Listen, Artikel) setzt WordPress aus seinen Beiträgen
 * in genau dem Markup, das deploy/inhaltsindex.mjs und deploy/meldungen.mjs
 * erzeugen, damit dieselben Stylesheets greifen.
 *
 * Stylesheets, Skripte, Schriften und Bilder der statischen Seite liegen im
 * Theme unter static/ (beim Paketbau aus chatgpt-site/assets kopiert) und
 * werden unter /assets/ ausgeliefert (Rewrite in .htaccess). Fehlt eine Datei,
 * leitet der Server auf die statische Seite um.
 */
if (!defined('ABSPATH')) { exit; }

const MA21_STATISCH = 'https://merzenichaktuell.hk-growthoperator.de';
const MA21_ORTE = ['merzenich' => 'Merzenich', 'golzheim' => 'Golzheim', 'girbelsrath' => 'Girbelsrath', 'morschenich' => 'Morschenich', 'buergewald' => 'Bürgewald'];
const MA21_RESSORT = ['blaulicht' => 'Blaulicht', 'sport' => 'Sport', 'rathaus' => 'Rathaus & Politik', 'leben' => 'Leben', 'wirtschaft' => 'Wirtschaft', 'menschen' => 'Menschen', 'vereine' => 'Vereine', 'tipp' => 'Tipp'];
// Bildhinweis je Bildtyp: auf der Startseite (knapp) und in Listen/Artikeln.
const MA21_HINWEIS_START = ['symbol' => 'Symbolbild', 'place' => 'Ortsansicht', 'original' => '', 'official' => 'Bild: Quelle', 'licensed' => 'Archivbild'];
const MA21_HINWEIS = ['symbol' => 'Symbolbild', 'place' => 'Ortsansicht', 'original' => 'Originalbild', 'official' => 'Quellenmotiv', 'licensed' => 'Archivbild'];

/* ------------------------------------------------------------ Vorlagen */

/** Vorlage aus vorlagen/ lesen (von deploy/wp-theme.mjs erzeugt). */
function ma21_vorlage(string $name): string {
    static $cache = [];
    if (!isset($cache[$name])) {
        $pfad = get_template_directory() . '/vorlagen/' . $name;
        $cache[$name] = is_readable($pfad) ? (string) file_get_contents($pfad) : '';
    }
    return $cache[$name];
}

/* /assets/ -> Theme-Verzeichnis static/, sonst Umleitung auf die statische Seite. */
add_filter('mod_rewrite_rules', function (string $regeln): string {
    $dir = trailingslashit(get_template_directory()) . 'static/';
    $rel = ltrim(str_replace(ABSPATH, '', $dir), '/');
    $block = "# BEGIN Merzenich Aktuell Assets\n<IfModule mod_rewrite.c>\nRewriteEngine On\n"
        . "RewriteCond {$dir}$1 -f\nRewriteRule ^assets/(.*)$ {$rel}$1 [L]\n"
        . "RewriteRule ^assets/(.*)$ " . MA21_STATISCH . "/assets/$1 [R=302,L]\n"
        . "</IfModule>\n# END Merzenich Aktuell Assets\n";
    return $block . $regeln;
});
add_action('after_switch_theme', function (): void { flush_rewrite_rules(true); });

/* Kopf aufräumen: WordPress-Blockstile und Emoji-Skripte gibt es auf der
   statischen Seite nicht, sie verschieben Abstände. */
add_action('wp_enqueue_scripts', function (): void {
    if (ma21_legacy()) return;
    foreach (['wp-block-library', 'wp-block-library-theme', 'global-styles', 'classic-theme-styles'] as $h) wp_dequeue_style($h);
}, 100);
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

/** Vorlagen mit eigenem, älterem Markup (Märkte, Anzeigen, Betriebe) brauchen noch die alten Stile. */
function ma21_legacy(): bool {
    $alt = ['ma_property', 'ma_job', 'ma_obituary', 'ma_family_notice', 'ma_business', 'ma_tip', 'ma_event', 'ma_club'];
    return is_singular($alt) || is_post_type_archive($alt);
}

add_filter('body_class', function (array $k): array { $k[] = 'v20'; return $k; });

/* ------------------------------------------------------------ Bausteine */

function ma21_e($s): string { return esc_html((string) $s); }

function ma21_ressort(WP_Post $p): array {
    foreach (get_the_category($p->ID) as $c) {
        if (isset(MA21_RESSORT[$c->slug])) return [$c->slug, MA21_RESSORT[$c->slug]];
    }
    $c = get_the_category($p->ID)[0] ?? null;
    return $c ? [$c->slug, $c->name] : ['nachrichten', 'Nachrichten'];
}

function ma21_ort(WP_Post $p): string {
    $t = get_the_terms($p->ID, 'ma_location');
    if (is_array($t)) foreach ($t as $term) if (isset(MA21_ORTE[$term->slug])) return $term->slug;
    return 'merzenich';
}

/** Ortsmarke: MERZENICH, Ortsteil, Rubrik (Ressort oder Kicker). */
function ma21_marke(WP_Post $p, string $rubrik = 'ressort'): string {
    $ort = ma21_ort($p);
    $label = $rubrik === 'kicker' ? (get_post_meta($p->ID, 'ma_kicker', true) ?: ma21_ressort($p)[1]) : ma21_ressort($p)[1];
    return '<p class="marke"><span class="marke-ort">Merzenich</span>'
        . ($ort !== 'merzenich' ? '<span class="marke-teil"> · ' . ma21_e(MA21_ORTE[$ort]) . '</span>' : '')
        . '<span class="marke-rubrik">' . ma21_e($label) . '</span></p>';
}

function ma21_zeit(WP_Post $p, bool $lang = false): string {
    $fmt = $lang ? 'd.m.Y · H:i' : 'd.m. · H:i';
    return '<time datetime="' . esc_attr(get_post_time('c', false, $p)) . '">' . ma21_e(get_post_time($fmt, false, $p, true)) . ' Uhr</time>';
}

function ma21_lesezeit(WP_Post $p): int {
    $w = str_word_count(wp_strip_all_tags($p->post_excerpt . ' ' . $p->post_content));
    return max(1, (int) round($w / 200));
}

function ma21_teaser(WP_Post $p): string {
    $t = trim($p->post_excerpt) ?: wp_trim_words(wp_strip_all_tags($p->post_content), 30, ' …');
    return $t;
}

/** Bilddaten des Beitragsbilds oder null. */
function ma21_bild(WP_Post $p): ?array {
    $id = (int) get_post_thumbnail_id($p->ID);
    if (!$id) return null;
    $voll = wp_get_attachment_image_src($id, 'full');
    if (!$voll) return null;
    $alt = get_post_meta($id, '_wp_attachment_image_alt', true) ?: get_the_title($id);
    $typ = get_post_meta($p->ID, 'ma_image_type', true) ?: get_post_meta($id, 'ma_image_type', true);
    return ['id' => $id, 'src' => $voll[0], 'w' => (int) $voll[1], 'h' => (int) $voll[2], 'srcset' => (string) wp_get_attachment_image_srcset($id, 'full'),
        'alt' => $alt, 'typ' => (string) $typ, 'credit' => (string) get_post_meta($p->ID, 'ma_image_credit', true)];
}

/** Echtes, redaktionell nutzbares Bild: kein Logo, kein Wappen. */
function ma21_echtes_bild(?array $b): bool {
    return $b && !preg_match('/logo|wappen/i', $b['alt'] . ' ' . $b['src']);
}

function ma21_img(array $b, string $sizes, bool $eager): string {
    return '<img src="' . esc_url($b['src']) . '"' . ($b['srcset'] ? ' srcset="' . esc_attr($b['srcset']) . '"' : '')
        . ' sizes="' . esc_attr($sizes) . '" alt="' . esc_attr($b['alt']) . '"' . ($b['w'] && $b['h'] ? ' width="' . $b['w'] . '" height="' . $b['h'] . '"' : '')
        . ' loading="' . ($eager ? 'eager' : 'lazy') . '"' . ($eager ? ' fetchpriority="high"' : '') . ' decoding="async" data-editorial-image class="">';
}

function ma21_badge(array $b, bool $start): string {
    $t = ($start ? MA21_HINWEIS_START : MA21_HINWEIS)[$b['typ']] ?? '';
    return $t !== '' ? '<span class="badge">' . ma21_e($t) . '</span>' : '';
}

function ma21_story_id(WP_Post $p): string { return get_post_time('Y-m-d', false, $p) . '-' . $p->post_name; }

function ma21_bildflaeche(WP_Post $p, array $b, string $sizes, bool $eager): string {
    return '<a class="karte-bild" href="' . esc_url(get_permalink($p)) . '" tabindex="-1" aria-hidden="true"><div class="media">'
        . ma21_img($b, $sizes, $eager) . ma21_badge($b, true) . '</div></a>';
}

/** Startseitenkarte in den Größen xl (Aufmacher), r/u (Bühne), l, m, s (Rubrikflächen). */
function ma21_karte(WP_Post $p, string $g, string $tag = 'h3'): string {
    $url = esc_url(get_permalink($p)); $titel = ma21_e(get_the_title($p)); $b = ma21_bild($p); $id = esc_attr(ma21_story_id($p));
    $kopf = "<{$tag}><a href=\"{$url}\">{$titel}</a></{$tag}>";
    if ($g === 'xl') {
        return "<article class=\"front-lead\" data-story=\"{$id}\">" . ($b ? ma21_bildflaeche($p, $b, '(max-width: 760px) 100vw, 860px', true) : '')
            . '<div class="front-lead-copy">' . ma21_marke($p) . "<h1><a href=\"{$url}\">{$titel}</a></h1><p>" . ma21_e(ma21_teaser($p)) . '</p>'
            . '<div class="meta">' . ma21_zeit($p) . '<span>' . ma21_lesezeit($p) . ' Min. Lesezeit</span></div></div></article>';
    }
    if ($g === 'r' || $g === 'u') {
        $sizes = $g === 'r' ? '(max-width: 760px) 132px, (max-width: 1100px) 50vw, 460px' : '(max-width: 760px) 132px, (max-width: 1100px) 33vw, 440px';
        return "<article class=\"front-neben-story buehne-karte buehne-karte--{$g}\" data-story=\"{$id}\">" . ($b ? ma21_bildflaeche($p, $b, $sizes, false) : '')
            . '<div class="karte-text">' . ma21_marke($p) . "<h2><a href=\"{$url}\">{$titel}</a></h2><div class=\"meta\">" . ma21_zeit($p) . '</div></div></article>';
    }
    if ($g === 'l') {
        return "<article class=\"desk-karte desk-karte--gross\" data-story=\"{$id}\">" . ma21_bildflaeche($p, $b, '(max-width: 900px) 100vw, 600px', false)
            . '<div class="karte-text">' . ma21_marke($p) . $kopf . '<p class="dek">' . ma21_e(ma21_teaser($p)) . '</p><div class="meta">' . ma21_zeit($p) . '</div></div></article>';
    }
    if ($g === 'm') {
        return "<article class=\"desk-karte desk-karte--mittel\" data-story=\"{$id}\">" . ma21_bildflaeche($p, $b, '(max-width: 1100px) 46vw, 390px', false)
            . '<div class="karte-text">' . ma21_marke($p) . $kopf . '<div class="meta">' . ma21_zeit($p) . '</div></div></article>';
    }
    $mitBild = ma21_echtes_bild($b);
    return '<article class="front-zeile' . ($mitBild ? ' front-zeile--bild' : '') . "\" data-story=\"{$id}\">"
        . ($mitBild ? ma21_bildflaeche($p, $b, '(max-width: 640px) 120px, 220px', false) : '')
        . '<div class="karte-text">' . ma21_marke($p) . $kopf . '<div class="meta">' . ma21_zeit($p) . '</div></div></article>';
}

/* ------------------------------------------------------------ Startseite */

/** Platzwahl der Redaktion (Metabox im Plugin): auto, aufmacher, buehne-1 … buehne-4, aus. */
function ma21_startplatz(WP_Post $p): string {
    $v = (string) get_post_meta($p->ID, 'ma_startplatz', true);
    if ($v === '' && get_post_meta($p->ID, 'ma_top_pinned', true) === '1') {
        $bis = (string) get_post_meta($p->ID, 'ma_top_until', true);
        if ($bis === '' || strtotime($bis) > time()) return 'aufmacher';
    }
    return $v ?: 'auto';
}

function ma21_ist_sport(WP_Post $p): bool { return has_category('sport', $p); }

/**
 * Belegung der Startseite wie deploy/inhaltsindex.mjs: Aufmacher, fünf
 * Nebenmeldungen (zwei rechts, drei darunter), dann die Rubrikflächen.
 * Gesetzte Plätze der Redaktion gehen vor; freie Plätze füllt die jüngste
 * passende Meldung. „aus“ = nur in der eigenen Rubrik, nie auf der Startseite.
 */
function ma21_startseite_belegung(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $alle = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 120, 'orderby' => 'date', 'order' => 'DESC', 'suppress_filters' => false]);
    $alle = array_values(array_filter($alle, fn($p) => ma21_startplatz($p) !== 'aus'));
    $vergeben = []; $motive = []; $MOTIV_MAX = 2;
    $motiv = fn($p) => (int) get_post_thumbnail_id($p->ID);
    $motivFrei = function ($p, array $extra = []) use (&$motive, $motiv, $MOTIV_MAX) { $k = $motiv($p); return !$k || (($motive[$k] ?? 0) + ($extra[$k] ?? 0)) < $MOTIV_MAX; };
    $belegen = function ($p) use (&$motive, &$vergeben, $motiv) { $vergeben[$p->ID] = true; $k = $motiv($p); if ($k) $motive[$k] = ($motive[$k] ?? 0) + 1; };
    $buehnenTauglich = fn($p) => !ma21_ist_sport($p) && !has_category('tipp', $p) && ma21_echtes_bild(ma21_bild($p));
    $breite = fn($p) => (ma21_bild($p)['w'] ?? 0);

    // Aufmacher: gesetzt, sonst jüngste bühnentaugliche Meldung mit großem Bild.
    $aufmacher = null;
    foreach ($alle as $p) if (ma21_startplatz($p) === 'aufmacher' && $buehnenTauglich($p)) { $aufmacher = $p; break; }
    if (!$aufmacher) foreach ($alle as $p) if (ma21_startplatz($p) === 'auto' && $buehnenTauglich($p) && $breite($p) >= 480 && (ma21_bild($p)['typ'] ?? '') !== 'place') { $aufmacher = $p; break; }
    if ($aufmacher) $belegen($aufmacher);

    // Nebenplätze 1–4 (der fünfte Platz unten rechts ist seit 30.09. eine
    // Anzeige): gesetzte zuerst auf ihren Platz, Rest automatisch.
    $neben = array_fill(1, 4, null);
    foreach ($alle as $p) {
        if (isset($vergeben[$p->ID]) || !preg_match('/^buehne-([1-4])$/', ma21_startplatz($p), $m) || !$buehnenTauglich($p)) continue;
        if ($neben[(int) $m[1]] === null) { $neben[(int) $m[1]] = $p; $belegen($p); }
    }
    $blaulicht = ($aufmacher && has_category('blaulicht', $aufmacher)) ? 1 : 0;
    foreach ($neben as $p) if ($p && has_category('blaulicht', $p)) $blaulicht++;
    $bMotive = array_filter(array_map($motiv, array_filter(array_merge([$aufmacher], $neben))));
    foreach ([false, true] as $mitOrt) {
        foreach ($alle as $p) {
            if (!in_array(null, $neben, true)) break;
            if (isset($vergeben[$p->ID]) || ma21_startplatz($p) !== 'auto' || !$buehnenTauglich($p) || $breite($p) < 360) continue;
            $istOrt = (ma21_bild($p)['typ'] ?? '') === 'place';
            if (!$mitOrt && $istOrt) continue;
            if (!$motivFrei($p) || in_array($motiv($p), $bMotive, true)) continue;
            if (has_category('blaulicht', $p)) { if ($blaulicht >= 2) continue; $blaulicht++; }
            $platz = array_search(null, $neben, true); $neben[$platz] = $p; $belegen($p); $bMotive[] = $motiv($p);
        }
    }

    // Rubrikflächen: erst die ressortgebundenen, dann die Leitsektion.
    $termine = function_exists('ma21_kommende_termine') ? count(ma21_kommende_termine(4)) : 3;
    $SEK = [
        'blaulicht' => ['titel' => 'Blaulicht', 'mehr' => '/blaulicht/', 'mehrText' => 'Alle Einsatzmeldungen', 'nimm' => fn($p) => has_category('blaulicht', $p)],
        'rathaus' => ['titel' => 'Politik & Gemeinde', 'mehr' => '/rathaus/', 'mehrText' => 'Zum Rathaus', 'nimm' => fn($p) => has_category('rathaus', $p)],
        'wirtschaft' => ['titel' => 'Wirtschaft', 'mehr' => '/wirtschaft/', 'mehrText' => 'Zur Wirtschaft', 'nimm' => fn($p) => has_category('wirtschaft', $p)],
        'vereine' => ['titel' => 'Vereine & Menschen', 'mehr' => '/vereine/', 'mehrText' => 'Zu den Vereinen', 'nimm' => fn($p) => (has_category('vereine', $p) || has_category('menschen', $p)) && !ma21_ist_sport($p)],
        'gemeinde' => ['titel' => 'Nachrichten aus Merzenich', 'mehr' => '/nachrichten/', 'mehrText' => 'Alle Meldungen', 'nimm' => fn($p) => !ma21_ist_sport($p) && !has_category('tipp', $p), 'jeRessort' => 3, 'fenster' => 44, 'zeilen' => max(2, min(4, $termine))],
    ];
    $belegung = [];
    foreach ($SEK as $id => $s) {
        $frei = array_slice(array_values(array_filter($alle, fn($p) => !isset($vergeben[$p->ID]) && $s['nimm']($p))), 0, $s['fenster'] ?? 20);
        $jeRessort = [];
        $waehle = function (array $pool, int $n, array $schon) use ($s, &$jeRessort, $motiv, $motivFrei) {
            $gez = $jeRessort; $extra = []; $raus = [];
            foreach ($schon as $a) { $r = ma21_ressort($a)[0]; $gez[$r] = ($gez[$r] ?? 0) + 1; $k = $motiv($a); if ($k) $extra[$k] = ($extra[$k] ?? 0) + 1; }
            foreach ($pool as $a) {
                if (count($raus) >= $n) break;
                if (in_array($a, $schon, true)) continue;
                $r = ma21_ressort($a)[0];
                if (!empty($s['jeRessort']) && ($gez[$r] ?? 0) >= $s['jeRessort']) continue;
                if (!$motivFrei($a, $extra)) continue;
                $gez[$r] = ($gez[$r] ?? 0) + 1; $k = $motiv($a); if ($k) $extra[$k] = ($extra[$k] ?? 0) + 1;
                $raus[] = $a;
            }
            return $raus;
        };
        $ortZuletzt = fn(array $l) => array_merge(array_filter($l, fn($a) => (ma21_bild($a)['typ'] ?? '') !== 'place'), array_filter($l, fn($a) => (ma21_bild($a)['typ'] ?? '') === 'place'));
        $lF = $ortZuletzt(array_filter($frei, function ($a) use ($breite) { $b = ma21_bild($a); return ma21_echtes_bild($b) && $breite($a) >= 480 && (!$b['h'] || $b['w'] / $b['h'] >= 1.2); }));
        $mF = $ortZuletzt(array_filter($frei, fn($a) => ma21_echtes_bild(ma21_bild($a)) && $breite($a) >= 360));
        $gross = $waehle($lF, 2, []);
        $mittel = count($gross) === 2 ? $waehle($mF, 3, $gross) : [];
        if (count($gross) !== 2 || count($mittel) !== 3) {
            $nurMittel = $waehle($mF, 3, []);
            if (count($nurMittel) === 3) { $gross = []; $mittel = $nurMittel; }
            elseif (count($gross) === 2) { $mittel = []; }
            else { $gross = []; $mittel = []; }
        }
        foreach (array_merge($gross, $mittel) as $a) { $r = ma21_ressort($a)[0]; $jeRessort[$r] = ($jeRessort[$r] ?? 0) + 1; $belegen($a); }
        $zeilen = [];
        foreach ($frei as $a) {
            if (count($zeilen) >= ($s['zeilen'] ?? 2)) break;
            if (isset($vergeben[$a->ID]) || !$motivFrei($a)) continue;
            $zeilen[] = $a; $belegen($a);
        }
        $belegung[$id] = ['s' => $s, 'gross' => $gross, 'mittel' => $mittel, 'zeilen' => $zeilen];
    }
    return $cache = ['aufmacher' => $aufmacher, 'neben' => $neben, 'sektionen' => $belegung];
}

function ma21_block(string $name): string {
    $b = ma21_startseite_belegung();
    if ($name === 'oben') {
        $neben = array_values(array_filter($b['neben']));
        $rechts = array_slice($neben, 0, 2); $unten = array_slice($neben, 2, 2);
        $inhalt = ($b['aufmacher'] ? ma21_karte($b['aufmacher'], 'xl') : '')
            . ($rechts ? '<div class="buehne-rechts">' . implode('', array_map(fn($p) => ma21_karte($p, 'r'), $rechts)) . '</div>' : '')
            . '<div class="buehne-unten">' . implode('', array_map(fn($p) => ma21_karte($p, 'u'), $unten)) . trim(ma21_vorlage('werbung-buehne.html')) . '</div>';
        return '<!-- start:oben:start --><section class="shell buehne" data-editorial-verified="1" aria-label="Die wichtigsten Nachrichten">' . $inhalt . '</section><!-- start:oben:end -->';
    }
    $sek = $b['sektionen'][$name] ?? null;
    $rahmen = fn($x) => "<!-- start:{$name}:start -->{$x}<!-- start:{$name}:end -->";
    if (!$sek || !($sek['gross'] || $sek['mittel'] || $sek['zeilen'])) return $rahmen('');
    $s = $sek['s'];
    $kopf = '<div class="desk-heading"><div><h2>' . ma21_e($s['titel']) . '</h2></div><a class="desk-more" href="' . esc_url(home_url($s['mehr'])) . '">' . ma21_e($s['mehrText']) . '</a></div>';
    $reihe = fn($klasse, $l, $g) => $l ? "<div class=\"{$klasse}\">" . implode('', array_map(fn($p) => ma21_karte($p, $g), $l)) . '</div>' : '';
    return $rahmen("<section class=\"desk shell\" data-sektion=\"{$name}\">{$kopf}" . $reihe('desk-gross', $sek['gross'], 'l') . $reihe('desk-mittel', $sek['mittel'], 'm') . $reihe('desk-zeilen', $sek['zeilen'], 's') . '</section>');
}

/** Startseite: Vorlage mit gefüllten Nachrichtenblöcken. */
function ma21_startseite(): string {
    $html = ma21_vorlage('startseite.html');
    return preg_replace_callback('/\{\{ma:([a-z]+)\}\}/', fn($m) => ma21_block($m[1]), $html);
}

/** Kommende Termine (ma_event), nach Beginn sortiert. */
function ma21_kommende_termine(int $n): array {
    $jetzt = current_time('Y-m-d\TH:i');
    $q = get_posts(['post_type' => 'ma_event', 'post_status' => 'publish', 'posts_per_page' => 50, 'meta_key' => 'ma_event_start', 'orderby' => 'meta_value', 'order' => 'ASC']);
    $raus = [];
    foreach ($q as $p) {
        $ende = get_post_meta($p->ID, 'ma_event_end', true) ?: substr((string) get_post_meta($p->ID, 'ma_event_start', true), 0, 10) . 'T23:59';
        if ($ende >= $jetzt) $raus[] = $p;
        if (count($raus) >= $n) break;
    }
    return $raus;
}

/* ------------------------------------------------------------ Listen */

/** Karte im „Weiterlesen“-Block unter einer Meldung. */
function ma21_news_card(WP_Post $p): string {
    $url = esc_url(get_permalink($p)); $titel = ma21_e(get_the_title($p)); $b = ma21_bild($p);
    return '<article class="news-card">' . ($b ? "\n  <a href=\"{$url}\" tabindex=\"-1\" aria-hidden=\"true\"><div class=\"media\">" . ma21_img($b, '(max-width: 640px) 100vw, 400px', false) . ma21_badge($b, false) . '</div></a>' : '')
        . "\n  <div class=\"news-card-body\">\n    " . ma21_marke($p, 'kicker') . "\n    <h3><a href=\"{$url}\">{$titel}</a></h3>\n    <p class=\"dek\">" . ma21_e(ma21_teaser($p)) . "</p>\n    <div class=\"meta\">" . ma21_zeit($p, true) . '</div>'
        . "<div class=\"story-actions\"><a class=\"read-more\" href=\"{$url}\">Mehr lesen<span class=\"sr-only\">: {$titel}</span></a></div>\n  </div>\n</article>";
}

/** Erste Meldung einer Liste (Rubrik, Ort, Thema). */
function ma21_feed_lead(WP_Post $p): string {
    $url = esc_url(get_permalink($p)); $titel = ma21_e(get_the_title($p)); $b = ma21_bild($p);
    return '<article class="feed-lead" data-story="' . esc_attr(ma21_story_id($p)) . '">'
        . ($b ? "<a href=\"{$url}\" tabindex=\"-1\" aria-hidden=\"true\"><div class=\"media\">" . ma21_img($b, '(max-width: 640px) 100vw, 800px', true) . ma21_badge($b, false) . '</div></a>' : '')
        . '<div class="lead-copy">' . ma21_marke($p, 'kicker') . "<h2><a href=\"{$url}\">{$titel}</a></h2><p class=\"dek\">" . ma21_e(ma21_teaser($p)) . '</p>'
        . '<div class="meta">' . ma21_zeit($p, true) . '<span class="readtime">' . ma21_lesezeit($p) . ' Min.</span></div></div></article>';
}

function ma21_feed_row(WP_Post $p): string {
    $url = esc_url(get_permalink($p)); $titel = ma21_e(get_the_title($p)); $b = ma21_bild($p);
    return '<article data-story="' . esc_attr(ma21_story_id($p)) . '" class="feed-row">'
        . ($b ? "<a class=\"feed-img\" href=\"{$url}\" tabindex=\"-1\" aria-hidden=\"true\"><div class=\"media\">" . ma21_img($b, '(max-width: 640px) 120px, 240px', false) . ma21_badge($b, false) . '</div></a>' : '')
        . '<div class="feed-copy">' . ma21_marke($p, 'kicker') . "<h3><a href=\"{$url}\">{$titel}</a></h3><p class=\"dek\">" . ma21_e(ma21_teaser($p)) . '</p>'
        . '<div class="meta">' . ma21_zeit($p, true) . '<span class="readtime">' . ma21_lesezeit($p) . ' Min.</span></div>'
        . "<div class=\"story-actions\"><a class=\"read-more\" href=\"{$url}\">Mehr lesen<span class=\"sr-only\">: {$titel}</span></a></div></div></article>";
}

/** Kopf und Beschreibung je Liste, wie auf der statischen Seite. */
function ma21_liste_kopf(): array {
    $o = get_queried_object();
    $TEXTE = [
        'blaulicht' => ['Blaulicht', 'Feuerwehr, Polizei und Rettungsdienst', 'Einsätze der Freiwilligen Feuerwehr Merzenich, Polizeimeldungen für das Gemeindegebiet und Verkehrsmeldungen, sachlich zusammengefasst und mit Originalquelle.'],
        'rathaus' => ['Rathaus & Politik', 'Rathaus, Rat und Gemeinde', 'Beschlüsse, Bekanntmachungen und Projekte der Gemeinde Merzenich.'],
        'sport' => ['Sport', 'Sport in Merzenich', 'Spielberichte, Ergebnisse und Neues aus den Sportvereinen der Gemeinde.'],
        'leben' => ['Leben', 'Leben in Merzenich', 'Schule, Familie, Kirche, Senioren und Alltag in den fünf Orten.'],
        'wirtschaft' => ['Wirtschaft', 'Wirtschaft und Arbeit', 'Betriebe, Arbeit, Infrastruktur und Strukturwandel im Rheinischen Revier.'],
        'vereine' => ['Vereine', 'Aus den Vereinen', 'Meldungen, Feste und Termine der Vereine in Merzenich.'],
    ];
    if (is_category() && $o instanceof WP_Term && isset($TEXTE[$o->slug])) return $TEXTE[$o->slug];
    if (is_tax('ma_location') && $o instanceof WP_Term) return ['Ort', $o->name, $o->description ?: "Meldungen aus {$o->name}."];
    if (is_tag() && $o instanceof WP_Term) return ['Thema', $o->name, $o->description ?: "Alle Meldungen zum Thema {$o->name}."];
    if (is_search()) return ['Suche', 'Suche: ' . get_search_query(), ''];
    if ($o instanceof WP_Term) return [$o->name, $o->name, $o->description];
    return ['Nachrichten', 'Alle Meldungen', 'Alle Meldungen aus der Gemeinde Merzenich, die jüngste zuerst.'];
}

/* ------------------------------------------------------------ Adressen der statischen Seite */

/* /nachrichten/ = alle Meldungen (statisch: Liste aller Ressorts). /api/weather.json
   liefert Open-Meteo wie der Dienst der statischen Seite (cockpit.js, app.js). */
add_action('init', function (): void {
    add_rewrite_rule('^nachrichten/?$', 'index.php?ma_alle=1', 'top');
    add_rewrite_rule('^nachrichten/page/([0-9]+)/?$', 'index.php?ma_alle=1&paged=$matches[1]', 'top');
    add_rewrite_rule('^api/weather\.json/?$', 'index.php?ma_api=weather', 'top');
});
add_filter('query_vars', function (array $v): array { $v[] = 'ma_alle'; $v[] = 'ma_api'; return $v; });
// Keine Schrägstrich-Umleitung für /api/weather.json (die Skripte rufen genau diese Adresse).
add_filter('redirect_canonical', fn($ziel) => get_query_var('ma_api') ? false : $ziel);
add_action('pre_get_posts', function (WP_Query $q): void {
    if (!is_admin() && $q->is_main_query() && $q->get('ma_alle')) {
        $q->set('post_type', 'post'); $q->set('posts_per_page', 20);
        $q->is_home = false; $q->is_archive = true; $q->is_404 = false;
    }
});
add_action('template_redirect', function (): void {
    if (get_query_var('ma_api') !== 'weather') return;
    $daten = get_transient('ma21_wetter');
    if (!$daten) {
        $url = 'https://api.open-meteo.com/v1/forecast?latitude=50.8317&longitude=6.5361&current=temperature_2m,weather_code&daily=temperature_2m_max,temperature_2m_min,weather_code&timezone=Europe%2FBerlin&forecast_days=3';
        $r = wp_remote_get($url, ['timeout' => 6]);
        if (!is_wp_error($r) && wp_remote_retrieve_response_code($r) === 200) {
            $daten = wp_remote_retrieve_body($r);
            set_transient('ma21_wetter', $daten, 10 * MINUTE_IN_SECONDS);
        }
    }
    nocache_headers();
    if (!$daten) { status_header(503); wp_send_json(['fehler' => 'Wetter nicht verfügbar']); }
    header('Content-Type: application/json; charset=utf-8');
    echo $daten; exit;
});

/* Nach einem Theme-Update Regeln und .htaccess einmal neu schreiben (Assets, Adressen). */
add_action('init', function (): void {
    $v = wp_get_theme(get_template())->get('Version');
    if (get_option('ma21_regeln') === $v) return;
    flush_rewrite_rules(true);
    update_option('ma21_regeln', $v, false);
}, 99);
