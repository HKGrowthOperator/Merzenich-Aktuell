<?php
/**
 * Drei echte Betriebe als Entwurf (includes/firmen-entwuerfe.php und
 * data/firmen-entwuerfe.json, 16.10.2026): nur Entwürfe, keine Einwilligung,
 * keine Logos, Werbebeitrag als kostenfreier Testeintrag, kein Platz auf der
 * Startseite, kein zweites Anlegen.
 * Aufruf: php qa/wordpress/firmen-entwuerfe-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('MA_CORE_PATH', dirname(__DIR__, 2) . '/wordpress/plugin/merzenich-aktuell-core/');
define('MA_STATUS_ARCHIV', 'ma_archiv');

$GLOBALS['posts'] = []; $GLOBALS['terms'] = []; $GLOBALS['naechste'] = 100;
function add_action(...$a) {}
function wp_slash($v) { return $v; }
function wp_kses($s, $erlaubt) { return strip_tags((string) $s, '<' . implode('><', array_keys($erlaubt)) . '>'); }
function esc_url($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function is_wp_error($x) { return false; }
function get_category_by_slug($s) { return $s === 'wirtschaft' ? (object) ['term_id' => 7] : false; }
function taxonomy_exists($t) { return true; }
function wp_set_object_terms($id, $t, $tax, $append) { $GLOBALS['terms'][$id][$tax] = $t; }
function get_post_status($id) { return $GLOBALS['posts'][$id]['post_status'] ?? false; }
function wp_insert_post($a, $err = false) { $id = $GLOBALS['naechste']++; $GLOBALS['posts'][$id] = $a; return $id; }
function get_posts($q) {
    $treffer = array_keys(array_filter($GLOBALS['posts'], fn($p) => $p['post_type'] === $q['post_type'] && $p['post_name'] === $q['name'] && in_array($p['post_status'], (array) $q['post_status'], true)));
    return array_slice($treffer, 0, 1);
}

require MA_CORE_PATH . 'includes/firmen-entwuerfe.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-74s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Daten (data/firmen-entwuerfe.json)\n";
$d = ma_firmen_entwuerfe_daten();
$firmen = (array) ($d['firmen'] ?? []);
pruefe('drei Betriebe', array_map(fn($f) => $f['profil']['titel'], $firmen), ['Bäckerei Wirtz', 'Hasberg Fenster & Rollladentechnik', 'Porschen Media']);
pruefe('Testeintrag 0 Euro, 30 Tage, keine Verlängerung', $d['test'] ?? null, ['preis_cent' => 0, 'tage' => 30, 'verlaengerung' => false]);
$text = json_encode($d, JSON_UNESCAPED_UNICODE);
pruefe('keine Wörter „Partner“, „Kunde“, „Sponsor“ über die Betriebe', preg_match('/partner|kunde|sponsor/i', $text), 0);
pruefe('kein Gedankenstrich „—“', strpos($text, '—'), false);
foreach ($firmen as $f) {
    $p = $f['profil']; $b = $f['beitrag'];
    $n = $p['titel'];
    pruefe("$n: Pflichtangaben vollständig", count(array_filter([$p['slug'], $p['branche'], $p['adresse'], $p['telefon'], $p['website'], $p['quelle'], $p['kurz'], $b['slug'], $b['titel'], $b['anriss']], fn($v) => trim((string) $v) !== '')), 10);
    pruefe("$n: Website, Logo-Quelle und Knopf mit https", (bool) preg_match('#^https://#', $p['website']) && (bool) preg_match('#^https://#', $p['logo']['quelle']) && (bool) preg_match('#^https://#', $b['knopf']['ziel']), true);
    pruefe("$n: Logo-Rechte offen", $p['logo']['rechte'], 'offen');
    pruefe("$n: Ortsteil bekannt", in_array($p['ortsteil'], ['merzenich', 'golzheim', 'girbelsrath', 'morschenich', 'buergewald'], true), true);
    pruefe("$n: SEO-Beschreibung höchstens 160 Zeichen", mb_strlen($b['seo_beschreibung']) <= 160, true);
}

echo "Beitragstext\n";
$h = ma_firmen_beitrag_html(['absaetze' => ['Erster <strong>Teil</strong><script>x()</script>'], 'knopf' => ['text' => 'Besuchen', 'ziel' => 'https://example.org/']]);
pruefe('erlaubtes HTML bleibt, Skript fliegt raus', strpos($h, '<strong>Teil</strong>') !== false && strpos($h, '<script') === false, true);
pruefe('Knopf ist als bezahlter Link gekennzeichnet', strpos($h, 'rel="noopener sponsored"') !== false, true);

echo "Anlegen\n";
$log = ma_firmen_entwuerfe_anlegen();
$profile = array_filter($GLOBALS['posts'], fn($p) => $p['post_type'] === 'ma_business');
$beitraege = array_filter($GLOBALS['posts'], fn($p) => $p['post_type'] === 'post');
pruefe('drei Profile und drei Beiträge', [count($profile), count($beitraege)], [3, 3]);
pruefe('alles ist Entwurf', array_values(array_unique(array_column($GLOBALS['posts'], 'post_status'))), ['draft']);
pruefe('keine Einwilligung gesetzt', array_filter(array_map(fn($p) => $p['meta_input']['ma_business_consent'] ?? null, $GLOBALS['posts'])), []);
pruefe('kein Logo (Beitragsbild) gesetzt', array_filter(array_map(fn($p) => $p['_thumbnail_id'] ?? $p['meta_input']['_thumbnail_id'] ?? null, $GLOBALS['posts'])), []);
$wirtz = $GLOBALS['posts'][100]; $wb = $GLOBALS['posts'][101];
pruefe('Profil: E-Mail, Quelle und Stand', [$GLOBALS['posts'][102]['meta_input']['ma_business_email'] ?? '', $wirtz['meta_input']['ma_business_stand']], ['info@hasberg.de', '2026-10-16']);
pruefe('Profil: Logo-Rechte offen', $wirtz['meta_input']['ma_business_logo_rechte'], 'offen');
pruefe('Beitrag: gesponsert und Testeintrag', [$wb['meta_input']['ma_gesponsert'], $wb['meta_input']['ma_gesponsert_art']], ['1', 'test']);
pruefe('Beitrag: mit dem Profil verknüpft', $wb['meta_input']['ma_business_id'], '100');
pruefe('Beitrag: nicht auf der Startseite', $wb['meta_input']['ma_startseite_freigabe'], 'nein');
pruefe('Beitrag: Rubrik Wirtschaft', $wb['post_category'], [7]);
pruefe('Porschen Media im Ortsteil Girbelsrath', $GLOBALS['terms'][105]['ma_location'] ?? '', 'girbelsrath');
pruefe('Rückmeldung je Profil und Beitrag', count($log), 6);
$vorher = count($GLOBALS['posts']);
$log2 = ma_firmen_entwuerfe_anlegen();
pruefe('zweiter Lauf legt nichts doppelt an', count($GLOBALS['posts']), $vorher);
pruefe('zweiter Lauf meldet „schon vorhanden“', count(array_filter($log2, fn($z) => str_contains($z, 'schon vorhanden'))), 6);
$GLOBALS['posts'][101]['post_status'] = 'trash';
$GLOBALS['posts'][101]['post_name'] .= '__trashed';
ma_firmen_entwuerfe_anlegen();
pruefe('Beitrag im Papierkorb wird nicht neu angelegt', count($GLOBALS['posts']), $vorher);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
