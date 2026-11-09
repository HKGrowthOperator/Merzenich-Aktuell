<?php
/**
 * Ladegewicht (Theme 21.13.0): Werbe- und Musterbilder, Logos und die kleinen
 * Fassungen für Karten. Bis 06.10.2026 lagen die Musterbilder bei bis zu 1,1 MB,
 * /unternehmen/ und /betriebe/ luden damit über 4 MB.
 * Aufruf: php qa/wordpress/ladegewicht-test.php
 */
$fehler = 0;
function pruefe(string $name, $ist, $soll) { global $fehler; $ok = $ist === $soll; if (!$ok) $fehler++; printf("  %-70s %s\n", $name, $ok ? 'ok' : 'FEHLER (ist: ' . var_export($ist, true) . ')'); }
$assets = __DIR__ . '/../../chatgpt-site/assets/';

echo "Werbe- und Musterbilder\n";
$config = json_decode((string) file_get_contents(__DIR__ . '/../../deploy/werben-bilder.json'), true);
foreach ($config['images'] as $bild) {
    $datei = $assets . 'werben/' . $bild['output'];
    $klein = preg_replace('/\.jpg$/', '-720.jpg', $datei);
    pruefe($bild['output'] . ' höchstens 300 KB und 1080 px', [filesize($datei) <= 300 * 1024, (getimagesize($datei)[0] ?? 0) <= 1080], [true, true]);
    pruefe($bild['output'] . ' mit 720er-Fassung unter 120 KB', [is_file($klein), is_file($klein) && filesize($klein) <= 120 * 1024], [true, true]);
}

echo "\nLogos\n";
// Logo 04 (09.11.2026): das freigegebene Original unverändert, deshalb ohne Größengrenze; es lädt
// einmal im Kopf, der Fuß nutzt dieselbe Datei aus dem Cache (loading="lazy").
$logo04 = $assets . 'marke/logo-04.png';
$g = getimagesize($logo04);
pruefe('logo-04.png ist Byte für Byte das Original (artifacts/branding/2026-11-logo/04-final-original.png)', hash_file('sha256', $logo04), hash_file('sha256', __DIR__ . '/../../artifacts/branding/2026-11-logo/04-final-original.png'));
pruefe('logo-04.png 1348×692, wie im Markup', [$g[0], $g[1]], [1348, 692]);
$kopfV = (string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/vorlagen/kopf.html');
pruefe('Kopf, Schublade und Fuß zeigen kein altes Logo mehr', [substr_count($kopfV, 'logo-04.png'), substr_count($kopfV . file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/vorlagen/fuss.html'), 'img/logo')], [2, 0]);

echo "\nMusterprofile laden die kleine Fassung\n";
$markt = (string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/markt.php');
pruefe('Kasten mit srcset 720w/1080w', str_contains($markt, "' 720w, ' . ma21_abs(\$b['src']) . ' 1080w'"), true);
pruefe('Unternehmen-Menü (Musterbeiträge) mit 720er-Bild', str_contains($markt, "'bild' => ma21_menue_bildnachweis(\$x[1]['bild'])"), true);
$unternehmen = (string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/unternehmen.php');
pruefe('Musterbilder haben eine 720er-Fassung', str_contains($unternehmen, "'klein' => ma21_muster_klein((string) \$i['src'])"), true);
pruefe('Firmenmenü lädt die 720er-Fassung', str_contains((string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/ma21.php'), "'bild' => ma21_menue_bildnachweis(\$b['bild'])") && str_contains($unternehmen, "['src' => ma21_abs(\$b['klein'])"), true);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
