<?php
/**
 * Firmenbeiträge nur unter „Unternehmen“ (includes/firmenbeitraege.php, 28.10.2026)
 * ohne WordPress: Welche Abfragen schließen Firmenbeiträge (ma_gesponsert = 1) aus?
 * Aufruf: php qa/wordpress/firmenbeitraege-test.php
 */
define('ABSPATH', __DIR__ . '/');
$GLOBALS['admin'] = false; $GLOBALS['cron'] = false; $GLOBALS['ajax'] = false; $GLOBALS['qv'] = [];
function add_action(...$a) {}
function is_admin() { return $GLOBALS['admin']; }
function wp_doing_cron() { return $GLOBALS['cron']; }
function wp_doing_ajax() { return $GLOBALS['ajax']; }
function get_query_var($k, $d = '') { return $GLOBALS['qv'][$k] ?? $d; }
class WP_Query {
    public array $v;
    public function __construct(array $v = []) { $this->v = $v; }
    public function get($k, $d = '') { return $this->v[$k] ?? $d; }
    public function set($k, $w) { $this->v[$k] = $w; }
}
require dirname(__DIR__, 2) . '/wordpress/plugin/merzenich-aktuell-core/includes/firmenbeitraege.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-74s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
/** Schließt die Abfrage Firmenbeiträge aus? */
function ohne(array $v): bool {
    $q = new WP_Query($v); ma_firmenbeitraege_ausblenden($q);
    return str_contains(json_encode($q->get('meta_query')), '"key":"ma_gesponsert","compare":"NOT EXISTS"');
}

echo "Ausgeblendet beim Seitenaufruf\n";
pruefe('Startseite, Rubrik, Nachrichten, Ortsteil (Hauptabfrage ohne Typ)', ohne([]), true);
pruefe('Liste der Beiträge (get_posts post)', ohne(['post_type' => 'post', 'posts_per_page' => 120]), true);
pruefe('Rubrik Wirtschaft (category_name)', ohne(['category_name' => 'wirtschaft']), true);
pruefe('Suche', ohne(['s' => 'reinigung']), true);
pruefe('Seitenspalte und „Das passt zum Thema“ (post__not_in)', ohne(['post_type' => 'post', 'post__not_in' => [5], 'category_name' => 'wirtschaft']), true);
pruefe('mehrere Inhaltsarten mit Beiträgen', ohne(['post_type' => ['post', 'ma_event']]), true);
$GLOBALS['qv']['feed'] = 'rss2';
pruefe('RSS-Feed', ohne([]), true);
unset($GLOBALS['qv']['feed']);

echo "\nVorhandene Bedingung bleibt erhalten\n";
$q = new WP_Query(['meta_query' => ['relation' => 'OR', ['key' => 'a', 'value' => 1], ['key' => 'b', 'value' => 2]]]);
ma_firmenbeitraege_ausblenden($q);
$mq = $q->get('meta_query');
pruefe('ODER-Bedingung des Themes wird eingeklammert, nicht aufgeweicht', [$mq['relation'] ?? '', $mq[0]['relation'] ?? '', $mq[1]['relation'] ?? ''], ['AND', 'OR', 'OR']);

echo "\nNicht ausgeblendet\n";
pruefe('/unternehmen/ und Kanäle (feste Auswahl post__in)', ohne(['post__in' => [3, 4]]), false);
pruefe('Abfragen nach Firmenbeiträgen (meta_query ma_gesponsert)', ohne(['post_type' => 'post', 'meta_query' => [['key' => 'ma_gesponsert', 'value' => '1']]]), false);
pruefe('Abfragen nach Firmenbeiträgen (meta_key ma_gesponsert)', ohne(['post_type' => 'post', 'meta_key' => 'ma_gesponsert', 'meta_value' => '1']), false);
pruefe('Kanal: verschachtelt nach ma_business_id', ohne(['meta_query' => ['relation' => 'AND', [['key' => 'ma_business_id', 'value' => '9']]]]), false);
pruefe('Merkmal ma_firmen_erlaubt (Beiträge der Unternehmens-Zugänge)', ohne(['post_type' => 'post', 'author__in' => [7], 'ma_firmen_erlaubt' => true]), false);
pruefe('die Beitragsseite selbst (name)', ohne(['name' => 'almera-clean-gebaeudereinigung-merzenich']), false);
pruefe('andere Inhaltsarten (Anzeigen, Termine)', [ohne(['post_type' => 'ma_ad']), ohne(['post_type' => 'ma_event'])], [false, false]);
$GLOBALS['qv']['sitemap'] = 'posts';
pruefe('Sitemap', ohne([]), false);
unset($GLOBALS['qv']['sitemap']);
$GLOBALS['admin'] = true;
pruefe('Admin', ohne([]), false);
$GLOBALS['admin'] = false; $GLOBALS['cron'] = true;
pruefe('Cron (Abgleich, Testeintrag, Bildverteilung)', ohne([]), false);
$GLOBALS['cron'] = false;
define('REST_REQUEST', true);
pruefe('REST (Layout-Board der Redaktion)', ohne([]), false);

echo "\nFeste Plätze\n";
pruefe('Layout sperrt Firmenbeiträge auf festen Plätzen', str_contains((string) file_get_contents(dirname(__DIR__, 2) . '/wordpress/plugin/merzenich-aktuell-core/includes/layout.php'), "(string) get_post_meta(\$id, 'ma_gesponsert', true) !== '1'"), true);
pruefe('Brotkrumen eines Firmenbeitrags führen zu „Unternehmen“', str_contains((string) file_get_contents(dirname(__DIR__, 2) . '/wordpress/theme/merzenich-aktuell/single.php'), "home_url('/unternehmen/')); ?>\">Unternehmen</a>"), true);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
