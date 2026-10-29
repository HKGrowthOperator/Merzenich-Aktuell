<?php
/**
 * Werbeanfrage mit Fotos und Video (29.10.2026, Entscheidung Betreiber).
 *
 * Im Formular „Werbung“ ([ma_formular typ="werbung"], forms.php) fragen Firmen bei
 * „Werbepaket“ und „Unternehmensprofil“ alles ab, was eine Anzeige wie die der fünf
 * Firmen braucht: Firma, Website (Klickziel), Format (werbeformate.php, ohne Preis),
 * Wunschstart, je Motiv Überschrift, Satz und Foto, dazu ein Video (MP4/WebM bis
 * 6 MB, größere als Link) und ein Logo. Der Browser verkleinert Fotos vor dem
 * Absenden; die ganze Anfrage bleibt unter 7,5 MB (der Server lehnt ab etwa 8 MB ab).
 *
 * Die Anfrage landet im Eingang (eingang.php) mit allen Angaben (Meta
 * _ma_eingang_werbung, Dateien mit Rolle _ma_eingang_rolle). Dort legt die
 * Redaktion mit einem Klick Firmenprofil und Anzeigen an (ma_werbeanfrage_anlegen),
 * prüft sie in den Freigaben und gibt sie frei. Der Absender bekommt eine
 * Eingangsbestätigung ohne Preis; die Redaktion eine Mail mit Format und Preis.
 */
if (!defined('ABSPATH')) { exit; }

const MA_WA_ARTEN = ['banner', 'unternehmen'];
const MA_WA_FOTO_MAX = 5 * 1024 * 1024;
const MA_WA_VIDEO_MAX = 6 * 1024 * 1024;
const MA_WA_LOGO_MAX = 2 * 1024 * 1024;
const MA_WA_GESAMT_MAX = 7680 * 1024;
const MA_WA_PRO_STUNDE = 5;

/** Höchstzahl der Motive über alle buchbaren Formate (1 bis 4, Vorgabe 2). */
function ma_werbeanfrage_motive(): int {
    $n = 0;
    foreach (function_exists('ma_werbeformate_aktiv') ? ma_werbeformate_aktiv() : [] as $f) $n = max($n, (int) $f['motive']);
    return max(1, min(4, $n ?: 2));
}

/** Öffentliche Kurzbeschreibung eines Formats, ohne Preis. */
function ma_werbeformat_oeffentlich(array $f): string {
    $plaetze = count($f['plaetze']) >= count(function_exists('ma_werbeformate_plaetze') ? ma_werbeformate_plaetze() : []) ? 'alle Werbeplätze' : count($f['plaetze']) . ' Werbeplätze';
    return $plaetze . ', ' . $f['motive'] . ' Motiv' . ($f['motive'] > 1 ? 'e' : '') . ' mit Foto' . ($f['video'] ? ' und Video' : '') . ($f['profil'] ? ', Firmenprofil unter „Unternehmen“' : '') . ', ' . $f['tage'] . ' Tage Laufzeit. Preis auf Anfrage.';
}

