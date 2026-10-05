</main>
<?php // kommentare.js gehört zur abgeschalteten Vorschauseite (Kommentare laufen über WordPress), daher raus (21.11.0).
echo preg_replace('#<script src="/assets/kommentare\.js[^"]*"[^>]*></script>#', '', ma21_vorlage('fuss.html')); ?>
<?php wp_footer(); ?>
</body>
</html>
