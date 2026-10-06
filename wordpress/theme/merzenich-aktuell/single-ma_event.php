<?php
/* Terminseite im neuen Markup (inc/termine.php, wie deploy/termine.mjs). */
get_header();
while (have_posts()): the_post();
    ma21_termin_einzel(get_post());
endwhile;
get_footer();