/** Felder der Werbeanfrage im Formular „Werbung“ (sichtbar bei Werbepaket und Unternehmensprofil). */
function ma_werbeanfrage_html(): string {
    $formate = function_exists('ma_werbeformate_aktiv') ? ma_werbeformate_aktiv() : [];
    $motive = ma_werbeanfrage_motive();
    $e = 'esc_html'; $a = 'esc_attr';
    $h = '<div class="ma-wa" data-ma-wa>';
    $h .= '<fieldset class="ma-wa__gruppe"><legend>Ihre Firma</legend>';
    $h .= '<p><label>Firma<br><input name="wa_firma" maxlength="120" autocomplete="organization" data-pflicht="banner unternehmen"></label></p>';
    $h .= '<p><label>Website (dorthin führt ein Klick auf Ihre Anzeige)<br><input name="wa_website" type="text" maxlength="200" placeholder="www.ihre-firma.de" inputmode="url" autocomplete="url" data-pflicht="banner"></label></p>';
    $h .= '<p><label>Branche oder Angebot in wenigen Worten (optional)<br><input name="wa_branche" maxlength="80" placeholder="z. B. Gebäudereinigung"></label></p>';
    $h .= '<p><label>Adresse (optional)<br><input name="wa_adresse" maxlength="160" autocomplete="street-address"></label></p>';
    $h .= '<p><label>Kurz über Ihre Firma, für das Profil (optional)<br><textarea name="wa_beschreibung" rows="3" maxlength="600"></textarea></label></p>';
    $h .= '<p><label>Logo (optional, JPG, PNG oder WebP, höchstens 2 MB)<br><input type="file" name="wa_logo" accept="image/jpeg,image/png,image/webp" data-ma-wa-datei data-max="' . MA_WA_LOGO_MAX . '"></label></p>';
    $h .= '</fieldset>';
    $h .= '<fieldset class="ma-wa__gruppe" data-nur="banner"><legend>Ihre Anzeige</legend>';
    if (count($formate) === 1) {
        $k = (string) array_key_first($formate); $f = $formate[$k];
        $h .= '<input type="hidden" name="wa_format" value="' . $a($k) . '"><p class="ma-wa__format"><strong>' . $e($f['name']) . '</strong><br>' . $e(ma_werbeformat_oeffentlich($f)) . '</p>';
    } elseif ($formate) {
        $h .= '<p><label>Format<br><select name="wa_format" data-pflicht="banner"><option value="">Bitte wählen</option>';
        foreach ($formate as $k => $f) $h .= '<option value="' . $a($k) . '">' . $e($f['name'] . ': ' . ma_werbeformat_oeffentlich($f)) . '</option>';
        $h .= '</select></label></p>';
    }
    $h .= '<p><label>Gewünschter Start (optional)<br><input name="wa_start" type="date" min="' . $a(wp_date('Y-m-d')) . '"></label></p>';
    $h .= '<p class="ma-wa__hinweis">So wirkt Ihre Anzeige am besten: Foto im Querformat, mindestens 1600 × 900 Pixel, das Wichtige in der Mitte; ohne Schrift im Bild. Überschrift und Satz setzen wir als Text darunter. Video: 6 bis 15 Sekunden, ohne Ton, ohne Schrift.</p>';
    for ($i = 1; $i <= $motive; $i++) {
        $pflicht = $i === 1 ? ' data-pflicht="banner"' : '';
        $h .= '<div class="ma-wa__motiv"><p class="ma-wa__motiv-titel">Motiv ' . $i . ($i === 1 ? '' : ' (optional)') . '</p>';
        $h .= '<p><label>Überschrift (bis 60 Zeichen)<br><input name="wa_titel[' . $i . ']" maxlength="60"' . $pflicht . '></label></p>';
        $h .= '<p><label>Ein Satz dazu (bis 140 Zeichen)<br><input name="wa_text[' . $i . ']" maxlength="140"></label></p>';
        $h .= '<p><label>Foto (JPG, PNG oder WebP)<br><input type="file" name="wa_foto_' . $i . '" accept="image/jpeg,image/png,image/webp" data-ma-wa-datei data-max="' . MA_WA_FOTO_MAX . '" data-ma-verkleinern="1600"' . $pflicht . '></label></p></div>';
    }
    $h .= '<p><label>Video (optional, MP4 oder WebM, stumm, höchstens 6 MB)<br><input type="file" name="wa_video" accept="video/mp4,video/webm" data-ma-wa-datei data-max="' . MA_WA_VIDEO_MAX . '"></label></p>';
    $h .= '<p><label>Größeres Video als Link (optional, z. B. WeTransfer, Google Drive, Dropbox)<br><input name="wa_video_link" type="text" maxlength="300" placeholder="https://" inputmode="url"></label></p>';
    $h .= '</fieldset>';
    $h .= '<p class="ma-bildrechte"><label><input type="checkbox" name="wa_einwilligung" value="1" data-pflicht="banner unternehmen"> ' . $e(ma_werbeanfrage_einwilligung_text()) . '</label></p>';
    $h .= '<p class="ma-wa__meldung" role="alert" data-ma-wa-meldung hidden></p>';
    $h .= '</div>';
    return $h . ma_werbeanfrage_script();
}

function ma_werbeanfrage_einwilligung_text(): string {
    return 'Ich willige ein, dass Merzenich Aktuell nach Prüfung ein Profil meiner Firma und die Anzeigen mit diesen Texten, Fotos und dem Video veröffentlicht. Preis und Zeitraum stimmen wir vorher ab; ohne Auftrag wird nichts veröffentlicht. (Pflicht)';
}

/**
 * Skript: Felder je Art ein- und ausblenden (Pflichtfelder nur, wenn sichtbar;
 * ausgeblendete Felder werden abgeschaltet und nicht mitgeschickt), Fotos vor dem
 * Absenden auf 1600 px verkleinern (Logo nicht: Transparenz), Größen prüfen.
 * Ein zweiter Klick schickt nichts doppelt; nach „Zurück“ ist der Knopf wieder
 * frei. Ohne JavaScript stehen alle Felder da und der Server prüft.
 */
