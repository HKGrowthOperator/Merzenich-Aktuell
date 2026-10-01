<?php
/**
 * Layout-Karte: Wer steht wo auf der Startseite und auf den Ressortseiten (02.10.2026).
 *
 * Eine Option je Seite (ma_layout_startseite, ma_layout_ressort_<slug>):
 *   ['v' => 1, 'rev' => 17, 'stand' => <unix>, 'von' => <user>,
 *    'slots' => ['aufmacher' => ['post' => 4123], 'blaulicht.gross.1' => ['post' => 4101, 'doppelt' => true]]]
 * Gespeichert werden nur feste Plätze. Alles andere füllt die Automatik
 * (Relevanz, Aktualität, Bildregeln) wie bisher. Ein fester Platz gewinnt
 * immer: die Redaktion darf eine Sportmeldung auf den Aufmacher setzen oder
 * eine Meldung ohne Bild in eine Bildreihe. Statt einer Sperre zeigt der
 * Editor Warnungen (ma_layout_warnungen). Einzige harte Bedingung: der
 * Beitrag ist veröffentlicht und steht nicht auf „Nur in der Rubrik“.
 *
 * Doppelungen (dieselbe Meldung auf zwei Plätzen) nur ausdrücklich
 * („Nochmal einsetzen“, Flag doppelt). Die Automatik lässt einen fest
 * platzierten Beitrag überall sonst aus.
 *
 * Plätze der Startseite: aufmacher, buehne-1..4, <sektion>.gross.1-2,
 * <sektion>.mittel.1-3, <sektion>.zeilen.1-4 (Sektionen blaulicht, rathaus,
 * wirtschaft, vereine, gemeinde). Ressortseiten: lead, raster.1-6 (nur Sport),
 * reihe.1-8.
 *
 * Das Meta ma_startplatz (Box „Startseite“, Freigaben) bleibt gültig: Aufmacher
 * und Bühne werden in beide Richtungen abgeglichen, „aus“ bleibt der
 * Ausschluss. Bestehende feste Plätze werden einmalig übernommen
 * (ma_layout_migrieren), nichts wird gelöscht.
 */
if (!defined('ABSPATH')) { exit; }

const MA_LAYOUT_VERSION = 1;

/** Seiten mit Layout-Karte: Schlüssel => Name. */
function ma_layout_seiten(): array {
    return ['startseite' => 'Startseite', 'ressort-sport' => 'Sport', 'ressort-blaulicht' => 'Blaulicht', 'ressort-rathaus' => 'Rathaus & Politik',
        'ressort-leben' => 'Leben', 'ressort-wirtschaft' => 'Wirtschaft', 'ressort-vereine' => 'Vereine', 'ressort-menschen' => 'Menschen', 'ressort-nachrichten' => 'Nachrichten'];
}

/** Sektionen der Startseite in Seitenreihenfolge (Titel wie im Theme). */
function ma_layout_sektionen(): array {
    return ['blaulicht' => 'Blaulicht', 'rathaus' => 'Politik & Gemeinde', 'wirtschaft' => 'Wirtschaft', 'vereine' => 'Vereine & Menschen', 'gemeinde' => 'Nachrichten aus Merzenich'];
}

function ma_layout_seite_gueltig(string $seite): bool { return isset(ma_layout_seiten()[$seite]); }

/** Rubrik-Slug einer Ressortseite ('' für die Startseite). */
function ma_layout_ressort_slug(string $seite): string { return str_starts_with($seite, 'ressort-') ? substr($seite, 8) : ''; }

/** Alle Platzschlüssel einer Seite: Schlüssel => Beschriftung. */
function ma_layout_slots(string $seite): array {
    if ($seite === 'startseite') {
        $s = ['aufmacher' => 'Aufmacher', 'buehne-1' => 'Bühne 1 (rechts oben)', 'buehne-2' => 'Bühne 2 (rechts unten)', 'buehne-3' => 'Bühne 3 (unten links)', 'buehne-4' => 'Bühne 4 (unten Mitte)'];
        foreach (ma_layout_sektionen() as $k => $titel) {
            for ($i = 1; $i <= 2; $i++) $s["$k.gross.$i"] = "$titel · groß $i";
            for ($i = 1; $i <= 3; $i++) $s["$k.mittel.$i"] = "$titel · mittel $i";
            for ($i = 1; $i <= 4; $i++) $s["$k.zeilen.$i"] = "$titel · Zeile $i";
        }
        return $s;
    }
    if (!ma_layout_seite_gueltig($seite)) return [];
    $s = ['lead' => 'Aufmacher'];
    if ($seite === 'ressort-sport') for ($i = 1; $i <= 6; $i++) $s["raster.$i"] = "Bildraster $i";
    for ($i = 1; $i <= 8; $i++) $s["reihe.$i"] = "Reihe $i";
    return $s;
}

