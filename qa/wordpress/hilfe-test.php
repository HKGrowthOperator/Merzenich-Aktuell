<?php
/**
 * Hilfe für alle Zugänge (includes/hilfe.php, 24.10.2026) ohne WordPress:
 * Kasten „So geht's“ nur für Partner und ganz oben, PDF vorhanden, Video nur
 * mit gültiger Angabe, Tour nur für Partner und nur bis „Nicht mehr zeigen“,
 * Menü „Merzenich Aktuell“ für Partner ausgeblendet, Wortlaut wie im Editor.
 * Aufruf: php qa/wordpress/hilfe-test.php
 */
define('ABSPATH', __DIR__ . '/');
define('MA_CORE_PATH', dirname(__DIR__, 2) . '/wordpress/plugin/merzenich-aktuell-core/');
define('MA_CORE_URL', 'https://merzenich-aktuell.de/wp-content/plugins/merzenich-aktuell-core/');
define('MA_CORE_VERSION', 'test');

$GLOBALS['partner'] = false; $GLOBALS['opt'] = []; $GLOBALS['umeta'] = []; $GLOBALS['skripte'] = []; $GLOBALS['entfernt'] = [];
function add_action(...$a) {} function add_filter(...$a) {}
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_url($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function admin_url($p = '') { return 'https://merzenich-aktuell.de/wp-admin/' . $p; }
function ma_current_partner_policy() { return $GLOBALS['partner'] ? ['label' => 'Unternehmens-Partner', 'post_types' => $GLOBALS['typen'] ?? ['post']] : null; }
function get_option($k, $d = false) { return $GLOBALS['opt'][$k] ?? $d; }
function wp_get_attachment_url($id) { return $id === 77 ? 'https://merzenich-aktuell.de/wp-content/uploads/2026/10/anleitung.mp4' : false; }
function get_current_user_id() { return 5; }
function get_user_meta($u, $k, $s = false) { return $GLOBALS['umeta'][$u][$k] ?? ''; }
function wp_add_dashboard_widget($id, $t, $cb) { $GLOBALS['wp_meta_boxes']['dashboard']['normal']['core'][$id] = ['id' => $id, 'title' => $t]; }
function remove_menu_page($s) { $GLOBALS['entfernt'][] = $s; }
function get_current_screen() { return new class { public function is_block_editor() { return true; } }; }
function wp_register_script(...$a) {} function wp_enqueue_script($h) { $GLOBALS['skripte'][] = $h; } function wp_add_inline_script($h, $js) { $GLOBALS['inline'] = $js; }
function wp_register_style(...$a) {} function wp_enqueue_style(...$a) {} function wp_add_inline_style(...$a) {}
function wp_json_encode($v) { return json_encode($v, JSON_UNESCAPED_UNICODE); } function wp_create_nonce($a) { return 'n'; }

require MA_CORE_PATH . 'includes/hilfe.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-74s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Anleitung und Video\n";
pruefe('PDF liegt im Plugin, Adresse mit Version', [is_file(MA_CORE_PATH . MA_HILFE_PDF), str_starts_with(ma_hilfe_pdf_url(), MA_CORE_URL . 'assets/hilfe/anleitung-zugang.pdf?v=')], [true, true]);
pruefe('PDF ist ein PDF mit mehreren Seiten', [str_starts_with((string) file_get_contents(MA_CORE_PATH . MA_HILFE_PDF, false, null, 0, 5), '%PDF-'), preg_match_all('#/Type\s*/Page[^s]#', (string) file_get_contents(MA_CORE_PATH . MA_HILFE_PDF)) >= 8], [true, true]);
pruefe('ohne Video-Angabe kein Video', ma_hilfe_video_url(), '');
$GLOBALS['opt']['ma_hilfe_video'] = '77';
pruefe('Video aus der Mediathek (Medien-ID)', ma_hilfe_video_url(), 'https://merzenich-aktuell.de/wp-content/uploads/2026/10/anleitung.mp4');
$GLOBALS['opt']['ma_hilfe_video'] = 'javascript:alert(1)';
pruefe('keine fremden Adressarten', ma_hilfe_video_url(), '');
$GLOBALS['opt']['ma_hilfe_video'] = '';

echo "\nKasten „So geht's“\n";
$GLOBALS['wp_meta_boxes'] = ['dashboard' => ['normal' => ['core' => ['ma_partner_bereich' => ['id' => 'ma_partner_bereich']]]]];
ma_hilfe_dashboard_setup();
pruefe('Redaktion: kein Kasten', array_keys($GLOBALS['wp_meta_boxes']['dashboard']['normal']['core']), ['ma_partner_bereich']);
$GLOBALS['partner'] = true;
ma_hilfe_dashboard_setup();
pruefe('Partner: Kasten ganz oben, vor „Ihr Bereich“', array_keys($GLOBALS['wp_meta_boxes']['dashboard']['normal']['core']), ['ma_hilfe_sogehts', 'ma_partner_bereich']);
$h = ma_hilfe_widget_html('https://x/anleitung.pdf', '', 'https://x/hilfe');
pruefe('vier Schritte, PDF-Knopf, kein Videoknopf ohne Video', [substr_count($h, '<li>'), str_contains($h, 'Anleitung als PDF'), str_contains($h, 'Erklärvideo')], [4, true, false]);
pruefe('mit Video: Knopf „Erklärvideo ansehen“', str_contains(ma_hilfe_widget_html('', 'https://x/hilfe#video', 'https://x/hilfe'), 'Erklärvideo ansehen'), true);
pruefe('Wortlaut wie im deutschen Editor: „Zur Überprüfung einreichen“', [str_contains(json_encode(ma_hilfe_schritte(), JSON_UNESCAPED_UNICODE), 'Zur Überprüfung einreichen'), str_contains((string) file_get_contents(MA_CORE_PATH . 'includes/hilfe.php'), 'Zur Prüfung einreichen')], [true, false]);

echo "\nMenü\n";
$GLOBALS['partner'] = false; ma_hilfe_menue_aufraeumen();
pruefe('Redaktion behält „Merzenich Aktuell“', $GLOBALS['entfernt'], []);
$GLOBALS['partner'] = true; ma_hilfe_menue_aufraeumen();
pruefe('Partner: „Merzenich Aktuell“ ausgeblendet', $GLOBALS['entfernt'], ['merzenich-aktuell']);

echo "\nTour im Editor\n";
$GLOBALS['partner'] = false; ma_hilfe_tour_laden('post-new.php');
pruefe('Redaktion: keine Tour', $GLOBALS['skripte'], []);
$GLOBALS['partner'] = true; ma_hilfe_tour_laden('edit.php');
pruefe('nur im Editor (post-new.php, post.php)', $GLOBALS['skripte'], []);
ma_hilfe_tour_laden('post-new.php');
pruefe('Partner im Editor: Tour geladen', $GLOBALS['skripte'], ['ma-hilfe-tour']);
$d = json_decode(substr($GLOBALS['inline'], strlen('window.maHilfeTour='), strpos($GLOBALS['inline'], ';(function') - strlen('window.maHilfeTour=')), true);
pruefe('Block-Editor: Titel, Text, Beitragsbild, Rechte, Ortsteil, Einreichen', array_column($d['schritte'] ?? [], 'titel'), ['Titel', 'Text', 'Beitragsbild', 'Bild- und Nutzungsrechte', 'Ortsteil', 'Einreichen']);
pruefe('Elemente auch im Editor-Rahmen und Bereiche über den Titel', [str_contains($GLOBALS['inline'], 'editor-canvas'), str_contains($GLOBALS['inline'], "indexOf('panel:')")], [true, true]);
$GLOBALS['skripte'] = []; $GLOBALS['umeta'][5][MA_HILFE_TOUR_META] = '1';
ma_hilfe_tour_laden('post-new.php');
pruefe('nach „Nicht mehr zeigen“: keine Tour mehr', $GLOBALS['skripte'], []);
pruefe('klassische Formulare haben eigene Schritte mit Bildrechten', in_array('Bild- und Nutzungsrechte', array_column(ma_hilfe_tour_schritte('klassisch'), 1), true), true);

echo "\nBeim Aufnehmen gefunden (24.10.2026)\n";
$c = fn(string $f) => (string) file_get_contents(MA_CORE_PATH . 'includes/' . $f);
pruefe('Tour startet, sobald der Titel im Editor-Rahmen da ist (nicht erst nach 10 s)', str_contains($c('hilfe.php'), 'function titelDa()') && str_contains($c('hilfe.php'), "fr.contentDocument.querySelector(q)"), true);
pruefe('Bildrechte: Bestätigung gilt in Seitenleiste und Einreichen-Fenster', [str_contains($c('bildrechte.php'), "window.dispatchEvent(new Event('ma-bildrechte'))"), str_contains($c('bildrechte.php'), "addEventListener('ma-bildrechte'")], [true, true]);
pruefe('Upload-Fenster nennt Partnern höchstens 10 MB', str_contains($c('bildrechte.php'), "add_filter('upload_size_limit'"), true);
pruefe('Kasten „Freigabe“ (Redaktions-Haken) nicht für Partner', (bool) preg_match("/function ma_gate_boxes\\(\\): void \\{\\s*\\/\\/[^\\n]*\\n\\s*if \\(function_exists\\('ma_current_partner_policy'\\) && ma_current_partner_policy\\(\\)\\) return;/", $c('freigabe.php')), true);
pruefe('Hinweise der Redaktion auch im Block-Editor (core/notices)', [str_contains($c('redaktion.php'), 'function ma_redaktion_editor_hinweise'), str_contains($c('redaktion.php'), "dispatch('core/notices').createNotice")], [true, true]);
pruefe('Termine, Immobilien, Stellen: Datenkästen im Block-Editor aufgeklappt', str_contains($c('content-admin.php'), "'metaBoxesMainIsOpen',true"), true);
pruefe('Dashboard der Partner einspaltig', str_contains($c('hilfe.php'), "\$spalten['dashboard'] = 1"), true);
pruefe('Änderungsvorschlag in der Liste als solcher gekennzeichnet', str_contains($c('vereine.php'), "' · Änderungsvorschlag'"), true);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
