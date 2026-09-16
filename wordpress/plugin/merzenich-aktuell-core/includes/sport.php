<?php
if (!defined('ABSPATH')) { exit; }
function ma_register_sport_hooks(): void { add_shortcode('ma_sport','ma_sport_shortcode'); }
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
        if ($mit_ergebnis) $out['score'] = (string)($m['score'] ?? '');
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
