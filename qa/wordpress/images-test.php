<?php
/**
 * Bildnachweis kürzen (includes/images.php, ma_credit_kurz) ohne WordPress:
 * echte Nachweise aus dem Import, Lizenz genau einmal am Ende.
 * Aufruf: php qa/wordpress/images-test.php
 */
define('ABSPATH', __DIR__);
function add_action(...$a) {} function add_filter(...$a) {} function get_option($k, $d = false) { return $d; }
require __DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/images.php';
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-70s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }

echo "Kürzung echter Nachweise\n";
pruefe('Commons-Floskel, Public domain → gemeinfrei', ma_credit_kurz('No machine-readable author provided. Papa1234 assumed (based on copyright claims). / Wikimedia Commons · Public domain', 'Public domain'), 'Papa1234 / Wikimedia Commons · gemeinfrei');
pruefe('Wohnort fällt weg', ma_credit_kurz('Håkan Dahlström from Malmö, Sweden / Wikimedia Commons · CC BY 2.0', 'CC BY 2.0'), 'Håkan Dahlström / Wikimedia Commons · CC BY 2.0');
pruefe('Benutzerkonto fällt weg', ma_credit_kurz('Henning Schlottmann (User:H-stt) / Wikimedia Commons · CC BY-SA 4.0'), 'Henning Schlottmann / Wikimedia Commons · CC BY-SA 4.0');
pruefe('(bearbeitet: …) wird (bearbeitet)', ma_credit_kurz('Käthe und Bernd Limburg / Wikimedia Commons (bearbeitet: Hausnummer unkenntlich) · CC BY-SA 3.0 DE', 'CC BY-SA 3.0 DE'), 'Käthe und Bernd Limburg / Wikimedia Commons (bearbeitet) · CC BY-SA 3.0 DE');
pruefe('Diskussionslink und Web-Adresse fallen weg', ma_credit_kurz('Max Muster ( Diskussion ), www.example.org / Wikimedia Commons · CC0'), 'Max Muster / Wikimedia Commons · CC0');
pruefe('Lizenz nur aus dem Feld, nicht doppelt', ma_credit_kurz('Dietmar Rabich / Wikimedia Commons', 'CC BY-SA 4.0'), 'Dietmar Rabich / Wikimedia Commons · CC BY-SA 4.0');
pruefe('Lizenz im Text und im Feld: einmal', ma_credit_kurz('Dietmar Rabich / Wikimedia Commons · CC BY-SA 4.0', 'CC BY-SA 4.0'), 'Dietmar Rabich / Wikimedia Commons · CC BY-SA 4.0');
pruefe('Eigene Quellen bleiben unverändert', [ma_credit_kurz('Freiwillige Feuerwehr Merzenich'), ma_credit_kurz('Gemeinde Merzenich · Heimat-Info'), ma_credit_kurz('Freiwillige Feuerwehr Merzenich (Beispielbild)'), ma_credit_kurz('alexgo.photography / Shutterstock (Symbolbild)')],
    ['Freiwillige Feuerwehr Merzenich', 'Gemeinde Merzenich · Heimat-Info', 'Freiwillige Feuerwehr Merzenich (Beispielbild)', 'alexgo.photography / Shutterstock (Symbolbild)']);
pruefe('Symbolbild-Vorsatz fällt weg', ma_credit_kurz('Symbolbild · Granpar / Wikimedia Commons · CC BY 3.0'), 'Granpar / Wikimedia Commons · CC BY 3.0');
pruefe('Leer bleibt leer, auch mit Lizenz', ma_credit_kurz('', 'CC0'), '');
pruefe('Freitext im Lizenzfeld wird nicht angehängt', ma_credit_kurz('Polizei Düren / Presseportal (ots)', 'Pressemitteilung, Nutzung für Berichterstattung'), 'Polizei Düren / Presseportal (ots)');

echo "\nBildzeile\n";
$bild = ['url' => 'x', 'type' => 'licensed', 'type_label' => 'Lizenziertes Bild', 'credit' => ma_credit_kurz('Frank Vincentz / Wikimedia Commons · CC BY-SA 3.0', 'CC BY-SA 3.0'), 'alt' => 'a', 'license' => 'CC BY-SA 3.0', 'source_url' => '', 'is_fallback' => false, 'disclaimer' => ''];
pruefe('Lizenz steht in der Bildzeile genau einmal', ma_image_caption($bild), 'Lizenziertes Bild. Foto: Frank Vincentz / Wikimedia Commons · CC BY-SA 3.0');
$q = file_get_contents(__DIR__ . '/../../wordpress/plugin/merzenich-aktuell-core/includes/feeds.php') . file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/ma21.php');
pruefe('Feed und Theme-Bildzeile nutzen ma_credit_kurz', substr_count($q, 'ma_credit_kurz('), 2);

echo "\nZwischengrößen (Nachlauf)\n";
pruefe('Zwischengrößen 480 und 240 px', MA_GROESSEN, ['ma-480' => 480, 'ma-240' => 240]);
$GLOBALS['anh'] = [
    11 => ['width' => 1600, 'sizes' => ['medium' => ['file' => 'a-300x200.webp'], 'ma-480' => ['file' => 'a-480x320.webp'], 'ma-240' => ['file' => 'a-240x160.webp']]],
    12 => ['width' => 1600, 'sizes' => ['medium' => ['file' => 'b-300x200.webp']]],
    13 => ['width' => 400, 'sizes' => ['medium' => ['file' => 'c-300x200.webp']]],
    14 => ['width' => 400, 'sizes' => ['medium' => ['file' => 'd-300x200.webp'], 'ma-240' => ['file' => 'd-240x160.webp']]],
    15 => [],
];
function get_posts($a) { return array_keys($GLOBALS['anh']); }
function wp_get_attachment_metadata($id) { return $GLOBALS['anh'][$id] ?? false; }
pruefe('Offen: ohne beide Größen (12), schmales Original ohne 240er (13); nicht: vollständig (11, 14), ohne Metadaten (15)', ma_groessen_offen(), [12, 13]);
pruefe('Limit greift', ma_groessen_offen(1), [12]);

echo "\n" . ($fehler ? "$fehler Fehler" : 'Alle Prüfungen bestanden') . "\n";
exit($fehler ? 1 : 0);
