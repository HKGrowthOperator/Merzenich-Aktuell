<?php
/* Branchenbuch /betriebe/ im neuen Markup (inc/markt.php, wie chatgpt-site/betriebe/).
   Es erscheinen nur Betriebe mit Einwilligung (Sperre im Core-Plugin); solange
   weniger als drei da sind, zeigt die rechte Spalte gekennzeichnete Musterprofile. */
get_header();
ma21_betriebe_seite();
get_footer();
