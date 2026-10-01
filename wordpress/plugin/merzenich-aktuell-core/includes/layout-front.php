<?php
/**
 * Bearbeitungsmodus auf der Seite (02.10.2026): „Seite bearbeiten“ in der
 * WordPress-Leiste auf der Startseite und auf den Ressortseiten (erste Seite).
 *
 * Eingeschaltet legt sich eine Leiste über jede Meldungskarte: ziehen und auf
 * eine andere Karte legen (tauschen), Ersetzen, Nochmal einsetzen, Platz
 * freigeben, Text (Titel und Anriss direkt ändern), Menü mit Verschieben,
 * Nur Rubrik, Neue Meldung, Editor. Die Seite bleibt, wie sie ist; nach jeder
 * Änderung tauscht das Skript nur die betroffenen Blöcke aus (Markup vom
 * Theme über ma_layout_block_html). Nur Redaktion und Administration.
 */
if (!defined('ABSPATH')) { exit; }

/** Schlüssel der Layout-Karte für die gerade angezeigte Seite, '' wenn nicht anordenbar. */
function ma_layout_front_seite(): string {
    if (is_admin() || is_paged() || !ma_layout_rest_darf()) return '';
    if (is_front_page()) return 'startseite';
    if (function_exists('ma21_ressort_schluessel')) return ma21_ressort_schluessel();
    if (is_category()) { $o = get_queried_object(); if ($o instanceof WP_Term && ma_layout_seite_gueltig('ressort-' . $o->slug)) return 'ressort-' . $o->slug; }
    return '';
}

add_action('admin_bar_menu', function (WP_Admin_Bar $leiste): void {
    $seite = ma_layout_front_seite();
    if ($seite === '') return;
    $leiste->add_node(['id' => 'ma-layout', 'title' => '<span class="ab-icon dashicons dashicons-move" aria-hidden="true"></span>Seite bearbeiten', 'href' => add_query_arg('bearbeiten', '1', ma_layout_rest_url($seite)), 'meta' => ['class' => 'ma-layout-toggle', 'title' => 'Meldungen auf dieser Seite anordnen']]);
    $leiste->add_node(['id' => 'ma-layout-board', 'parent' => 'ma-layout', 'title' => 'Im Backend anordnen', 'href' => admin_url('admin.php?page=ma-startseite&seite=' . $seite)]);
}, 80);

add_action('wp_enqueue_scripts', function (): void {
    $seite = ma_layout_front_seite();
    if ($seite === '') return;
    wp_enqueue_style('ma-layout');
    wp_enqueue_script('ma-layout');
    wp_add_inline_script('ma-layout', 'window.maLayout=' . wp_json_encode(ma_layout_js_konfig('seite', $seite)) . ';', 'before');
}, 20);
