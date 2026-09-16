<?php
/**
 * Suchformular.
 *
 * @package MerzenichAktuell
 */
?>
<form class="search-input" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="ma8-s"><?php esc_html_e( 'Suchbegriff', 'merzenich-aktuell' ); ?></label>
	<input id="ma8-s" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Suchen …', 'merzenich-aktuell' ); ?>">
	<button class="btn" type="submit"><?php esc_html_e( 'Suchen', 'merzenich-aktuell' ); ?></button>
</form>