function ma_werbeanfrage_script(): string {
    return '<script>(function(f){if(!f)return;var w=f.querySelector("[data-ma-wa]"),art=f.querySelector("[name=listing_kind]"),meld=f.querySelector("[data-ma-wa-meldung]");if(!w||!art)return;'
        . 'var GESAMT=' . MA_WA_GESAMT_MAX . ',laeuft=false,knopf=null,knopfText="";'
        // Nachricht und allgemeiner Anhang stehen im Formular erst nach diesem Skript: erst beim Umschalten suchen.
        . 'function zeige(){var a=art.value,an=a==="banner"||a==="unternehmen",sonst=f.querySelector("[data-ma-datei]");sonst=sonst&&sonst.closest("p");w.hidden=!an;if(sonst){sonst.hidden=an;sonst.querySelector("input").disabled=an;}'
        . 'w.querySelectorAll("[data-nur]").forEach(function(g){g.hidden=g.getAttribute("data-nur").split(" ").indexOf(a)<0;});'
        . 'w.querySelectorAll("input,select,textarea").forEach(function(x){x.disabled=!an||!!x.closest("[hidden]");});'
        . 'w.querySelectorAll("[data-pflicht]").forEach(function(x){x.required=!x.disabled&&x.getAttribute("data-pflicht").split(" ").indexOf(a)>=0;});'
        . 'var n=f.querySelector("[name=message]");if(n){n.required=!an;var l=n.closest("label");if(l&&l.firstChild&&l.firstChild.nodeType===3)l.firstChild.nodeValue=an?"Anmerkungen (optional)":"Nachricht";}rechte();}'
        . 'function rechte(){var c=f.querySelector("[data-ma-bildrechte]");if(c)c.required=[].some.call(f.querySelectorAll("input[type=file]"),function(d){return !d.disabled&&d.files&&d.files.length;});}'
        . 'function sag(t){meld.textContent=t;meld.hidden=!t;if(t)meld.scrollIntoView({block:"center"});}'
        . 'function frei(){laeuft=false;if(knopf){knopf.disabled=false;knopf.textContent=knopfText;}}'
        . 'art.addEventListener("change",zeige);if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",zeige);else zeige();'
        . 'window.addEventListener("pageshow",function(e){if(e.persisted)frei();});'
        . 'f.addEventListener("change",function(e){if(e.target.type==="file")rechte();});'
        . 'w.addEventListener("change",function(e){var d=e.target;if(!d.matches||!d.matches("[data-ma-wa-datei]")||!d.files[0])return;var max=+d.getAttribute("data-max");if(!d.hasAttribute("data-ma-verkleinern")&&d.files[0].size>max){sag("„"+d.files[0].name+"“ ist größer als "+Math.round(max/1048576)+" MB. Bitte kleiner speichern oder als Link angeben.");d.value="";rechte();}else sag("");});'
        . 'function klein(d){var file=d.files&&d.files[0],px=+d.getAttribute("data-ma-verkleinern");if(d.disabled||!file||!/^image\//.test(file.type)||!window.createImageBitmap||!window.DataTransfer)return Promise.resolve();'
        . 'return createImageBitmap(file,{imageOrientation:"from-image"}).then(function(b){if(b.width<=px&&file.size<=1.5*1048576)return;var s=Math.min(1,px/b.width),c=document.createElement("canvas");c.width=Math.round(b.width*s);c.height=Math.round(b.height*s);var g=c.getContext("2d");g.fillStyle="#fff";g.fillRect(0,0,c.width,c.height);g.drawImage(b,0,0,c.width,c.height);'
        . 'return new Promise(function(r){c.toBlob(function(bl){try{if(bl&&bl.size<file.size){var dt=new DataTransfer();dt.items.add(new File([bl],file.name.replace(/\.\w+$/,"")+".jpg",{type:"image/jpeg"}));d.files=dt.files;}}catch(x){}r();},"image/jpeg",.85);});}).catch(function(){});}'
        . 'f.addEventListener("submit",function(e){if(w.hidden)return;e.preventDefault();if(laeuft)return;laeuft=true;knopf=f.querySelector("[type=submit]");if(knopf){knopfText=knopf.textContent;knopf.disabled=true;knopf.textContent="Wird vorbereitet …";}'
        . 'var ds=[].slice.call(w.querySelectorAll("[data-ma-verkleinern]"));'
        . 'Promise.all(ds.map(klein)).then(function(){var sum=0,zu="";[].slice.call(f.querySelectorAll("input[type=file]")).forEach(function(d){if(d.disabled)return;[].slice.call(d.files||[]).forEach(function(x){sum+=x.size;if(x.size>+(d.getAttribute("data-max")||5242880))zu=x.name;});});'
        . 'if(zu){frei();sag("„"+zu+"“ ist zu groß.");return;}if(sum>GESAMT){frei();sag("Alle Dateien zusammen sind "+(sum/1048576).toFixed(1)+" MB groß, erlaubt sind 7,5 MB. Bitte das Video kleiner speichern oder als Link angeben.");return;}'
        . 'sag("");if(knopf)knopf.textContent="Wird gesendet …";HTMLFormElement.prototype.submit.call(f);}).catch(function(){frei();HTMLFormElement.prototype.submit.call(f);});});'
        . '})(document.currentScript.closest("form"));</script>';
}

/** Ein- und Ausgabe säubern: http(s)-Adresse, ohne Schema mit https:// davor. */
function ma_werbeanfrage_url(string $u): string {
    $u = trim($u);
    if ($u === '') return '';
    if (!preg_match('#^https?://#i', $u)) $u = 'https://' . ltrim($u, '/');
    $u = esc_url_raw($u, ['http', 'https']);
    $host = (string) wp_parse_url($u, PHP_URL_HOST);
    return $host !== '' && str_contains($host, '.') ? $u : '';
}

/**
 * Angaben der Werbeanfrage lesen und prüfen. $post: $_POST (ungeslasht),
 * $files: $_FILES. Rückgabe ['daten' => [...], 'fehler' => ''].
 */
