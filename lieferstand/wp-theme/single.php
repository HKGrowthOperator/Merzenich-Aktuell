<?php
/**
 * Einzelne Meldung.
 *
 * @package MerzenichAktuell
 */

get_header();

while ( have_posts() ) :
	the_post();
	$ma8_id       = get_the_ID();
	$ma8_cats     = get_the_category();
	$ma8_district = ma8_district( $ma8_id );
	?>
<main id="main">
<article>

<div class="article-head"><div class="shell">
	<nav class="crumbs" aria-label="<?php esc_attr_e( 'Brotkrumen', 'merzenich-aktuell' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Start', 'merzenich-aktuell' ); ?></a>
		<?php if ( $ma8_cats ) : ?>
			<span class="sep">&rsaquo;</span><a href="<?php echo esc_url( get_category_link( $ma8_cats[0]->term_id ) ); ?>"><?php echo esc_html( $ma8_cats[0]->name ); ?></a>
		<?php endif; ?>
		<?php if ( $ma8_district ) : $ma8_term = get_term_by( 'name', $ma8_district, 'ma_district' ); ?>
			<span class="sep">&rsaquo;</span><a href="<?php echo esc_url( get_term_link( $ma8_term ) ); ?>"><?php echo esc_html( $ma8_district ); ?></a>
		<?php endif; ?>
	</nav>

	<?php echo ma8_kicker_line( $ma8_id ); ?>
	<?php if ( function_exists( 'ma8_format_badge' ) ) { echo ma8_format_badge( $ma8_id ); } ?>
	<h1><?php the_title(); ?></h1>
	<?php if ( ma8_dek( $ma8_id ) ) : ?><p class="dek"><?php echo esc_html( ma8_dek( $ma8_id ) ); ?></p><?php endif; ?>

	<div class="byline">
		<span class="avatar" aria-hidden="true">MA</span>
		<span class="who">
			<b><?php the_author(); ?></b>
			<span><?php esc_html_e( 'Lokalredaktion', 'merzenich-aktuell' ); ?></span>
		</span>
		<span class="dates">
			<?php if ( get_post_meta( $ma8_id, 'ma8_undated', true ) ) : ?>
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( ma8_stamp( $ma8_id, true ) ); ?></time>
			<?php else : ?>
				<?php esc_html_e( 'Veröffentlicht', 'merzenich-aktuell' ); ?> <time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( ma8_dt( $ma8_id ) . ' Uhr' ); ?></time>
			<?php endif; ?>
			<?php if ( get_the_modified_date( 'Ymd' ) !== get_the_date( 'Ymd' ) ) : ?>
				<br><?php esc_html_e( 'Aktualisiert', 'merzenich-aktuell' ); ?> <time datetime="<?php echo esc_attr( get_the_modified_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_modified_date( 'd.m.Y · H:i' ) . ' Uhr' ); ?></time>
			<?php endif; ?>
			<br><span class="readtime"><?php printf( esc_html( _n( '%d Minute Lesezeit', '%d Minuten Lesezeit', ma8_read_minutes( $ma8_id ), 'merzenich-aktuell' ) ), ma8_read_minutes( $ma8_id ) ); ?></span>
		</span>
	</div>

	<div class="share">
		<a href="https://api.whatsapp.com/send?text=<?php echo rawurlencode( get_the_title() . ' ' . get_permalink() ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Per WhatsApp teilen', 'merzenich-aktuell' ); ?>"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 00-8.6 15.1L2 22l5.1-1.3A10 10 0 1012 2zm0 2a8 8 0 016.9 12.1l-.3.5.6 2.2-2.3-.6-.5.3A8 8 0 1112 4zm-3.2 4c-.2 0-.5.1-.7.4-.3.3-.8.9-.8 1.9 0 1 .7 2 .8 2.1.1.2 1.4 2.3 3.5 3.1 1.7.7 2.1.6 2.5.5.5 0 1.3-.6 1.5-1.1.2-.6.2-1 .1-1.1l-1.5-.7c-.2-.1-.4-.1-.5.1l-.6.8c-.1.1-.3.2-.5.1-.3-.1-1-.4-1.6-1s-.8-1-.9-1.2c-.1-.2 0-.3.1-.4l.4-.5c.1-.2.1-.3 0-.5l-.6-1.4c-.2-.4-.4-.4-.5-.4h-.2z"/></svg></a>
		<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( get_permalink() ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Auf Facebook teilen', 'merzenich-aktuell' ); ?>"><svg viewBox="0 0 24 24"><path d="M13.5 21v-7h2.4l.4-2.8h-2.8V9.4c0-.8.2-1.4 1.4-1.4h1.5V5.4c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.1H8.1V14h2.4v7h3z"/></svg></a>
		<a href="mailto:?subject=<?php echo rawurlencode( get_the_title() ); ?>&amp;body=<?php echo rawurlencode( get_permalink() ); ?>" aria-label="<?php esc_attr_e( 'Per E-Mail teilen', 'merzenich-aktuell' ); ?>"><svg viewBox="0 0 24 24"><path d="M3 5h18v14H3V5zm2 2v.5l7 4.2 7-4.2V7H5zm0 3v7h14v-7l-7 4.2L5 10z"/></svg></a>
		<button type="button" data-share aria-label="<?php esc_attr_e( 'Link kopieren', 'merzenich-aktuell' ); ?>"><svg viewBox="0 0 24 24"><path d="M10.6 13.4a1 1 0 001.4 0l3.5-3.5a2.5 2.5 0 10-3.5-3.5l-1 1 1.4 1.4 1-1a.7.7 0 011 1l-3.5 3.5a1 1 0 000 1.1zm2.8-2.8a1 1 0 00-1.4 0l-3.5 3.5a2.5 2.5 0 103.5 3.5l1-1-1.4-1.4-1 1a.7.7 0 01-1-1l3.5-3.5a1 1 0 000-1.1z"/></svg></button>
	</div>
</div></div>

<div class="shell article-grid">
	<div class="article-body" data-readable>
		<?php if ( ma8_img( $ma8_id ) ) : ?>
			<figure>
				<?php echo ma8_media( $ma8_id, 'ma8-hero', array( 'eager' => true ) ); ?>
				<figcaption>
					<span><span class="figure-badge"><?php echo esc_html( ma8_image_type_label( $ma8_id ) ); ?></span> · <?php echo esc_html( ma8_image_alt( $ma8_id ) ); ?></span>
					<?php $ma8_credit = get_post_meta( $ma8_id, 'ma8_image_credit', true ); ?>
					<?php if ( $ma8_credit ) : ?><span><?php esc_html_e( 'Bild:', 'merzenich-aktuell' ); ?> <?php echo esc_html( $ma8_credit ); ?></span><?php endif; ?>
				</figcaption>
			</figure>
		<?php endif; ?>

		<?php echo ma8_facts_list( $ma8_id ); ?>

		<?php
		// Artikeltext an Absatzgrenzen aufteilen: erster Absatz bekommt die Initiale (Drop-Cap),
		// nach dem zweiten Absatz erscheint ein Werbeplatz statt nur am Textende.
		$ma8_html = apply_filters( 'the_content', get_the_content() );
		$ma8_html = preg_replace( '/<p>/i', '<p class="first-p">', $ma8_html, 1 );
		$ma8_paras = preg_split( '/(?<=<\/p>)/i', $ma8_html );
		if ( isset( $ma8_paras[1] ) ) {
			ob_start();
			ma8_ad( 'article' );
			$ma8_paras[1] .= '<div class="prose-ad">' . ob_get_clean() . '</div>';
			$ma8_html      = implode( '', $ma8_paras );
		}
		?>
		<div class="prose"><?php echo $ma8_html; // phpcs:ignore WordPress.Security.EscapeOutput -- bereits durch den the_content-Filter gerendert. ?></div>

		<?php
		// Weitere an den Beitrag angehängte Bilder als Serie mit Großansicht.
		$ma8_gallery = get_attached_media( 'image', $ma8_id );
		if ( count( $ma8_gallery ) > 1 ) :
			$ma8_credit_line = get_post_meta( $ma8_id, 'ma8_image_credit', true );
			$ma8_i = 0;
			?>
			<div class="gallery">
				<?php foreach ( $ma8_gallery as $ma8_shot ) : $ma8_i++; ?>
					<figure>
						<button class="shot" type="button" data-shot data-credit="<?php echo esc_attr( ma8_image_type_label( $ma8_id ) . ( $ma8_credit_line ? ' · ' . $ma8_credit_line : '' ) ); ?>" aria-label="<?php printf( esc_attr__( 'Bild %1$d von %2$d vergrößern', 'merzenich-aktuell' ), $ma8_i, count( $ma8_gallery ) ); ?>">
							<div class="media">
								<img src="<?php echo esc_url( wp_get_attachment_image_url( $ma8_shot->ID, 'ma8-card' ) ); ?>" alt="<?php printf( esc_attr__( '%1$s – Bild %2$d von %3$d', 'merzenich-aktuell' ), esc_attr( ma8_image_alt( $ma8_id ) ), $ma8_i, count( $ma8_gallery ) ); ?>" loading="lazy" decoding="async">
								<span class="ph"><?php esc_html_e( 'Bild folgt', 'merzenich-aktuell' ); ?></span>
							</div>
						</button>
					</figure>
				<?php endforeach; ?>
			</div>
			<p style="font-size:12px;color:var(--muted);margin:-16px 0 26px">
				<?php printf( esc_html__( 'Bildserie: %s · Zum Vergrößern auf ein Bild klicken', 'merzenich-aktuell' ), esc_html( $ma8_credit_line ) ); ?>
			</p>
		<?php endif; ?>

		<?php ma8_ad( 'in_article' ); ?>

		<?php
		$ma8_source_url  = get_post_meta( $ma8_id, 'ma8_source_url', true );
		$ma8_source_name = get_post_meta( $ma8_id, 'ma8_source_name', true );
		$ma8_source_date = get_post_meta( $ma8_id, 'ma8_source_date', true );
		if ( $ma8_source_url || $ma8_source_name ) :
			?>
			<div class="source-box">
				<b><?php esc_html_e( 'Quelle und Datenstand.', 'merzenich-aktuell' ); ?></b>
				<?php if ( $ma8_source_url ) : ?>
					<?php esc_html_e( 'Grundlage dieser Meldung ist', 'merzenich-aktuell' ); ?> <a href="<?php echo esc_url( $ma8_source_url ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $ma8_source_name ? $ma8_source_name : __( 'die Originalquelle', 'merzenich-aktuell' ) ); ?> &#8599;</a>.
				<?php else : ?>
					<?php printf( esc_html__( 'Grundlage dieser Meldung ist %s.', 'merzenich-aktuell' ), esc_html( $ma8_source_name ) ); ?>
				<?php endif; ?>
				<?php if ( $ma8_source_date ) : ?><span class="stand"><?php printf( esc_html__( 'Datenstand %s.', 'merzenich-aktuell' ), esc_html( $ma8_source_date ) ); ?></span><?php endif; ?>
				<?php esc_html_e( 'Angaben wurden redaktionell geprüft und zusammengefasst. Bildtyp und Bildcredit sind sichtbar ausgewiesen.', 'merzenich-aktuell' ); ?>
			</div>
		<?php endif; ?>

		<?php
		$ma8_tags = get_the_tags();
		if ( $ma8_tags ) :
			?>
			<div class="tags">
				<?php foreach ( $ma8_tags as $ma8_tag ) : ?>
					<a href="<?php echo esc_url( get_tag_link( $ma8_tag->term_id ) ); ?>"><?php echo esc_html( $ma8_tag->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="author-box">
			<span class="avatar" aria-hidden="true">MA</span>
			<div class="b">
				<b><?php the_author(); ?></b>
				<p><?php esc_html_e( 'Die Redaktion prüft jede Meldung gegen die Originalquelle, dokumentiert Bildtyp und Credit und ergänzt eigene Einordnung.', 'merzenich-aktuell' ); ?></p>
			</div>
		</div>
	</div>

	<aside class="sidebar">
		<?php
		$ma8_more_args = array( 'post_type' => 'post', 'posts_per_page' => 4, 'post__not_in' => array( $ma8_id ) );
		if ( $ma8_district ) {
			$ma8_more_args['tax_query'] = array( array( 'taxonomy' => 'ma_district', 'field' => 'name', 'terms' => $ma8_district ) );
		}
		$ma8_more = get_posts( $ma8_more_args );
		if ( ! $ma8_more ) {
			$ma8_more = get_posts( array( 'post_type' => 'post', 'posts_per_page' => 4, 'post__not_in' => array( $ma8_id ) ) );
		}
		if ( $ma8_more ) :
			?>
			<div class="sidebox">
				<h3><?php echo esc_html( $ma8_district ? sprintf( __( 'Mehr aus %s', 'merzenich-aktuell' ), $ma8_district ) : __( 'Mehr aus der Gemeinde', 'merzenich-aktuell' ) ); ?></h3>
				<ul>
					<?php foreach ( $ma8_more as $ma8_item ) : ?>
						<li class="termin" style="grid-template-columns:1fr">
							<span class="t">
								<a href="<?php echo esc_url( get_permalink( $ma8_item ) ); ?>"><?php echo esc_html( get_the_title( $ma8_item ) ); ?></a>
								<small><?php echo esc_html( ma8_kicker( $ma8_item->ID ) . ' · ' . ma8_dt( $ma8_item->ID, 'd.m.' ) ); ?></small>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
		<?php
		ma8_box_events( 4 );
		ma8_box_service();
		ma8_ad( 'sidebar' );
		?>
	</aside>
</div>
</article>

<?php
$ma8_rest = get_posts( array( 'post_type' => 'post', 'posts_per_page' => 3, 'post__not_in' => array_merge( array( $ma8_id ), wp_list_pluck( $ma8_more, 'ID' ) ) ) );
if ( $ma8_rest ) :
	?>
	<section class="section"><div class="shell">
		<div class="section-head"><div class="left"><span class="eyebrow"><?php esc_html_e( 'Weiterlesen', 'merzenich-aktuell' ); ?></span><h2><?php esc_html_e( 'Ebenfalls aktuell', 'merzenich-aktuell' ); ?></h2></div></div>
		<div class="cards-3"><?php foreach ( $ma8_rest as $ma8_item ) { ma8_news_card( $ma8_item->ID ); } ?></div>
	</div></section>
<?php endif; ?>

</main>
	<?php
endwhile;

get_footer();
