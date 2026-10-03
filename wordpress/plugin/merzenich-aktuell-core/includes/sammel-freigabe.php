<?php
/**
 * Sammelfreigabe für Beiträge (30.09.2026).
 *
 * Beiträge → Alle Beiträge → Mehrfachaktionen:
 *  - „Redaktionell freigeben und veröffentlichen“: setzt Quelle, Datum, Ort und
 *    Human Review auf geprüft (mit Prüfer und Zeitpunkt) und veröffentlicht.
 *    Die Veröffentlichungssperre (ma_publication_gate) bleibt wirksam: ein
 *    Beitrag mit Beitragsbild ohne bestätigte Bildrechte bleibt Entwurf und
 *    wird in der Rückmeldung gezählt.
 *  - „Bildrechte bestätigen“: setzt nur „Bildrechte geprüft“. Bewusst eine
 *    eigene Aktion, damit niemand Bildrechte nebenbei bestätigt.
 *
 *  - „Startseite: freigeben“ / „Startseite: nicht freigeben“ (03.10.2026): die
 *    zweite, eigene Entscheidung (relevanz.php). Veröffentlichen allein bringt
 *    eine neue Meldung nicht auf die Startseite.
 *  - „Archivieren / zurückziehen“: Veröffentlichtes wird Status „Archiviert“,
 *    nicht mehr öffentlich, mit Verlaufseintrag.
 *
 * Nur für Redaktion und Administration (edit_others_posts), nie für Partner.
 */
if (!defined('ABSPATH')) { exit; }

add_filter('bulk_actions-edit-post', function (array $aktionen): array {
    if (!current_user_can('edit_others_posts')) return $aktionen;
    $aktionen['ma_freigeben'] = 'Redaktionell freigeben und veröffentlichen';
    $aktionen['ma_bildrechte'] = 'Bildrechte bestätigen';
    $aktionen['ma_startseite_ja'] = 'Startseite: freigeben';
    $aktionen['ma_startseite_nein'] = 'Startseite: nicht freigeben';
    $aktionen['ma_archivieren'] = 'Archivieren / zurückziehen';
    return $aktionen;
});

add_filter('handle_bulk_actions-edit-post', function (string $ziel, string $aktion, array $ids): string {
    if (!in_array($aktion, ['ma_freigeben', 'ma_bildrechte', 'ma_startseite_ja', 'ma_startseite_nein', 'ma_archivieren'], true)) return $ziel;
    if (!current_user_can('edit_others_posts')) return $ziel;
    $wer = wp_get_current_user()->display_name;
    $jetzt = current_time('mysql');
    $ok = 0; $gesperrt = 0;
    foreach (array_map('intval', $ids) as $id) {
        if (get_post_type($id) !== 'post' || !current_user_can('edit_post', $id)) continue;
        if ($aktion === 'ma_bildrechte') {
            update_post_meta($id, 'ma_image_rights_verified', '1');
            $ok++;
            continue;
        }
        if ($aktion === 'ma_startseite_ja' || $aktion === 'ma_startseite_nein') {
            if (function_exists('ma_startseite_freigabe_setzen')) { ma_startseite_freigabe_setzen($id, $aktion === 'ma_startseite_ja', 'Sammelaktion'); $ok++; }
            continue;
        }
        if ($aktion === 'ma_archivieren') {
            $e = function_exists('ma_redaktion_aktion') ? ma_redaktion_aktion($id, 'archivieren') : ['ok' => false];
            $e['ok'] ? $ok++ : $gesperrt++;
            continue;
        }
        foreach (['ma_source_verified', 'ma_date_verified', 'ma_place_verified', 'ma_human_reviewed'] as $k) update_post_meta($id, $k, '1');
        update_post_meta($id, 'ma_reviewed_by', $wer);
        update_post_meta($id, 'ma_reviewed_at', $jetzt);
        if (get_post_status($id) !== 'publish') wp_update_post(['ID' => $id, 'post_status' => 'publish']);
        in_array(get_post_status($id), ['publish', 'future'], true) ? $ok++ : $gesperrt++;
    }
    return add_query_arg(['ma_sammel' => $aktion, 'ma_ok' => $ok, 'ma_gesperrt' => $gesperrt], $ziel);
}, 10, 3);

add_action('admin_notices', function (): void {
    if (empty($_GET['ma_sammel'])) return;
    $aktion = sanitize_key(wp_unslash($_GET['ma_sammel']));
    $ok = (int) ($_GET['ma_ok'] ?? 0);
    $gesperrt = (int) ($_GET['ma_gesperrt'] ?? 0);
    if ($aktion === 'ma_bildrechte') {
        printf('<div class="notice notice-success is-dismissible"><p>Bildrechte bei %d Beiträgen bestätigt.</p></div>', $ok);
        return;
    }
    if ($aktion === 'ma_startseite_ja' || $aktion === 'ma_startseite_nein') {
        printf('<div class="notice notice-success is-dismissible"><p>Startseite bei %d Beiträgen %s.</p></div>', $ok, $aktion === 'ma_startseite_ja' ? 'freigegeben' : 'nicht freigegeben (nur Rubrik)');
        return;
    }
    if ($aktion === 'ma_archivieren') {
        printf('<div class="notice notice-success is-dismissible"><p>%d Beiträge archiviert, nicht mehr öffentlich.</p></div>', $ok);
        if ($gesperrt) printf('<div class="notice notice-warning is-dismissible"><p>%d Beiträge übersprungen: nur Veröffentlichtes lässt sich archivieren.</p></div>', $gesperrt);
        return;
    }
    printf('<div class="notice notice-success is-dismissible"><p>%d Beiträge freigegeben und veröffentlicht. Die Startseite ist eine eigene Entscheidung: „Startseite: freigeben“ in den Mehrfachaktionen oder Box „Startseite“ im Beitrag.</p></div>', $ok);
    if ($gesperrt) printf('<div class="notice notice-warning is-dismissible"><p>%d Beiträge sind Entwurf geblieben: Beitragsbild ohne bestätigte Bildrechte. Bildrechte prüfen und mit „Bildrechte bestätigen“ freigeben, dann erneut veröffentlichen.</p></div>', $gesperrt);
});
