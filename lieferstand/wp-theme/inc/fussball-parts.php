<?php
/**
 * Ausgabebausteine für den SC 1919 Merzenich: Tabelle, Spielplan, Sidebar-Box.
 * Die Daten liefert inc/fussball-data.php, erzeugt aus src/data.js.
 *
 * @package MerzenichAktuell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Datum eines Spiels als Zeitstempel. */
function ma8_fb_ts( $iso ) {
	return strtotime( $iso );
}

/** Eine Spielzeile. */
function ma8_fb_match( $m ) {
	$ts = ma8_fb_ts( $m['start'] );
	?>
	<li class="match">
		<span class="d"><b><?php echo esc_html( wp_date( 'j', $ts ) ); ?></b><span><?php echo esc_html( wp_date( 'M', $ts ) ); ?></span></span>
		<span class="t">
			<span class="ha <?php echo $m['heim'] ? 'home' : 'away'; ?>"><?php echo $m['heim'] ? esc_html__( 'Heim', 'merzenich-aktuell' ) : esc_html__( 'Auswärts', 'merzenich-aktuell' ); ?></span>
			<b><?php echo esc_html( $m['gegner'] ); ?></b>
			<small><?php echo esc_html( wp_date( 'H:i', $ts ) ); ?> Uhr · <?php echo esc_html( $m['wettbewerb'] ); ?></small>
		</span>
	</li>
	<?php
}

/** Ligatabelle. $limit begrenzt auf die vorderen Plätze. */
function ma8_fb_table( $limit = 0 ) {
	$fb   = ma8_fussball();
	$rows = $limit ? array_slice( $fb['tabelle'], 0, $limit ) : $fb['tabelle'];
	?>
	<div class="table-wrap"><table class="liga">
		<caption><?php printf( esc_html__( 'Saison %1$s · Stand %2$s · Quelle FUSSBALL.DE', 'merzenich-aktuell' ), esc_html( $fb['saison'] ), esc_html( wp_date( 'd.m.Y', strtotime( $fb['abgerufen'] ) ) ) ); ?></caption>
		<thead><tr>
			<th><?php esc_html_e( 'Pl.', 'merzenich-aktuell' ); ?></th>
			<th><?php esc_html_e( 'Mannschaft', 'merzenich-aktuell' ); ?></th>
			<th><?php esc_html_e( 'Sp.', 'merzenich-aktuell' ); ?></th>
			<th>S</th><th>U</th><th>N</th>
			<th><?php esc_html_e( 'Tore', 'merzenich-aktuell' ); ?></th>
			<th><?php esc_html_e( 'Diff', 'merzenich-aktuell' ); ?></th>
			<th><?php esc_html_e( 'Pkt.', 'merzenich-aktuell' ); ?></th>
		</tr></thead>
		<tbody>
		<?php foreach ( $rows as $r ) : ?>
			<tr<?php echo ! empty( $r['self'] ) ? ' class="self"' : ''; ?>>
				<td><?php echo (int) $r['pl']; ?></td>
				<td class="tm"><?php echo esc_html( $r['team'] ); ?></td>
				<td><?php echo (int) $r['sp']; ?></td>
				<td><?php echo (int) $r['g']; ?></td>
				<td><?php echo (int) $r['u']; ?></td>
				<td><?php echo (int) $r['v']; ?></td>
				<td><?php echo esc_html( $r['tore'] ); ?></td>
				<td><?php echo esc_html( ( $r['diff'] > 0 ? '+' : '' ) . $r['diff'] ); ?></td>
				<td class="pkt"><?php echo (int) $r['pkt']; ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table></div>
	<?php
}

/** Vier Kennzahlen zum aktuellen Stand. */
function ma8_fb_stats() {
	$st = ma8_fussball()['stand'];
	?>
	<div class="stat-grid">
		<div class="stat"><b><?php echo (int) $st['platz']; ?></b><span><?php esc_html_e( 'Tabellenplatz', 'merzenich-aktuell' ); ?></span></div>
		<div class="stat"><b><?php echo (int) $st['punkte']; ?></b><span><?php esc_html_e( 'Punkte', 'merzenich-aktuell' ); ?></span></div>
		<div class="stat"><b><?php echo esc_html( $st['tore'] ); ?></b><span><?php esc_html_e( 'Torverhältnis', 'merzenich-aktuell' ); ?></span></div>
		<div class="stat"><b><?php echo (int) $st['spiele']; ?></b><span><?php echo esc_html( _n( 'Spiel', 'Spiele', (int) $st['spiele'], 'merzenich-aktuell' ) ); ?></span></div>
	</div>
	<?php
}

/** Sidebar-Box mit Platzierung und nächstem Spiel. */
function ma8_box_sc() {
	$fb   = ma8_fussball();
	$next = isset( $fb['spielplan'][0] ) ? $fb['spielplan'][0] : null;
	$page = get_page_by_path( 'sc-merzenich' );
	$url  = $page ? get_permalink( $page ) : home_url( '/sc-merzenich/' );
	?>
	<div class="sidebox sc-box">
		<h3><?php echo esc_html( $fb['club'] ); ?> <a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Vereinsseite', 'merzenich-aktuell' ); ?></a></h3>
		<a class="sc-badge" href="<?php echo esc_url( $url ); ?>">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/' . $fb['logo'] ); ?>" alt="<?php esc_attr_e( 'Vereinswappen des SC 1919 Merzenich', 'merzenich-aktuell' ); ?>" width="120" height="120" loading="lazy" decoding="async">
			<span>
				<b><?php printf( esc_html__( 'Platz %d', 'merzenich-aktuell' ), (int) $fb['stand']['platz'] ); ?></b>
				<small><?php echo esc_html( $fb['liga'] . ' · ' . $fb['stand']['punkte'] . ' Punkte · ' . $fb['stand']['tore'] ); ?></small>
			</span>
		</a>
		<?php if ( $next ) : $ts = ma8_fb_ts( $next['start'] ); ?>
			<p class="sc-next">
				<span><?php esc_html_e( 'Nächstes Spiel', 'merzenich-aktuell' ); ?></span>
				<b><?php echo esc_html( ( $next['heim'] ? 'gegen ' : 'bei ' ) . $next['gegner'] ); ?></b>
				<small><?php echo esc_html( wp_date( 'd.m.Y', $ts ) . ' · ' . wp_date( 'H:i', $ts ) ); ?> Uhr</small>
			</p>
		<?php endif; ?>
	</div>
	<?php
}