/** Gruppen einer Seite für Board und Bearbeitungsmodus: Reihenfolge, Plätze, Art. */
function ma_layout_struktur(string $seite): array {
    if ($seite === 'startseite') {
        $g = [['id' => 'oben', 'titel' => 'Bühne', 'art' => 'buehne', 'reihen' => [['art' => 'aufmacher', 'plaetze' => ['aufmacher']], ['art' => 'rechts', 'plaetze' => ['buehne-1', 'buehne-2']], ['art' => 'unten', 'plaetze' => ['buehne-3', 'buehne-4'], 'anzeige' => 'Anzeige (Werbeplatz „Startseite – Bühne“)']]]];
        foreach (ma_layout_sektionen() as $k => $titel) {
            $g[] = ['id' => $k, 'titel' => $titel, 'art' => 'sektion', 'reihen' => [
                ['art' => 'gross', 'plaetze' => ["$k.gross.1", "$k.gross.2"]],
                ['art' => 'mittel', 'plaetze' => ["$k.mittel.1", "$k.mittel.2", "$k.mittel.3"]],
                ['art' => 'zeilen', 'plaetze' => ["$k.zeilen.1", "$k.zeilen.2", "$k.zeilen.3", "$k.zeilen.4"]],
            ]];
        }
        return $g;
    }
    $reihen = [['art' => 'lead', 'plaetze' => ['lead']]];
    if ($seite === 'ressort-sport') $reihen[] = ['art' => 'raster', 'plaetze' => array_map(fn($i) => "raster.$i", range(1, 6))];
    $reihen[] = ['art' => 'reihen', 'plaetze' => array_map(fn($i) => "reihe.$i", range(1, 8))];
    return [['id' => 'feed', 'titel' => ma_layout_seiten()[$seite] ?? $seite, 'art' => 'ressort', 'reihen' => $reihen]];
}

function ma_layout_option(string $seite): string { return $seite === 'startseite' ? 'ma_layout_startseite' : 'ma_layout_ressort_' . ma_layout_ressort_slug($seite); }

/** Karte einer Seite, normalisiert. */
function ma_layout_get(string $seite): array {
    $o = ma_layout_seite_gueltig($seite) ? get_option(ma_layout_option($seite), []) : [];
    $o = is_array($o) ? $o : [];
    $slots = [];
    $gueltig = ma_layout_slots($seite);
    foreach ((array) ($o['slots'] ?? []) as $slot => $e) {
        if (!isset($gueltig[$slot]) || !is_array($e) || (int) ($e['post'] ?? 0) <= 0) continue;
        $slots[$slot] = ['post' => (int) $e['post']] + (!empty($e['doppelt']) ? ['doppelt' => true] : []);
    }
    return ['v' => MA_LAYOUT_VERSION, 'rev' => (int) ($o['rev'] ?? 0), 'stand' => (int) ($o['stand'] ?? 0), 'von' => (int) ($o['von'] ?? 0), 'slots' => $slots];
}

function ma_layout_speichern(string $seite, array $karte): array {
    $karte['v'] = MA_LAYOUT_VERSION;
    $karte['rev'] = (int) ($karte['rev'] ?? 0) + 1;
    $karte['stand'] = time();
    $karte['von'] = function_exists('get_current_user_id') ? (int) get_current_user_id() : 0;
    ksort($karte['slots']);
    update_option(ma_layout_option($seite), $karte, true);
    if (function_exists('do_action')) do_action('ma_layout_gespeichert', $seite, $karte);
    return $karte;
}

/** Steht der Beitrag öffentlich zur Verfügung (veröffentlicht, nicht „Nur Rubrik“)? */
function ma_layout_post_platzierbar(int $id): bool {
    return $id > 0 && get_post_type($id) === 'post' && get_post_status($id) === 'publish' && (string) get_post_meta($id, 'ma_startplatz', true) !== 'aus';
}

/** Feste Plätze einer Seite: Platz => Beitrags-ID, nur platzierbare Beiträge. */
function ma_layout_feste_plaetze(string $seite): array {
    ma_layout_migrieren();
    $raus = [];
    foreach (ma_layout_get($seite)['slots'] as $slot => $e) if (ma_layout_post_platzierbar($e['post'])) $raus[$slot] = $e['post'];
    return $raus;
}

/** Auf welchen Plätzen steht ein Beitrag (alle Seiten)? Seite => [Plätze]. */
function ma_layout_plaetze_von(int $post_id): array {
    $raus = [];
    foreach (array_keys(ma_layout_seiten()) as $seite) {
        $p = array_keys(array_filter(ma_layout_get($seite)['slots'], fn($e) => $e['post'] === $post_id));
        if ($p) $raus[$seite] = $p;
    }
    return $raus;
}

function ma_layout_fehler(string $code, string $meldung, int $status = 400) {
    return new WP_Error($code, $meldung, ['status' => $status]);
}

