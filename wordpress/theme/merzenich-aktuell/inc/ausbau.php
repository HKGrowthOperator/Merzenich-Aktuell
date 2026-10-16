<?php
/**
 * Ausbaustufe (Wunsch Betreiber 15.10.2026). Für die erste Vorstellung beim
 * Bürgermeister zeigt die Seite eine einfache Fassung. Was dafür ausgeblendet
 * wurde, bleibt im Code und in den Daten gespeichert und lässt sich im Backend
 * unter Design → Ausbaustufe einzeln wieder einschalten, ohne neues Theme.
 *
 * - ma21_ausbau($schluessel): Ist die Funktion eingeschaltet?
 * - ma21_ausbau_html($html): Elemente mit data-ausbau="<schluessel>" fallen
 *   weg, solange die Funktion aus ist; eingeschaltet verlieren sie nur das
 *   Merkmal (die statische Kopie blendet sie per CSS aus, korrekturen.css).
 *
 * Gespeichert in der Option ma21_ausbau; ohne Eintrag gilt der Standard unten.
 */
if (!defined('ABSPATH')) { exit; }

/** Schalter: Standard = einfache Fassung für die Vorstellung. */
const MA21_AUSBAU = [
    'menue_voll' => [
        'titel' => 'Unterpunkte in den Aufklappmenüs',
        'text' => 'Kleine Links unter den roten Titeln wie bis zum 14.10.2026, zum Beispiel Blaulicht › Diebstahl, Sport › SC 1919: Tabelle & Spielplan, Termine › Ortsfest. Mit eingeschalteten Vereinen auch die Anzeige im Vereine-Menü: Anzeigen auf dem Werbeplatz „Menü Vereine aufgeklappt“ erscheinen nur, wenn beide Schalter an sind.',
        'standard' => false,
    ],
    'vereine' => [
        'titel' => 'Vereine in der Ressortleiste',
        'text' => 'Ressort „Vereine“ in der Leiste, im Menü „Mehr“ und im Handy-Menü, mit eigenem Aufklappmenü. Die Seite /vereine/ und der Link im Fuß bleiben immer erreichbar.',
        'standard' => false,
    ],
    'endlos' => [
        'titel' => 'Endlos-Scrollen',
        'text' => 'Listen laden beim Scrollen weiter, auf der Startseite folgen „Weitere Meldungen“. Ausgeschaltet: Blättern mit Seite 1, 2 wie bis zum 14.10.2026.',
        'standard' => true,
    ],
    'sport_daten' => [
        'titel' => 'Ergebnisse und Tabellen auf der Sportseite',
        'text' => 'Spielstand-Ecke des SC 1919 (Platz, Punkte, nächstes Spiel), Kasten mit letztem und nächstem Spiel samt Tabellenauszug und „Vereinssport · Tabellenstand“ auf /sport/, wie bis zum 16.10.2026. Sie brauchen laufende Pflege. Ausgeschaltet zeigt /sport/ die Sportmeldungen und darunter alle Sportvereine der Gemeinde.',
        'standard' => false,
    ],
    'muster_alt' => [
        'titel' => 'Fünf Musterfirmen statt drei Beispielfirmen',
        'text' => 'Im Menü „Unternehmen“ die fünf Musterfirmen bis zum 14.10.2026 (Muster-Backstube, Muster-Café am Markt, Muster-Schreinerei, Muster-Steuerbüro, Muster-Immobilien) statt Backhaus Morgenrot, Café Feldlerche und Holzwerkstatt Jahresring. Beide erscheinen nur, solange es weniger als drei echte Firmenkanäle gibt.',
        'standard' => false,
    ],
];

/** Ist die Funktion eingeschaltet? Unbekannte Schlüssel sind aus. */
function ma21_ausbau(string $schluessel): bool {
    if (!isset(MA21_AUSBAU[$schluessel])) return false;
    $o = function_exists('get_option') ? get_option('ma21_ausbau', []) : [];
    if (is_array($o) && array_key_exists($schluessel, $o)) return (bool) $o[$schluessel];
    return (bool) MA21_AUSBAU[$schluessel]['standard'];
}

