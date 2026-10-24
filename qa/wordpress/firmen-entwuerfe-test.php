<?php
/**
 * Unternehmen als Entwurf (includes/firmen-entwuerfe.php und
 * data/firmen-entwuerfe.json; seit 24.10.2026 Almera Clean, KBS Management,
 * Merzlicht, AJ Sports): nur Entwürfe, keine Einwilligung, keine Logos, kein
 * Platz auf der Startseite, kein zweites Anlegen; Profile nur mit Namen ohne
 * erfundene Angaben. Zusage eintragen: Einwilligung und Logo getrennt mit
 * Nachweis, Logo einmal in die Mediathek, Profil erst mit Einwilligung
 * veröffentlicht, Werbebeitrag bleibt für die Freigaben, Werbebanner einmal
 * angelegt. Firma zurückziehen: alles Entwurf, nichts gelöscht.
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
    $typ = (array) ($q['post_type'] ?? 'post'); $st = (array) ($q['post_status'] ?? ['publish']);
    $wert = fn($id, $k) => (string) ($GLOBALS['meta'][$id][$k] ?? ($GLOBALS['posts'][$id]['meta_input'][$k] ?? ''));
    $treffer = array_keys(array_filter($GLOBALS['posts'], function ($p, $id) use ($q, $typ, $st, $wert) {
        if (!in_array($p['post_type'], $typ, true) || !in_array($p['post_status'], $st, true)) return false;
        if (isset($q['name']) && $p['post_name'] !== $q['name']) return false;
        if (isset($q['meta_key']) && $wert($id, $q['meta_key']) !== (string) $q['meta_value']) return false;
        foreach ((array) ($q['meta_query'] ?? []) as $c) if ($wert($id, $c['key']) !== (string) $c['value']) return false;
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
pruefe('vier Unternehmen (24.10.2026)', array_map(fn($f) => $f['profil']['titel'], $firmen), ['Almera Clean', 'KBS Management GmbH', 'Merzlicht', 'AJ Sports']);
pruefe('frühere Betriebe zum Zurückziehen', array_column($d['frueher'] ?? [], 'slug'), ['baeckerei-wirtz-merzenich', 'hasberg-fenster-rollladentechnik-merzenich', 'porschen-media-girbelsrath']);
$text = json_encode($d, JSON_UNESCAPED_UNICODE);
pruefe('keine Wörter „Partner“, „Kunde“, „Sponsor“ über die Betriebe', preg_match('/partner|kunde|sponsor/i', $text), 0);
pruefe('kein Gedankenstrich „—“', strpos($text, '—'), false);
pruefe('Art je Firma: Anzeige, eigene Sache (Herausgeberin), Anzeige, Anzeige', array_map('ma_firmen_art', $firmen), ['anzeige', 'eigen', 'anzeige', 'anzeige']);
pruefe('nur KBS ist als Herausgeberin markiert', array_map(fn($f) => !empty($f['herausgeberin']), $firmen), [false, true, false, false]);
foreach ($firmen as $f) {
    $p = $f['profil']; $n = $p['titel'];
    if (!empty($f['nur_name'])) {
        pruefe("$n: nur Name, keine erfundenen Angaben (Adresse, Telefon, Website, Branche, Logo, Beitrag)", [$p['adresse'], $p['telefon'], $p['website'], $p['branche'], $p['logo'] ?? null, $f['beitrag'] ?? null], ['', '', '', '', null, null]);
        pruefe("$n: Banner mit Bilddatei im Plugin", is_file(MA_CORE_PATH . 'data/banner/' . ($f['anzeige']['bild'] ?? '-')), true);
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
pruefe('Banner nur für Merzlicht und AJ Sports', array_map(fn($f) => !empty($f['anzeige']), $firmen), [false, false, true, true]);

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
pruefe('vier Profile, zwei Werbebeiträge (Merzlicht und AJ Sports ohne Beitrag)', [count($profile), count($beitraege)], [4, 2]);
pruefe('alles ist Entwurf', array_values(array_unique(array_column($GLOBALS['posts'], 'post_status'))), ['draft']);
pruefe('keine Einwilligung gesetzt', array_filter(array_map(fn($p) => $p['meta_input']['ma_business_consent'] ?? null, $GLOBALS['posts'])), []);
pruefe('kein Logo (Beitragsbild) gesetzt', array_filter(array_map(fn($p) => $p['_thumbnail_id'] ?? $p['meta_input']['_thumbnail_id'] ?? null, $GLOBALS['posts'])), []);
pruefe('noch kein Werbebanner (kommt mit der Einwilligung)', count(array_filter($GLOBALS['posts'], fn($p) => $p['post_type'] === 'ma_ad')), 0);
$alm = $GLOBALS['posts'][100]; $ab = $GLOBALS['posts'][101]; $kbs = $GLOBALS['posts'][102]; $kb = $GLOBALS['posts'][103]; $merz = $GLOBALS['posts'][104];
pruefe('Profil: E-Mail, Stand, als Kanal angelegt', [$alm['meta_input']['ma_business_email'], $alm['meta_input']['ma_business_stand'], $alm['meta_input']['ma_business_kanal']], ['info@almera-clean.de', '2026-10-24', '1']);
pruefe('Profil: Logo-Rechte offen', $alm['meta_input']['ma_business_logo_rechte'], 'offen');
pruefe('Beitrag Almera: gesponsert als Anzeige, kein Testeintrag', [$ab['meta_input']['ma_gesponsert'], $ab['meta_input']['ma_gesponsert_art']], ['1', 'anzeige']);
pruefe('Beitrag: mit dem Profil verknüpft, nicht auf der Startseite, Rubrik Wirtschaft', [$ab['meta_input']['ma_business_id'], $ab['meta_input']['ma_startseite_freigabe'], $ab['post_category']], ['100', 'nein', [7]]);
pruefe('Almera im Ortsteil Merzenich', $GLOBALS['terms'][101]['ma_location'] ?? '', 'merzenich');
pruefe('KBS: Sitz Leverkusen, Herausgeberin, kein Ortsteil', [$kbs['meta_input']['ma_business_ort'] ?? '', $kbs['meta_input']['ma_business_herausgeberin'] ?? '', $kbs['meta_input']['ma_business_ortsteil'] ?? ''], ['Leverkusen', '1', '']);
pruefe('KBS-Beitrag: Anzeige in eigener Sache, ohne Ortsteil', [$kb['meta_input']['ma_gesponsert_art'], isset($GLOBALS['terms'][103])], ['eigen', false]);
pruefe('Merzlicht: nur Name, als Kanal, ohne Logo-Angaben', [$merz['post_title'], $merz['meta_input']['ma_business_nur_name'] ?? '', $merz['meta_input']['ma_business_kanal'] ?? '', isset($merz['meta_input']['ma_business_logo_rechte']), isset($merz['meta_input']['ma_business_adresse'])], ['Merzlicht', '1', '1', false, false]);
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
pruefe('Almera ohne Banner-Angabe: kein Werbebanner', count(array_filter($GLOBALS['posts'], fn($p) => $p['post_type'] === 'ma_ad')), 0);
ma_firmen_zusage_eintragen('almera-clean-merzenich', 'anderer Nachweis', true, true);
pruefe('erneut: kein zweites Logo-Medium, erste Einwilligung bleibt', [$GLOBALS['medien'], get_post_meta(100, 'ma_business_consent_quelle')], [1, 'Zusage laut Betreiber, 24.10.2026']);
pruefe('Logo-Freigabe und Einwilligung je genau einmal im Verlauf', [count(array_filter($GLOBALS['verlauf'], fn($v) => $v[0] === 100 && $v[1] === 'Logo freigegeben')), count(array_filter($GLOBALS['verlauf'], fn($v) => $v[0] === 100 && $v[1] === 'Einwilligung eingetragen'))], [1, 1]);

echo "Nur Name und Werbebanner\n";
$log = ma_firmen_zusage_eintragen('merzlicht', 'Zusage laut Betreiber, 24.10.2026', true, true);
$ads = array_filter($GLOBALS['posts'], fn($p) => $p['post_type'] === 'ma_ad');
$adId = (int) array_key_first($ads); $ad = $ads[$adId] ?? [];
pruefe('Merzlicht: Profil veröffentlicht, Logo-Haken ohne Logo ignoriert', [$GLOBALS['posts'][104]['post_status'], get_post_meta(104, 'ma_business_logo_rechte'), str_contains(implode(' ', $log), 'Logo')], ['publish', '', false]);
pruefe('Werbebanner angelegt: veröffentlicht, aktiv, ohne Link, ohne Ende, mit Profil', [$ad['post_status'] ?? '', $ad['post_title'] ?? '', $ad['meta_input']['ma_ad_active'] ?? '', $ad['meta_input']['ma_ad_url'] ?? 'x', isset($ad['meta_input']['ma_ad_end']), $ad['meta_input']['ma_business_id'] ?? ''], ['publish', 'Merzlicht', '1', '', false, '104']);
pruefe('Plätze: sechs Startseiten-Bänder, unter Artikeln, Unternehmensspalte', array_merge([$ad['meta_input']['ma_ad_slot'] ?? ''], $GLOBALS['weitere'][$adId]['ma_ad_slot_weitere'] ?? []), ma_firmen_banner_plaetze());
pruefe('Bannerbild aus data/banner als Beitragsbild, Alt-Text', [get_post_meta($GLOBALS['meta'][$adId]['_thumbnail_id'] ?? 0, 'ma_firmen_banner_datei'), get_post_meta($GLOBALS['meta'][$adId]['_thumbnail_id'] ?? 0, '_wp_attachment_image_alt')], ['merzlicht.png', 'Anzeige Merzlicht']);
pruefe('Rückmeldung: Werbebanner läuft', str_contains(implode(' ', $log), 'Merzlicht: Werbebanner läuft.'), true);
$n = count($GLOBALS['posts']);
$log = ma_firmen_zusage_eintragen('merzlicht', 'noch einmal', true, false);
pruefe('zweite Zusage: kein zweites Banner', [count($GLOBALS['posts']), str_contains(implode(' ', $log), 'schon vorhanden')], [$n, true]);
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

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
