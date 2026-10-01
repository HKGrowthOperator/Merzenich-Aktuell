<?php
/**
 * Redaktionsablauf, Feldsperre für Partner und Vereinsstruktur
 * (includes/redaktion.php, includes/vereine.php) ohne WordPress.
 * Der vollständige Ablauf (Einreichen, Änderungen anfordern, In Prüfung,
 * Freigabe, Änderungsvorschlag) läuft im Integrationstest in WordPress.
 * Aufruf: php qa/wordpress/redaktion-vereine-test.php
 */
define('ABSPATH', __DIR__);
define('MA_CORE_PATH', __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/');
class WP_Post { public $ID = 1; public $post_date = '2026-10-05 18:00:00'; public $post_type = 'post'; }
class WP_User { public $ID; public $roles; function __construct($id, $r) { $this->ID = $id; $this->roles = $r; } function exists() { return true; } }
function add_action(...$a) {} function add_filter(...$a) {} function register_post_status(...$a) {} function _n_noop($a, $b) { return [$a, $b]; }
function mysql2date($f, $d) { return date($f, strtotime($d)); }
function remove_accents($s) { return $s; }
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES); }
function esc_url($s) { return (string) $s; }
$GLOBALS['partner'] = false;
function ma_current_partner_policy() { return $GLOBALS['partner'] ? ['role' => 'ma_sport_partner', 'label' => 'Sport-Partner', 'post_types' => ['post']] : null; }
require MA_CORE_PATH . 'includes/redaktion.php';
require MA_CORE_PATH . 'includes/vereinszugaenge.php';
require MA_CORE_PATH . 'includes/vereine.php';

$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-66s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Zustände\n";
foreach (['draft' => 'Entwurf', 'pending' => 'Zur Prüfung eingereicht', 'ma_in_pruefung' => 'In redaktioneller Prüfung', 'ma_aenderung' => 'Änderungen angefordert', 'future' => 'Freigegeben, geplant', 'publish' => 'Veröffentlicht', 'ma_archiv' => 'Archiviert'] as $s => $n) pruefe($s . ' heißt „' . $n . '“', ma_status_name($s), $n);
$p = new WP_Post();
pruefe('Wiedereinreichung nach Änderungswunsch', ma_verlauf_text('pending', 'ma_aenderung', $p), 'Überarbeitet und erneut eingereicht');
pruefe('Geplante Veröffentlichung nennt den Zeitpunkt', ma_verlauf_text('future', 'pending', $p), 'Freigegeben, Veröffentlichung geplant für 05.10.2026 18:00 Uhr');
pruefe('Zurückziehen', ma_verlauf_text('draft', 'publish', $p), 'Zurückgezogen (wieder Entwurf)');

echo "\nFeldsperre\n";
foreach (['ma_relevanz', 'ma_startplatz', 'ma_editorial_priority', 'ma_top_until', 'ma_top_pinned', 'ma_gesponsert'] as $k) pruefe("$k nur für die Redaktion", in_array($k, ma_redaktion_nur_felder(), true), true);
$GLOBALS['partner'] = true;
pruefe('Partner: Relevanz wird nicht geschrieben', ma_redaktion_meta_sperre(null, 5, 'ma_relevanz'), false);
pruefe('Partner: normales Feld bleibt frei', ma_redaktion_meta_sperre(null, 5, 'ma_kicker'), null);
$GLOBALS['partner'] = false;
pruefe('Redaktion: Relevanz frei', ma_redaktion_meta_sperre(null, 5, 'ma_relevanz'), null);

echo "\nVereinsstruktur\n";
$s = ma_vereine_struktur();
$alle = ma_vereinsverzeichnis();
pruefe('Verzeichnis geladen', count($alle) > 50, true);
pruefe('Ein Hauptverein je Eintrag, Abteilungen eingerechnet', count($s) + array_sum(array_map(fn($e) => count($e['abteilungen']), $s)), count($alle));
pruefe('SC 1919: Tennis und Badminton als Abteilungen', array_map(fn($a) => $a['sportart'], $s['sc-1919-merzenich']['abteilungen']), ['Tennis', 'Badminton']);
pruefe('Kein Hauptverein heißt „… – …abteilung“', count(array_filter($s, fn($e) => str_contains($e['verein']['name'], 'abteilung'))), 0);
pruefe('„F.A.K. e.V. Düren – Tagespflegehaus“ bleibt eigenständig', isset($s[ma_verein_kurz('F.A.K. e.V. Düren – Tagespflegehaus Merzenich')]), true);
pruefe('Sportarten: mehr als Fußball', count(array_unique(array_map(fn($e) => $e['verein']['sportart'] ?? '', array_filter($s, fn($e) => ($e['verein']['kategorie'] ?? '') === 'Sport')))) >= 8, true);

echo "\nProfiltexte\n";
pruefe('Geprüfte Profiltexte vorhanden', count(ma_vereinsprofile_daten()) >= 10, true);
pruefe('SC-Profiltext über den Slug', ma_vereinsprofil_text($s['sc-1919-merzenich']['verein'])['slug'] ?? '', 'sc-1919-merzenich');
pruefe('KG Mir hahle Poohl über den Namen', ma_vereinsprofil_text(['name' => 'KG Mir hahle Poohl Golzheim 1905 e.V.'])['slug'] ?? '', 'kg-mir-hahle-poohl-golzheim');
pruefe('Markdown-Link wird Link, Text wird escaped', ma_vereinsprofil_html('A <b> [Sport](/sport/)'), '<p>A &lt;b&gt; <a href="/sport/">Sport</a></p>');

echo $fehler ? "\n$fehler Fehler.\n" : "\nAlle Pruefungen bestanden.\n";
exit($fehler ? 1 : 0);
