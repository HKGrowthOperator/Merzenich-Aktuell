<?php
if (!defined('ABSPATH')) { exit; }
function ma_register_sport_hooks(): void {
    add_shortcode('ma_sport','ma_sport_shortcode');
    // Dashboard-Hinweis, sobald ein Ergebnis fehlt (1.21.0).
    add_action('admin_notices', function (): void {
        if (!current_user_can('edit_others_posts') || !function_exists('get_current_screen') || (get_current_screen()->id ?? '') !== 'dashboard') return;
        $offen = ma_sport_ueberfaellig();
        if ($offen !== '') echo '<div class="notice notice-warning"><p><strong>Sport:</strong> ' . esc_html($offen) . ' <a href="' . esc_url(admin_url('admin.php?page=ma-sport')) . '">Jetzt eintragen</a></p></div>';
    });
}
/**
 * Der eine Datenstand. wp_parse_args fuellt fehlende Schluessel, prueft aber
 * keine Typen: ein kaputter Optionswert (String statt Array, Zeile ohne
 * Schluessel) liess die Ausgabe vorher mit einem Fatal Error stehen - mitten
 * auf der Startseite. Deshalb wird hier jedes Teil auf seinen Typ gebracht.
 */
function ma_sport_data(): array {
    $d = wp_parse_args((array)get_option('ma_sport_data',[]),['checked_at'=>'','source_url'=>'','last_match'=>[],'pending_match'=>[],'next_match'=>[],'table'=>[]]);
    foreach (['last_match','pending_match','next_match'] as $k) {
        $d[$k] = is_array($d[$k]) ? array_map('strval', array_filter($d[$k], 'is_scalar')) : [];
        if (($d[$k]['home'] ?? '') === '' && ($d[$k]['away'] ?? '') === '') $d[$k] = [];
    }
    $zeilen = [];
    foreach ((is_array($d['table']) ? $d['table'] : []) as $row) {
        if (!is_array($row) || trim((string)($row['team'] ?? '')) === '') continue;
        $zeilen[] = array_map('strval', array_filter($row, 'is_scalar'));
    }
    $d['table'] = $zeilen;
    $d['checked_at'] = (string)$d['checked_at'];
    $d['source_url'] = (string)$d['source_url'];
    return $d;
}

/**
 * Datenstand aus dem JSON uebernehmen, das auch die statische Seite
 * ausliefert (api/sport-current.json). Damit gibt es EINEN Stand fuer beide
 * Auslieferungen statt zwei von Hand gepflegter.
 *
 * Erwartete Form: { generated, sourceUrl, lastMatch{date,home,away,score,
 * confirmed}, nextMatch{date,home,away}, table[{place,team,played,goals,points}] }
 *
 * @return array|WP_Error
 */
