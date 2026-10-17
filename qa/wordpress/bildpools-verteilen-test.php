<?php
/**
 * Symbolbilder aus den Bildpools gleichmäßig verteilen (includes/bildpools.php,
 * 17.10.2026): seltenstes geprüftes Foto, höchstens zweimal unter den sichtbaren
 * Inhalten, übergeordneter Pool, nur automatisch vergebene Bilder.
 * Aufruf: php qa/wordpress/bildpools-verteilen-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('MA_CORE_VERSION', 'test');
class WP_Post { public $ID = 0; public $post_type = 'post'; public $post_title = ''; public $post_excerpt = ''; public $post_content = ''; public $post_date = ''; public function __construct(array $a = []) { foreach ($a as $k => $v) $this->$k = $v; } }

$GLOBALS['pools'] = []; $GLOBALS['inhalte'] = []; $GLOBALS['meta'] = []; $GLOBALS['verlauf'] = []; $GLOBALS['aktionen'] = []; $GLOBALS['optionen'] = [];
function add_action($h, $f = null, ...$r) { $GLOBALS['aktionen'][$h][] = $f; } function add_filter(...$a) {} function register_taxonomy(...$a) {}
function get_option($k, $d = false) { return $GLOBALS['optionen'][$k] ?? $d; }
function has_term($t, $tax, $id) { foreach ($GLOBALS['pools'] as $ids) if (in_array((int) $id, $ids, true)) return true; return false; }
function current_time($f) { return $f === 'Y-m-d' ? '2026-10-17' : '2026-10-17 12:00:00'; }
function get_post_meta($id, $k, $s = false) { return $GLOBALS['meta'][$id][$k] ?? ''; }
function update_post_meta($id, $k, $v) { $GLOBALS['meta'][$id][$k] = $v; return true; }
function delete_post_meta($id, $k) { unset($GLOBALS['meta'][$id][$k]); return true; }
function get_post_thumbnail_id($id) { return (int) ($GLOBALS['meta'][$id]['_thumbnail_id'] ?? 0); }
function has_post_thumbnail($id) { return get_post_thumbnail_id($id) > 0; }
// Wie WordPress: Das Setzen des Beitragsbilds löst added_/updated_post_meta aus.
function set_post_thumbnail($id, $b) { $neu = !isset($GLOBALS['meta'][$id]['_thumbnail_id']); $GLOBALS['meta'][$id]['_thumbnail_id'] = (int) $b; ma_bildpool_merkmal_pruefen(1, $id, '_thumbnail_id', (int) $b); return true; }
function get_the_category($id) { return array_map(fn($s) => (object) ['slug' => $s], $GLOBALS['inhalte'][$id]['kats'] ?? []); }
function wp_strip_all_tags($s) { return strip_tags((string) $s); }
function ma_verlauf_eintragen($id, $a) { $GLOBALS['verlauf'][] = [$id, $a]; }
function get_post($id) { return isset($GLOBALS['inhalte'][$id]) ? new WP_Post(['ID' => $id] + $GLOBALS['inhalte'][$id]['post']) : null; }
function get_posts($q) {
    if (($q['post_type'] ?? '') === 'attachment') {
        $pool = $q['tax_query'][0]['terms'] ?? '';
        return $GLOBALS['pools'][$pool] ?? [];
    }
    $typen = (array) $q['post_type'];
    $ids = [];
    foreach ($GLOBALS['inhalte'] as $id => $x) {
        if (!in_array($x['post']['post_type'], $typen, true)) continue;
        if (isset($q['date_query']) && $x['post']['post_date'] < '2026-09-02') continue;
        // Kommende und laufende Termine (Ende >= jetzt oder Beginn >= heute), wie in ma_bildpool_sichtbar().
        if (($q['meta_query']['relation'] ?? '') === 'OR' && !((string) ($GLOBALS['meta'][$id]['ma_event_end'] ?? '') >= '2026-10-17 12:00:00' || (string) ($GLOBALS['meta'][$id]['ma_event_start'] ?? '') >= '2026-10-17')) continue;
        $passt = true;
        foreach ((array) ($q['meta_query'] ?? []) as $m) if (is_array($m) && in_array($m['compare'] ?? '', ['EXISTS', 'NOT EXISTS'], true) && isset($GLOBALS['meta'][$id][$m['key']]) !== ($m['compare'] === 'EXISTS')) $passt = false;
        if (!$passt) continue;
        if (isset($q['post__in']) && !in_array($id, $q['post__in'], true)) continue;
        $ids[] = $id;
    }
    if (($q['orderby'] ?? '') === 'date') usort($ids, fn($a, $b) => strcmp($GLOBALS['inhalte'][$a]['post']['post_date'], $GLOBALS['inhalte'][$b]['post']['post_date']) * (($q['order'] ?? 'DESC') === 'ASC' ? 1 : -1));
    return ($q['fields'] ?? '') === 'ids' ? $ids : array_map('get_post', $ids);
}
require dirname(__DIR__, 2) . '/wordpress/plugin/merzenich-aktuell-core/includes/fotoschluessel.php';
require dirname(__DIR__, 2) . '/wordpress/plugin/merzenich-aktuell-core/includes/bildpools.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-74s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
function termin(int $id, string $titel, string $start, int $bild = 0, string $auto = 'kirchedetail', string $datum = '2026-10-05 10:00:00') {
    $GLOBALS['inhalte'][$id] = ['post' => ['post_type' => 'ma_event', 'post_title' => $titel, 'post_date' => $datum]];
    $GLOBALS['meta'][$id] = ['ma_event_start' => $start] + ($bild ? ['_thumbnail_id' => $bild] : []) + ($auto !== '' ? ['_ma_bildpool_auto' => $auto] : []);
}
$zaehle = function (array $ids): array { $z = []; foreach ($ids as $id) { $b = get_post_thumbnail_id($id); if ($b) $z[$b] = ($z[$b] ?? 0) + 1; } arsort($z); return $z; };

echo "Pools wie live: Kirche 4 geprüfte Fotos, Termine 20\n";
$GLOBALS['pools'] = ['kirchedetail' => [101, 102, 103, 104], 'termine' => range(201, 220), 'aktuell' => range(301, 322), 'tanzdetail' => [401]];
// Sieben Kirchentermine mit demselben Foto (Stand 05.10.), einer davon schon vorbei.
for ($i = 1; $i <= 7; $i++) termin($i, "Messe $i", '2026-10-' . (17 + $i) . 'T10:00', 101);
termin(10, 'Messe 10', '2026-10-28T10:00', 101);
termin(8, 'Messe vorbei', '2026-10-10T10:00', 101);
// Redaktionsfoto ohne _ma_bildpool_auto bleibt unberührt.
termin(9, 'Gottesdienst mit eigenem Foto', '2026-10-20T10:00', 101, '');
$sicht = ma_bildpool_sichtbar();
pruefe('sichtbar: kommende Termine, nicht der vergangene', in_array(8, $sicht, true) || !in_array(1, $sicht, true), false);
pruefe('vorher: Foto 101 neunmal sichtbar', $zaehle($sicht)[101] ?? 0, 9);
$n = ma_bildpool_verteilen();
$nachher = $zaehle($sicht);
pruefe('nachher: kein Foto öfter als 2× (das Redaktionsfoto zählt mit)', max($nachher), 2);
pruefe('alle vier Kirchenfotos sind im Einsatz', count(array_intersect([101, 102, 103, 104], array_keys($nachher))), 4);
pruefe('Redaktionsfoto (ohne _ma_bildpool_auto) bleibt', get_post_thumbnail_id(9), 101);
pruefe('vergangener Termin bleibt unverändert', get_post_thumbnail_id(8), 101);
pruefe('reicht der Kirchenpool nicht, kommt der Terminpool dazu', count(array_filter(array_keys($nachher), fn($b) => $b >= 201 && $b <= 220)) > 0, true);
pruefe('vorgesehener Pool bleibt vermerkt (kein Hin und Her mit dem Nachlauf)', $GLOBALS['meta'][3]['_ma_bildpool_auto'], 'kirchedetail');
pruefe('Typ Symbolbild, Rechte geprüft', [$GLOBALS['meta'][7]['ma_image_type'] ?? '', $GLOBALS['meta'][7]['ma_image_rights_verified'] ?? ''], ['symbol', '1']);
pruefe('jeder Wechsel steht im Verlauf', count($GLOBALS['verlauf']), $n);
pruefe('zweiter Lauf: nichts mehr zu tun', ma_bildpool_verteilen(), 0);

echo "Neues Symbolbild: seltenstes Foto\n";
$GLOBALS['inhalte'][50] = ['post' => ['post_type' => 'ma_event', 'post_title' => 'Andacht in der Kapelle', 'post_date' => '2026-10-17 09:00:00']];
$GLOBALS['meta'][50] = ['ma_event_start' => '2026-10-30T18:00'];
ma_bildpool_nutzung(true);
$b = ma_bildpool_symbolbild_setzen(get_post(50));
pruefe('Kirchentermin bekommt kein Foto, das schon zweimal steht', ($zaehle(ma_bildpool_sichtbar())[$b] ?? 0) <= 2, true);
$GLOBALS['inhalte'][51] = ['post' => ['post_type' => 'ma_event', 'post_title' => 'Tanz in den Mai', 'post_date' => '2026-10-17 09:00:00']];
$GLOBALS['meta'][51] = ['ma_event_start' => '2026-10-31T20:00'];
for ($i = 60; $i < 62; $i++) termin($i, "Tanzabend $i", '2026-11-0' . ($i - 58) . 'T20:00', 401, 'tanzdetail');
ma_bildpool_nutzung(true);
$b = ma_bildpool_symbolbild_setzen(get_post(51));
pruefe('Tanzpool hat nur ein Foto, schon zweimal: Terminpool', $b >= 201 && $b <= 220, true);
pruefe('vermerkt bleibt der Tanzpool', $GLOBALS['meta'][51]['_ma_bildpool_auto'], 'tanzdetail');

echo "Redaktionsfoto mit altem Merkmal (Review 17.10.)\n";
$GLOBALS['pools'] = ['kirchedetail' => [101, 102, 103, 104], 'termine' => range(201, 220), 'aktuell' => range(301, 322), 'tanzdetail' => [401]];
$GLOBALS['inhalte'] = []; $GLOBALS['meta'] = []; $GLOBALS['verlauf'] = [];
// Vier Termine einer Reihe mit demselben Veranstalterfoto 900; das Merkmal stammt vom Poolfoto, das beim Veröffentlichen kurz gesetzt war.
for ($i = 70; $i < 74; $i++) { termin($i, "Seniorentreff $i", '2026-10-2' . ($i - 68) . 'T15:00', 900); $GLOBALS['meta'][$i]['ma_image_credit'] = 'Veranstalter'; $GLOBALS['meta'][$i]['ma_image_type'] = 'original'; }
ma_bildpool_verteilen();
pruefe('Redaktionsfoto bleibt an allen vier Terminen', array_map('get_post_thumbnail_id', range(70, 73)), [900, 900, 900, 900]);
pruefe('Bildnachweis und Typ bleiben', [$GLOBALS['meta'][73]['ma_image_credit'] ?? '', $GLOBALS['meta'][73]['ma_image_type'] ?? ''], ['Veranstalter', 'original']);
pruefe('Veraltetes Merkmal ist entfernt', isset($GLOBALS['meta'][70]['_ma_bildpool_auto']), false);
termin(75, 'Messe 75', '2026-10-25T10:00');
ma_bildpool_nutzung(true);
$b = ma_bildpool_symbolbild_setzen(get_post(75));
pruefe('Automatisch gesetzt: Merkmal und gemerktes Bild', [$GLOBALS['meta'][75]['_ma_bildpool_auto'] ?? '', (int) ($GLOBALS['meta'][75]['_ma_bildpool_auto_bild'] ?? 0)], ['kirchedetail', $b]);
set_post_thumbnail(75, 900);
pruefe('Bild von Hand ersetzt: Merkmal fällt weg (Hook auf _thumbnail_id)', [isset($GLOBALS['meta'][75]['_ma_bildpool_auto']), isset($GLOBALS['meta'][75]['_ma_bildpool_auto_bild'])], [false, false]);
termin(76, 'Messe 76', '2026-10-26T10:00', 103);
$GLOBALS['meta'][76]['_ma_bildpool_auto_bild'] = '103';
ma_bildpool_bild_setzen(get_post(76), 104, 'kirchedetail');
pruefe('Neu verteilt: Merkmal bleibt, gemerkt ist das neue Bild', [$GLOBALS['meta'][76]['_ma_bildpool_auto'] ?? '', $GLOBALS['meta'][76]['_ma_bildpool_auto_bild'] ?? ''], ['kirchedetail', '104']);

echo "Merkmale von vor 1.29.0\n";
$GLOBALS['inhalte'] = []; $GLOBALS['meta'] = [];
termin(85, 'Messe 85', '2026-10-25T10:00', 101); termin(86, 'Messe 86', '2026-10-26T10:00', 900);
pruefe('Nachtragen: Poolfoto wird gemerkt, Redaktionsfoto verliert das Merkmal', [ma_bildpool_merkmale_nachtragen(), $GLOBALS['meta'][85]['_ma_bildpool_auto_bild'] ?? '', isset($GLOBALS['meta'][86]['_ma_bildpool_auto'])], [1, '101', false]);
termin(87, 'Messe 87', '2026-10-27T10:00', 101);
set_post_thumbnail(87, 102);
pruefe('Redaktion wählt selbst ein anderes Poolfoto: Merkmal fällt weg', isset($GLOBALS['meta'][87]['_ma_bildpool_auto']), false);

echo "Symbolbild vor dem eigentlichen Bild (Abgleich, Block-Editor)\n";
$GLOBALS['inhalte'][88] = ['post' => ['post_type' => 'post', 'post_title' => 'Meldung mit Foto', 'post_date' => '2026-10-17 09:00:00']];
$GLOBALS['meta'][88] = ['ma_image_credit' => 'Gemeinde Merzenich', 'ma_image_type' => 'original', 'ma_image_rights_verified' => '0'];
ma_bildpool_nutzung(true);
ma_bildpool_symbolbild_setzen(get_post(88));
pruefe('Erst Poolfoto: Typ Symbolbild, Rechte geprüft', [$GLOBALS['meta'][88]['ma_image_type'], $GLOBALS['meta'][88]['ma_image_rights_verified']], ['symbol', '1']);
set_post_thumbnail(88, 950);
pruefe('Dann das eigentliche Foto: Nachweis, Typ und Rechtestand wie vorher', [$GLOBALS['meta'][88]['ma_image_credit'] ?? '', $GLOBALS['meta'][88]['ma_image_type'] ?? '', $GLOBALS['meta'][88]['ma_image_rights_verified'] ?? '', isset($GLOBALS['meta'][88]['ma_image_original_url']), isset($GLOBALS['meta'][88]['_ma_bildpool_vorher'])], ['Gemeinde Merzenich', 'original', '0', false, false]);
termin(89, 'Messe 89', '2026-10-28T10:00');
$GLOBALS['meta'][89]['ma_image_credit'] = 'leer vorher';
ma_bildpool_nutzung(true);
ma_bildpool_symbolbild_setzen(get_post(89));
$erst = $GLOBALS['meta'][89]['_ma_bildpool_vorher'] ?? null;
ma_bildpool_bild_setzen(get_post(89), 104, 'kirchedetail');
pruefe('Neu verteilt: die erste Sicherung bleibt (nicht die Angaben des ersten Poolfotos)', $GLOBALS['meta'][89]['_ma_bildpool_vorher'] ?? null, $erst);

echo "Dasselbe Foto als Abgleich- und Pool-Anhang\n";
$GLOBALS['inhalte'] = []; $GLOBALS['meta'] = [];
$GLOBALS['meta'][555] = ['ma_image_static_src' => '/assets/editorial-pools/kirchedetail/kirchedetail-01-995c6c09d8-1200.webp'];
$GLOBALS['meta'][101] = ['_wp_attached_file' => '2026/10/kirchedetail-01-995c6c09d8.jpg'];
termin(80, 'Patrozinium', '2026-10-20T10:00', 555, ''); termin(81, 'Orte von Kirche', '2026-10-21T10:00', 555, '');
termin(82, 'Abendmesse', '2026-10-22T10:00');
ma_bildpool_nutzung(true);
$b = ma_bildpool_symbolbild_setzen(get_post(82));
pruefe('Pool-Anhang desselben Fotos gilt als schon zweimal genutzt', $b !== 101 && in_array($b, [102, 103, 104], true), true);
termin(83, 'Rosenkranz', '2026-10-23T10:00', 101);
ma_bildpool_verteilen();
pruefe('Verteilen zählt Abgleich- und Pool-Anhang zusammen: Termin 83 wechselt', get_post_thumbnail_id(83) !== 101, true);

echo "Laufender Termin\n";
$GLOBALS['inhalte'] = []; $GLOBALS['meta'] = [];
termin(90, 'Ausstellung (seit gestern, bis Sonntag)', '2026-10-16T10:00', 102); $GLOBALS['meta'][90]['ma_event_end'] = '2026-10-18 18:00:00';
termin(91, 'Konzert', '2026-10-24T19:00', 102);
pruefe('Laufender mehrtägiger Termin zählt als sichtbar', in_array(90, ma_bildpool_sichtbar(), true), true);
ma_bildpool_nutzung(true);
termin(92, 'Andacht', '2026-10-27T18:00');
$b = ma_bildpool_symbolbild_setzen(get_post(92));
pruefe('Foto des laufenden Termins zählt mit: kein drittes Mal', $b !== 102, true);

echo "Täglicher Lauf und Schalter\n";
$cron = $GLOBALS['aktionen']['ma_bildpool_verteilen_taeglich'][0] ?? null;
$GLOBALS['inhalte'] = []; $GLOBALS['meta'] = [];
for ($i = 1; $i <= 4; $i++) termin($i, "Messe $i", '2026-10-2' . $i . 'T10:00', 101);
$GLOBALS['optionen']['ma_bildpool_auto'] = '0';
if (is_callable($cron)) $cron();
pruefe('Automatik aus: der tägliche Lauf tauscht nichts', array_map('get_post_thumbnail_id', range(1, 4)), [101, 101, 101, 101]);
$GLOBALS['optionen']['ma_bildpool_auto'] = '1';
if (is_callable($cron)) $cron();
pruefe('Automatik an: der tägliche Lauf verteilt', count(array_unique(array_map('get_post_thumbnail_id', range(1, 4)))) > 1, true);

echo "Leerer Pool\n";
// Eigener Name: Die Kandidaten je Pool merkt sich ma_bildpool_kandidaten() für den Seitenaufruf.
$GLOBALS['pools'] = ['leer' => []];
pruefe('ohne geprüfte Fotos: 0', ma_bildpool_waehlen('leer', 5), 0);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
