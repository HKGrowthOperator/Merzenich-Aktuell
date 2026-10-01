<?php
/**
 * Bildstarker Breitensport-/Vereinsbereich für /sport/.
 *
 * Quelle der Vereine: bestehendes, geprüftes Vereinsverzeichnis; keine zweite
 * Vereinsdatenbank. Eine Abteilung kann eine eigene Sport-Karte haben, aber
 * verwendet weiterhin das Profil ihres Hauptvereins.
 * Bilder: freigegebenes Abteilungsfoto > freigegebener Medienpool > eigene
 * klar als solche bezeichnete Symbolgrafik. Keine fremden Vereinsfotos scrapen.
 */
if (!defined('ABSPATH')) { exit; }

function ma_sportart_schluessel(string $name): string {
    $n = strtolower(remove_accents($name));
    // Mehrsparten-Bezeichnungen wie "Turnen/Breitensport (u. a. Tischtennis)"
    // sind ein Vereinsprofil, kein separater Tischtennis-Eintrag.
    if (str_contains($n, 'turnen') || str_contains($n, 'breitensport')) return 'breitensport';
    if (str_contains($n, 'pickleball')) return 'pickleball';
    if (str_contains($n, 'volleyball')) return 'volleyball';
    if (str_contains($n, 'boule')) return 'boule';
    if (str_contains($n, 'ju-jutsu') || str_contains($n, 'ju jutsu')) return 'ju-jutsu';
    if (str_contains($n, 'aqua') || str_contains($n, 'schwimmen')) return 'wasser';
    if (str_contains($n, 'fitness') || str_contains($n, 'gymnastik') || str_contains($n, 'zumba') || str_contains($n, 'qi gong')) return 'fitness';
    if (str_contains($n, 'wandern')) return 'wandern';
    if (str_contains($n, 'tischtennis')) return 'tischtennis';
    if (str_contains($n, 'badminton')) return 'badminton';
    if (str_contains($n, 'tennis')) return 'tennis';
    if (str_contains($n, 'billard')) return 'billard';
    if (str_contains($n, 'schach')) return 'schach';
    if (str_contains($n, 'schiess')) return 'schiesssport';
    if (str_contains($n, 'luftsport') || str_contains($n, 'ultraleicht')) return 'luftsport';
    if (str_contains($n, 'discofox') || str_contains($n, 'tanzsport')) return 'tanzsport';
    if (str_contains($n, 'american football')) return 'american-football';
    return '';
}

function ma_sportart_titel(string $k): string {
    $titel = [
        'tischtennis' => 'Tischtennis', 'tennis' => 'Tennis',
        'badminton' => 'Badminton', 'billard' => 'Billard',
        'schach' => 'Schach', 'breitensport' => 'Turnen & Breitensport',
        'schiesssport' => 'Sportschießen', 'tanzsport' => 'Tanzsport',
        'luftsport' => 'Luftsport', 'american-football' => 'American Football',
        'pickleball' => 'Pickleball', 'volleyball' => 'Volleyball',
        'boule' => 'Boule', 'ju-jutsu' => 'Ju-Jutsu',
        'wasser' => 'Schwimmen & Aquafitness', 'fitness' => 'Fitness & Gesundheit',
        'wandern' => 'Wandern',
    ];
    return $titel[$k] ?? 'Sport';
}

/**
 * Ein Originalfoto ist je Abteilung als ma_sportfoto_<sportart> am ma_club
 * hinterlegt (Attachment-ID). Rechte und Quelle liegen am Attachment.
 * Medienpool: ma_sportpool=<sportart> plus ma_image_rights_verified=1;
 * die Auswahl ist pro Verein stabil, nicht zufällig bei jedem Seitenaufruf.
 */
