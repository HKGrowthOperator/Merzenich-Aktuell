<?php
/**
 * Foto-Schlüssel (17.10.2026): dasselbe Foto erkennen, auch wenn es als mehrere
 * Anhänge oder in mehreren Breiten vorliegt. Abgleich (ma_image_static_src) und
 * Bildpool (Datei aus editorial-pools) legen dasselbe Foto als zwei Anhänge an;
 * die Pipeline erzeugt Breiten wie -720, -1200, -1440.
 *
 * - h:<name-prüfsumme>  Dateien der Pipeline mit Prüfsumme (kirchedetail-01-995c6c09d8)
 * - p:<ordner>/<name>   übrige Dateien unter /assets/ (places/buergewald), ohne erzeugte Breite
 * - a:<id>              alles andere: der Anhang selbst (zwei „plakat.jpg“ bleiben zwei Fotos)
 *
 * Dieselben Regeln stehen im Theme (ma21_foto_schluessel); qa/wordpress/bildwiederholung-test.php
 * vergleicht beide.
 */
if (!defined('ABSPATH')) { exit; }

/** Breiten, die die Pipeline an Dateinamen hängt (deploy/, chatgpt-site/assets). */
const MA_FOTO_BREITEN = '240|320|360|480|526|590|600|640|720|768|800|960|1080|1200|1440|1600|1920';

/** Schlüssel aus einem Pfad oder einer Adresse; '' = keine feste Kennung (dann zählt der Anhang). */
function ma_foto_schluessel_pfad(string $pfad): string {
    $pfad = strtolower((string) preg_replace('#[?\#].*$#', '', $pfad));
    $name = (string) preg_replace('#\.[a-z0-9]+$#', '', basename($pfad));
    $name = (string) preg_replace('#-\d+x\d+$#', '', $name);
    $name = (string) preg_replace('#-(scaled|rotated)$#', '', $name);
    if (preg_match('#^((?:.*-)?(?=[0-9a-f]*[a-f])[0-9a-f]{10,})(?:-\d{2,4})?(?:-\d)?$#', $name, $m)) return 'h:' . $m[1];
    $i = strpos($pfad, '/assets/');
    if ($i === false) return '';
    $ordner = dirname(substr($pfad, $i + 8));
    $name = (string) preg_replace('#-(?:' . MA_FOTO_BREITEN . ')$#', '', $name);
    return 'p:' . ($ordner === '.' ? '' : $ordner . '/') . $name;
}

/** Schlüssel eines Anhangs (Medien-ID). Die Meta-Werte hält WordPress im Objekt-Cache. */
function ma_foto_schluessel(int $anhang): string {
    if (!$anhang) return '';
    $k = ma_foto_schluessel_pfad((string) get_post_meta($anhang, 'ma_image_static_src', true));
    if ($k === '') { $k = ma_foto_schluessel_pfad('/' . ltrim((string) get_post_meta($anhang, '_wp_attached_file', true), '/')); if (!str_starts_with($k, 'h:')) $k = ''; }
    return $k !== '' ? $k : 'a:' . $anhang;
}