function ma_sport_import_from_json(string $json) {
    $j = json_decode($json, true);
    if (!is_array($j)) return new WP_Error('json', 'Kein gültiges JSON.');

    $datum = static function ($iso): string {
        if (!is_string($iso) || $iso === '') return '';
        try { $t = new DateTimeImmutable($iso); } catch (Exception $e) { return ''; }
        $t = $t->setTimezone(new DateTimeZone('Europe/Berlin'));
        return $t->format('d.m.Y') . ' · ' . $t->format('H:i') . ' Uhr';
    };
    $spiel = static function ($m, bool $mit_ergebnis) use ($datum): array {
        if (!is_array($m)) return [];
        $out = ['home'=>(string)($m['home'] ?? ''), 'away'=>(string)($m['away'] ?? ''), 'date'=>$datum($m['date'] ?? '')];
        if ($mit_ergebnis) { $out['score'] = (string)($m['score'] ?? ''); if (!empty($m['reportUrl'])) $out['report'] = (string)$m['reportUrl']; }
        return ($out['home'] === '' && $out['away'] === '') ? [] : $out;
    };

    $letztes = $j['lastMatch'] ?? null;
    $bestaetigt = !is_array($letztes) || !array_key_exists('confirmed', $letztes) || (bool)$letztes['confirmed'];

    $tabelle = [];
    foreach ((is_array($j['table'] ?? null) ? $j['table'] : []) as $row) {
        if (!is_array($row) || trim((string)($row['team'] ?? '')) === '') continue;
        $tabelle[] = [
            'rank'   => (string)($row['place'] ?? $row['rank'] ?? ''),
            'team'   => (string)$row['team'],
            'played' => (string)($row['played'] ?? ''),
            'wins'   => (string)($row['wins'] ?? ''),
            'draws'  => (string)($row['draws'] ?? ''),
            'losses' => (string)($row['losses'] ?? ''),
            'points' => (string)($row['points'] ?? ''),
            'goals'  => (string)($row['goals'] ?? ''),
        ];
    }

    $checked = $datum($j['generated'] ?? '');
    return [
        'checked_at'    => $checked !== '' ? $checked : (string)($j['generated'] ?? ''),
        'source_url'    => (string)($j['sourceUrl'] ?? $j['source_url'] ?? ''),
        // Ein unbestaetigtes Ergebnis wird nicht als Ergebnis gezeigt, sondern
        // als offenes Spiel - dieselbe Regel wie auf der statischen Seite.
        'last_match'    => $bestaetigt ? $spiel($letztes, true) : [],
        'pending_match' => $bestaetigt ? $spiel($j['pendingMatch'] ?? null, false) : $spiel($letztes, false),
        'next_match'    => $spiel($j['nextMatch'] ?? null, false),
        'table'         => $tabelle,
    ];
}
/** Datum aus dem Backend („18.09.2026 · 19:30 Uhr“ oder ISO) als ISO 8601 in Berliner Zeit; '' wenn unlesbar. */
function ma_sport_datum_iso(string $t): string {
    $t = trim($t);
    if ($t === '') return '';
    $zone = new DateTimeZone('Europe/Berlin');
    if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})(?:\D+(\d{1,2}):(\d{2}))?/', $t, $m)) {
        if (!checkdate((int)$m[2], (int)$m[1], (int)$m[3])) return '';
        return (new DateTimeImmutable(sprintf('%04d-%02d-%02d %02d:%02d', $m[3], $m[2], $m[1], (int)($m[4] ?? 0), (int)($m[5] ?? 0)), $zone))->format('c');
    }
    try { return (new DateTimeImmutable($t, $zone))->setTimezone($zone)->format('c'); } catch (Exception $e) { return ''; }
}

/**
 * Backend-Datenstand im Format von /api/sport-current.json (liest assets/v20.js,
 * Theme 21.11.0: eine Quelle für Sportseite, Startseite und [ma_sport]). null,
 * solange im Backend nichts gepflegt ist; dann gilt der Stand im Repository.
 */
function ma_sport_als_json(): ?array {
    $d = ma_sport_data();
    if (!$d['last_match'] && !$d['pending_match'] && !$d['next_match'] && !$d['table']) return null;
    $spiel = static fn(array $m): array => ['date' => ma_sport_datum_iso((string)($m['date'] ?? '')), 'home' => (string)($m['home'] ?? ''), 'away' => (string)($m['away'] ?? '')];
    $last = null;
    if ($d['last_match']) $last = $spiel($d['last_match']) + ['score' => (string)($d['last_match']['score'] ?? ''), 'confirmed' => true] + (!empty($d['last_match']['report']) ? ['reportUrl' => (string)$d['last_match']['report']] : []);
    elseif ($d['pending_match']) $last = $spiel($d['pending_match']) + ['score' => '', 'confirmed' => false];
    $zahl = static fn($v): int => (int)preg_replace('/[^0-9-]/', '', (string)$v);
    $tabelle = array_map(static function (array $r) use ($zahl): array {
        $tore = array_map('intval', array_pad(explode(':', str_replace(' ', '', (string)($r['goals'] ?? ''))), 2, 0));
        return ['place' => $zahl($r['rank'] ?? ''), 'team' => (string)$r['team'], 'played' => $zahl($r['played'] ?? ''), 'wins' => $zahl($r['wins'] ?? ''), 'draws' => $zahl($r['draws'] ?? ''), 'losses' => $zahl($r['losses'] ?? ''),
            'goals' => (string)($r['goals'] ?? ''), 'diff' => $tore[0] - $tore[1], 'points' => $zahl($r['points'] ?? ''), 'homeTeam' => (bool)preg_match('/merzenich/i', (string)$r['team'])];
    }, $d['table']);
    $host = (string)parse_url($d['source_url'], PHP_URL_HOST);
    return ['generated' => ma_sport_datum_iso($d['checked_at']) ?: (string)wp_date('c'), 'source' => $host !== '' ? strtoupper((string)preg_replace('/^www\./', '', $host)) : 'Redaktion', 'sourceUrl' => $d['source_url'], 'quelle' => 'backend',
        'lastMatch' => $last, 'nextMatch' => $d['next_match'] ? $spiel($d['next_match']) : null, 'table' => $tabelle];
}

