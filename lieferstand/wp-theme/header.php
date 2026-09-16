<?php
/**
 * Kopfbereich: Topbar, Masthead mit Wortmarke, Ressortnavigation, Ortsteilleiste und Ticker.
 *
 * @package MerzenichAktuell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ma8_settings      = ma8_settings();
$ma8_search_icon   = '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M15.5 15.5L21 21" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>';

// Service-Leiste: Stellenmarkt, Familienanzeigen, Termine, SC 1919.
$ma8_svc_jobs = post_type_exists( 'ma_job' ) ? get_post_type_archive_link( 'ma_job' ) : false;
$ma8_svc_jobs = $ma8_svc_jobs ? $ma8_svc_jobs : '#';

$ma8_svc_familie_cat = get_cat_ID( 'Menschen' );
$ma8_svc_familie     = $ma8_svc_familie_cat ? get_category_link( $ma8_svc_familie_cat ) : home_url( '/' );

$ma8_svc_termine = post_type_exists( 'ma_event' ) ? get_post_type_archive_link( 'ma_event' ) : false;
$ma8_svc_termine = $ma8_svc_termine ? $ma8_svc_termine : '#';

$ma8_svc_sc_posts = get_posts( array(
	'post_type'      => 'page',
	'meta_key'       => '_wp_page_template',
	'meta_value'     => 'template-sc-merzenich.php',
	'posts_per_page' => 1,
	'fields'         => 'ids',
) );
$ma8_svc_sc = $ma8_svc_sc_posts ? get_permalink( $ma8_svc_sc_posts[0] ) : '#';
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#main"><?php esc_html_e( 'Zum Inhalt springen', 'merzenich-aktuell' ); ?></a>

<div class="topbar"><div class="shell">
	<div class="date">
		<b><?php echo esc_html( wp_date( 'l, j. F Y' ) ); ?></b>
		<span class="stand"><?php echo esc_html( $ma8_settings['editorial_stand'] ? $ma8_settings['editorial_stand'] : sprintf( __( 'Redaktionsstand %s Uhr', 'merzenich-aktuell' ), wp_date( 'H:i' ) ) ); ?></span>
	</div>
	<div class="toplinks">
		<span class="svc">
			<a href="<?php echo esc_url( $ma8_svc_jobs ); ?>"><?php esc_html_e( 'Stellenmarkt', 'merzenich-aktuell' ); ?></a>
			<a href="<?php echo esc_url( $ma8_svc_familie ); ?>"><?php esc_html_e( 'Familienanzeigen', 'merzenich-aktuell' ); ?></a>
			<a href="<?php echo esc_url( $ma8_svc_termine ); ?>"><?php esc_html_e( 'Termine', 'merzenich-aktuell' ); ?></a>
			<a href="<?php echo esc_url( $ma8_svc_sc ); ?>"><?php esc_html_e( 'SC 1919', 'merzenich-aktuell' ); ?></a>
		</span>
		<?php if ( $ma8_settings['whatsapp'] ) : ?>
			<a class="wa" href="<?php echo esc_url( $ma8_settings['whatsapp'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'merzenich-aktuell' ); ?></a>
		<?php endif; ?>
		<a class="opt-hide" href="<?php echo esc_url( home_url( '/meldung-senden/' ) ); ?>"><?php esc_html_e( 'Meldung senden', 'merzenich-aktuell' ); ?></a>
		<a class="opt-hide" href="<?php echo esc_url( home_url( '/werben/' ) ); ?>"><?php esc_html_e( 'Werben', 'merzenich-aktuell' ); ?></a>
	</div>
</div></div>

<header class="masthead"><div class="shell mast-inner">
	<div class="mast-left"><b><?php esc_html_e( 'Lokalzeitung online', 'merzenich-aktuell' ); ?></b><?php esc_html_e( 'Gemeinde Merzenich · Kreis Düren · NRW', 'merzenich-aktuell' ); ?></div>
	<button class="menu-btn" type="button" data-menu aria-label="<?php esc_attr_e( 'Menü öffnen', 'merzenich-aktuell' ); ?>" aria-expanded="false">&#9776;</button>
	<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/logo.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="600" height="168">
	</a>
	<div class="mast-right">
		<form class="mast-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Suchen und finden …', 'merzenich-aktuell' ); ?>" aria-label="<?php esc_attr_e( 'Suchbegriff', 'merzenich-aktuell' ); ?>" autocomplete="off">
			<button type="submit" aria-label="<?php esc_attr_e( 'Suchen', 'merzenich-aktuell' ); ?>"><?php echo $ma8_search_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		</form>
		<div class="mast-meta">
			<span class="mast-weather" data-weather-mini><?php esc_html_e( 'Wetter Merzenich', 'merzenich-aktuell' ); ?></span>
			<span class="mast-date"><?php echo esc_html( wp_date( 'l, j. F Y' ) ); ?></span>
		</div>
		<a class="search-btn" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" aria-label="<?php esc_attr_e( 'Suche', 'merzenich-aktuell' ); ?>"><?php echo $ma8_search_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
	</div>
</div></header>
<div class="mast-line"><div class="shell"><div class="brandline" aria-hidden="true"><i></i><svg viewBox="0 0 240 48"><path d="M0 24 H118 L124 20 L130 29 L136 21 L144 4 L152 44 L158 25 L166 24 L174 24 L180 18 L186 30 L192 24 H240" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linejoin="round" stroke-linecap="round"/></svg></div></div></div>

<nav class="mainnav" aria-label="<?php esc_attr_e( 'Ressorts', 'merzenich-aktuell' ); ?>"><div class="shell navrow">
	<div class="navscroll">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'items_wrap' => '<ul>%3$s</ul>', 'depth' => 1, 'fallback_cb' => false ) );
		} else {
			wp_list_categories( array( 'title_li' => '', 'hide_empty' => false, 'style' => 'none', 'separator' => '' ) );
		}
		?>
	</div>
	<a class="nav-search" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>"><?php esc_html_e( 'Suche', 'merzenich-aktuell' ); ?> &#8981;</a>
</div></nav>

<?php
$ma8_districts = get_terms( array( 'taxonomy' => 'ma_district', 'hide_empty' => false ) );
if ( $ma8_districts && ! is_wp_error( $ma8_districts ) ) : ?>
	<div class="districtbar" aria-label="<?php esc_attr_e( 'Ortsteile', 'merzenich-aktuell' ); ?>"><div class="shell">
		<span class="lbl"><?php esc_html_e( 'Ortsteile', 'merzenich-aktuell' ); ?></span>
		<?php foreach ( $ma8_districts as $ma8_term ) : ?>
			<a href="<?php echo esc_url( get_term_link( $ma8_term ) ); ?>"<?php echo is_tax( 'ma_district', $ma8_term->term_id ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $ma8_term->name ); ?></a>
		<?php endforeach; ?>
	</div></div>
<?php endif; ?>

<?php
$ma8_ticker = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 1, 'meta_key' => 'ma8_breaking', 'meta_value' => 1, 'ignore_sticky_posts' => true ) );
$ma8_is_breaking = $ma8_ticker->have_posts();
// Kein Rueckfall auf den neuesten Beitrag. Ein rotes Alarmband bedeutet nur so
// lange etwas, wie es selten erscheint; ohne echte Eilmeldung entfaellt es,
// genau wie in der Netlify-Fassung.
if ( $ma8_is_breaking ) :
	$ma8_ticker->the_post();
	?>
	<div class="ticker"><div class="shell">
		<span class="tag"><?php esc_html_e( 'EILT', 'merzenich-aktuell' ); ?></span>
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( ma8_dt( get_the_ID(), 'd.m. · H:i' ) ); ?></time>
	</div></div>
	<?php
endif;
wp_reset_postdata();
?>

<div class="drawer" data-drawer>
	<div class="scrim"></div>
	<div class="panel">
		<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/logo.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
		<div class="grp"><?php esc_html_e( 'Ressorts', 'merzenich-aktuell' ); ?></div>
		<?php
		foreach ( get_categories( array( 'hide_empty' => false, 'exclude' => array( get_option( 'default_category' ) ) ) ) as $ma8_cat ) {
			printf( '<a href="%s">%s</a>', esc_url( get_category_link( $ma8_cat->term_id ) ), esc_html( $ma8_cat->name ) );
		}
		?>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'ma_event' ) ); ?>"><?php esc_html_e( 'Veranstaltungen', 'merzenich-aktuell' ); ?></a>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'ma_club' ) ); ?>"><?php esc_html_e( 'Vereine', 'merzenich-aktuell' ); ?></a>
		<?php if ( $ma8_districts && ! is_wp_error( $ma8_districts ) ) : ?>
			<div class="grp"><?php esc_html_e( 'Ortsteile', 'merzenich-aktuell' ); ?></div>
			<?php foreach ( $ma8_districts as $ma8_term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $ma8_term ) ); ?>"><?php echo esc_html( $ma8_term->name ); ?></a>
			<?php endforeach; ?>
		<?php endif; ?>
		<div class="grp"><?php esc_html_e( 'Service', 'merzenich-aktuell' ); ?></div>
		<a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>"><?php esc_html_e( 'Suche', 'merzenich-aktuell' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/meldung-senden/' ) ); ?>"><?php esc_html_e( 'Meldung senden', 'merzenich-aktuell' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/grundsaetze/' ) ); ?>"><?php esc_html_e( 'Quellen & Bilder', 'merzenich-aktuell' ); ?></a>
	</div>
</div>