function ma_sport_vereinsbild(string $kurz, string $art, ?WP_Post $profil): array {
    $ids = [];
    if ($profil) $ids[] = (int) get_post_meta($profil->ID, 'ma_sportfoto_' . $art, true);
    foreach ($ids as $id) {
        if ($id > 0 && wp_attachment_is_image($id)
            && (string) get_post_meta($id, 'ma_image_rights_verified', true) === '1'
            && (string) get_post_meta($id, 'ma_image_credit', true) !== ''
            && (string) get_post_meta($id, 'ma_image_license', true) !== '') {
            $url = wp_get_attachment_image_url($id, 'medium_large');
            if ($url) return [
                'src' => $url, 'alt' => (string) get_post_meta($id, '_wp_attachment_image_alt', true) ?: ma_sportart_titel($art),
                'credit' => (string) get_post_meta($id, 'ma_image_credit', true),
                'source' => (string) get_post_meta($id, 'ma_image_original_url', true),
                'type' => 'Originalbild / freigegebenes Vereinsfoto',
            ];
        }
    }
    $pool = get_posts([
        'post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image',
        'posts_per_page' => 30, 'orderby' => 'ID', 'order' => 'ASC',
        'meta_query' => [
            'relation' => 'AND',
            ['key' => 'ma_sportpool', 'value' => $art, 'compare' => '='],
            ['key' => 'ma_image_rights_verified', 'value' => '1', 'compare' => '='],
            ['key' => 'ma_image_license', 'compare' => 'EXISTS'],
            ['key' => 'ma_image_credit', 'compare' => 'EXISTS'],
        ],
    ]);
    $pool = array_values(array_filter($pool, static function ($p): bool {
        return $p instanceof WP_Post && wp_attachment_is_image($p->ID)
            && trim((string) get_post_meta($p->ID, 'ma_image_license', true)) !== ''
            && trim((string) get_post_meta($p->ID, 'ma_image_credit', true)) !== '';
    }));
    if ($pool) {
        $start = hexdec(substr(md5($kurz . ':' . $art), 0, 6)) % count($pool);
        $id = $pool[$start]->ID;
        $url = wp_get_attachment_image_url($id, 'medium_large');
        if ($url) return [
            'src' => $url, 'alt' => (string) get_post_meta($id, '_wp_attachment_image_alt', true) ?: 'Symbolbild: ' . ma_sportart_titel($art),
            'credit' => (string) get_post_meta($id, 'ma_image_credit', true),
            'source' => (string) get_post_meta($id, 'ma_image_original_url', true),
            'type' => 'Symbolbild',
        ];
    }
    // Zusätzlich fest im Theme mitgelieferte, bereits redaktionell gesichtete
    // Commons-Fotos. Die Quelldateien und ihre Lizenz stehen im Manifest.
    static $lieferfotos = null;
    if ($lieferfotos === null) {
        $datei = MA_CORE_PATH . 'data/sport-fotopool.json';
        $json = is_readable($datei) ? json_decode((string) file_get_contents($datei), true) : [];
        $lieferfotos = is_array($json['pools'] ?? null) ? $json['pools'] : [];
    }
    $auswahl = $lieferfotos[$art] ?? [];
    if (is_array($auswahl) && $auswahl) {
        $index = hexdec(substr(md5($kurz . ':' . $art), 0, 6)) % count($auswahl);
        $foto = $auswahl[$index];
        if (!empty($foto['datei']) && !empty($foto['license']) && !empty($foto['credit']) && !empty($foto['sourceUrl'])) {
            return [
                'src' => get_template_directory_uri() . '/assets/img/sportfotos/' . $foto['datei'],
                'alt' => (string) ($foto['alt'] ?? ('Symbolbild: ' . ma_sportart_titel($art))),
                'credit' => (string) $foto['credit'] . ' · ' . $foto['license'],
                'source' => (string) $foto['sourceUrl'],
                'license_url' => (string) ($foto['licenseUrl'] ?? ''),
                'type' => 'Symbolbild / Archivfoto (kein Foto des Vereins)',
            ];
        }
    }
    return [
        'src' => get_template_directory_uri() . '/assets/img/sportarten/' . $art . '.svg',
        'alt' => 'Symbolgrafik: ' . ma_sportart_titel($art),
        'credit' => 'Merzenich Aktuell', 'source' => '', 'type' => 'Symbolgrafik',
    ];
}