/** Hinweis für die Redaktion: das nächste Spiel ist seit mehr als drei Stunden vorbei, ein neueres Ergebnis fehlt. '' = alles aktuell. */
function ma_sport_ueberfaellig(?int $jetzt = null): string {
    $d = ma_sport_data();
    if (!$d['next_match']) return '';
    $naechstes = ma_sport_datum_iso((string)($d['next_match']['date'] ?? ''));
    if ($naechstes === '' || strtotime($naechstes) + 3 * 3600 > ($jetzt ?? time())) return '';
    $letztes = $d['last_match'] ? ma_sport_datum_iso((string)($d['last_match']['date'] ?? '')) : '';
    if ($letztes !== '' && strtotime($letztes) >= strtotime($naechstes)) return '';
    return 'Das Spiel ' . trim(($d['next_match']['home'] ?? '') . ' – ' . ($d['next_match']['away'] ?? '')) . ' (' . ($d['next_match']['date'] ?? '') . ') ist vorbei. Bitte Ergebnis, nächstes Spiel und Tabelle eintragen.';
}

function ma_sport_shortcode(): string {
    $d=ma_sport_data();
    if (!$d['last_match'] && !$d['next_match'] && !$d['pending_match']) return '<p class="ma-empty">Sportdaten werden redaktionell gepflegt. Noch kein freigegebener Datenstand.</p>';
    ob_start(); ?>
    <section class="ma-sport-block">
      <?php if ($d['last_match']): ?><div class="ma-match"><small>LETZTES BESTÄTIGTES SPIEL</small><strong><?php echo esc_html($d['last_match']['home']??''); ?> <b><?php echo esc_html($d['last_match']['score']??''); ?></b> <?php echo esc_html($d['last_match']['away']??''); ?></strong><span><?php echo esc_html($d['last_match']['date']??''); ?></span></div><?php endif; ?>
      <?php if ($d['pending_match']): ?><div class="ma-match ma-match--pending"><small>ERGEBNIS NOCH NICHT BESTÄTIGT</small><strong><?php echo esc_html($d['pending_match']['home']??''); ?> – <?php echo esc_html($d['pending_match']['away']??''); ?></strong><span><?php echo esc_html($d['pending_match']['date']??''); ?></span></div><?php endif; ?>
      <?php if ($d['next_match']): ?><div class="ma-match"><small>NÄCHSTES SPIEL</small><strong><?php echo esc_html($d['next_match']['home']??''); ?> – <?php echo esc_html($d['next_match']['away']??''); ?></strong><span><?php echo esc_html($d['next_match']['date']??''); ?></span></div><?php endif; ?>
      <?php if ($d['table']): ?><div class="table-wrap"><table><thead><tr><th>Pl.</th><th>Mannschaft</th><th>Sp.</th><th>Pkt.</th><th>Tore</th></tr></thead><tbody><?php foreach($d['table'] as $row): ?><tr><td><?php echo esc_html($row['rank']??''); ?></td><td><?php echo esc_html($row['team']??''); ?></td><td><?php echo esc_html($row['played']??''); ?></td><td><?php echo esc_html($row['points']??''); ?></td><td><?php echo esc_html($row['goals']??''); ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
      <?php if ($d['checked_at']): ?><p class="ma-data-state">Datenstand: <?php echo esc_html($d['checked_at']); ?><?php if ($d['source_url']): ?> · <a href="<?php echo esc_url($d['source_url']); ?>" target="_blank" rel="noopener">Quelle</a><?php endif; ?></p><?php endif; ?>
    </section>
    <?php return (string)ob_get_clean();
}
