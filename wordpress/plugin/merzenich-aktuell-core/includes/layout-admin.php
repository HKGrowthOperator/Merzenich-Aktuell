<?php
/**
 * Board „Startseite & Ressorts“ (02.10.2026): Merzenich Aktuell → Startseite & Ressorts.
 *
 * Spiegelt die Seite: Bühne (Aufmacher, Bühne 1–4, Anzeige), die Rubrikflächen
 * mit ihren Reihen, bei Ressortseiten Aufmacher, Bildraster (Sport) und Reihen.
 * Jede Kachel: Ersetzen (Auswahl mit Suche), Nochmal einsetzen, Verschieben,
 * Platz freigeben, Nur Rubrik, Neue Meldung (Schnellformular an Ort und
 * Stelle), Bearbeiten, Ansehen. Ziehen und Ablegen tauscht zwei Plätze.
 * Logik und Markup: assets/layout.js, assets/layout.css; Daten: layout-rest.php.
 */
if (!defined('ABSPATH')) { exit; }

add_action('admin_menu', function (): void {
    add_submenu_page('merzenich-aktuell', 'Startseite & Ressorts', 'Startseite & Ressorts', 'edit_others_posts', 'ma-startseite', 'ma_layout_board_seite', 1);
}, 20);

/** Skript und Stil des Editors registrieren (Board und Seite). */
function ma_layout_assets_registrieren(): void {
    wp_register_script('ma-layout', MA_CORE_URL . 'assets/layout.js', [], MA_CORE_VERSION, ['in_footer' => true, 'strategy' => 'defer']);
    wp_register_style('ma-layout', MA_CORE_URL . 'assets/layout.css', [], MA_CORE_VERSION);
}
add_action('init', 'ma_layout_assets_registrieren');

function ma_layout_js_konfig(string $modus, string $seite): array {
    return [
        'modus' => $modus, 'seite' => $seite, 'name' => ma_layout_seiten()[$seite] ?? $seite,
        'api' => esc_url_raw(rest_url('ma/v1/')), 'nonce' => wp_create_nonce('wp_rest'),
        'board' => admin_url('admin.php?page=ma-startseite&seite=' . $seite),
        'werbeplaetze' => admin_url('edit.php?post_type=ma_ad&page=ma-ads'),
        'editUrl' => admin_url('post.php?post=%d&action=edit'),
    ];
}

add_action('admin_enqueue_scripts', function (string $hook): void {
    if ($hook !== 'merzenich-aktuell_page_ma-startseite' || !ma_startplatz_darf()) return;
    $seite = sanitize_key(wp_unslash($_GET['seite'] ?? 'startseite'));
    if (!ma_layout_seite_gueltig($seite)) $seite = 'startseite';
    wp_enqueue_media();
    wp_enqueue_style('ma-layout');
    wp_enqueue_script('ma-layout');
    wp_add_inline_script('ma-layout', 'window.maLayout=' . wp_json_encode(ma_layout_js_konfig('board', $seite)) . ';', 'before');
});

function ma_layout_board_seite(): void {
    if (!ma_startplatz_darf()) wp_die('Keine Berechtigung.');
    $seite = sanitize_key(wp_unslash($_GET['seite'] ?? 'startseite'));
    if (!ma_layout_seite_gueltig($seite)) $seite = 'startseite';
    echo '<div class="wrap"><h1>Startseite &amp; Ressorts</h1>';
    echo '<p>Jeder Platz zeigt, was dort gerade steht. <strong>Fest</strong> heißt: von der Redaktion gesetzt, bleibt stehen. <strong>Automatisch</strong> heißt: die jüngste passende Meldung nach Relevanz. Ziehen Sie eine Karte auf einen anderen Platz, um zu tauschen. „Nochmal einsetzen“ zeigt dieselbe Meldung zusätzlich an anderer Stelle, „Neue Meldung“ legt eine Meldung direkt hier an. Jede Änderung wird sofort gespeichert; Leser sehen sie nach spätestens einer Stunde (Seitencache).</p>';
    echo '<nav class="ma-lb__tabs" aria-label="Seiten">';
    foreach (ma_layout_seiten() as $k => $name) printf('<a href="%s"%s>%s</a>', esc_url(admin_url('admin.php?page=ma-startseite&seite=' . $k)), $k === $seite ? ' class="ist-aktiv" aria-current="page"' : '', esc_html($name));
    echo '</nav>';
    echo '<div id="ma-layout-kopf" class="ma-lb__kopf"></div><div id="ma-layout-board">Lädt …</div>';
    echo '<p class="description">Hinweise an einer Karte (gelb) sind keine Sperre: ein fester Platz gilt immer, auch für eine Sportmeldung auf dem Aufmacher oder eine Meldung ohne Bild. Meldungen auf „Nur in der Rubrik“ und Entwürfe lassen sich nicht einsetzen.</p></div>';
}
