<?php
/**
 * Startseite ohne Sport (Entscheidung KBS 26.09.2026).
 *
 * Dieselbe Regel wie die statische Auslieferung (deploy/lib-artikel.mjs,
 * sportBezug): Rubrik Sport ist immer Sport; Blaulicht, Rathaus und
 * Wirtschaft nie. In allen anderen Rubriken entscheidet ein Muster ueber
 * Titel, Auszug und Schlagworte (Sportlerheim-Ausbau, Fanclub-Fahrt usw.).
 * Sport bleibt auf /sport/ und im Vereinskanal sichtbar.
 */
if (!defined('ABSPATH')) { exit; }

const MA_SPORT_MUSTER = '/fu(?:ß|ss)ball|kreisliga|bezirksliga|landesliga|bundesliga|sc 1919|sc merzenich|fc golzheim|sv morschenich|rhenania|fan-?club|fc-fanclub|1\. fc k(?:ö|oe)ln|sportlerheim|sportplatz|kunstrasen|tennis|tischtennis|billard|badminton|tv merzenich|turnverein|handball|volleyball|leichtathlet|sportverein|e-jugend|d-jugend|c-jugend|spieltag|\bsport\b/iu';
const MA_SPORT_RESSORTS = ['sport'];
const MA_NIE_SPORT_RESSORTS = ['blaulicht','rathaus','wirtschaft'];

/** Kategorie-Slugs des Beitrags, einschliesslich uebergeordneter Rubriken. */
function ma_post_ressort_slugs(int $post_id): array {
    $slugs = [];
    $terms = get_the_category($post_id);
    foreach ((array)$terms as $t) {
        if (!is_object($t)) continue;
        $slugs[] = strtolower((string)$t->slug);
        if (!empty($t->parent) && function_exists('get_ancestors')) {
            foreach ((array)get_ancestors((int)$t->term_id, 'category') as $ancestor_id) {
                $a = get_term((int)$ancestor_id, 'category');
                if ($a && is_object($a) && !empty($a->slug)) $slugs[] = strtolower((string)$a->slug);
            }
        }
    }
    return array_values(array_unique($slugs));
}

/** Text, gegen den das Sportmuster laeuft: Titel, Auszug, Schlagworte. */
function ma_post_sport_text(WP_Post $post): string {
    $teile = [(string)$post->post_title, (string)$post->post_excerpt];
    foreach ((array)get_the_tags($post->ID) as $tag) {
        // Slug mit Leerzeichen statt Bindestrich: "sc-merzenich" trifft so
        // dasselbe Muster wie "SC Merzenich".
        if (is_object($tag)) $teile[] = $tag->name.' '.str_replace('-', ' ', (string)$tag->slug);
    }
    $text = implode(' | ', $teile);
    return html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function ma_text_has_sport(string $text): bool {
    return (bool)preg_match(MA_SPORT_MUSTER, $text);
}

/** Hat der Beitrag Sportbezug? */
function ma_is_sport_post($post): bool {
    $post = $post instanceof WP_Post ? $post : get_post($post);
    if (!$post instanceof WP_Post) return false;
    $ressorts = ma_post_ressort_slugs((int)$post->ID);
    if (array_intersect($ressorts, MA_SPORT_RESSORTS)) return true;
    if (array_intersect($ressorts, MA_NIE_SPORT_RESSORTS)) return false;
    return ma_text_has_sport(ma_post_sport_text($post));
}

/** Liste fuer die Startseite: Sportbeitraege raus, Reihenfolge bleibt. */
function ma_home_without_sport(array $posts): array {
    return array_values(array_filter($posts, static fn($p) => $p instanceof WP_Post && !ma_is_sport_post($p)));
}