/** Integration in das vorhandene WP-Sportarchiv; bestehendes Fußballmodul bleibt. */
function ma_sport_vereinsraster(): string {
    if (!function_exists('ma_vereine_struktur') || !function_exists('ma_verein_profil')) return '';
    $reihenfolge = ['tischtennis', 'tennis', 'badminton', 'pickleball', 'volleyball',
        'billard', 'boule', 'schach', 'ju-jutsu', 'breitensport', 'wasser', 'fitness',
        'wandern', 'schiesssport', 'tanzsport', 'luftsport', 'american-football'];
    $gruppen = array_fill_keys($reihenfolge, []);
    $gesehen = [];
    $orte = ['merzenich'=>'Merzenich','golzheim'=>'Golzheim','girbelsrath'=>'Girbelsrath',
        'morschenich'=>'Morschenich','buergewald'=>'Bürgewald'];
    foreach (ma_vereine_struktur() as $kurz => $struktur) {
        $haupt = $struktur['verein'] ?? [];
        if (($haupt['kategorie'] ?? '') !== 'Sport') continue;
        $profil = ma_verein_profil((string) $kurz);
        foreach (array_merge([$haupt], $struktur['abteilungen'] ?? []) as $eintrag) {
            $art = ma_sportart_schluessel((string) ($eintrag['sportart'] ?? ''));
            if (!$art || isset($gesehen[$kurz . '/' . $art])) continue;
            $gesehen[$kurz . '/' . $art] = true;
            // Kein Fußball-Eintrag doppelt: nur Nicht-Fußball-Sport bzw. eigene Abteilung.
            $url = '';
            if ($profil && $profil->post_status === 'publish') $url = get_permalink($profil);
            elseif (!empty($eintrag['website'])) $url = (string) $eintrag['website'];
            elseif (!empty($haupt['website'])) $url = (string) $haupt['website'];
            $gruppen[$art][] = [
                'name' => (string) ($eintrag['name'] ?? $haupt['name'] ?? ''),
                'ort' => $orte[$eintrag['ort'] ?? ''] ?? 'Gemeinde Merzenich',
                'url' => $url,
                'hinweis' => $art === 'american-football' ? 'Vereinssitz in Merzenich · Spielbetrieb in Düren' : '',
                'bild' => ma_sport_vereinsbild((string) $kurz, $art, $profil),
            ];
        }
    }
    // Weitere im aktuellen Vereins-/Hallenplan ausdrücklich belegte Angebote
    // (keine neuen Rechtsträger, keine künstlichen Untervereinsprofile).
    $pfad = MA_CORE_PATH . 'data/sport-angebote.json';
    $angeboteDaten = is_readable($pfad) ? json_decode((string) file_get_contents($pfad), true) : null;
    $angebote = is_array($angeboteDaten['angebote'] ?? null) ? $angeboteDaten['angebote'] : [];
    foreach ($angebote as $angebot) {
        $vereinName = (string) ($angebot['verein'] ?? '');
        $art = ma_sportart_schluessel((string) ($angebot['sportart'] ?? ''));
        if (!$art || !isset($gruppen[$art]) || $vereinName === '') continue;
        foreach (ma_vereine_struktur() as $kurz => $struktur) {
            $haupt = $struktur['verein'] ?? [];
            if (($haupt['name'] ?? '') !== $vereinName || ($haupt['kategorie'] ?? '') !== 'Sport') continue;
            if (isset($gesehen[$kurz . '/' . $art])) break;
            $gesehen[$kurz . '/' . $art] = true;
            $profil = ma_verein_profil((string) $kurz);
            $quelle = (string) ($angebot['quelle'] ?? '');
            $url = $profil && $profil->post_status === 'publish' ? get_permalink($profil) : $quelle;
            $gruppen[$art][] = [
                'name' => (string) ($angebot['titel'] ?? ma_sportart_titel($art)) . ' · ' . $haupt['name'],
                'ort' => $orte[$haupt['ort'] ?? ''] ?? 'Gemeinde Merzenich',
                'url' => $url,
                'hinweis' => 'Belegtes Sportangebot des Hauptvereins · keine eigenständige Vereinsabteilung',
                'bild' => ma_sport_vereinsbild((string) $kurz, $art, $profil),
            ];
            break;
        }
    }
    $anzahl = array_sum(array_map('count', $gruppen));
    if (!$anzahl) return '';
    ob_start(); ?>
    <section class="ma-breitensport" aria-labelledby="ma-breitensport-titel">
      <div class="ma-breitensport__kopf">
        <span class="eyebrow">VEREINSSPORT</span>
        <h2 id="ma-breitensport-titel">Mehr als Fußball: Sport in unserer Gemeinde</h2>
        <p><?php echo esc_html($anzahl); ?> Vereins- und Abteilungsangebote aus Merzenich und seinen Ortsteilen. Originalfotos erscheinen nur nach geklärter Bildfreigabe; ansonsten zeigt die Karte ein gekennzeichnetes Sportmotiv.</p>
        <nav class="ma-breitensport__navigation" aria-label="Sportarten">
          <?php foreach ($reihenfolge as $art) if ($gruppen[$art]): ?>
            <a href="#ma-sportart-<?php echo esc_attr($art); ?>"><?php echo esc_html(ma_sportart_titel($art)); ?> <small><?php echo count($gruppen[$art]); ?></small></a>
          <?php endif; ?>
        </nav>
      </div>
      <?php foreach ($reihenfolge as $art): if (!$gruppen[$art]) continue; ?>
      <div class="ma-sportart-block" id="ma-sportart-<?php echo esc_attr($art); ?>">
        <div class="ma-sportart-block__kopf"><h3><?php echo esc_html(ma_sportart_titel($art)); ?></h3><span><?php echo count($gruppen[$art]); ?> <?php echo count($gruppen[$art]) === 1 ? 'Eintrag' : 'Einträge'; ?></span></div>
        <div class="ma-sportart-raster">
          <?php foreach ($gruppen[$art] as $v): $bild = $v['bild']; ?>
            <article class="ma-sportverein">
              <figure class="ma-sportverein__bild">
                <img src="<?php echo esc_url($bild['src']); ?>" alt="<?php echo esc_attr($bild['alt']); ?>" loading="lazy" decoding="async">
                <figcaption><?php echo esc_html($bild['type']); ?> · <?php echo esc_html($bild['credit']); ?>
                  <?php if ($bild['source']): ?> · <a href="<?php echo esc_url($bild['source']); ?>" target="_blank" rel="noopener noreferrer">Bildquelle</a><?php endif; ?>
                  <?php if (!empty($bild['license_url'])): ?> · <a href="<?php echo esc_url($bild['license_url']); ?>" target="_blank" rel="noopener noreferrer">Lizenz</a><?php endif; ?>
                </figcaption>
              </figure>
              <div class="ma-sportverein__info">
                <span class="ma-sportverein__ort"><?php echo esc_html($v['ort']); ?></span>
                <h4><?php if ($v['url']): ?><a href="<?php echo esc_url($v['url']); ?>"<?php echo (strpos($v['url'], home_url()) === 0) ? '' : ' target="_blank" rel="noopener noreferrer"'; ?>><?php echo esc_html($v['name']); ?></a><?php else: echo esc_html($v['name']); endif; ?></h4>
                <?php if ($v['hinweis']): ?><p><?php echo esc_html($v['hinweis']); ?></p><?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <p class="ma-breitensport__quelle">Vereinsdaten: Vereinsverzeichnis Gemeinde Merzenich (im Plugin dokumentiert). Hinweise oder Vereinsfotos mit Nutzungserlaubnis: <a href="<?php echo esc_url(home_url('/meldung-senden/')); ?>">an die Redaktion senden</a>.</p>
    </section>
    <?php return (string) ob_get_clean();
}