function ma_werbeanfrage_lesen(string $art, array $post, array $files): array {
    $t = fn(string $k, int $max): string => mb_substr(sanitize_text_field((string) ($post[$k] ?? '')), 0, $max);
    $d = [
        'art' => $art, 'firma' => $t('wa_firma', 120), 'website' => ma_werbeanfrage_url((string) ($post['wa_website'] ?? '')),
        'branche' => $t('wa_branche', 80), 'adresse' => $t('wa_adresse', 160),
        'beschreibung' => mb_substr(sanitize_textarea_field((string) ($post['wa_beschreibung'] ?? '')), 0, 600),
        'format' => '', 'start' => '', 'motive' => [], 'video_link' => ma_werbeanfrage_url((string) ($post['wa_video_link'] ?? '')),
        'einwilligung' => !empty($post['wa_einwilligung']) ? '1' : '',
    ];
    $f = fn(string $k): bool => !empty($files[$k]['name']) && (int) ($files[$k]['error'] ?? 0) === 0;
    if (mb_strlen($d['firma']) < 2) return ['daten' => $d, 'fehler' => 'Bitte geben Sie den Namen Ihrer Firma an.'];
    if (trim((string) ($post['wa_website'] ?? '')) !== '' && $d['website'] === '') return ['daten' => $d, 'fehler' => 'Die Website-Adresse ist nicht gültig, bitte so: https://www.ihre-firma.de/'];
    if ($d['einwilligung'] !== '1') return ['daten' => $d, 'fehler' => 'Bitte willigen Sie in die Veröffentlichung nach Prüfung ein.'];
    $groessen = ['wa_logo' => MA_WA_LOGO_MAX, 'wa_video' => MA_WA_VIDEO_MAX];
    if ($art === 'banner') {
        if ($d['website'] === '') return ['daten' => $d, 'fehler' => 'Bitte geben Sie Ihre Website an; dorthin führt ein Klick auf die Anzeige.'];
        $formate = function_exists('ma_werbeformate_aktiv') ? ma_werbeformate_aktiv() : [];
        $k = sanitize_key((string) ($post['wa_format'] ?? ''));
        if ($k === '' && count($formate) === 1) $k = (string) array_key_first($formate);
        if (!isset($formate[$k])) return ['daten' => $d, 'fehler' => 'Bitte wählen Sie ein Format.'];
        $d['format'] = $k;
        $s = sanitize_text_field((string) ($post['wa_start'] ?? ''));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s) && strtotime($s)) $d['start'] = max($s, wp_date('Y-m-d'));
        $titel = (array) ($post['wa_titel'] ?? []); $text = (array) ($post['wa_text'] ?? []);
        for ($i = 1; $i <= $formate[$k]['motive']; $i++) {
            $m = ['titel' => mb_substr(sanitize_text_field((string) ($titel[$i] ?? '')), 0, 60), 'text' => mb_substr(sanitize_text_field((string) ($text[$i] ?? '')), 0, 140)];
            if ($m['titel'] === '' && !$f('wa_foto_' . $i)) continue;
            if ($m['titel'] === '') return ['daten' => $d, 'fehler' => 'Bitte geben Sie für Motiv ' . $i . ' eine Überschrift an.'];
            if (!$f('wa_foto_' . $i)) return ['daten' => $d, 'fehler' => 'Bitte laden Sie für Motiv ' . $i . ' ein Foto hoch.'];
            $d['motive'][$i] = $m;
            $groessen['wa_foto_' . $i] = MA_WA_FOTO_MAX;
        }
        if (!$d['motive']) return ['daten' => $d, 'fehler' => 'Bitte geben Sie mindestens ein Motiv mit Überschrift und Foto an.'];
        if ($f('wa_video') && !$formate[$k]['video']) return ['daten' => $d, 'fehler' => 'Dieses Format läuft ohne Video.'];
    } else {
        unset($groessen['wa_video']);
    }
    $summe = 0;
    foreach ($groessen as $feld => $max) {
        if (!$f($feld)) continue;
        $n = (int) ($files[$feld]['size'] ?? 0); $summe += $n;
        if ($n > $max) return ['daten' => $d, 'fehler' => 'Die Datei „' . sanitize_file_name((string) $files[$feld]['name']) . '“ ist größer als ' . round($max / 1048576) . ' MB.'];
    }
    if ($summe > MA_WA_GESAMT_MAX) return ['daten' => $d, 'fehler' => 'Alle Dateien zusammen sind größer als 7,5 MB. Bitte das Video kleiner speichern oder als Link angeben.'];
    $d['dateien'] = array_keys(array_filter($groessen, fn($max, $feld) => $f($feld), ARRAY_FILTER_USE_BOTH));
    return ['daten' => $d, 'fehler' => ''];
}

