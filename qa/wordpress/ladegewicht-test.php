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
foreach (['logo.png', 'logo-on-light.png'] as $logo) {
    $g = getimagesize($assets . 'img/' . $logo);
    pruefe("$logo unter 100 KB, Seitenverhältnis wie im Markup (600×169)", [filesize($assets . 'img/' . $logo) <= 100 * 1024, abs($g[0] / $g[1] - 600 / 169) < 0.02], [true, true]);
}

echo "\nMusterprofile laden die kleine Fassung\n";
$markt = (string) file_get_contents(__DIR__ . '/../../wordpress/theme/merzenich-aktuell/inc/markt.php');
pruefe('Kasten mit srcset 720w/1080w', str_contains($markt, "' 720w, ' . home_url(\$m['bild']) . ' 1080w'"), true);
pruefe('Unternehmen-Menü (Musterbeiträge) mit 720er-Bild', str_contains($markt, "'src' => home_url(ma21_muster_klein(\$m['bild']))"), true);

echo $fehler ? "\n$fehler Fehler\n" : "\nAlle Prüfungen bestanden\n";
exit($fehler ? 1 : 0);
