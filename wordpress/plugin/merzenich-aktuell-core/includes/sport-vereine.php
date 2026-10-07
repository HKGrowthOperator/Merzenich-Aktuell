<?php
/**
 * Vereinssport auf /sport/ (Plugin 1.25.0, Wunsch Betreiber 07.10.2026):
 * nur aktive Vereine und Abteilungen, also mit Mannschaften im Spielbetrieb
 * oder belegten Wettkämpfen, je Mannschaft Liga, Platz, Punkte und Link zur
 * Tabelle. Quelle ist data/sport-aktiv.json (FUSSBALL.DE, nuLiga, click-TT,
 * Verbandsseiten; Prüfdatum steht dabei). Ein neuerer Stand aus dem
 * Repository kommt täglich mit dem Abgleich, ohne neues Plugin.
 * Keine Bilder in diesem Bereich: die früheren selbstgezeichneten
 * Sportart-Grafiken sind entfernt. Das Vereinsverzeichnis bleibt unverändert.
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
        'fussball' => 'Fußball', 'tischtennis' => 'Tischtennis', 'tennis' => 'Tennis',
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

const MA_SPORT_AKTIV_QUELLE = 'https://raw.githubusercontent.com/HKGrowthOperator/Merzenich-Aktuell/main/wordpress/plugin/merzenich-aktuell-core/data/sport-aktiv.json';
const MA_SPORT_AKTIV_REIHE = ['fussball', 'tischtennis', 'tennis', 'billard', 'schach'];

/** Gültiger Datenstand: {stand, vereine:[…]} mit Stand JJJJ-MM-TT. */
function ma_sport_aktiv_gueltig($d): bool {
    return is_array($d) && is_array($d['vereine'] ?? null) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($d['stand'] ?? ''));
}

/** Der neuere Stand aus mitgelieferter Datei und täglich geholter Fassung. */
function ma_sport_aktiv_daten(): array {
    $datei = MA_CORE_PATH . 'data/sport-aktiv.json';
    $mit = is_readable($datei) ? json_decode((string) file_get_contents($datei), true) : null;
    $geholt = get_option('ma_sport_aktiv_geholt');
    $mit = ma_sport_aktiv_gueltig($mit) ? $mit : ['stand' => '0000-00-00', 'vereine' => []];
    return ma_sport_aktiv_gueltig($geholt) && $geholt['stand'] > $mit['stand'] ? $geholt : $mit;
}

/* Einmal täglich mit dem Abgleich: neueren Tabellenstand aus dem Repository holen. */
add_action(defined('MA_ABGLEICH_CRON') ? MA_ABGLEICH_CRON : 'ma_abgleich_stuendlich', function (): void {
    if (get_transient('ma_sport_aktiv_lauf')) return;
    set_transient('ma_sport_aktiv_lauf', 1, 20 * HOUR_IN_SECONDS);
    $r = wp_remote_get(MA_SPORT_AKTIV_QUELLE . '?t=' . time(), ['timeout' => 15]);
    if (is_wp_error($r) || (int) wp_remote_retrieve_response_code($r) !== 200) return;
    $d = json_decode((string) wp_remote_retrieve_body($r), true);
    if (ma_sport_aktiv_gueltig($d)) update_option('ma_sport_aktiv_geholt', $d, false);
}, 30);

/**
 * Vereine nach Sportart in fester Reihenfolge (pure Funktion, testbar).
 * Nur Einträge mit Mannschaft oder Wettkampf; Fußball je Liga sortiert.
 */
function ma_sport_aktiv_gruppen(array $daten): array {
    $g = [];
    foreach ((array) ($daten['vereine'] ?? []) as $v) {
        if (!is_array($v) || empty($v['name']) || (empty($v['teams']) && empty($v['turniere']) && empty($v['ohneTabelle']))) continue;
        $g[(string) ($v['sportart'] ?? 'sport')][] = $v;
    }
    uksort($g, static function ($a, $b): int {
        $ia = array_search($a, MA_SPORT_AKTIV_REIHE, true); $ib = array_search($b, MA_SPORT_AKTIV_REIHE, true);
        return ($ia === false ? 99 : $ia) <=> ($ib === false ? 99 : $ib);
    });
    return $g;
}

/**
 * Lokalvergleich: die erste Mannschaft jedes Vereins einer Sportart, nach
 * Liga (A vor B vor C) und Platz. Nur bei mindestens zwei Vereinen.
 */
