<?php
/**
 * Fußbereich.
 *
 * @package MerzenichAktuell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ma8_settings = ma8_settings();

$ma8_svc_jobs = post_type_exists( 'ma_job' ) ? get_post_type_archive_link( 'ma_job' ) : false;
$ma8_svc_jobs = $ma8_svc_jobs ? $ma8_svc_jobs : '#';

$ma8_svc_termine = post_type_exists( 'ma_event' ) ? get_post_type_archive_link( 'ma_event' ) : false;
$ma8_svc_termine = $ma8_svc_termine ? $ma8_svc_termine : '#';
?>
<?php ma8_ad( 'footer_banner' ); ?>
<footer>
	<!-- Die Wortmarke steht ueber dem Linkraster, nicht darin: in der ersten
	     Rasterspalte fuellte sie nur einen Teil der Spalte und klebte ohne
	     Schutzabstand an der Kante. Die goldene Pulslinie schliesst den Fuss
	     nach oben so ab, wie sie den Kopf nach unten abschliesst. -->
	<div class="foot-top"><div class="shell">
		<img class="foot-logo" src="<?php echo esc_url( get_template_directory_uri() . '/assets/logo.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="600" height="168">
		<div class="brandline" aria-hidden="true"><i></i><svg viewBox="0 0 240 48"><path d="M0 24 H118 L124 20 L130 29 L136 21 L144 4 L152 44 L158 25 L166 24 L174 24 L180 18 L186 30 L192 24 H240" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linejoin="round" stroke-linecap="round"/></svg></div>
	</div></div>
	<div class="shell foot-grid">
		<div class="foot-brand">
				<?php if ( $ma8_settings['whatsapp'] ) : ?>
				<p class="foot-wa"><a href="<?php echo esc_url( $ma8_settings['whatsapp'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Sie wissen etwas? Melden Sie es im WhatsApp-Kanal.', 'merzenich-aktuell' ); ?></a></p>
				<?php endif; ?>
			<p><?php esc_html_e( 'Nachrichten aus Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald. Jede Meldung nennt ihre Originalquelle, den Datenstand sowie Bildtyp und Bildcredit.', 'merzenich-aktuell' ); ?></p>
		</div>
		<div>
			<h3><?php esc_html_e( 'Ressorts', 'merzenich-aktuell' ); ?></h3>
			<ul>
				<?php
				// Die Standardkategorie heisst in einer frischen Installation
				// "Uncategorized" und hat in der Ressortspalte nichts verloren.
				foreach ( get_categories( array( 'hide_empty' => false, 'number' => 8, 'exclude' => array( get_option( 'default_category' ) ) ) ) as $ma8_cat ) {
					printf( '<li><a href="%s">%s</a></li>', esc_url( get_category_link( $ma8_cat->term_id ) ), esc_html( $ma8_cat->name ) );
				}
				?>
				<li><a href="<?php echo esc_url( get_post_type_archive_link( 'ma_event' ) ); ?>"><?php esc_html_e( 'Veranstaltungen', 'merzenich-aktuell' ); ?></a></li>
			</ul>
		</div>
		<div>
			<h3><?php esc_html_e( 'Ortsteile', 'merzenich-aktuell' ); ?></h3>
			<ul>
				<?php
				$ma8_terms = get_terms( array( 'taxonomy' => 'ma_district', 'hide_empty' => false ) );
				if ( $ma8_terms && ! is_wp_error( $ma8_terms ) ) {
					foreach ( $ma8_terms as $ma8_term ) {
						printf( '<li><a href="%s">%s</a></li>', esc_url( get_term_link( $ma8_term ) ), esc_html( $ma8_term->name ) );
					}
				}
				?>
			</ul>
		</div>
		<div>
			<h3><?php esc_html_e( 'Service', 'merzenich-aktuell' ); ?></h3>
			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => '', 'depth' => 1 ) ); ?>
			<?php else : ?>
				<ul>
					<li><a href="<?php echo esc_url( $ma8_svc_termine ); ?>"><?php esc_html_e( 'Termine', 'merzenich-aktuell' ); ?></a></li>
					<li><a href="<?php echo esc_url( $ma8_svc_jobs ); ?>"><?php esc_html_e( 'Stellenmarkt', 'merzenich-aktuell' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/service/' ) ); ?>"><?php esc_html_e( 'Notdienste & Rathaus', 'merzenich-aktuell' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>"><?php esc_html_e( 'Suche', 'merzenich-aktuell' ); ?></a></li>
					<li><a href="<?php echo esc_url( get_feed_link() ); ?>"><?php esc_html_e( 'RSS-Feed', 'merzenich-aktuell' ); ?></a></li>
				</ul>
			<?php endif; ?>
		</div>
		<div>
			<h3><?php esc_html_e( 'Mitmachen', 'merzenich-aktuell' ); ?></h3>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/meldung-senden/' ) ); ?>"><?php esc_html_e( 'Meldung senden', 'merzenich-aktuell' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/termine/melden/' ) ); ?>"><?php esc_html_e( 'Termin melden', 'merzenich-aktuell' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/whatsapp/' ) ); ?>"><?php esc_html_e( 'WhatsApp-Kanal', 'merzenich-aktuell' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/werben/' ) ); ?>"><?php esc_html_e( 'Werben & Mediadaten', 'merzenich-aktuell' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/unterstuetzen/' ) ); ?>"><?php esc_html_e( 'Unterstützen', 'merzenich-aktuell' ); ?></a></li>
			</ul>
		</div>
		<div>
			<h3><?php esc_html_e( 'Redaktion', 'merzenich-aktuell' ); ?></h3>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/ueber-uns/' ) ); ?>"><?php esc_html_e( 'Über uns & Team', 'merzenich-aktuell' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/grundsaetze/' ) ); ?>"><?php esc_html_e( 'Publizistische Grundsätze', 'merzenich-aktuell' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/korrekturen/' ) ); ?>"><?php esc_html_e( 'Korrekturen', 'merzenich-aktuell' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/redaktionshandbuch/' ) ); ?>"><?php esc_html_e( 'Redaktionshandbuch', 'merzenich-aktuell' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/impressum/' ) ); ?>"><?php esc_html_e( 'Impressum', 'merzenich-aktuell' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/datenschutz/' ) ); ?>"><?php esc_html_e( 'Datenschutz', 'merzenich-aktuell' ); ?></a></li>
			</ul>
		</div>
	</div>
	<div class="shell foot-bottom">
		<div>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> · <?php esc_html_e( 'Gemeinde Merzenich · Kreis Düren', 'merzenich-aktuell' ); ?></div>
		<div>
			<a href="<?php echo esc_url( home_url( '/impressum/' ) ); ?>"><?php esc_html_e( 'Impressum', 'merzenich-aktuell' ); ?></a> ·
			<a href="<?php echo esc_url( home_url( '/datenschutz/' ) ); ?>"><?php esc_html_e( 'Datenschutz', 'merzenich-aktuell' ); ?></a> ·
			<a href="<?php echo esc_url( home_url( '/grundsaetze/' ) ); ?>"><?php esc_html_e( 'Grundsätze', 'merzenich-aktuell' ); ?></a> ·
			<a href="<?php echo esc_url( home_url( '/meldung-senden/' ) ); ?>"><?php esc_html_e( 'Redaktion', 'merzenich-aktuell' ); ?></a>
		</div>
	</div>
	<div class="foot-seal" aria-hidden="true"></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
