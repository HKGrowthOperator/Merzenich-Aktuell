<?php
if (!defined('ABSPATH')) { exit; }
function ma_register_sport_hooks(): void { add_shortcode('ma_sport','ma_sport_shortcode'); }
function ma_sport_data(): array { return wp_parse_args((array)get_option('ma_sport_data',[]),['checked_at'=>'','source_url'=>'','last_match'=>[],'pending_match'=>[],'next_match'=>[],'table'=>[]]); }
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
