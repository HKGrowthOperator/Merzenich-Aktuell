</main>
<?php // kommentare.js gehört zur abgeschalteten Vorschauseite (Kommentare laufen über WordPress), daher raus (21.11.0).
// Jahr im Copyright aus dem aktuellen Datum, nicht aus dem Bau der Vorlage (21.13.0).
// Link „Heute in Merzenich“ nach „Termine“ (21.17.0, inc/termine.php).
echo ma21_heute_links(str_replace('© 2026 ', '© ' . wp_date('Y') . ' ', (string) preg_replace('#<script src="/assets/kommentare\.js[^"]*"[^>]*></script>#', '', ma21_vorlage('fuss.html'))), 'fuss'); ?>
<?php wp_footer(); ?>
</body>
</html>
