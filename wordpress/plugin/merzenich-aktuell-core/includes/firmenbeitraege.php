<?php
/**
 * Firmenbeiträge nur unter „Unternehmen“ (28.10.2026, Auftrag Betreiber).
 *
 * Ein Firmenbeitrag ist ein Beitrag mit ma_gesponsert = 1 (Anzeige eines Unternehmens;
 * Beiträge der Unternehmens-Zugänge werden beim Speichern so markiert, werbung-stat.php).
 * Er erscheint nur unter /unternehmen/, im Firmenkanal, im Unternehmen-Menü und auf
 * seiner eigenen Seite, nicht auf der Startseite, in Rubriken, /nachrichten/,
 * Ortsteilen, Themen, Suche, Seitenspalten, „Das passt zum Thema“ oder im RSS-Feed.
 *
 * Umsetzung: Jede Beitragsabfrage beim Seitenaufruf schließt Firmenbeiträge aus.
 * Ausgenommen sind Abfragen, die ausdrücklich nach Firmenbeiträgen fragen (meta
 * ma_gesponsert oder ma_business_id), eine feste Auswahl (post__in), einzelne
 * Beiträge, das Merkmal ma_firmen_erlaubt sowie Admin, Cron, AJAX, REST, WP-CLI
 * und die Sitemaps.
 */
if (!defined('ABSPATH')) { exit; }

/** Fragt diese Abfrage ausdrücklich nach Firmenbeiträgen (meta_key oder meta_query mit ma_gesponsert/ma_business_id)? */
function ma_firmenbeitraege_gefragt(WP_Query $q): bool {
    if (in_array((string) $q->get('meta_key'), ['ma_gesponsert', 'ma_business_id'], true)) return true;
    $suche = function ($mq) use (&$suche): bool {
        foreach ((array) $mq as $k => $v) {
            if ($k === 'key' && in_array((string) $v, ['ma_gesponsert', 'ma_business_id'], true)) return true;
            if (is_array($v) && $suche($v)) return true;
        }
        return false;
    };
    return $suche($q->get('meta_query'));
}

/** Soll der Ausschluss für diese Abfrage gelten? */
function ma_firmenbeitraege_filter_aktiv(WP_Query $q): bool {
    if (is_admin() || wp_doing_cron() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST) || (defined('WP_CLI') && WP_CLI)) return false;
    if ($q->get('ma_firmen_erlaubt') || (string) get_query_var('sitemap') !== '') return false;
    if ($q->get('p') || $q->get('name') || $q->get('page_id') || $q->get('pagename') || $q->get('attachment')) return false;
    if (array_filter((array) $q->get('post__in'))) return false;
    $typ = $q->get('post_type');
    if ($typ !== '' && $typ !== 'any' && !in_array('post', (array) $typ, true)) return false;
    return !ma_firmenbeitraege_gefragt($q);
}

/** Bedingung „kein Firmenbeitrag“ an die vorhandene meta_query hängen, ohne deren Verknüpfung zu ändern. */
function ma_firmenbeitraege_ausblenden(WP_Query $q): void {
    if (!ma_firmenbeitraege_filter_aktiv($q)) return;
    $ohne = ['relation' => 'OR', ['key' => 'ma_gesponsert', 'compare' => 'NOT EXISTS'], ['key' => 'ma_gesponsert', 'value' => '1', 'compare' => '!=']];
    $mq = $q->get('meta_query');
    $q->set('meta_query', $mq ? ['relation' => 'AND', $mq, $ohne] : [$ohne]);
}
// Nach den Abfragen des Themes (Priorität 10), damit post__in von /unternehmen/ schon gesetzt ist.
add_action('pre_get_posts', 'ma_firmenbeitraege_ausblenden', 20);
