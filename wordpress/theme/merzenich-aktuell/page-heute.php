<?php
/* /heute/ (21.17.0): Termine von heute und den nächsten sieben Tagen, neueste Meldungen (inc/termine.php). */
get_header();
while (have_posts()): the_post(); ma21_heute_seite(get_post()); endwhile;
get_footer();
