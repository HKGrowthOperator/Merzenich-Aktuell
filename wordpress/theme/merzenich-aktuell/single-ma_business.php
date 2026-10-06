<?php
/* Einzelseite im neuen Markup (inc/einzel.php, 21.13.0). */
get_header();
while (have_posts()): the_post();
    ma21_anzeige_einzel(get_post());
endwhile;
get_footer();