function ma_sport_aktiv_vergleich(array $vereine): array {
    $z = [];
    foreach ($vereine as $v) {
        $t = $v['teams'][0] ?? null;
        if (!$t || !is_numeric($t['platz'] ?? null)) continue;
        $z[] = ['verein' => (string) $v['name'], 'liga' => (string) $t['liga'], 'platz' => (int) $t['platz'], 'punkte' => (string) $t['punkte'], 'url' => (string) ($t['tabelleUrl'] ?? '')];
    }
    usort($z, static fn($a, $b) => [$a['liga'], $a['platz']] <=> [$b['liga'], $b['platz']]);
    return count($z) >= 2 ? $z : [];
}

/** „E-Junioren (3 Teams), F-Junioren (3 Teams), G-Junioren“. */
function ma_sport_ohne_tabelle(array $namen): string {
    $n = [];
    foreach ($namen as $name) { $k = trim((string) preg_replace('/\s+(II|III|IV|V)$/u', '', (string) $name)); $n[$k] = ($n[$k] ?? 0) + 1; }
    $teile = [];
    foreach ($n as $k => $anzahl) $teile[] = $k . ($anzahl > 1 ? ' (' . $anzahl . ' Teams)' : '');
    return implode(', ', $teile);
}

/** Link zum Vereinsprofil auf der Seite, sonst die Vereinsseite. */
function ma_sport_aktiv_link(array $v): string {
    if (!function_exists('ma_vereine_struktur') || !function_exists('ma_verein_profil')) return '';
    foreach (ma_vereine_struktur() as $kurz => $s) {
        if (($s['verein']['name'] ?? '') !== ($v['verein'] ?? '')) continue;
        $profil = ma_verein_profil((string) $kurz);
        if ($profil && $profil->post_status === 'publish') return (string) get_permalink($profil);
        return (string) ($s['verein']['website'] ?? '');
    }
    return '';
}

function ma_sport_datum(string $iso): string {
    $t = strtotime($iso);
    return $t ? date('d.m.Y', $t) : '';
}