function ma_layout_rev_pruefen(array $karte, int $rev) {
    if ($rev > 0 && $rev !== $karte['rev']) return ma_layout_fehler('ma_layout_konflikt', 'Jemand anderes hat die Seite inzwischen geändert. Bitte neu laden.', 409);
    return null;
}

/** Aufmacher/Bühne der Startseite spiegeln sich im Meta ma_startplatz (Box „Startseite“, Listen, Freigaben). */
function ma_layout_meta_abgleichen(string $seite, array $slots): void {
    if ($seite !== 'startseite') return;
    $festMeta = ['aufmacher' => true, 'buehne-1' => true, 'buehne-2' => true, 'buehne-3' => true, 'buehne-4' => true];
    $soll = [];
    foreach ($slots as $slot => $e) if (isset($festMeta[$slot])) $soll[$e['post']] = $slot;
    // Beiträge, deren Meta einen festen Platz nennt, der nicht (mehr) stimmt: zurück auf „auto“.
    $alte = get_posts(['post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids',
        'meta_key' => 'ma_startplatz', 'meta_value' => array_keys($festMeta), 'meta_compare' => 'IN']);
    foreach ($alte as $id) if (($soll[(int) $id] ?? '') !== (string) get_post_meta((int) $id, 'ma_startplatz', true)) update_post_meta((int) $id, 'ma_startplatz', $soll[(int) $id] ?? 'auto');
    foreach ($soll as $id => $slot) {
        if ((string) get_post_meta($id, 'ma_startplatz', true) !== $slot) update_post_meta($id, 'ma_startplatz', $slot);
        // Die alte Fixierung (ma_top_pinned) darf den neuen Aufmacher nicht überstimmen.
        if ($slot === 'aufmacher') foreach (get_posts(['post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => 'ma_top_pinned', 'meta_value' => '1', 'post__not_in' => [$id]]) as $alt) update_post_meta((int) $alt, 'ma_top_pinned', '0');
    }
}

function ma_layout_platz_label(string $seite, string $slot): string {
    return (ma_layout_seiten()[$seite] ?? $seite) . ': ' . (ma_layout_slots($seite)[$slot] ?? $slot);
}

/**
 * Beitrag auf einen Platz setzen. $opt: doppelt (bool), rev (int), still (kein Verlauf).
 * Steht der Beitrag auf der Seite schon fest und doppelt fehlt: Fehler.
 */
function ma_layout_set(string $seite, string $slot, int $post, array $opt = []) {
    if (!ma_layout_seite_gueltig($seite) || !isset(ma_layout_slots($seite)[$slot])) return ma_layout_fehler('ma_layout_platz', 'Unbekannter Platz.');
    if (!ma_layout_post_platzierbar($post)) return ma_layout_fehler('ma_layout_beitrag', 'Die Meldung ist nicht veröffentlicht oder steht auf „Nur in der Rubrik“.');
    $karte = ma_layout_get($seite);
    if ($f = ma_layout_rev_pruefen($karte, (int) ($opt['rev'] ?? 0))) return $f;
    $schon = array_keys(array_filter($karte['slots'], fn($e, $s) => $e['post'] === $post && $s !== $slot, ARRAY_FILTER_USE_BOTH));
    $doppelt = !empty($opt['doppelt']);
    if ($schon && !$doppelt) return ma_layout_fehler('ma_layout_doppelt', 'Die Meldung steht auf dieser Seite schon fest (' . ma_layout_slots($seite)[$schon[0]] . '). Für einen zweiten Platz „Nochmal einsetzen“ wählen.', 409);
    $karte['slots'][$slot] = ['post' => $post] + (($schon || $doppelt) ? ['doppelt' => true] : []);
    $karte = ma_layout_speichern($seite, $karte);
    ma_layout_meta_abgleichen($seite, $karte['slots']);
    if (empty($opt['still']) && function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($post, ($schon ? 'Nochmal eingesetzt: ' : 'Fester Platz: ') . ma_layout_platz_label($seite, $slot));
    return $karte;
}

/** Platz freigeben (wieder automatisch). */
function ma_layout_entfernen(string $seite, string $slot, int $rev = 0, bool $still = false) {
    if (!ma_layout_seite_gueltig($seite)) return ma_layout_fehler('ma_layout_platz', 'Unbekannte Seite.');
    $karte = ma_layout_get($seite);
    if ($f = ma_layout_rev_pruefen($karte, $rev)) return $f;
    $alt = $karte['slots'][$slot]['post'] ?? 0;
    unset($karte['slots'][$slot]);
    $karte = ma_layout_speichern($seite, $karte);
    ma_layout_meta_abgleichen($seite, $karte['slots']);
    if ($alt && !$still && function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($alt, 'Platz freigegeben: ' . ma_layout_platz_label($seite, $slot));
    return $karte;
}

/**
 * Zwei Plätze tauschen bzw. verschieben. Beide Plätze werden fest. Ist der
 * Zielplatz leer, wandert der Beitrag; der Quellplatz wird frei (automatisch).
 * $vonPost: Beitrag am Quellplatz, falls dieser automatisch belegt war.
 */
function ma_layout_tauschen(string $seite, string $von, string $nach, int $rev = 0, int $vonPost = 0, int $nachPost = 0) {
    $slots = ma_layout_slots($seite);
    if (!isset($slots[$von]) || !isset($slots[$nach]) || $von === $nach) return ma_layout_fehler('ma_layout_platz', 'Unbekannter Platz.');
    $karte = ma_layout_get($seite);
    if ($f = ma_layout_rev_pruefen($karte, $rev)) return $f;
    $a = $karte['slots'][$von]['post'] ?? $vonPost;
    $b = $karte['slots'][$nach]['post'] ?? $nachPost;
    if (!ma_layout_post_platzierbar($a)) return ma_layout_fehler('ma_layout_beitrag', 'Die Meldung ist nicht veröffentlicht oder steht auf „Nur in der Rubrik“.');
    $neu = $karte['slots'];
    unset($neu[$von], $neu[$nach]);
    $neu[$nach] = ['post' => $a] + (!empty($karte['slots'][$von]['doppelt']) ? ['doppelt' => true] : []);
    if ($b && ma_layout_post_platzierbar($b)) $neu[$von] = ['post' => $b] + (!empty($karte['slots'][$nach]['doppelt']) ? ['doppelt' => true] : []);
    $karte['slots'] = $neu;
    $karte = ma_layout_speichern($seite, $karte);
    ma_layout_meta_abgleichen($seite, $karte['slots']);
    if (function_exists('ma_verlauf_eintragen')) {
        ma_verlauf_eintragen($a, 'Verschoben: ' . ma_layout_platz_label($seite, $nach));
        if ($b && isset($neu[$von])) ma_verlauf_eintragen($b, 'Verschoben: ' . ma_layout_platz_label($seite, $von));
    }
    return $karte;
}

/** Ganze feste Karte ersetzen (Rückgängig, Zurücksetzen). $slots: Platz => ['post' => id, 'doppelt' => bool]. */
function ma_layout_ersetzen(string $seite, array $slots, int $rev = 0) {
    if (!ma_layout_seite_gueltig($seite)) return ma_layout_fehler('ma_layout_platz', 'Unbekannte Seite.');
    $karte = ma_layout_get($seite);
    if ($f = ma_layout_rev_pruefen($karte, $rev)) return $f;
    $gueltig = ma_layout_slots($seite);
    $neu = [];
    foreach ($slots as $slot => $e) {
        $id = (int) (is_array($e) ? ($e['post'] ?? 0) : $e);
        if (!isset($gueltig[$slot]) || !ma_layout_post_platzierbar($id)) continue;
        $neu[$slot] = ['post' => $id] + (is_array($e) && !empty($e['doppelt']) ? ['doppelt' => true] : []);
    }
    $karte['slots'] = $neu;
    $karte = ma_layout_speichern($seite, $karte);
    ma_layout_meta_abgleichen($seite, $karte['slots']);
    return $karte;
}

/** Beitrag von allen festen Plätzen nehmen (eine Seite oder alle). */
function ma_layout_post_entfernen(int $post_id, string $nurSeite = '', bool $still = false): void {
    foreach (array_keys(ma_layout_seiten()) as $seite) {
        if ($nurSeite !== '' && $seite !== $nurSeite) continue;
        $karte = ma_layout_get($seite);
        $slots = array_keys(array_filter($karte['slots'], fn($e) => $e['post'] === $post_id));
        if (!$slots) continue;
        foreach ($slots as $s) unset($karte['slots'][$s]);
        $karte = ma_layout_speichern($seite, $karte);
        ma_layout_meta_abgleichen($seite, $karte['slots']);
        if (!$still && function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($post_id, 'Von festen Plätzen genommen: ' . (ma_layout_seiten()[$seite] ?? $seite));
    }
}

/* ---------------------------------------------------------- Warnungen */

/** Hinweise zu einem festen Platz (kein Fehler, der Platz gilt trotzdem). */
function ma_layout_warnungen(string $seite, string $slot, int $post_id): array {
    $w = [];
    if (get_post_status($post_id) !== 'publish') $w[] = 'nicht-veroeffentlicht';
    if ((string) get_post_meta($post_id, 'ma_startplatz', true) === 'aus') $w[] = 'nur-rubrik';
    $bild = (int) get_post_thumbnail_id($post_id);
    $src = $bild ? wp_get_attachment_image_src($bild, 'full') : false;
    $breite = $src ? (int) ($src[1] ?? 0) : 0;
    $alt = $bild ? (string) get_post_meta($bild, '_wp_attachment_image_alt', true) : '';
    $art = preg_replace('/^[a-z]+\.|[-.]\d+$/', '', $slot); // aufmacher, buehne, gross, mittel, zeilen, lead, raster, reihe
    $brauchtBild = in_array($art, ['aufmacher', 'buehne', 'gross', 'mittel', 'raster'], true);
    if ($brauchtBild && !$bild) $w[] = 'kein-bild';
    elseif ($bild && preg_match('/logo|wappen/i', $alt . ' ' . (string) ($src[0] ?? ''))) $w[] = 'logo-motiv';
    elseif ($bild && (($art === 'aufmacher' || $art === 'gross') && $breite && $breite < 480 || ($art === 'buehne' || $art === 'mittel') && $breite && $breite < 360)) $w[] = 'bild-klein';
    if ($seite === 'startseite' && has_category('sport', $post_id)) $w[] = 'sport';
    $slug = ma_layout_ressort_slug($seite);
    if ($slug !== '' && $slug !== 'nachrichten' && !has_category($slug, $post_id)) $w[] = 'nicht-im-ressort';
    return $w;
}

function ma_layout_warnung_text(string $w): string {
    return ['nicht-veroeffentlicht' => 'Nicht veröffentlicht', 'nur-rubrik' => 'Steht auf „Nur in der Rubrik“', 'kein-bild' => 'Ohne Bild auf einem Bildplatz', 'logo-motiv' => 'Logo oder Wappen als Bild',
        'bild-klein' => 'Bild zu klein für diesen Platz', 'sport' => 'Sportmeldung auf der Startseite', 'nicht-im-ressort' => 'Gehört nicht zu diesem Ressort', 'reihe-unvollstaendig' => 'Reihe nicht vollständig gefüllt'][$w] ?? $w;
}

/* ---------------------------------------------------------- Auflösung */

/**
 * Belegung einer Seite: feste Plätze zuerst, dann die Automatik.
 *
 * $fest: Platz => Beitrags-ID (ma_layout_feste_plaetze). $kandidaten: WP_Post[]
 * in Automatik-Reihenfolge. $regeln (vom Theme, damit die Logik ohne Theme
 * testbar bleibt):
 *   Startseite: vorrang (WP_Post[] nach Zone), buehnenTauglich(p), breite(p),
 *     motiv(p) => int, bildtyp(p) => string, echtesBild(p), ressort(p) => slug,
 *     kategorie(p, slug) => bool, sektionen (id => ['s' => Konfig mit nimm,
 *     jeRessort, fenster, zeilen]), holen(id) => ?WP_Post
 *   Ressort: raster (bool), echtesBild(p), holen(id)
 * Rückgabe Startseite: aufmacher, neben (1..4), sektionen (id => s/gross/
 * mittel/zeilen), plaetze (Platz => post/fest/warnungen).
 * Rückgabe Ressort: lead, raster, reihen, plaetze.
 */
function ma_layout_aufloesen(string $seite, array $fest, array $kandidaten, array $r): array {
    return $seite === 'startseite' ? ma_layout_aufloesen_startseite($fest, $kandidaten, $r) : ma_layout_aufloesen_ressort($seite, $fest, $kandidaten, $r);
}

function ma_layout_aufloesen_startseite(array $fest, array $alle, array $r): array {
    $vorrang = $r['vorrang'] ?? $alle;
    $holen = $r['holen'] ?? fn($id) => null;
    $motiv = $r['motiv'] ?? fn($p) => 0;
    $tauglich = $r['buehnenTauglich'] ?? fn($p) => true;
    $breite = $r['breite'] ?? fn($p) => 0;
    $bildtyp = $r['bildtyp'] ?? fn($p) => '';
    $echt = $r['echtesBild'] ?? fn($p) => true;
    $ressort = $r['ressort'] ?? fn($p) => '';
    $kat = $r['kategorie'] ?? fn($p, $slug) => false;
    $warn = function_exists('ma_layout_warnungen') && empty($r['ohneWarnungen']) ? fn($slot, $p) => ma_layout_warnungen('startseite', $slot, $p->ID) : fn($slot, $p) => [];
    $byId = [];
    foreach ($alle as $p) $byId[$p->ID] = $p;
    $post = function (int $id) use (&$byId, $holen) { if (!isset($byId[$id])) { $p = $holen($id); if ($p) $byId[$id] = $p; } return $byId[$id] ?? null; };
    $MOTIV_MAX = 2; $vergeben = []; $motive = []; $plaetze = [];
    $motivFrei = function ($p, array $extra = []) use (&$motive, $motiv, $MOTIV_MAX) { $k = $motiv($p); return !$k || (($motive[$k] ?? 0) + ($extra[$k] ?? 0)) < $MOTIV_MAX; };
    $belegen = function ($p) use (&$motive, &$vergeben, $motiv) { $vergeben[$p->ID] = true; $k = $motiv($p); if ($k) $motive[$k] = ($motive[$k] ?? 0) + 1; };
    $festPost = function (string $slot) use ($fest, $post) { return !empty($fest[$slot]) ? $post((int) $fest[$slot]) : null; };
    $merke = function (string $slot, $p, bool $istFest) use (&$plaetze, $warn) { if ($p) $plaetze[$slot] = ['post' => $p->ID, 'fest' => $istFest, 'warnungen' => $istFest ? $warn($slot, $p) : []]; };
    // Alle festen Beiträge gelten als vergeben, auch für den Motivzähler.
    foreach ($fest as $slot => $id) { $p = $post((int) $id); if ($p) $belegen($p); }

    // Aufmacher: fest, sonst jüngste bühnentaugliche Meldung mit großem Bild.
    $aufmacher = $festPost('aufmacher');
    if (!$aufmacher) foreach ($vorrang as $p) if (!isset($vergeben[$p->ID]) && $tauglich($p) && $breite($p) >= 480 && $bildtyp($p) !== 'place') { $aufmacher = $p; $belegen($p); break; }
    $merke('aufmacher', $aufmacher, !empty($fest['aufmacher']));

    // Nebenplätze 1–4: fest auf ihren Platz, Rest automatisch.
    $neben = array_fill(1, 4, null);
    for ($i = 1; $i <= 4; $i++) { $neben[$i] = $festPost("buehne-$i"); $merke("buehne-$i", $neben[$i], true); }
    $blaulicht = ($aufmacher && $kat($aufmacher, 'blaulicht')) ? 1 : 0;
    foreach ($neben as $p) if ($p && $kat($p, 'blaulicht')) $blaulicht++;
    $bMotive = array_filter(array_map($motiv, array_filter(array_merge([$aufmacher], $neben))));
    foreach ([false, true] as $mitOrt) {
        foreach ($vorrang as $p) {
            if (!in_array(null, $neben, true)) break;
            if (isset($vergeben[$p->ID]) || !$tauglich($p) || $breite($p) < 360) continue;
            $istOrt = $bildtyp($p) === 'place';
            if (!$mitOrt && $istOrt) continue;
            if (!$motivFrei($p) || in_array($motiv($p), $bMotive, true)) continue;
            if ($kat($p, 'blaulicht')) { if ($blaulicht >= 2) continue; $blaulicht++; }
            $platz = array_search(null, $neben, true); $neben[$platz] = $p; $belegen($p); $bMotive[] = $motiv($p);
            $merke("buehne-$platz", $p, false);
        }
    }

    // Rubrikflächen: erst die ressortgebundenen, dann die Leitsektion.
    $belegung = [];
    foreach ((array) ($r['sektionen'] ?? []) as $id => $s) {
        $frei = array_slice(array_values(array_filter($alle, fn($p) => !isset($vergeben[$p->ID]) && $s['nimm']($p))), 0, $s['fenster'] ?? 20);
        $jeRessort = [];
        $waehle = function (array $pool, int $n, array $schon) use ($s, &$jeRessort, $motiv, $motivFrei, $ressort) {
            $gez = $jeRessort; $extra = []; $raus = [];
            foreach ($schon as $a) { $rs = $ressort($a); $gez[$rs] = ($gez[$rs] ?? 0) + 1; $k = $motiv($a); if ($k) $extra[$k] = ($extra[$k] ?? 0) + 1; }
            foreach ($pool as $a) {
                if (count($raus) >= $n) break;
                if (in_array($a, $schon, true)) continue;
                $rs = $ressort($a);
                if (!empty($s['jeRessort']) && ($gez[$rs] ?? 0) >= $s['jeRessort']) continue;
                if (!$motivFrei($a, $extra)) continue;
                $gez[$rs] = ($gez[$rs] ?? 0) + 1; $k = $motiv($a); if ($k) $extra[$k] = ($extra[$k] ?? 0) + 1;
                $raus[] = $a;
            }
            return $raus;
        };
        $ortZuletzt = fn(array $l) => array_merge(array_filter($l, fn($a) => $bildtyp($a) !== 'place'), array_filter($l, fn($a) => $bildtyp($a) === 'place'));
        $lF = $ortZuletzt(array_filter($frei, function ($a) use ($breite, $echt, $r) { $b = ($r['bild'] ?? fn($p) => null)($a); return $echt($a) && $breite($a) >= 480 && (!$b || !$b['h'] || $b['w'] / $b['h'] >= 1.2); }));
        $mF = $ortZuletzt(array_filter($frei, fn($a) => $echt($a) && $breite($a) >= 360));
        $fg = []; $fm = []; $fz = [];
        for ($i = 1; $i <= 2; $i++) $fg[$i] = $festPost("$id.gross.$i");
        for ($i = 1; $i <= 3; $i++) $fm[$i] = $festPost("$id.mittel.$i");
        for ($i = 1; $i <= 4; $i++) $fz[$i] = $festPost("$id.zeilen.$i");
        $hatFest = array_filter($fg) || array_filter($fm) || array_filter($fz);
        $nZeilen = $s['zeilen'] ?? 2;
        foreach ($fz as $i => $p) if ($p) $nZeilen = max($nZeilen, $i);
        $nZeilen = min(4, $nZeilen);
        if (!$hatFest) {
            // Bisherige Regel: nur vollständige Reihen (2 groß + 3 mittel, 3 mittel oder 2 groß).
            $gross = $waehle($lF, 2, []);
            $mittel = count($gross) === 2 ? $waehle($mF, 3, $gross) : [];
            if (count($gross) !== 2 || count($mittel) !== 3) {
                $nurMittel = $waehle($mF, 3, []);
                if (count($nurMittel) === 3) { $gross = []; $mittel = $nurMittel; }
                elseif (count($gross) === 2) { $mittel = []; }
                else { $gross = []; $mittel = []; }
            }
            foreach (array_merge($gross, $mittel) as $a) { $rs = $ressort($a); $jeRessort[$rs] = ($jeRessort[$rs] ?? 0) + 1; $belegen($a); }
            foreach ($gross as $i => $a) $merke("$id.gross." . ($i + 1), $a, false);
            foreach ($mittel as $i => $a) $merke("$id.mittel." . ($i + 1), $a, false);
            $zeilen = [];
            foreach ($frei as $a) {
                if (count($zeilen) >= $nZeilen) break;
                if (isset($vergeben[$a->ID]) || !$motivFrei($a)) continue;
                $zeilen[] = $a; $belegen($a); $merke("$id.zeilen." . count($zeilen), $a, false);
            }
        } else {
            // Mit festen Karten wird die Reihe immer ausgegeben, Lücken füllt die Automatik.
            $schon = array_values(array_filter(array_merge($fg, $fm)));
            $gross = []; $mittel = []; $zeilen = [];
            $fuelle = function (array $festListe, array $pool, string $art) use (&$schon, &$jeRessort, $waehle, $belegen, $merke, $ressort, $id, &$vergeben) {
                $raus = [];
                foreach ($festListe as $i => $p) {
                    $istFest = (bool) $p;
                    if (!$p) { $n = $waehle(array_values(array_filter($pool, fn($a) => !isset($vergeben[$a->ID]))), 1, $schon); $p = $n[0] ?? null; if ($p) { $belegen($p); $schon[] = $p; } }
                    if (!$p) continue;
                    $rs = $ressort($p); $jeRessort[$rs] = ($jeRessort[$rs] ?? 0) + 1;
                    $raus[] = $p; $merke("$id.$art.$i", $p, $istFest);
                }
                return $raus;
            };
            $wollenGross = array_filter($fg) || $waehle($lF, 2, $schon);
            $gross = $wollenGross ? $fuelle($fg, $lF, 'gross') : [];
            $mittel = $fuelle($fm, $mF, 'mittel');
            foreach ($fz as $i => $p) {
                if ($i > $nZeilen) break;
                $istFest = (bool) $p;
                if (!$p) foreach ($frei as $a) { if (isset($vergeben[$a->ID]) || !$motivFrei($a)) continue; $p = $a; $belegen($a); break; }
                if (!$p) continue;
                $zeilen[] = $p; $merke("$id.zeilen.$i", $p, $istFest);
            }
            foreach (['gross' => [$gross, 2], 'mittel' => [$mittel, 3]] as $art => [$liste, $soll]) {
                if ($liste && count($liste) < $soll) foreach ($plaetze as $slot => &$e) if (str_starts_with($slot, "$id.$art.") && $e['fest']) $e['warnungen'][] = 'reihe-unvollstaendig';
                unset($e);
            }
        }
        $belegung[$id] = ['s' => $s, 'gross' => $gross, 'mittel' => $mittel, 'zeilen' => $zeilen];
    }
    return ['aufmacher' => $aufmacher, 'neben' => $neben, 'sektionen' => $belegung, 'plaetze' => $plaetze];
}

function ma_layout_aufloesen_ressort(string $seite, array $fest, array $posts, array $r): array {
    $holen = $r['holen'] ?? fn($id) => null;
    $echt = $r['echtesBild'] ?? fn($p) => true;
    $warn = function_exists('ma_layout_warnungen') && empty($r['ohneWarnungen']) ? fn($slot, $p) => ma_layout_warnungen($seite, $slot, $p->ID) : fn($slot, $p) => [];
    $byId = [];
    foreach ($posts as $p) $byId[$p->ID] = $p;
    $post = function (int $id) use (&$byId, $holen) { if (!isset($byId[$id])) { $p = $holen($id); if ($p) $byId[$id] = $p; } return $byId[$id] ?? null; };
    $festIds = [];
    foreach ($fest as $slot => $id) if ($post((int) $id)) $festIds[(int) $id] = true;
    $plaetze = [];
    $merke = function (string $slot, $p, bool $istFest) use (&$plaetze, $warn) { if ($p) $plaetze[$slot] = ['post' => $p->ID, 'fest' => $istFest, 'warnungen' => $istFest ? $warn($slot, $p) : []]; };
    $rest = array_values(array_filter($posts, fn($p) => !isset($festIds[$p->ID])));
    $naechster = function (?callable $bed = null) use (&$rest) { foreach ($rest as $i => $p) if (!$bed || $bed($p)) { array_splice($rest, $i, 1); return $p; } return null; };
    $lead = !empty($fest['lead']) ? $post((int) $fest['lead']) : $naechster();
    $merke('lead', $lead, !empty($fest['lead']));
    $raster = [];
    if (!empty($r['raster'])) {
        for ($i = 1; $i <= 6; $i++) {
            $istFest = !empty($fest["raster.$i"]);
            $p = $istFest ? $post((int) $fest["raster.$i"]) : $naechster(fn($a) => $echt($a));
            if (!$p) continue;
            $raster[] = $p; $merke("raster.$i", $p, $istFest);
        }
    }
    $reihen = [];
    for ($i = 1; $i <= 8; $i++) {
        $istFest = !empty($fest["reihe.$i"]);
        $p = $istFest ? $post((int) $fest["reihe.$i"]) : $naechster();
        if (!$p) continue;
        $reihen[] = $p; $merke("reihe.$i", $p, $istFest);
    }
    foreach ($rest as $p) $reihen[] = $p;
    return ['lead' => $lead, 'raster' => $raster, 'reihen' => $reihen, 'plaetze' => $plaetze];
}

/* ---------------------------------------------------------- Übernahme bestehender Plätze */

/** Einmalig: feste Plätze aus ma_startplatz / ma_top_pinned in die Karte der Startseite. Nichts wird gelöscht. */
function ma_layout_migrieren(): void {
    if (get_option('ma_layout_migration') === '1') return;
    if (!get_option('ma_layout_startseite')) {
        $slots = [];
        foreach (['aufmacher', 'buehne-1', 'buehne-2', 'buehne-3', 'buehne-4'] as $platz) {
            $q = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1, 'meta_key' => 'ma_startplatz', 'meta_value' => $platz, 'orderby' => 'modified', 'order' => 'DESC']);
            if ($q) $slots[$platz] = ['post' => (int) $q[0]->ID];
        }
        if (empty($slots['aufmacher'])) {
            foreach (get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 5, 'meta_key' => 'ma_top_pinned', 'meta_value' => '1', 'orderby' => 'date', 'order' => 'DESC']) as $p) {
                if ((string) get_post_meta($p->ID, 'ma_startplatz', true) === 'aus') continue;
                $bis = (string) get_post_meta($p->ID, 'ma_top_until', true);
                if ($bis === '' || strtotime($bis) > time()) { $slots['aufmacher'] = ['post' => (int) $p->ID]; break; }
            }
        }
        $karte = ma_layout_speichern('startseite', ['rev' => 0, 'slots' => $slots]);
        ma_layout_meta_abgleichen('startseite', $karte['slots']);
        if (function_exists('ma_verlauf_eintragen')) foreach ($slots as $platz => $e) ma_verlauf_eintragen($e['post'], 'Platz in die Layout-Karte übernommen: ' . ma_layout_platz_label('startseite', $platz), '', 0);
    }
    update_option('ma_layout_migration', '1', true);
}
add_action('admin_init', 'ma_layout_migrieren');

/** Verlässt ein Beitrag die Veröffentlichung, verschwindet er von allen festen Plätzen. */
add_action('transition_post_status', function (string $neu, string $alt, $p): void {
    if (!$p instanceof WP_Post || $p->post_type !== 'post' || $neu === 'publish' || $alt !== 'publish') return;
    ma_layout_post_entfernen($p->ID, '', true); // Der Statuswechsel selbst steht im Verlauf (redaktion.php).
}, 20, 3);
/** Wird ein Beitrag veröffentlicht, dessen Box „Startseite“ einen festen Platz nennt, kommt er auf diesen Platz. */
add_action('transition_post_status', function (string $neu, string $alt, $p): void {
    if (!$p instanceof WP_Post || $p->post_type !== 'post' || $neu !== 'publish' || $alt === 'publish') return;
    $platz = (string) get_post_meta($p->ID, 'ma_startplatz', true);
    if (!preg_match('/^(aufmacher|buehne-[1-4])$/', $platz)) return;
    $fest = ma_layout_get('startseite')['slots'];
    if (($fest[$platz]['post'] ?? 0) !== $p->ID) ma_layout_set('startseite', $platz, $p->ID, ['still' => true]);
}, 25, 3);
add_action('before_delete_post', function (int $id): void { if (get_post_type($id) === 'post') ma_layout_post_entfernen($id, '', true); });