/**
 * Ausgeblendete Elemente aus fertigem HTML: <a> und <li> mit
 * data-ausbau="<schluessel>". Aus: Element weg. An: nur das Merkmal weg.
 */
function ma21_ausbau_html(string $html): string {
    if (!str_contains($html, 'data-ausbau="')) return $html;
    foreach (array_keys(MA21_AUSBAU) as $k) {
        $q = preg_quote($k, '#');
        if (ma21_ausbau($k)) $html = (string) preg_replace('#\s+data-ausbau="' . $q . '"#', '', $html);
        else $html = (string) preg_replace('#<(a|li)\b[^>]*\sdata-ausbau="' . $q . '"[^>]*>.*?</\1>#s', '', $html);
    }
    return $html;
}

/* ------------------------------------------------------------ Backend */

add_action('admin_init', function (): void {
    register_setting('ma21_ausbau', 'ma21_ausbau', [
        'type' => 'array',
        'default' => [],
        'sanitize_callback' => function ($ein): array {
            $ein = is_array($ein) ? $ein : [];
            $aus = [];
            foreach (array_keys(MA21_AUSBAU) as $k) $aus[$k] = !empty($ein[$k]);
            return $aus;
        },
    ]);
});

// options.php verlangt sonst manage_options; die Seite steht unter „Design“ (edit_theme_options).
add_filter('option_page_capability_ma21_ausbau', fn() => 'edit_theme_options');

// Zwischenspeicher, die vom Schalter abhängen, sofort leeren.
add_action('update_option_ma21_ausbau', 'ma21_ausbau_caches_leeren');
add_action('add_option_ma21_ausbau', 'ma21_ausbau_caches_leeren');
function ma21_ausbau_caches_leeren(): void {
    foreach (['ma21_menue_firmen_v2', 'ma21_rubrik_zahlen'] as $t) delete_transient($t);
}

add_action('admin_menu', function (): void {
    add_theme_page('Ausbaustufe', 'Ausbaustufe', 'edit_theme_options', 'ma21-ausbau', 'ma21_ausbau_seite');
});

function ma21_ausbau_seite(): void {
    if (!current_user_can('edit_theme_options')) return;
    echo '<div class="wrap"><h1>Ausbaustufe</h1>';
    // Unter „Design“ zeigt WordPress die Meldung „Einstellungen gespeichert“ nicht von selbst.
    settings_errors();
    echo '<p style="max-width:720px">Für die Vorstellung zeigt die Seite eine einfache Fassung. Was dafür ausgeblendet wurde, ist nicht gelöscht: Es bleibt gespeichert und lässt sich hier einzeln wieder einschalten. Die Menüs übernehmen eine Änderung spätestens nach 5 Minuten.</p>';
    echo '<form method="post" action="options.php">';
    settings_fields('ma21_ausbau');
    echo '<table class="form-table" role="presentation"><tbody>';
    foreach (MA21_AUSBAU as $k => $s) {
        $id = 'ma21-ausbau-' . $k;
        echo '<tr><th scope="row"><label for="' . esc_attr($id) . '">' . esc_html($s['titel']) . '</label></th><td>'
            . '<label><input type="checkbox" id="' . esc_attr($id) . '" name="ma21_ausbau[' . esc_attr($k) . ']" value="1"' . checked(ma21_ausbau($k), true, false) . '> eingeschaltet</label>'
            . '<p class="description" style="max-width:640px">' . esc_html($s['text']) . ' Standard: ' . ($s['standard'] ? 'an' : 'aus') . '.</p></td></tr>';
    }
    echo '</tbody></table>';
    submit_button('Speichern');
    echo '</form></div>';
}
