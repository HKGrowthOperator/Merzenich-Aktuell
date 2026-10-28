<?php
/**
 * Unternehmen als Entwurf (includes/firmen-entwuerfe.php und
 * data/firmen-entwuerfe.json; seit 24.10.2026 Almera Clean, KBS Management,
 * Merzlicht, AJ Sports): nur Entwürfe, keine Einwilligung, keine Logos, kein
 * Platz auf der Startseite, kein zweites Anlegen; Profile nur mit Namen ohne
 * erfundene Angaben. Zusage eintragen: Einwilligung und Logo getrennt mit
 * Nachweis, Logo einmal in die Mediathek, Profil erst mit Einwilligung
 * veröffentlicht, Werbebeitrag bleibt für die Freigaben, je Firma zwei
 * Werbebanner mit Foto, Video und Knopf (28.10.2026), geänderte Motive werden
 * nachgezogen, im Backend bearbeitete nicht. Firma zurückziehen: alles Entwurf,
 * nichts gelöscht.
 * Aufruf: php qa/wordpress/firmen-entwuerfe-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('MA_CORE_PATH', dirname(__DIR__, 2) . '/wordpress/plugin/merzenich-aktuell-core/');
define('MA_STATUS_ARCHIV', 'ma_archiv');

$GLOBALS['posts'] = []; $GLOBALS['terms'] = []; $GLOBALS['naechste'] = 100;
$GLOBALS['aktionen'] = [];
function add_action($h, $f, $prio = 10, $n = 1) { $GLOBALS['aktionen'][$h] = $prio; }
function wp_slash($v) { return $v; }
function wp_json_encode($v) { return json_encode($v); }
function get_permalink($id) { return 'https://merzenich-aktuell.de/unternehmen/' . ($GLOBALS['posts'][$id]['post_name'] ?? $id) . '/'; }
function get_post_field($f, $id) { return $GLOBALS['posts'][$id][$f] ?? ''; }
function sanitize_title($s) { return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', (string) $s), '-')); }
function get_option($k, $d = false) { return $GLOBALS['optionen'][$k] ?? $d; }
function update_option($k, $v, $a = null) { $GLOBALS['optionen'][$k] = $v; return true; }
function add_option($k, $v, $x = '', $a = null) { if (isset($GLOBALS['optionen'][$k])) return false; $GLOBALS['optionen'][$k] = $v; return true; }
function delete_option($k) { unset($GLOBALS['optionen'][$k]); return true; }
function get_post_type($id) { return $GLOBALS['posts'][$id]['post_type'] ?? false; }
function get_the_title($id) { return $GLOBALS['posts'][$id]['post_title'] ?? ''; }
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
    $typ = (array) ($q['post_type'] ?? 'post'); $st = (array) ($q['post_status'] ?? ['publish']);
    $wert = fn($id, $k) => (string) ($GLOBALS['meta'][$id][$k] ?? ($GLOBALS['posts'][$id]['meta_input'][$k] ?? ''));
    $treffer = array_keys(array_filter($GLOBALS['posts'], function ($p, $id) use ($q, $typ, $st, $wert) {
        if (!in_array($p['post_type'], $typ, true) || !in_array($p['post_status'], $st, true)) return false;
        if (isset($q['name']) && $p['post_name'] !== $q['name']) return false;
        if (isset($q['meta_key']) && $wert($id, $q['meta_key']) !== (string) $q['meta_value']) return false;
        foreach ((array) ($q['meta_query'] ?? []) as $c) if (($c['compare'] ?? '=') === 'IN' ? !in_array($wert($id, $c['key']), (array) $c['value'], true) : $wert($id, $c['key']) !== (string) $c['value']) return false;
        return true;
    }, ARRAY_FILTER_USE_BOTH));
    return isset($q['name']) || isset($q['meta_key']) ? array_slice($treffer, 0, 1) : $treffer;
}

require MA_CORE_PATH . 'includes/firmen-entwuerfe.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-74s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Daten (data/firmen-entwuerfe.json)\n";
$d = ma_firmen_entwuerfe_daten();
$firmen = (array) ($d['firmen'] ?? []);
pruefe('fünf Unternehmen (24.10. und 28.10.2026)', array_map(fn($f) => $f['profil']['titel'], $firmen), ['Almera Clean', 'KBS Management GmbH', 'ASS Elektrotechnik', 'Merzlicht', 'AJ Sports']);
pruefe('frühere Betriebe zum Zurückziehen', array_column($d['frueher'] ?? [], 'slug'), ['baeckerei-wirtz-merzenich', 'hasberg-fenster-rollladentechnik-merzenich', 'porschen-media-girbelsrath']);
// Redaktionelle Angaben (Profil, Beitrag, Hinweis); den Anzeigentext der Banner hat der Betreiber gewählt (28.10.2026).
$text = json_encode(array_map(fn($f) => array_diff_key($f, ['anzeigen' => 1]), $firmen) + ['hinweis' => $d['hinweis'] ?? ''], JSON_UNESCAPED_UNICODE);
pruefe('keine Wörter „Partner“, „Kunde“, „Sponsor“ über die Betriebe', preg_match('/partner|kunde|sponsor/i', $text), 0);
pruefe('kein Gedankenstrich „—“', strpos($text, '—'), false);
pruefe('Art je Firma: Anzeige, eigene Sache (Herausgeberin), Anzeige, Anzeige, Anzeige', array_map('ma_firmen_art', $firmen), ['anzeige', 'eigen', 'anzeige', 'anzeige', 'anzeige']);
pruefe('nur KBS ist als Herausgeberin markiert', array_map(fn($f) => !empty($f['herausgeberin']), $firmen), [false, true, false, false, false]);
pruefe('Werbekontingent: alle fünf 5 Anzeigen', array_map(fn($f) => $f['werbung']['max'] ?? null, $firmen), [5, 5, 5, 5, 5]);
pruefe('je Firma zwei Anzeigen', array_map(fn($f) => count(ma_firmen_anzeigen($f)), $firmen), [2, 2, 2, 2, 2]);
pruefe('KI-Bilder nur bei Merzlicht und AJ Sports', array_map(fn($f) => array_map(fn($a) => !empty($a['ki']), $f['anzeigen']), $firmen), [[false, false], [false, false], [false, false], [true, true], [true, true]]);
foreach ($firmen as $f) foreach (ma_firmen_anzeigen($f) as $i => $a) {
    $n = $f['profil']['titel'] . ' ' . ($i + 1);
    pruefe("$n: Überschrift, Satz, Zeile oben, Knopf, Alt-Text, Nachweis", count(array_filter([$a['titel'] ?? '', $a['text'] ?? '', $a['zeile'] ?? '', $a['knopf'] ?? '', $a['alt'] ?? '', $a['nachweis'] ?? ''], fn($v) => trim((string) $v) !== '')), 6);
    $webm = MA_CORE_PATH . 'data/banner/' . preg_replace('/\.mp4$/', '.webm', $a['video']);
    pruefe("$n: Foto, MP4 und WebM im Plugin, Videos unter 1 MB", [is_file(MA_CORE_PATH . 'data/banner/' . $a['bild']), filesize(MA_CORE_PATH . 'data/banner/' . $a['video']) < 1024 * 1024, is_file($webm) && filesize($webm) < 1024 * 1024], [true, true, true]);
    pruefe("$n: keine Preise", preg_match('/€|\bEUR\b|\d+\s*Euro/i', $a['titel'] . ' ' . $a['text']), 0);
}
foreach ($firmen as $f) {
    $p = $f['profil']; $n = $p['titel'];
    if (!empty($f['nur_name'])) {
        pruefe("$n: nur Name, keine erfundenen Angaben (Adresse, Telefon, Website, Logo, Beitrag)", [$p['adresse'], $p['telefon'], $p['website'], $p['logo'] ?? null, $f['beitrag'] ?? null], ['', '', '', null, null]);
        pruefe("$n: Branche nur mit Quelle", $p['branche'] === '' || !empty($p['quellen']), true);
        continue;
    }
    $b = $f['beitrag'];
    pruefe("$n: Pflichtangaben vollständig", count(array_filter([$p['slug'], $p['branche'], $p['adresse'], $p['email'], $p['website'], $p['quelle'], $p['kurz'], $b['slug'], $b['titel'], $b['anriss']], fn($v) => trim((string) $v) !== '')), 10);
    pruefe("$n: Website, Logo-Quelle und Knopf mit https", (bool) preg_match('#^https://#', $p['website']) && (bool) preg_match('#^https://#', $p['logo']['quelle']) && (bool) preg_match('#^https://#', $b['knopf']['ziel']), true);
    pruefe("$n: Logo-Rechte offen", $p['logo']['rechte'], 'offen');
    pruefe("$n: Ortsteil bekannt oder Sitz außerhalb genannt", in_array($p['ortsteil'], ['merzenich', 'golzheim', 'girbelsrath', 'morschenich', 'buergewald'], true) || ($p['ortsteil'] === '' && trim($p['ort'] ?? '') !== ''), true);
    pruefe("$n: SEO-Beschreibung höchstens 160 Zeichen", mb_strlen($b['seo_beschreibung']) <= 160, true);
}
pruefe('KBS: Hinweis „Herausgeberin“ in Profil, Anriss und Text', [str_contains($firmen[1]['profil']['text'], 'Herausgeberin von Merzenich Aktuell'), str_contains($firmen[1]['beitrag']['anriss'], 'Herausgeberin'), str_contains($firmen[1]['beitrag']['absaetze'][0], 'In eigener Sache')], [true, true, true]);
pruefe('ASS Elektrotechnik: Sitz Düren, Meisterbetrieb, Logo und Beitrag', [$firmen[2]['profil']['ort'], str_contains($firmen[2]['profil']['branche'], 'Meisterbetrieb'), $firmen[2]['profil']['logo']['datei'], $firmen[2]['beitrag']['slug'] !== ''], ['Düren', true, 'ass-elektrotechnik.png', true]);

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
pruefe('fünf Profile, drei Werbebeiträge (Merzlicht und AJ Sports ohne Beitrag)', [count($profile), count($beitraege)], [5, 3]);
pruefe('alles ist Entwurf', array_values(array_unique(array_column($GLOBALS['posts'], 'post_status'))), ['draft']);
pruefe('keine Einwilligung gesetzt', array_filter(array_map(fn($p) => $p['meta_input']['ma_business_consent'] ?? null, $GLOBALS['posts'])), []);
pruefe('kein Logo (Beitragsbild) gesetzt', array_filter(array_map(fn($p) => $p['_thumbnail_id'] ?? $p['meta_input']['_thumbnail_id'] ?? null, $GLOBALS['posts'])), []);
pruefe('noch kein Werbebanner (kommt mit der Einwilligung)', count(array_filter($GLOBALS['posts'], fn($p) => $p['post_type'] === 'ma_ad')), 0);
$alm = $GLOBALS['posts'][100]; $ab = $GLOBALS['posts'][101]; $kbs = $GLOBALS['posts'][102]; $kb = $GLOBALS['posts'][103]; $ass = $GLOBALS['posts'][104]; $merz = $GLOBALS['posts'][106];
pruefe('Profil: E-Mail, Stand, als Kanal angelegt', [$alm['meta_input']['ma_business_email'], $alm['meta_input']['ma_business_stand'], $alm['meta_input']['ma_business_kanal']], ['info@almera-clean.de', '2026-10-28', '1']);
pruefe('Profil: Logo-Rechte offen', $alm['meta_input']['ma_business_logo_rechte'], 'offen');
pruefe('Beitrag Almera: gesponsert als Anzeige, kein Testeintrag', [$ab['meta_input']['ma_gesponsert'], $ab['meta_input']['ma_gesponsert_art']], ['1', 'anzeige']);
pruefe('Beitrag: mit dem Profil verknüpft, nicht auf der Startseite, Rubrik Wirtschaft', [$ab['meta_input']['ma_business_id'], $ab['meta_input']['ma_startseite_freigabe'], $ab['post_category']], ['100', 'nein', [7]]);
pruefe('Almera im Ortsteil Merzenich', $GLOBALS['terms'][101]['ma_location'] ?? '', 'merzenich');
pruefe('KBS: Sitz Leverkusen, Herausgeberin, kein Ortsteil', [$kbs['meta_input']['ma_business_ort'] ?? '', $kbs['meta_input']['ma_business_herausgeberin'] ?? '', $kbs['meta_input']['ma_business_ortsteil'] ?? ''], ['Leverkusen', '1', '']);
pruefe('KBS-Beitrag: Anzeige in eigener Sache, ohne Ortsteil', [$kb['meta_input']['ma_gesponsert_art'], isset($GLOBALS['terms'][103])], ['eigen', false]);
pruefe('ASS: Sitz Düren, kein Ortsteil, Logo-Rechte offen', [$ass['meta_input']['ma_business_ort'] ?? '', $ass['meta_input']['ma_business_ortsteil'] ?? '', $ass['meta_input']['ma_business_logo_rechte'] ?? ''], ['Düren', '', 'offen']);
pruefe('Merzlicht: nur Name, als Kanal, ohne Logo-Angaben', [$merz['post_title'], $merz['meta_input']['ma_business_nur_name'] ?? '', $merz['meta_input']['ma_business_kanal'] ?? '', isset($merz['meta_input']['ma_business_logo_rechte']), isset($merz['meta_input']['ma_business_adresse'])], ['Merzlicht', '1', '1', false, false]);
pruefe('Rückmeldung je Profil und Beitrag', count($log), 8);
$vorher = count($GLOBALS['posts']);
$log2 = ma_firmen_entwuerfe_anlegen();
pruefe('zweiter Lauf legt nichts doppelt an', count($GLOBALS['posts']), $vorher);
pruefe('zweiter Lauf meldet „schon vorhanden“', count(array_filter($log2, fn($z) => str_contains($z, 'schon vorhanden'))), 8);
$GLOBALS['posts'][101]['post_status'] = 'trash';
$GLOBALS['posts'][101]['post_name'] .= '__trashed';
ma_firmen_entwuerfe_anlegen();
pruefe('Beitrag im Papierkorb wird nicht neu angelegt', count($GLOBALS['posts']), $vorher);
foreach (['ma_in_pruefung', 'ma_aenderung', 'ma_abgelehnt', 'ma_archiv'] as $st) {
    $GLOBALS['posts'][103]['post_status'] = $st; $GLOBALS['posts'][100]['post_status'] = $st;
    ma_firmen_entwuerfe_anlegen();
    pruefe("Status $st: nichts doppelt", count($GLOBALS['posts']), $vorher);
}
pruefe('leerer Slug wird nie gesucht (WordPress lieferte sonst den neuesten Inhalt)', ma_firmen_vorhanden('post', ''), 0);

echo "Fehler beim Anlegen\n";
$GLOBALS['posts'] = []; $GLOBALS['terms'] = []; $GLOBALS['fehler_beim_anlegen'] = true;
$log3 = ma_firmen_entwuerfe_anlegen();
pruefe('nichts angelegt, nichts an fremde Inhalte gehängt', [count($GLOBALS['posts']), count($GLOBALS['terms'])], [0, 0]);
pruefe('Rückmeldung nennt den Fehler', str_contains($log3[0] ?? '', 'nicht angelegt (Datenbankfehler)'), true);
$GLOBALS['fehler_beim_anlegen'] = false;


echo "Zusage eintragen\n";
$GLOBALS['posts'] = []; $GLOBALS['terms'] = []; $GLOBALS['naechste'] = 100; $GLOBALS['meta'] = []; $GLOBALS['medien'] = 0; $GLOBALS['verlauf'] = []; $GLOBALS['weitere'] = [];
function get_post_meta($id, $k, $s = false) { return $GLOBALS['meta'][$id][$k] ?? ($GLOBALS['posts'][$id]['meta_input'][$k] ?? ''); }
function update_post_meta($id, $k, $v) { $GLOBALS['meta'][$id][$k] = $v; return true; }
function add_post_meta($id, $k, $v) { $GLOBALS['weitere'][$id][$k][] = $v; return true; }
function set_post_thumbnail($id, $m) { $GLOBALS['meta'][$id]['_thumbnail_id'] = $m; return true; }
function get_post_thumbnail_id($id) { return (int) ($GLOBALS['meta'][$id]['_thumbnail_id'] ?? 0); }
function wp_get_current_user() { return (object) ['display_name' => 'Redaktion', 'user_login' => 'admin']; }
function current_time($f) { return '24.10.2026 14:00'; }
function wp_tempnam($n) { return tempnam(sys_get_temp_dir(), 'logo'); }
function media_handle_sideload($datei, $pid, $alt) { $GLOBALS['medien']++; @unlink($datei['tmp_name']); $id = 900 + $GLOBALS['medien']; $GLOBALS['posts'][$id] = ['post_type' => 'attachment', 'post_status' => 'inherit', 'post_name' => $datei['name'], 'datei' => $datei['name']]; return $id; }
function ma_verlauf_eintragen($id, $a, $t = '') { $GLOBALS['verlauf'][] = [$id, $a, $t]; }
// Veröffentlichen wie WordPress mit der Sperre aus business.php: ohne Einwilligung bleibt es Entwurf.
function wp_update_post($a, $err = false) { $id = $a['ID']; if (($a['post_status'] ?? '') === 'publish' && ($GLOBALS['posts'][$id]['post_type'] ?? '') === 'ma_business' && get_post_meta($id, 'ma_business_consent') !== '1') $a['post_status'] = 'draft'; $GLOBALS['posts'][$id] = array_merge($GLOBALS['posts'][$id], $a); return $id; }
ma_firmen_entwuerfe_anlegen();
foreach ($firmen as $f) if (!empty($f['profil']['logo'])) pruefe($f['profil']['titel'] . ': Logo-Datei im Plugin', is_file(MA_CORE_PATH . 'data/logos/' . $f['profil']['logo']['datei']), true);
pruefe('ohne Nachweis: nichts', [ma_firmen_zusage_eintragen('almera-clean-merzenich', ' ', true, true)[0] ?? '', $GLOBALS['posts'][100]['post_status'], $GLOBALS['medien']], ['Almera Clean: Ohne Nachweis der Zusage wird nichts eingetragen.', 'draft', 0]);
pruefe('ohne Haken: nichts', str_contains(ma_firmen_zusage_eintragen('almera-clean-merzenich', 'Zusage laut Betreiber, 24.10.2026', false, false)[0] ?? '', 'Kein Haken'), true);
ma_firmen_zusage_eintragen('almera-clean-merzenich', 'Zusage laut Betreiber, 24.10.2026', false, true);
pruefe('nur Logo: Beitragsbild, Rechte geklärt, Profil weiter Entwurf', [$GLOBALS['meta'][100]['_thumbnail_id'] ?? 0, get_post_meta(100, 'ma_business_logo_rechte'), $GLOBALS['posts'][100]['post_status']], [901, 'geklaert', 'draft']);
pruefe('Logo-Medium: Nachweis, Alt-Text, Rechte geprüft, Herkunft', [get_post_meta(901, 'ma_image_credit'), get_post_meta(901, '_wp_attachment_image_alt'), get_post_meta(901, 'ma_image_rights_verified'), str_starts_with(get_post_meta(901, 'ma_image_original_url'), 'https://www.almera-clean.de/')],
    ['Logo: Almera Clean, Nutzung mit Freigabe des Unternehmens', 'Logo Almera Gebäudereinigung', '1', true]);
$log = ma_firmen_zusage_eintragen('almera-clean-merzenich', 'Zusage laut Betreiber, 24.10.2026', true, false);
pruefe('Einwilligung: mit Nachweis, wer, wann', [get_post_meta(100, 'ma_business_consent'), get_post_meta(100, 'ma_business_consent_quelle'), get_post_meta(100, 'ma_business_consent_by'), get_post_meta(100, 'ma_business_consent_at')], ['1', 'Zusage laut Betreiber, 24.10.2026', 'Redaktion', '24.10.2026 14:00']);
pruefe('Profil veröffentlicht, Werbebeitrag zur Prüfung in den Freigaben', [$GLOBALS['posts'][100]['post_status'], $GLOBALS['posts'][101]['post_status'], str_contains(implode(' ', $log), 'in den Freigaben zur Prüfung')], ['publish', 'pending', true]);
pruefe('Almera: zwei Werbebanner mit der Einwilligung', [count(array_filter($GLOBALS['posts'], fn($p) => $p['post_type'] === 'ma_ad')), str_contains(implode(' ', $log), 'Almera Clean, Anzeige 2: läuft.')], [2, true]);
ma_firmen_zusage_eintragen('almera-clean-merzenich', 'anderer Nachweis', true, true);
pruefe('erneut: kein zweites Logo-Medium, erste Einwilligung bleibt', [count(array_filter($GLOBALS['meta'], fn($m) => isset($m['ma_firmen_logo_datei']))), get_post_meta(100, 'ma_business_consent_quelle')], [1, 'Zusage laut Betreiber, 24.10.2026']);
pruefe('Logo-Freigabe und Einwilligung je genau einmal im Verlauf', [count(array_filter($GLOBALS['verlauf'], fn($v) => $v[0] === 100 && $v[1] === 'Logo freigegeben')), count(array_filter($GLOBALS['verlauf'], fn($v) => $v[0] === 100 && $v[1] === 'Einwilligung eingetragen'))], [1, 1]);

echo "Nur Name und Werbebanner\n";
// Profil aus der Zeit vor der Branche (lokal 24.10. angelegt): die Zusage ergänzt sie, vorhandene Felder bleiben.
$GLOBALS['posts'][106]['meta_input']['ma_business_branche'] = ''; $GLOBALS['meta'][106]['ma_business_ortsteil'] = 'golzheim';
$log = ma_firmen_zusage_eintragen('merzlicht', 'Zusage laut Betreiber, 24.10.2026', true, true);
$ads = array_filter($GLOBALS['posts'], fn($p, $id) => $p['post_type'] === 'ma_ad' && get_post_meta($id, 'ma_business_id') === '106', ARRAY_FILTER_USE_BOTH);
$adId = (int) array_key_first($ads); $ad = $ads[$adId] ?? [];
$m = fn(string $k) => get_post_meta($adId, $k);
pruefe('Merzlicht: Profil veröffentlicht, Logo-Haken ohne Logo ignoriert', [$GLOBALS['posts'][106]['post_status'], get_post_meta(106, 'ma_business_logo_rechte'), str_contains(implode(' ', $log), 'Logo')], ['publish', '', false]);
pruefe('Merzlicht: zwei Werbebanner, mit festem Motiv-Schlüssel markiert', [count($ads), array_values(array_map(fn($id) => get_post_meta($id, 'ma_firmen_banner'), array_keys($ads)))], [2, ['merzlicht:merzlicht-1', 'merzlicht:merzlicht-2']]);
pruefe('Banner 1: veröffentlicht, aktiv, Knopf zum Firmenkanal, ohne Ende, mit Profil', [$ad['post_status'] ?? '', $ad['post_title'] ?? '', $m('ma_ad_active'), $m('ma_ad_url'), $m('ma_ad_knopf'), $m('ma_ad_end'), $m('ma_business_id')], ['publish', 'Ihre Werbung auf der Videowall.', '1', 'https://merzenich-aktuell.de/unternehmen/merzlicht/', 'Zum Profil', '', '106']);
pruefe('Zusage ergänzt leere Branche, eigene Angaben bleiben', [get_post_meta(106, 'ma_business_branche'), get_post_meta(106, 'ma_business_ortsteil')], ['Werbung auf der Videowall', 'golzheim']);
pruefe('Banner im Stil der Anzeigen: Zeile oben und Satz, als KI-Bild gekennzeichnet', [$m('ma_ad_sponsor'), $ad['post_excerpt'] ?? '', $m('ma_ad_ki')], ['Merzlicht · Videowall am Bürgewaldzentrum', 'Am Sportplatz Bürgewaldzentrum, sichtbar zum Spielfeld und zum Merzpark.', '1']);
pruefe('Plätze: sechs Startseiten-Bänder, unter Artikeln, Unternehmensspalte', array_merge([$m('ma_ad_slot')], $GLOBALS['weitere'][$adId]['ma_ad_slot_weitere'] ?? []), ma_firmen_banner_plaetze());
$bild = (int) ($GLOBALS['meta'][$adId]['_thumbnail_id'] ?? 0);
pruefe('Bannerbild aus data/banner als Beitragsbild (Merkmal mit Prüfsumme), Alt-Text, Nachweis', [strtok(get_post_meta($bild, 'ma_firmen_banner_datei'), '#'), get_post_meta($bild, '_wp_attachment_image_alt'), get_post_meta($bild, 'ma_image_credit')], ['merzlicht-1.jpg', 'LED-Videowall am Sportplatz in der Dämmerung (KI-Bild)', 'KI-Bild (Symbolbild), erstellt für Merzenich Aktuell']);
pruefe('Video-Schleife aus data/banner in der Mediathek, MP4 und WebM', [strtok(get_post_meta((int) $m('ma_ad_video'), 'ma_firmen_banner_datei'), '#'), strtok(get_post_meta((int) $m('ma_ad_video_webm'), 'ma_firmen_banner_datei'), '#')], ['merzlicht-1.mp4', 'merzlicht-1.webm']);
pruefe('Rückmeldung: beide Banner laufen', [str_contains(implode(' ', $log), 'Merzlicht, Anzeige 1: läuft.'), str_contains(implode(' ', $log), 'Merzlicht, Anzeige 2: läuft.')], [true, true]);
$n = count($GLOBALS['posts']); $medien = $GLOBALS['medien'];
$log = ma_firmen_zusage_eintragen('merzlicht', 'noch einmal', true, false);
pruefe('zweite Zusage: kein zweites Banner, keine neuen Medien, „aktuell“', [count($GLOBALS['posts']), $GLOBALS['medien'], str_contains(implode(' ', $log), 'Anzeige 1: aktuell')], [$n, $medien, true]);
update_post_meta($adId, '_ma_firmen_banner_pruef', 'alter Stand');
pruefe('geändertes Motiv in der Datei: Banner wird nachgezogen', [str_contains(implode(' ', ma_firmen_zusage_eintragen('merzlicht', 'noch einmal', true, false)), 'Anzeige 1: aktualisiert'), count($GLOBALS['posts'])], [true, $n]);
update_post_meta($adId, '_ma_firmen_banner_pruef', 'alter Stand'); $GLOBALS['posts'][$adId]['post_modified_gmt'] = '2026-10-29 10:00:00';
pruefe('im Backend bearbeitet: nicht überschrieben', str_contains(implode(' ', ma_firmen_zusage_eintragen('merzlicht', 'noch einmal', true, false)), 'Anzeige 1: im Backend bearbeitet'), true);
$GLOBALS['posts'][$adId]['post_modified_gmt'] = ''; update_post_meta($adId, '_ma_firmen_banner_geschrieben', '');
unset($GLOBALS['meta'][$adId]['_thumbnail_id']);
pruefe('Banner ohne Bild (Übernahme gescheitert): Bild wird nachgetragen', [str_contains(implode(' ', ma_firmen_zusage_eintragen('merzlicht', 'noch einmal', true, false)), 'Anzeige 1: aktualisiert'), ($GLOBALS['meta'][$adId]['_thumbnail_id'] ?? 0) > 0], [true, true]);
// Früheres einzelnes Banner (Markierung ohne Nummer, 28.10. vormittags) wird Anzeige 1, kein drittes.
$GLOBALS['posts'][700] = ['post_type' => 'ma_ad', 'post_status' => 'publish', 'post_title' => 'AJ Sports Entertainment', 'meta_input' => ['ma_firmen_banner' => 'aj-sports', 'ma_business_id' => '107', 'ma_ad_active' => '1']];
ma_firmen_zusage_eintragen('aj-sports', 'Zusage laut Betreiber, 24.10.2026', true, false);
$aj = array_keys(array_filter($GLOBALS['posts'], fn($p, $id) => $p['post_type'] === 'ma_ad' && get_post_meta($id, 'ma_business_id') === '107', ARRAY_FILTER_USE_BOTH));
pruefe('AJ Sports: altes Banner wird Anzeige 1, dazu Anzeige 2', [count($aj), get_post_meta(700, 'ma_firmen_banner'), get_post_meta(700, 'ma_ad_video') !== ''], [2, 'aj-sports:aj-sports-1', true]);
$GLOBALS['posts'][700]['post_status'] = 'trash';
pruefe('Banner im Papierkorb: nichts neu angelegt', [str_contains(implode(' ', ma_firmen_zusage_eintragen('aj-sports', 'x', true, false)), 'Anzeige 1: liegt im Papierkorb'), count(array_filter($GLOBALS['posts'], fn($p) => $p['post_type'] === 'ma_ad'))], [true, count(array_filter($GLOBALS['posts'], fn($p) => $p['post_type'] === 'ma_ad'))]);
// Handänderung an einem alten Banner ohne Prüfsumme (vor 1.36.0) bleibt erhalten.
$GLOBALS['posts'][701] = ['post_type' => 'ma_ad', 'post_status' => 'publish', 'post_title' => 'Eigener Titel', 'post_date_gmt' => '2026-10-28 10:00:00', 'post_modified_gmt' => '2026-10-28 12:00:00', 'meta_input' => ['ma_firmen_banner' => 'ass-elektrotechnik', 'ma_business_id' => '104', 'ma_ad_active' => '1']];
$GLOBALS['posts'][104]['post_status'] = 'publish'; $GLOBALS['meta'][104]['ma_business_consent'] = '1';
$log = ma_firmen_zusage_eintragen('ass-elektrotechnik', 'x', true, false);
pruefe('altes Banner, später von Hand geändert: nicht überschrieben', [str_contains(implode(' ', $log), 'ASS Elektrotechnik, Anzeige 1: im Backend bearbeitet'), $GLOBALS['posts'][701]['post_title']], [true, 'Eigener Titel']);
// Endgültig gelöschtes Banner wird nicht neu angelegt.
$ass2 = (int) array_key_first(array_filter($GLOBALS['posts'], fn($p, $id) => $p['post_type'] === 'ma_ad' && get_post_meta($id, 'ma_firmen_banner') === 'ass-elektrotechnik:ass-elektrotechnik-2', ARRAY_FILTER_USE_BOTH));
ma_firmen_banner_geloescht_merken($ass2); unset($GLOBALS['posts'][$ass2]);
$log = ma_firmen_zusage_eintragen('ass-elektrotechnik', 'x', true, false);
pruefe('endgültig gelöschtes Banner: nicht neu angelegt', [str_contains(implode(' ', $log), 'Anzeige 2: wurde gelöscht'), count(array_filter($GLOBALS['posts'], fn($p, $id) => $p['post_type'] === 'ma_ad' && get_post_meta($id, 'ma_business_id') === '104', ARRAY_FILTER_USE_BOTH))], [true, 1]);
// Motiv, das nicht mehr in der Datei steht: Schaltung aus.
$GLOBALS['posts'][702] = ['post_type' => 'ma_ad', 'post_status' => 'publish', 'post_title' => 'Altes Motiv', 'meta_input' => ['ma_firmen_banner' => 'merzlicht:merzlicht-alt', 'ma_business_id' => '106', 'ma_ad_active' => '1']];
$log = ma_firmen_zusage_eintragen('merzlicht', 'x', true, false);
pruefe('Motiv nicht mehr in der Datei: Schaltung aus, Rückmeldung', [get_post_meta(702, 'ma_ad_active'), str_contains(implode(' ', $log), '„Altes Motiv“ steht nicht mehr in der Datei')], ['0', true]);
pruefe('laufende Motive bleiben an', array_values(array_unique(array_map(fn($id) => get_post_meta($id, 'ma_ad_active'), array_keys(array_filter($GLOBALS['posts'], fn($p, $id) => $p['post_type'] === 'ma_ad' && str_starts_with(get_post_meta($id, 'ma_firmen_banner'), 'merzlicht:merzlicht-') && $id !== 702, ARRAY_FILTER_USE_BOTH))))), ['1']);
// Nur ein Lauf zur Zeit.
$GLOBALS['optionen']['ma_firmen_lauf'] = (string) time();
pruefe('zweiter Lauf während eines Laufs: abgelehnt', str_contains(ma_firmen_gesperrt(fn() => ['lief'])[0], 'noch nicht fertig'), true);
unset($GLOBALS['optionen']['ma_firmen_lauf']);
pruefe('ohne laufenden Lauf: läuft und gibt die Sperre frei', [ma_firmen_gesperrt(fn() => ['lief']), isset($GLOBALS['optionen']['ma_firmen_lauf'])], [['lief'], false]);
pruefe('unbekannter Betrieb', ma_firmen_zusage_eintragen('gibt-es-nicht', 'x', true, true), ['Unbekannter Betrieb.']);
$GLOBALS['posts'][102]['post_status'] = 'trash';
pruefe('Profil im Papierkorb: nichts', str_contains(ma_firmen_zusage_eintragen('kbs-management', 'x', true, true)[0] ?? '', 'Profil fehlt'), true);

echo "Firma zurückziehen\n";
// Früherer Betrieb mit veröffentlichtem Profil, Beitrag und laufendem Banner.
$GLOBALS['posts'][500] = ['post_type' => 'ma_business', 'post_status' => 'publish', 'post_name' => 'baeckerei-wirtz-merzenich'];
$GLOBALS['posts'][501] = ['post_type' => 'post', 'post_status' => 'publish', 'post_name' => 'wirtz-beitrag', 'meta_input' => ['ma_business_id' => '500']];
$GLOBALS['posts'][502] = ['post_type' => 'post', 'post_status' => 'pending', 'post_name' => 'wirtz-zusatz', 'meta_input' => ['ma_business_id' => '500']];
$GLOBALS['posts'][503] = ['post_type' => 'ma_ad', 'post_status' => 'publish', 'post_name' => 'wirtz-ad', 'meta_input' => ['ma_business_id' => '500', 'ma_ad_active' => '1']];
$GLOBALS['posts'][504] = ['post_type' => 'post', 'post_status' => 'publish', 'post_name' => 'fremd', 'meta_input' => ['ma_business_id' => '777']];
pruefe('nur bekannte Betriebe', ma_firmen_zurueckziehen('irgendwer'), ['Unbekannter Betrieb.']);
$log = ma_firmen_zurueckziehen('baeckerei-wirtz-merzenich');
pruefe('Profil, Beitrag, wartender Beitrag und Banner werden Entwurf', array_map(fn($i) => $GLOBALS['posts'][$i]['post_status'], [500, 501, 502, 503]), ['draft', 'draft', 'draft', 'draft']);
pruefe('Banner: Schaltung aus', get_post_meta(503, 'ma_ad_active'), '0');
pruefe('fremder Beitrag bleibt', $GLOBALS['posts'][504]['post_status'], 'publish');
pruefe('Rückmeldung nennt Zahl und Profil', [str_contains($log[0] ?? '', '3 Beiträge und Banner'), str_contains($log[1] ?? '', 'Profil auf Entwurf')], [true, true]);
pruefe('nichts gelöscht', isset($GLOBALS['posts'][500], $GLOBALS['posts'][501], $GLOBALS['posts'][503]), true);
pruefe('zweiter Lauf: Profil war schon Entwurf', str_contains(ma_firmen_zurueckziehen('baeckerei-wirtz-merzenich')[1] ?? '', 'schon Entwurf'), true);
$GLOBALS['posts'][510] = ['post_type' => 'ma_business', 'post_status' => 'trash', 'post_name' => 'hasberg-fenster-rollladentechnik-merzenich__trashed'];
pruefe('Profil im Papierkorb wird nicht herausgeholt', [ma_firmen_zurueckziehen('hasberg-fenster-rollladentechnik-merzenich'), $GLOBALS['posts'][510]['post_status']], [['Hasberg Fenster & Rollladentechnik: Profil liegt im Papierkorb, nichts geändert.'], 'trash']);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
