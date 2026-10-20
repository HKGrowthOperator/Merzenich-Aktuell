<?php
/**
 * Drei echte Betriebe als Entwurf (includes/firmen-entwuerfe.php und
 * data/firmen-entwuerfe.json, 16.10.2026): nur Entwürfe, keine Einwilligung,
 * keine Logos, Werbebeitrag als kostenfreier Testeintrag, kein Platz auf der
 * Startseite, kein zweites Anlegen. Zusage eintragen (20.10.2026): Einwilligung
 * und Logo getrennt mit Nachweis, Logo einmal in die Mediathek, Profil erst mit
 * Einwilligung veröffentlicht, Werbebeitrag bleibt für die Freigaben.
 * Aufruf: php qa/wordpress/firmen-entwuerfe-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('MA_CORE_PATH', dirname(__DIR__, 2) . '/wordpress/plugin/merzenich-aktuell-core/');
define('MA_STATUS_ARCHIV', 'ma_archiv');

$GLOBALS['posts'] = []; $GLOBALS['terms'] = []; $GLOBALS['naechste'] = 100;
$GLOBALS['aktionen'] = [];
function add_action($h, $f, $prio = 10, $n = 1) { $GLOBALS['aktionen'][$h] = $prio; }
function wp_slash($v) { return $v; }
function wp_kses($s, $erlaubt) { return strip_tags((string) $s, '<' . implode('><', array_keys($erlaubt)) . '>'); }
function esc_url($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
class WP_Error { public function get_error_message() { return 'Datenbankfehler'; } }
function is_wp_error($x) { return $x instanceof WP_Error; }
function get_post_stati() { return ['publish', 'draft', 'pending', 'future', 'private', 'trash', 'auto-draft', 'inherit', 'ma_archiv', 'ma_in_pruefung', 'ma_aenderung', 'ma_abgelehnt']; }
function get_category_by_slug($s) { return $s === 'wirtschaft' ? (object) ['term_id' => 7] : false; }
function taxonomy_exists($t) { return true; }
function wp_set_object_terms($id, $t, $tax, $append) { $GLOBALS['terms'][$id][$tax] = $t; }
function get_post_status($id) { return $GLOBALS['posts'][$id]['post_status'] ?? false; }
function wp_insert_post($a, $err = false) { if (!empty($GLOBALS['fehler_beim_anlegen'])) return new WP_Error(); $id = $GLOBALS['naechste']++; $GLOBALS['posts'][$id] = $a; return $id; }
function get_posts($q) {
    if (($q['post_type'] ?? '') === 'attachment') return array_keys(array_filter($GLOBALS['meta'] ?? [], fn($m) => ($m[$q['meta_key']] ?? null) === $q['meta_value']));
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

echo "Menü\n";
pruefe('Untermenü nach dem Hauptmenü angemeldet (sonst 403)', ($GLOBALS['aktionen']['admin_menu'] ?? 0) > 10, true);

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
foreach (['ma_in_pruefung', 'ma_aenderung', 'ma_abgelehnt', 'ma_archiv'] as $st) {
    $GLOBALS['posts'][103]['post_status'] = $st; $GLOBALS['posts'][100]['post_status'] = $st;
    ma_firmen_entwuerfe_anlegen();
    pruefe("Status $st: nichts doppelt", count($GLOBALS['posts']), $vorher);
}

echo "Fehler beim Anlegen\n";
$GLOBALS['posts'] = []; $GLOBALS['terms'] = []; $GLOBALS['fehler_beim_anlegen'] = true;
$log3 = ma_firmen_entwuerfe_anlegen();
pruefe('nichts angelegt, nichts an fremde Inhalte gehängt', [count($GLOBALS['posts']), count($GLOBALS['terms'])], [0, 0]);
pruefe('Rückmeldung nennt den Fehler', str_contains($log3[0] ?? '', 'nicht angelegt (Datenbankfehler)'), true);
$GLOBALS['fehler_beim_anlegen'] = false;


echo "Zusage eintragen\n";
$GLOBALS['posts'] = []; $GLOBALS['terms'] = []; $GLOBALS['naechste'] = 100; $GLOBALS['meta'] = []; $GLOBALS['medien'] = 0; $GLOBALS['verlauf'] = [];
function get_post_meta($id, $k, $s = false) { return $GLOBALS['meta'][$id][$k] ?? ($GLOBALS['posts'][$id]['meta_input'][$k] ?? ''); }
function update_post_meta($id, $k, $v) { $GLOBALS['meta'][$id][$k] = $v; return true; }
function set_post_thumbnail($id, $m) { $GLOBALS['meta'][$id]['_thumbnail_id'] = $m; return true; }
function wp_get_current_user() { return (object) ['display_name' => 'Redaktion', 'user_login' => 'admin']; }
function current_time($f) { return '20.10.2026 14:00'; }
function wp_tempnam($n) { return tempnam(sys_get_temp_dir(), 'logo'); }
function media_handle_sideload($datei, $pid, $alt) { $GLOBALS['medien']++; @unlink($datei['tmp_name']); $id = 900 + $GLOBALS['medien']; $GLOBALS['posts'][$id] = ['post_type' => 'attachment', 'post_status' => 'inherit', 'post_name' => $datei['name'], 'datei' => $datei['name']]; return $id; }
function ma_verlauf_eintragen($id, $a, $t = '') { $GLOBALS['verlauf'][] = [$id, $a, $t]; }
// Veröffentlichen wie WordPress mit der Sperre aus business.php: ohne Einwilligung bleibt es Entwurf.
function wp_update_post($a, $err = false) { $id = $a['ID']; if (($a['post_status'] ?? '') === 'publish' && ($GLOBALS['posts'][$id]['post_type'] ?? '') === 'ma_business' && get_post_meta($id, 'ma_business_consent') !== '1') $a['post_status'] = 'draft'; $GLOBALS['posts'][$id] = array_merge($GLOBALS['posts'][$id], $a); return $id; }
$alteSuche = 'get_posts';
ma_firmen_entwuerfe_anlegen();
foreach ($firmen as $f) pruefe($f['profil']['titel'] . ': Logo-Datei im Plugin', is_file(MA_CORE_PATH . 'data/logos/' . ($f['profil']['logo']['datei'] ?? '-')), true);
pruefe('ohne Nachweis: nichts', [ma_firmen_zusage_eintragen('baeckerei-wirtz-merzenich', ' ', true, true)[0] ?? '', $GLOBALS['posts'][100]['post_status'], $GLOBALS['medien']], ['Bäckerei Wirtz: Ohne Nachweis der Zusage wird nichts eingetragen.', 'draft', 0]);
pruefe('ohne Haken: nichts', str_contains(ma_firmen_zusage_eintragen('baeckerei-wirtz-merzenich', 'Zusage laut Betreiber, 20.10.2026', false, false)[0] ?? '', 'Kein Haken'), true);
// Nur Logo: Profil bleibt Entwurf, Logo ist Beitragsbild und freigegeben.
ma_firmen_zusage_eintragen('baeckerei-wirtz-merzenich', 'Zusage laut Betreiber, 20.10.2026', false, true);
pruefe('nur Logo: Beitragsbild, Rechte geklärt, Profil weiter Entwurf', [$GLOBALS['meta'][100]['_thumbnail_id'] ?? 0, get_post_meta(100, 'ma_business_logo_rechte'), $GLOBALS['posts'][100]['post_status']], [901, 'geklaert', 'draft']);
pruefe('Logo-Medium: Nachweis, Alt-Text, Rechte geprüft, Herkunft', [get_post_meta(901, 'ma_image_credit'), get_post_meta(901, '_wp_attachment_image_alt'), get_post_meta(901, 'ma_image_rights_verified'), get_post_meta(901, 'ma_image_original_url')],
    ['Logo: Bäckerei Wirtz, Nutzung mit Freigabe des Unternehmens', 'Logo Bäckerei Wirtz', '1', 'https://www.baeckerei-wirtz.de/bilder/logo-frei0_500x500.png']);
pruefe('Logo-Freigabe mit Nachweis, Person und Zeit', get_post_meta(100, 'ma_business_logo_freigabe'), 'Zusage laut Betreiber, 20.10.2026 · eingetragen von Redaktion am 20.10.2026 14:00');
// Einwilligung: Profil veröffentlicht, Werbebeitrag bleibt Entwurf (Freigaben).
$log = ma_firmen_zusage_eintragen('baeckerei-wirtz-merzenich', 'Zusage laut Betreiber, 20.10.2026', true, false);
pruefe('Einwilligung: mit Nachweis, wer, wann', [get_post_meta(100, 'ma_business_consent'), get_post_meta(100, 'ma_business_consent_quelle'), get_post_meta(100, 'ma_business_consent_by'), get_post_meta(100, 'ma_business_consent_at')], ['1', 'Zusage laut Betreiber, 20.10.2026', 'Redaktion', '20.10.2026 14:00']);
pruefe('Profil veröffentlicht, Werbebeitrag bleibt Entwurf für die Freigaben', [$GLOBALS['posts'][100]['post_status'], $GLOBALS['posts'][101]['post_status'], str_contains($log[0], 'in den Freigaben')], ['publish', 'draft', true]);
// Zweites Mal (beide Haken): kein zweites Medium, Einwilligung unverändert.
ma_firmen_zusage_eintragen('baeckerei-wirtz-merzenich', 'anderer Nachweis', true, true);
pruefe('erneut: kein zweites Logo-Medium, erste Einwilligung bleibt', [$GLOBALS['medien'], get_post_meta(100, 'ma_business_consent_quelle')], [1, 'Zusage laut Betreiber, 20.10.2026']);
pruefe('Verlauf: Logo und Einwilligung je einmal (plus erneute Logo-Zeile)', count(array_filter($GLOBALS['verlauf'], fn($v) => $v[0] === 100 && $v[1] === 'Einwilligung eingetragen')), 1);
pruefe('unbekannter Betrieb', ma_firmen_zusage_eintragen('gibt-es-nicht', 'x', true, true), ['Unbekannter Betrieb.']);
$GLOBALS['posts'][102]['post_status'] = 'trash';
pruefe('Profil im Papierkorb: nichts', str_contains(ma_firmen_zusage_eintragen('hasberg-fenster-rollladentechnik-merzenich', 'x', true, true)[0] ?? '', 'Profil fehlt'), true);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