/** Bereich „Vereinssport: Tabellenstand“ im Sportarchiv. */
function ma_sport_vereinsraster(): string {
    $daten = ma_sport_aktiv_daten();
    $gruppen = ma_sport_aktiv_gruppen($daten);
    if (!$gruppen) return '';
    $anzahl = array_sum(array_map('count', $gruppen));
    $teams = 0;
    foreach ($gruppen as $vs) foreach ($vs as $v) $teams += count($v['teams'] ?? []) + count($v['ohneTabelle'] ?? []);
    $orte = ['merzenich' => 'Merzenich', 'golzheim' => 'Golzheim', 'girbelsrath' => 'Girbelsrath', 'morschenich' => 'Morschenich', 'buergewald' => 'Bürgewald'];
    $extern = static fn(string $u): string => strpos($u, home_url()) === 0 ? '' : ' target="_blank" rel="noopener noreferrer"';
    ob_start(); ?>
    <section class="ma-breitensport ma-tabellen" aria-labelledby="ma-breitensport-titel">
      <div class="ma-breitensport__kopf">
        <span class="eyebrow">VEREINSSPORT · TABELLENSTAND</span>
        <h2 id="ma-breitensport-titel">Wer spielt wo: unsere Vereine im Wettkampf</h2>
        <p><?php echo esc_html($anzahl); ?> Vereine und Abteilungen mit <?php echo esc_html($teams); ?> Mannschaften im Spielbetrieb. Zwischenstand vom <?php echo esc_html(ma_sport_datum((string) $daten['stand'])); ?>, jede Zeile führt zur Tabelle beim Verband.</p>
        <nav class="ma-breitensport__navigation" aria-label="Sportarten">
          <?php foreach ($gruppen as $art => $vs): ?><a href="#ma-sportart-<?php echo esc_attr($art); ?>"><?php echo esc_html(ma_sportart_titel($art)); ?> <small><?php echo count($vs); ?></small></a><?php endforeach; ?>
        </nav>
      </div>
      <?php foreach ($gruppen as $art => $vs): $vergleich = ma_sport_aktiv_vergleich($vs); ?>
      <div class="ma-sportart-block" id="ma-sportart-<?php echo esc_attr($art); ?>">
        <div class="ma-sportart-block__kopf"><h3><?php echo esc_html(ma_sportart_titel($art)); ?></h3><span><?php echo count($vs) === 1 ? '1 Verein' : count($vs) . ' Vereine'; ?></span></div>
        <?php if ($vergleich && $art === 'fussball'): ?>
        <div class="ma-tabellen__vergleich"><h4>Erste Mannschaften im Vergleich</h4><ol>
          <?php foreach ($vergleich as $z): ?><li><span class="ma-tabellen__platz"><?php echo (int) $z['platz']; ?>.</span> <b><?php echo esc_html($z['verein']); ?></b> <span><?php echo esc_html($z['liga']); ?> · <?php echo esc_html($z['punkte']); ?> Punkte</span></li><?php endforeach; ?>
        </ol></div>
        <?php endif; ?>
        <div class="ma-sportart-raster ma-tabellen__raster">
          <?php foreach ($vs as $v): $link = ma_sport_aktiv_link($v); ?>
          <article class="ma-sportverein ma-tabellen__verein">
            <div class="ma-sportverein__info">
              <span class="ma-sportverein__ort"><?php echo esc_html($orte[$v['ort'] ?? ''] ?? 'Gemeinde Merzenich'); ?></span>
              <h4><?php if ($link): ?><a href="<?php echo esc_url($link); ?>"<?php echo $extern($link); ?>><?php echo esc_html($v['name']); ?></a><?php else: echo esc_html($v['name']); endif; ?></h4>
            </div>
            <?php if (!empty($v['teams'])): ?>
            <table class="ma-tabellen__tabelle">
              <thead><tr><th scope="col">Mannschaft · Liga</th><th scope="col">Platz</th><th scope="col">Punkte</th></tr></thead>
              <tbody>
              <?php foreach ($v['teams'] as $t): $platz = is_numeric($t['platz'] ?? null) ? (int) $t['platz'] . '.' : '–'; ?>
                <tr>
                  <th scope="row"><?php if (!empty($t['tabelleUrl'])): ?><a href="<?php echo esc_url($t['tabelleUrl']); ?>" target="_blank" rel="noopener noreferrer nofollow"><?php echo esc_html($t['mannschaft']); ?> ↗</a><?php else: echo esc_html($t['mannschaft']); endif; ?>
                    <small><?php echo esc_html($t['liga']); ?> · <?php echo esc_html($t['saison']); ?><?php echo (int) ($t['spiele'] ?? 0) > 0 ? ' · ' . (int) $t['spiele'] . ' Spiele' : ' · noch kein Spiel'; ?></small></th>
                  <td><?php echo esc_html($platz); ?></td>
                  <td><?php echo esc_html((string) ($t['punkte'] ?? '') !== '' && (int) ($t['spiele'] ?? 0) > 0 ? $t['punkte'] : '–'); ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
            <?php endif; ?>
            <?php if (!empty($v['ohneTabelle'])): ?><p class="ma-tabellen__extra"><b>Kinderfußball</b> (Spielrunden ohne Tabelle): <?php echo esc_html(ma_sport_ohne_tabelle($v['ohneTabelle'])); ?></p><?php endif; ?>
            <?php if (!empty($v['turniere'])): ?><ul class="ma-tabellen__extra">
              <?php foreach ($v['turniere'] as $e): ?><li><b><?php echo esc_html(ma_sport_datum((string) ($e['datum'] ?? ''))); ?></b> <?php if (!empty($e['quelle'])): ?><a href="<?php echo esc_url($e['quelle']); ?>" target="_blank" rel="noopener noreferrer nofollow"><?php echo esc_html($e['titel']); ?></a><?php else: echo esc_html($e['titel']); endif; ?></li><?php endforeach; ?>
            </ul><?php endif; ?>
            <p class="ma-tabellen__quelle">Quelle: <?php if (!empty($v['quelle'])): ?><a href="<?php echo esc_url($v['quelle']); ?>" target="_blank" rel="noopener noreferrer nofollow"><?php echo esc_html($v['quelleName'] ?? ''); ?></a><?php else: echo esc_html($v['quelleName'] ?? ''); endif; ?> · geprüft <?php echo esc_html(ma_sport_datum((string) ($v['geprueft'] ?? $daten['stand']))); ?></p>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <p class="ma-breitensport__quelle">Hier stehen nur Vereine mit Mannschaften im Spielbetrieb oder Wettkämpfen im letzten Jahr. Alle Sportvereine der Gemeinde stehen im <a href="<?php echo esc_url(home_url('/vereine/')); ?>">Vereinsverzeichnis</a>. Fehlt eine Mannschaft? <a href="<?php echo esc_url(home_url('/meldung-senden/')); ?>">An die Redaktion senden</a>.</p>
    </section>
    <?php return (string) ob_get_clean();
}