/** Schlüssel je Absender und Stunde: Besucher-IP wie in live.php (hinter dem Proxy des Hosters), nur als Hash. */
function ma_werbeanfrage_schluessel(): string {
    $ip = function_exists('ma_live_ip') ? ma_live_ip() : (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    if (str_contains($ip, ':')) $ip = implode(':', array_slice(explode(':', $ip), 0, 4)); // IPv6: das /64-Netz
    return 'ma_wa_' . md5($ip . wp_salt('nonce') . gmdate('YmdH'));
}

/** Höchstens fünf Werbeanfragen je Stunde und Absender; gezählt wird erst eine angenommene Anfrage (ma_werbeanfrage_zaehlen). */
function ma_werbeanfrage_limit_ok(): bool {
    return (int) get_transient(ma_werbeanfrage_schluessel()) < MA_WA_PRO_STUNDE;
}

function ma_werbeanfrage_zaehlen(): void {
    $k = ma_werbeanfrage_schluessel();
    set_transient($k, (int) get_transient($k) + 1, HOUR_IN_SECONDS);
}

/**
 * Dateien der Anfrage hochladen. Rückgabe [pfad => rolle] (foto-1, foto-2, video,
 * logo); bei einem Fehler werden schon geladene Dateien gelöscht.
 */
function ma_werbeanfrage_dateien(array $felder, array $files, string &$fehler = ''): array {
    if (!function_exists('wp_handle_upload')) require_once ABSPATH . 'wp-admin/includes/file.php';
    $bild = ['jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
    $r = [];
    foreach ($felder as $feld) {
        $rolle = $feld === 'wa_video' ? 'video' : ($feld === 'wa_logo' ? 'logo' : 'foto-' . (int) substr($feld, 8));
        $datei = $files[$feld];
        $pruefung = ma_werbeanfrage_datei_pruefen((string) ($datei['tmp_name'] ?? ''), $rolle === 'video');
        if ($pruefung !== '') {
            foreach (array_keys($r) as $p) @unlink($p);
            $fehler = 'Die Datei „' . sanitize_file_name((string) $datei['name']) . '“: ' . $pruefung;
            return [];
        }
        // Zufälliger Name: Einsendungen liegen vor der Prüfung nicht unter einer erratbaren Adresse.
        $endung = strtolower((string) pathinfo((string) $datei['name'], PATHINFO_EXTENSION));
        $datei['name'] = 'werbeanfrage-' . $rolle . '-' . wp_generate_password(12, false) . ($endung !== '' ? '.' . preg_replace('/[^a-z0-9]/', '', $endung) : '');
        $u = wp_handle_upload($datei, ['test_form' => false, 'mimes' => $rolle === 'video' ? ['mp4|m4v' => 'video/mp4', 'webm' => 'video/webm'] : $bild]);
        if (!isset($u['file'])) {
            foreach (array_keys($r) as $p) @unlink($p);
            $fehler = ($rolle === 'video' ? 'Das Video' : 'Die Datei „' . sanitize_file_name((string) $files[$feld]['name']) . '“') . ' konnte nicht übernommen werden: ' . (string) ($u['error'] ?? 'unbekannter Fehler') . ' Erlaubt sind JPG, PNG, WebP und für das Video MP4 oder WebM.';
            return [];
        }
        $r[$u['file']] = $rolle;
    }
    return $r;
}

/**
 * Inhalt einer hochgeladenen Datei prüfen, bevor WordPress sie übernimmt: Videos
 * müssen wirklich MP4 (ftyp) oder WebM sein; Bilder höchstens 8000 Pixel je Seite
 * und 40 Megapixel (sonst bricht das Erzeugen der Vorschaubilder ab). Leer = in Ordnung.
 */
function ma_werbeanfrage_datei_pruefen(string $pfad, bool $video): string {
    if ($pfad === '' || !is_readable($pfad)) return 'konnte nicht gelesen werden.';
    if ($video) {
        $kopf = (string) file_get_contents($pfad, false, null, 0, 12);
        return substr($kopf, 4, 4) === 'ftyp' || str_starts_with($kopf, "\x1A\x45\xDF\xA3") ? '' : 'ist kein MP4- oder WebM-Video.';
    }
    $g = @getimagesize($pfad);
    if (!$g) return 'ist kein Bild (JPG, PNG oder WebP).';
    return max($g[0], $g[1]) > 8000 || $g[0] * $g[1] > 40000000 ? 'ist zu groß in Pixeln (höchstens 8000 je Seite).' : '';
}

/** Lesbare Zusammenfassung für Eingang und Mail. $mitPreis nur für die Redaktion. */
function ma_werbeanfrage_text(array $d, bool $mitPreis = false): string {
    $z = ['Firma: ' . $d['firma']];
    if ($d['website'] !== '') $z[] = 'Website (Klickziel): ' . $d['website'];
    if ($d['branche'] !== '') $z[] = 'Branche: ' . $d['branche'];
    if ($d['adresse'] !== '') $z[] = 'Adresse: ' . $d['adresse'];
    if ($d['beschreibung'] !== '') $z[] = 'Über die Firma: ' . $d['beschreibung'];
    if ($d['format'] !== '') $z[] = 'Format: ' . (function_exists('ma_werbeformat_zeile') ? ma_werbeformat_zeile($d['format'], $mitPreis) : $d['format']);
    if ($d['start'] !== '') $z[] = 'Gewünschter Start: ' . wp_date('d.m.Y', (int) strtotime($d['start']));
    foreach ($d['motive'] as $i => $m) $z[] = 'Motiv ' . $i . ': ' . $m['titel'] . ($m['text'] !== '' ? ' / ' . $m['text'] : '');
    if ($d['video_link'] !== '') $z[] = 'Video als Link: ' . $d['video_link'];
    $z[] = 'Einwilligung zur Veröffentlichung nach Prüfung: ja';
    return implode("\n", $z);
}

/**
 * Eingangsbestätigung an den Absender: fester Text ohne Angaben aus dem Formular
 * (sonst ließe sich darüber beliebiger Text an fremde Adressen schicken) und ohne
 * Preis; je Empfänger höchstens einmal am Tag.
 */
function ma_werbeanfrage_bestaetigen(string $email): bool {
    $k = 'ma_wa_mail_' . md5(strtolower($email) . gmdate('Ymd'));
    if (get_transient($k)) return false;
    set_transient($k, 1, DAY_IN_SECONDS);
    $text = "Guten Tag,\n\n"
        . "vielen Dank für Ihre Werbeanfrage bei Merzenich Aktuell. Sie ist bei uns angekommen.\n\n"
        . "Wir prüfen Texte, Fotos und Video und melden uns mit Angebot und Zeitraum. Veröffentlicht wird erst nach Ihrem Auftrag und unserer Prüfung.\n\n"
        . "Haben Sie diese Anfrage nicht gestellt, können Sie diese Nachricht ignorieren.\n\n"
        . "Freundliche Grüße\nRedaktion Merzenich Aktuell\n" . home_url('/');
    return (bool) wp_mail($email, 'Ihre Anfrage bei Merzenich Aktuell', $text);
}

/* ---------------------------------------------------------- Eingang: Profil und Anzeigen anlegen */

/** Vorhandenes Firmenprofil zur Anfrage: gleiche Website (Host) oder gleicher Name. 0 = keines. */
function ma_werbeanfrage_profil_finden(array $d): int {
    $host = preg_replace('/^www\./', '', (string) wp_parse_url((string) $d['website'], PHP_URL_HOST));
    $status = ['publish', 'draft', 'pending', 'future', 'private'];
    if ($host !== '') {
        foreach (get_posts(['post_type' => 'ma_business', 'post_status' => $status, 'numberposts' => 20, 'fields' => 'ids', 'meta_query' => [['key' => 'ma_business_website', 'value' => $host, 'compare' => 'LIKE']]]) as $id) {
            if (preg_replace('/^www\./', '', (string) wp_parse_url((string) get_post_meta((int) $id, 'ma_business_website', true), PHP_URL_HOST)) === $host) return (int) $id;
        }
    }
    foreach (get_posts(['post_type' => 'ma_business', 'post_status' => $status, 'numberposts' => 20, 'fields' => 'ids', 's' => $d['firma']]) as $id) {
        if (mb_strtolower(trim(get_the_title((int) $id))) === mb_strtolower(trim($d['firma']))) return (int) $id;
    }
    return 0;
}

/** Dateien einer Einsendung nach Rolle (foto-1, foto-2, video, logo) => Medien-ID. */
function ma_werbeanfrage_medien(int $eingang): array {
    $r = [];
    foreach (get_attached_media('', $eingang) as $a) if (($rolle = (string) get_post_meta($a->ID, '_ma_eingang_rolle', true)) !== '') $r[$rolle] = (int) $a->ID;
    return $r;
}

/**
 * Aus einer Werbeanfrage im Eingang: Firmenprofil (neu als „Zur Prüfung“, sonst
 * das vorhandene verknüpft, mit Einwilligung aus der Anfrage) und beim Werbepaket
 * je Motiv eine Anzeige „Zur Prüfung“ mit Text, Foto, Video, Website als Ziel,
 * Plätzen und Laufzeit des Formats. Nicht aktiv: aktiv wird sie mit „Freigeben“.
 * Fotos, Logo und Video nur mit bestätigten Bildrechten. Ein zweiter Aufruf legt
 * nichts doppelt an. Rückgabe ['profil' => id, 'anzeigen' => [ids], 'log' => [Zeilen]].
 */
function ma_werbeanfrage_anlegen(int $eingang): array {
    $d = get_post_meta($eingang, '_ma_eingang_werbung', true);
    if (!is_array($d) || empty($d['firma'])) return ['profil' => 0, 'anzeigen' => [], 'log' => ['Keine Werbeanfrage.']];
    $schon = get_post_meta($eingang, '_ma_eingang_angelegt', true);
    if (is_array($schon) && !empty($schon['profil']) && get_post((int) $schon['profil'])) return $schon + ['log' => ['Schon angelegt.']];
    // Zwei Klicks zugleich (zwei Redakteure, Doppelklick): nur einer legt an.
    $sperre = 'ma_wa_lauf_' . $eingang;
    if (get_transient($sperre)) return ['profil' => 0, 'anzeigen' => [], 'log' => ['Wird gerade angelegt, bitte gleich neu laden.']];
    set_transient($sperre, 1, 60);
    $m = fn(string $k): string => (string) get_post_meta($eingang, '_ma_eingang_' . $k, true);
    $rechte = $m('bildrechte') === '1';
    $medien = ma_werbeanfrage_medien($eingang);
    $datum = get_the_date('d.m.Y', $eingang) ?: wp_date('d.m.Y');
    $quelle = 'Werbeanfrage vom ' . $datum . ' über das Formular';
    $log = [];

    $pid = ma_werbeanfrage_profil_finden($d);
    $neu = !$pid;
    if ($pid) {
        // Vorhandenes Profil: Einwilligung und Logo bleiben, wie sie sind (die Anfrage kann von jemand anderem stammen).
        $log[] = 'Firmenprofil „' . get_the_title($pid) . '“ ist schon da und wird verknüpft (' . get_post_status($pid) . '). Einwilligung und Logo bleiben unverändert.';
        $webProfil = (string) get_post_meta($pid, 'ma_business_website', true);
        if ($webProfil === '' && $d['website'] !== '') update_post_meta($pid, 'ma_business_website', $d['website']);
        elseif ($d['website'] !== '' && preg_replace('/^www\./', '', (string) wp_parse_url($webProfil, PHP_URL_HOST)) !== preg_replace('/^www\./', '', (string) wp_parse_url($d['website'], PHP_URL_HOST))) {
            $log[] = 'Achtung: Die Website der Anfrage (' . $d['website'] . ') weicht von der Website im Profil (' . $webProfil . ') ab. Bitte vor dem Freigeben prüfen.';
        }
    } else {
        $r = wp_insert_post(wp_slash([
            'post_type' => 'ma_business', 'post_status' => 'pending', 'post_title' => $d['firma'], 'post_author' => get_current_user_id(),
            'post_excerpt' => mb_substr($d['beschreibung'], 0, 160), 'post_content' => $d['beschreibung'] !== '' ? '<p>' . esc_html($d['beschreibung']) . '</p>' : '',
            'meta_input' => array_filter(['ma_business_website' => $d['website'], 'ma_business_branche' => $d['branche'], 'ma_business_adresse' => $d['adresse'],
                'ma_business_quelle' => $quelle, 'ma_business_stand' => wp_date('Y-m-d'), 'ma_business_kanal' => '1', '_ma_aus_eingang' => (string) $eingang], fn($v) => $v !== ''),
        ]), true);
        if (is_wp_error($r) || !$r) return ['profil' => 0, 'anzeigen' => [], 'log' => ['Firmenprofil nicht angelegt' . (is_wp_error($r) ? ': ' . $r->get_error_message() : '') . '.']];
        $pid = (int) $r;
        // Einwilligung aus der Anfrage, dann „Zur Prüfung“ (ohne Einwilligung macht business.php beim Anlegen einen Entwurf daraus).
        update_post_meta($pid, 'ma_business_consent', '1');
        update_post_meta($pid, 'ma_business_consent_by', $m('name') . ' (' . $m('email') . ')');
        update_post_meta($pid, 'ma_business_consent_at', wp_date('d.m.Y H:i'));
        update_post_meta($pid, 'ma_business_consent_quelle', $quelle);
        if (get_post_status($pid) !== 'pending') wp_update_post(['ID' => $pid, 'post_status' => 'pending']);
        $log[] = 'Firmenprofil „' . $d['firma'] . '“ angelegt (zur Prüfung, Einwilligung aus der Anfrage).';
    }
    if ($neu && !empty($medien['logo']) && $rechte && !get_post_thumbnail_id($pid)) {
        set_post_thumbnail($pid, $medien['logo']);
        update_post_meta($pid, 'ma_business_logo_rechte', 'geklaert');
        update_post_meta($medien['logo'], '_wp_attachment_image_alt', 'Logo ' . $d['firma']);
        update_post_meta($medien['logo'], 'ma_image_credit', 'Logo: ' . $d['firma'] . ', vom Unternehmen eingereicht');
        $log[] = 'Logo als Bild des Profils gesetzt.';
    }
    if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($pid, 'Aus Werbeanfrage', $quelle . ' (Eingang ' . $eingang . ').');

    $anzeigen = [];
    $format = $d['format'] !== '' && function_exists('ma_werbeformat') ? ma_werbeformat($d['format']) : null;
    if (($d['art'] ?? '') === 'banner' && $format) {
        // Start nur mit Wunschtermin; das Ende setzt die Freigabe (Laufzeit ab dem tatsächlichen Start, werbeformate.php).
        $start = $d['start'] !== '' ? $d['start'] . ' 00:00:00' : '';
        foreach ((array) $d['motive'] as $i => $mo) {
            $a = wp_insert_post(wp_slash(['post_type' => 'ma_ad', 'post_status' => 'pending', 'post_title' => (string) $mo['titel'], 'post_excerpt' => (string) $mo['text'], 'post_author' => get_current_user_id()]), true);
            if (is_wp_error($a) || !$a) { $log[] = 'Anzeige ' . $i . ' nicht angelegt.'; continue; }
            $a = (int) $a; $anzeigen[] = $a;
            foreach (['ma_ad_sponsor' => $d['firma'] . ($d['branche'] !== '' ? ' · ' . $d['branche'] : ''), 'ma_ad_url' => $d['website'], 'ma_ad_knopf' => 'Zur Website',
                'ma_ad_start' => $start, 'ma_business_id' => (string) $pid, 'ma_ad_format' => (string) $d['format'], '_ma_aus_eingang' => (string) $eingang] as $k => $v) if ($v !== '') update_post_meta($a, $k, $v);
            ma_werbeformat_anwenden($a, (string) $d['format']);
            $ohne = [];
            if (!empty($medien['foto-' . $i]) && $rechte) {
                set_post_thumbnail($a, $medien['foto-' . $i]);
                update_post_meta($medien['foto-' . $i], '_wp_attachment_image_alt', (string) $mo['titel']);
                update_post_meta($medien['foto-' . $i], 'ma_image_credit', 'Foto: ' . $d['firma'] . ', vom Unternehmen eingereicht');
            } else $ohne[] = 'Foto';
            if ($format['video'] && !empty($medien['video']) && $rechte) update_post_meta($a, 'ma_ad_video', (string) $medien['video']);
            if (function_exists('ma_verlauf_eintragen')) ma_verlauf_eintragen($a, 'Aus Werbeanfrage', $quelle . '. Läuft erst nach „Freigeben“.');
            $log[] = 'Anzeige ' . $i . ' „' . $mo['titel'] . '“ angelegt (zur Prüfung' . ($ohne ? ', ohne ' . implode(' und ', $ohne) : '') . ').';
        }
    }
    $erg = ['profil' => $pid, 'anzeigen' => $anzeigen];
    delete_transient($sperre);
    // Nur vollständig Angelegtes merken; fehlt eine Anzeige, legt ein neuer Klick die Anfrage noch einmal an.
    if (count($anzeigen) < count((array) $d['motive']) && ($d['art'] ?? '') === 'banner') return $erg + ['log' => array_merge($log, ['Nicht alle Anzeigen angelegt; bitte die angelegten löschen und erneut versuchen.'])];
    update_post_meta($eingang, '_ma_eingang_angelegt', $erg);
    update_post_meta($eingang, '_ma_eingang_entwurf', $anzeigen[0] ?? $pid);
    wp_update_post(['ID' => $eingang, 'post_status' => 'ma_erledigt']);
    return $erg + ['log' => $log];
}

/** Kasten „Werbeanfrage“ in der Einsendung: alle Angaben, Format mit Preis, Medien, Knopf. */
add_action('add_meta_boxes_ma_eingang', function (WP_Post $p): void {
    $d = get_post_meta($p->ID, '_ma_eingang_werbung', true);
    if (!is_array($d) || empty($d['firma'])) return;
    add_meta_box('ma-werbeanfrage', 'Werbeanfrage', function (WP_Post $p) use ($d): void {
        $med = ma_werbeanfrage_medien($p->ID);
        $e = 'esc_html';
        echo '<table class="form-table" role="presentation">';
        $zeile = fn(string $k, string $v) => print('<tr><th>' . esc_html($k) . '</th><td>' . $v . '</td></tr>');
        $zeile('Firma', $e($d['firma']) . ($d['branche'] !== '' ? ' · ' . $e($d['branche']) : ''));
        if ($d['website'] !== '') $zeile('Website (Klickziel)', '<a href="' . esc_url($d['website']) . '" target="_blank" rel="noopener">' . $e($d['website']) . '</a>');
        if ($d['adresse'] !== '') $zeile('Adresse', $e($d['adresse']));
        if ($d['beschreibung'] !== '') $zeile('Über die Firma', nl2br($e($d['beschreibung'])));
        if ($d['format'] !== '') $zeile('Format', $e(function_exists('ma_werbeformat_zeile') ? (ma_werbeformat_zeile($d['format'], true) ?: $d['format'] . ' (nicht mehr vorhanden)') : $d['format']));
        if ($d['start'] !== '') $zeile('Gewünschter Start', $e(wp_date('d.m.Y', (int) strtotime($d['start']))));
        foreach ((array) $d['motive'] as $i => $mo) {
            $bild = !empty($med['foto-' . $i]) ? wp_get_attachment_image($med['foto-' . $i], 'medium', false, ['style' => 'max-width:320px;height:auto;display:block;margin-bottom:6px']) : '<em>kein Foto</em><br>';
            $zeile('Motiv ' . $i, $bild . '<strong>' . $e($mo['titel']) . '</strong>' . ($mo['text'] !== '' ? '<br>' . $e($mo['text']) : ''));
        }
        if (!empty($med['video'])) {
            $groesse = (int) @filesize((string) get_attached_file($med['video']));
            $zeile('Video', '<video src="' . esc_url((string) wp_get_attachment_url($med['video'])) . '" controls muted style="max-width:320px;display:block"></video>' . $e(size_format($groesse, 1))
                . ($groesse > 1024 * 1024 ? ' <span class="description">Für schnelle Seiten besser unter 1 MB: vor der Freigabe verkleinern oder durch eine kleinere Fassung ersetzen.</span>' : ''));
        }
        if ($d['video_link'] !== '') $zeile('Video als Link', '<a href="' . esc_url($d['video_link']) . '" target="_blank" rel="noopener">' . $e($d['video_link']) . '</a>');
        if (!empty($med['logo'])) $zeile('Logo', wp_get_attachment_image($med['logo'], 'thumbnail'));
        echo '</table>';
    }, 'ma_eingang', 'normal', 'high');
});
