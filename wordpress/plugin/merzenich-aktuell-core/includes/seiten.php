<?php
/**
 * Redaktionsseiten (02.10.2026): Über uns, Redaktion, Grundsätze, Korrekturen,
 * Kommentarrichtlinien, KI & Redaktion, Kontakt, Meldung senden, Termin
 * melden, Werben, Unterstützen, Anzeigen (mit Aufgeben), Archiv, Diskussion,
 * WhatsApp-Kanal.
 *
 * Die Fußzeile und jede Meldung verlinken diese Adressen (wie die statische
 * Seite); auf WordPress liefen sie bisher auf 404. Dieselbe Mechanik wie die
 * Rechtstexte (includes/rechtstexte.php): das Plugin legt fehlende Seiten an,
 * hebt unveränderte Seiten auf eine neue Fassung (MA_SEITEN_VERSION) und lässt
 * von Hand geänderte Seiten stehen (Hash-Vergleich); das Backend bietet dann
 * das Einspielen an (Merzenich Aktuell → Redaktionsseiten).
 *
 * Texte: die Seiten der statischen Ausgabe (chatgpt-site/<slug>/), angepasst
 * an das, was diese Installation tatsächlich tut (Kommentare: WordPress-
 * Moderation, Formulare: includes/forms.php mit Eingang). Keine erfundenen
 * Kontakt- oder Vertragsdaten.
 */
if (!defined('ABSPATH')) { exit; }

const MA_SEITEN_VERSION = '2026-10-07';

/** Slug → Seite. 'eltern' = Slug der übergeordneten Seite. 'art' für JSON-LD (includes/seo.php). */
function ma_seiten(): array {
    return [
        'ueber-uns' => ['titel' => 'Über uns', 'eyebrow' => 'Redaktion', 'anriss' => 'Merzenich Aktuell ist die Lokalzeitung online für die Gemeinde Merzenich im Kreis Düren. Wer dahintersteht, wie wir arbeiten und wie wir uns finanzieren.', 'html' => 'ma_seite_ueber_uns'],
        'redaktion' => ['titel' => 'Redaktion Merzenich Aktuell', 'eyebrow' => 'Lokalredaktion', 'anriss' => 'Die Redaktion prüft jede Meldung gegen die Originalquelle, dokumentiert Bildtyp und Bildcredit und ergänzt eigene Einordnung.', 'html' => 'ma_seite_redaktion'],
        'grundsaetze' => ['titel' => 'Publizistische Grundsätze', 'eyebrow' => 'Transparenz', 'anriss' => 'Wie diese Redaktion arbeitet, welche Bildtypen unterschieden werden, wie mit Fehlern umgegangen wird und was nicht veröffentlicht wird.', 'html' => 'ma_seite_grundsaetze'],
        'korrekturen' => ['titel' => 'Korrekturen', 'eyebrow' => 'Transparenz', 'anriss' => 'Fehler passieren. Hier melden Sie sie, hier dokumentieren wir sie. Jede Korrektur wird im betroffenen Artikel mit Datum ausgewiesen.', 'html' => 'ma_seite_korrekturen'],
        'kommentarregeln' => ['titel' => 'Kommentarrichtlinien', 'eyebrow' => 'Redaktion', 'anriss' => 'Was für Kommentare unter den Meldungen von Merzenich Aktuell gilt und was dabei gespeichert wird.', 'html' => 'ma_seite_kommentarregeln'],
        'ki-redaktion' => ['titel' => 'KI & Redaktion', 'eyebrow' => 'Redaktion', 'anriss' => 'Wo künstliche Intelligenz die Redaktion unterstützen darf und wo die Verantwortung bei Menschen bleibt.', 'html' => 'ma_seite_ki_redaktion'],
        'kontakt' => ['titel' => 'Kontakt', 'eyebrow' => 'Redaktion & Anzeigen', 'anriss' => 'So erreichen Sie die Redaktion und die Anzeigenannahme von Merzenich Aktuell.', 'html' => 'ma_seite_kontakt'],
        'meldung-senden' => ['titel' => 'Meldung senden', 'eyebrow' => 'Mitmachen', 'anriss' => 'Hinweise, Vereinsmeldungen, Fotos und Leserbriefe erreichen die Redaktion direkt über dieses Formular. Wir prüfen jede Einsendung und melden uns bei Rückfragen.', 'html' => 'ma_seite_meldung_senden'],
        'termin-melden' => ['titel' => 'Termin melden', 'eyebrow' => 'Kalender', 'anriss' => 'Veranstaltungen aus Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald für den Terminkalender melden.', 'html' => 'ma_seite_termin_melden'],
        'werben' => ['titel' => 'Werben auf Merzenich Aktuell', 'eyebrow' => 'Mediadaten', 'anriss' => 'Lokal sichtbar, klar gekennzeichnet: Werbebanner, Tipp und Sponsoring, Unternehmensprofil.', 'html' => 'ma_seite_werben'],
        'unterstuetzen' => ['titel' => 'Merzenich Aktuell unterstützen', 'eyebrow' => 'Mitmachen', 'anriss' => 'Drei Wege, Merzenich Aktuell zu stärken: weitersagen, mitschreiben, als Betrieb sichtbar werden.', 'html' => 'ma_seite_unterstuetzen'],
        'anzeigen' => ['titel' => 'Anzeigen', 'eyebrow' => 'Anzeigen', 'anriss' => 'Immobilien, Stellen, Trauer- und Familienanzeigen aus Merzenich: ansehen oder aufgeben.', 'html' => 'ma_seite_anzeigen'],
        'aufgeben' => ['titel' => 'Anzeige aufgeben', 'eyebrow' => 'Anzeigen', 'anriss' => 'Werbung, Immobilie, Traueranzeige oder Familienanzeige aufgeben: Angaben, Bild und Kontakt in einem Formular, Prüfung durch die Redaktion.', 'html' => 'ma_seite_aufgeben', 'eltern' => 'anzeigen'],
        'archiv' => ['titel' => 'Archiv', 'eyebrow' => 'Archiv', 'anriss' => 'Alle Meldungen von Merzenich Aktuell nach Monat.', 'html' => 'ma_seite_archiv'],
        'diskussion' => ['titel' => 'Diskussion', 'eyebrow' => 'Mitreden', 'anriss' => 'Die jüngsten Kommentare aus allen Meldungen von Merzenich Aktuell.', 'html' => 'ma_seite_diskussion'],
        'thema' => ['titel' => 'Themen', 'eyebrow' => 'Themen', 'anriss' => 'Alle Themen und Schlagworte auf Merzenich Aktuell, nach Zahl der Meldungen.', 'html' => 'ma_seite_themen'],
        'whatsapp' => ['titel' => 'WhatsApp-Kanal', 'eyebrow' => 'Immer informiert', 'anriss' => 'Eilmeldungen, Blaulicht und die wichtigsten Termine aus der Gemeinde Merzenich als Broadcast direkt aufs Handy. Kein Gruppenchat, keine sichtbare Nummer.', 'html' => 'ma_seite_whatsapp'],
    ];
}

/* ------------------------------------------------------------ Texte */

function ma_seite_ueber_uns(): string {
    return '<h2 id="was-wir-sind">Was wir sind</h2>'
        . '<p>Merzenich Aktuell berichtet über Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald: Nachrichten, Blaulicht, Sport, Rathaus, Vereine, Wirtschaft und die Menschen im Ort. Alles ist frei zugänglich, ohne Bezahlschranke.</p>'
        . '<p>Wir sind keine Agentur- oder Pressemitteilungs-Kopie: Jede Meldung nennt ihre Originalquelle mit Link und Datenstand, jedes Bild seinen Typ und seinen Credit. Fehler korrigieren wir sichtbar im Artikel.</p>'
        . '<h2 id="redaktion">Redaktion</h2>'
        . '<p>Die <a href="/redaktion/">Redaktion Merzenich Aktuell</a> prüft jede Meldung gegen die Originalquelle, dokumentiert Bildtyp und Bildcredit und ergänzt eigene Einordnung. Namentlich gezeichnete Beiträge tragen ein Kürzel; die Redaktion ist auf jeder Meldung verlinkt.</p>'
        . '<h2 id="finanzierung">Finanzierung</h2>'
        . '<p>Merzenich Aktuell finanziert sich über gekennzeichnete Anzeigen sowie Firmenporträts und freiwillige Beiträge von Leserinnen und Lesern. Werbekunden haben keinen Einfluss auf redaktionelle Entscheidungen; siehe <a href="/grundsaetze/">Publizistische Grundsätze</a>.</p>'
        . '<h2 id="kontakt">Kontakt</h2>'
        . '<p>E-Mail: <a href="mailto:info@kbs-management.tv">info@kbs-management.tv</a>. Meldungen, Termine und Bilder bitte über die Formulare (<a href="/meldung-senden/">Meldung senden</a>, <a href="/termin-melden/">Termin melden</a>). Anbieterin ist die KBS Management GmbH, Leverkusen; siehe <a href="/impressum/">Impressum</a>.</p>';
}

function ma_seite_redaktion(): string {
    return '<p>Die Redaktion Merzenich Aktuell ist die Lokalredaktion für die Gemeinde Merzenich im Kreis Düren. Sie prüft jede Meldung gegen die Originalquelle, dokumentiert Bildtyp und Bildcredit und ergänzt eigene Einordnung. Unter jeder Meldung stehen Quelle, Datenstand und Bildnachweis.</p>'
        . '<p>Kontakt: <a href="mailto:info@kbs-management.tv">info@kbs-management.tv</a> · <a href="/meldung-senden/">Meldung senden</a> · <a href="/korrekturen/">Fehler melden</a> · <a href="/grundsaetze/">Publizistische Grundsätze</a></p>'
        . '<h2 id="meldungen">Die jüngsten Meldungen</h2>[ma_redaktion_meldungen]';
}

function ma_seite_grundsaetze(): string {
    return '<h2 id="jede-meldung-nennt-ihre-quelle">Jede Meldung nennt ihre Quelle</h2>'
        . '<p>Unter jeder Meldung steht, worauf sie beruht: die Originalquelle mit Link, der Datenstand und der Hinweis, dass die Angaben redaktionell geprüft und zusammengefasst wurden. Wer nachlesen will, kommt mit einem Klick zum Original. Pressemitteilungen, die wir ohne eigene Einordnung übernehmen, kennzeichnen wir und schließen sie von Suchmaschinen aus.</p>'
        . '<h2 id="bildtypen-sichtbar-unterschieden">Bildtypen, sichtbar unterschieden</h2><ul>'
        . '<li><strong>Originalbild.</strong> Das Foto zeigt das Ereignis selbst, etwa Einsatzbilder der Feuerwehr.</li>'
        . '<li><strong>Offizielles Veranstaltungsbild.</strong> Ankündigungsmotiv des Veranstalters, nicht das Ereignis.</li>'
        . '<li><strong>Quellenmotiv.</strong> Ein Motiv der Quelle, zum Beispiel eine Grafik der Fördergesellschaft.</li>'
        . '<li><strong>Archivbild.</strong> Eine frühere Aufnahme, die den Ort oder das Thema zeigt.</li>'
        . '<li><strong>Symbolbild.</strong> Ein Bild, das das Thema illustriert, ohne das Ereignis zu zeigen.</li>'
        . '<li><strong>Leserfoto.</strong> Von Leserinnen und Lesern eingesandt, mit Namensnennung.</li></ul>'
        . '<p><strong>Keine KI-Bilder für reale Ereignisse.</strong> Für Meldungen über tatsächliche Ereignisse wird kein generiertes Ersatzbild als Ereignisfoto verwendet. Wenn kein passendes Bild vorliegt, erscheint die Meldung mit einer klar gekennzeichneten Grafik oder ohne Bild.</p>'
        . '<h2 id="korrekturen">Korrekturen</h2>'
        . '<p>Fehler werden korrigiert und die Änderung wird im Artikel mit Datum ausgewiesen. Hinweise gehen an <a href="mailto:info@kbs-management.tv">info@kbs-management.tv</a> oder über das <a href="/korrekturen/">Korrekturformular</a>. Eine Übersicht aller Korrekturen führen wir auf der Seite <a href="/korrekturen/">Korrekturen</a>.</p>'
        . '<h2 id="ethik">Ethik</h2>'
        . '<p>Wir berichten wahrhaftig, sorgfältig und fair. Persönlichkeitsrechte, insbesondere von Kindern, Opfern und Angehörigen, haben Vorrang vor dem Nachrichtenwert. Bei Blaulichtmeldungen nennen wir keine Namen und zeigen keine identifizierbaren Personen ohne Einwilligung. Wir trennen Nachricht und Meinung: Kommentare und Kolumnen sind als solche gekennzeichnet.</p>'
        . '<h2 id="unabhaengigkeit">Unabhängigkeit</h2>'
        . '<p>Werbung und Sponsoring werden gekennzeichnet und haben keinen Einfluss auf die Auswahl oder Darstellung von Meldungen. Firmenporträts und hervorgehobene Einträge sind sichtbar als Anzeige markiert.</p>'
        . '<h2 id="vielfalt">Vielfalt</h2>'
        . '<p>Alle fünf Ortsteile bekommen dieselbe Aufmerksamkeit, unabhängig von ihrer Größe. Wir berichten über alle Vereine, Kirchen, Parteien und Initiativen nach denselben Maßstäben.</p>'
        . '<h2 id="leserbriefe-und-kommentare">Leserbriefe und Kommentare</h2>'
        . '<p>Leserbriefe veröffentlichen wir mit Namen und Ortsteil, nicht anonym. Wir kürzen, wenn nötig, und veröffentlichen keine Beleidigungen oder reine Polemik. Was Redaktion, Leserstimme, Anzeige und Vereinsmeldung unterscheidet, steht an jedem Beitrag. Die Regeln für Kommentare stehen in den <a href="/kommentarregeln/">Kommentarrichtlinien</a>.</p>';
}

function ma_seite_korrekturen(): string {
    return '<h2 id="so-gehen-wir-mit-fehlern-um">So gehen wir mit Fehlern um</h2>'
        . '<p>Wenn eine Meldung einen Fehler enthält, korrigieren wir sie und schreiben unter den Artikel, was wann geändert wurde. Ein Artikel wird nicht kommentarlos gelöscht.</p>'
        . '<h2 id="korrekturliste">Korrekturliste</h2>'
        . '<p>Bislang gibt es keine dokumentierten Korrekturen. Diese Liste wird bei jeder Korrektur ergänzt.</p>'
        . '<h2 id="fehler-melden">Fehler melden</h2>'
        . '<p>Bitte nennen Sie im Betreff die Meldung (Titel oder Adresse) und in der Nachricht, was falsch ist oder fehlt, möglichst mit Beleg oder Quelle. Wir prüfen jeden Hinweis; Korrekturen werden im Artikel mit Datum ausgewiesen.</p>'
        . '[ma_formular typ="korrektur"]';
}

function ma_seite_kommentarregeln(): string {
    return '<p>Kommentare dürfen keine Beleidigungen, Hass, Drohungen, privaten oder sensiblen Angaben über Dritte, Spam oder rechtswidrige Inhalte enthalten. Die Redaktion kann Kommentare zurückhalten oder entfernen. E-Mail-Adressen werden nicht veröffentlicht.</p>'
        . '<p>Jeder Kommentar wird vor der Veröffentlichung von der Redaktion geprüft und erscheint erst nach der Freigabe. Wer eine E-Mail-Adresse angibt, wird per E-Mail benachrichtigt, sobald der Kommentar freigegeben ist.</p>'
        . '<p>Kommentare gibt es unter jeder Meldung; die jüngsten Kommentare aller Meldungen stehen auf der Seite <a href="/diskussion/">Diskussion</a>. Ein Konto ist nicht nötig; Name und Text werden öffentlich angezeigt, die E-Mail-Adresse wird nie veröffentlicht. Die IP-Adresse wird beim Kommentieren nicht gespeichert. Was sonst gespeichert wird, steht in den <a href="/datenschutz/">Datenschutzhinweisen</a>.</p>'
        . '<p>Einen Kommentar, der gegen diese Regeln verstößt, melden Sie bitte über das <a href="/kontakt/">Kontaktformular</a> mit Name und Zeitpunkt des Kommentars.</p>';
}

function ma_seite_ki_redaktion(): string {
    return '<h2 id="menschliche-verantwortung">Menschliche Verantwortung</h2>'
        . '<p>KI kann bei Recherche, Sprachkorrektur, Übersetzung und Textvorbereitung unterstützen. Quellen, Fakten und Nutzungsrechte müssen vor Veröffentlichung redaktionell geprüft werden. Der tatsächliche Einsatz und die menschliche Prüfung werden dokumentiert.</p>'
        . '<h2 id="bilder-und-herkunft">Bilder und Herkunft</h2>'
        . '<p>Künstliche Bilder werden nicht als Aufnahmen realer Einsätze oder Veranstaltungen ausgegeben. KI-generierte oder entsprechend manipulierte Medien erhalten eine sichtbare Kennzeichnung. Originaldateien und Herkunftsnachweise werden aufbewahrt.</p>'
        . '<h2 id="hinweise-und-korrekturen">Hinweise und Korrekturen</h2>'
        . '<p>Verantwortliche nennt das <a href="/impressum/">Impressum</a>. <a href="/korrekturen/">Fehler melden</a>.</p>';
}

function ma_seite_kontakt(): string {
    return '<h2 id="die-richtige-anlaufstelle">Die richtige Anlaufstelle</h2>'
        . '<ul><li><a href="/meldung-senden/">Nachricht oder Foto einsenden</a></li><li><a href="/termin-melden/">Veranstaltung melden</a></li><li><a href="/korrekturen/">Fehler in einer Meldung melden</a></li><li><a href="/werben/">Werbung und Anzeigen anfragen</a></li></ul>'
        . '<p>Die Redaktion erreichen Sie unter <a href="mailto:info@kbs-management.tv">info@kbs-management.tv</a> oder über das Formular unten. Bitte nennen Sie bei einer Korrektur den Beitrag und bei einer Kommentarmeldung zusätzlich den Namen und Zeitpunkt des Kommentars. Vertrauliche personenbezogene Angaben gehören nicht in öffentliche Kommentare.</p>'
        . '[ma_formular typ="kontakt"]';
}

function ma_seite_meldung_senden(): string {
    return '<h2 id="so-funktioniert-es">So funktioniert es</h2>'
        . '<p>Vereine, Gemeinde, Kirchen, Schulen und Leserinnen und Leser können Hinweise einsenden. Hilfreich sind: <strong>was</strong> passiert, <strong>wann</strong> und <strong>wo</strong>, <strong>wer</strong> der Veranstalter oder Ansprechpartner ist, eine Kontaktmöglichkeit für Rückfragen und, wenn vorhanden, ein Bild mit der Angabe, wer es aufgenommen hat.</p>'
        . '<p>Die Redaktion prüft jede Meldung gegen die Originalquelle, dokumentiert Bildtyp und Bildcredit und ergänzt eigene Einordnung. Was wir nicht veröffentlichen: anonyme Vorwürfe gegen Einzelpersonen, Werbung ohne Kennzeichnung und Inhalte, deren Rechte nicht beim Einsender liegen.</p>'
        . '<p><strong>Familienanzeigen</strong> (Hochzeit, Geburt, Jubiläum, Nachruf) geben Sie bitte mit Foto und dem Einverständnis der Betroffenen über <a href="/anzeigen/aufgeben/">Anzeige aufgeben</a> auf.</p>'
        . '<p>Lieber per E-Mail? <a href="mailto:info@kbs-management.tv">info@kbs-management.tv</a>. Für Termine gibt es ein <a href="/termin-melden/">eigenes Formular</a>, Fotos für das Foto des Tages schicken Sie <a href="#foto-des-tages">weiter unten</a>.</p>'
        . '<div id="formular"></div>[ma_formular typ="meldung"]'
        . '<h2 id="foto-des-tages">Foto des Tages einsenden</h2>'
        . '<p>Ein schöner Blick auf Merzenich, Golzheim, Girbelsrath, Morschenich oder Bürgewald? Die Redaktion wählt aus den Einsendungen das Foto des Tages für die Startseite, mit Ihrem Namen im Bildnachweis. Am besten im Querformat und ohne erkennbare Personen.</p>'
        . '[ma_formular typ="foto"]';
}

function ma_seite_termin_melden(): string {
    return '<h2 id="was-ein-guter-termineintrag-braucht">Was ein guter Termineintrag braucht</h2>'
        . '<p>Titel, Datum und Uhrzeit, Ort mit Adresse, Veranstalter, kurze Beschreibung und, wenn vorhanden, ein Plakat. Wiederkehrende Termine (Proben, Trainings, Sprechstunden) bitte einmalig mit dem Hinweis auf den Rhythmus melden.</p>'
        . '<p>Alle Termine sind kostenlos für Vereine, Kirchen, Schulen, Gemeinde und gemeinnützige Veranstalter. Gewerbliche Veranstaltungen werden als Anzeige gekennzeichnet; <a href="/werben/">Konditionen</a>.</p>'
        . '[ma_formular typ="termin"]';
}

function ma_seite_werben(): string {
    return '<p>Merzenich Aktuell lesen Menschen aus Merzenich, Golzheim, Girbelsrath, Morschenich und Bürgewald. Werbung erscheint hier mitten im lokalen Geschehen, immer klar als Anzeige gekennzeichnet und vor der Veröffentlichung von der Redaktion geprüft.</p>'
        . '<p><a class="btn" href="#anfrage">Werbung anfragen</a></p>'
        . '<h2 id="werbeflaechen">Buchbare Flächen</h2><ul>'
        . '<li><strong>Werbeband auf der Startseite.</strong> Breite Fläche zwischen den Rubriken der Startseite, auf Handy und Computer.</li>'
        . '<li><strong>Fläche in der Bühne.</strong> Neben den Aufmachern ganz oben auf der Startseite.</li>'
        . '<li><strong>Seitenspalte.</strong> Rechts neben Meldungen und Listen, auf dem Computer.</li>'
        . '<li><strong>Artikelanzeige.</strong> Unter dem Text jeder Meldung.</li>'
        . '<li><strong>Tipp und Sponsoring.</strong> Ein gekennzeichneter Beitrag für Veranstaltung, Angebot oder Aktion unter <a href="/tipp/">Tipps</a>.</li>'
        . '<li><strong>Unternehmensprofil.</strong> Dauerhafter Eintrag unter <a href="/unternehmen/">Unternehmen</a> mit Bild, Öffnungszeiten, Kontakt und eigenen Beiträgen.</li></ul>'
        . '<p>Wo die Flächen liegen, zeigen die mit „Musteranzeige“ beschrifteten Plätze auf der Seite. Echte Kampagnen erscheinen dort nach Buchung und Freigabe.</p>'
        . '<h2 id="anfrage">Anfrage</h2>'
        . '<p>Schreiben Sie uns, was Sie sichtbar machen möchten, für welchen Zeitraum und mit welchem Bildmaterial. Wir melden uns mit Format, Laufzeit und Preis. E-Mail: <a href="mailto:info@kbs-management.tv">info@kbs-management.tv</a>.</p>'
        . '[ma_formular typ="werbung"]';
}

function ma_seite_unterstuetzen(): string {
    return '<p>Merzenich Aktuell lebt davon, dass Menschen aus der Gemeinde mitlesen, mitschreiben und weitersagen.</p>'
        . '<h2 id="drei-wege">Drei Wege</h2><ol>'
        . '<li><strong>Weitersagen.</strong> Teilen Sie Meldungen mit den Knöpfen unter jedem Artikel und empfehlen Sie die Seite in Ihrem Verein.</li>'
        . '<li><strong>Mitschreiben.</strong> Vereinsberichte, Spielberichte, Fotos vom Fest: Alles, was Sie über <a href="/meldung-senden/">Meldung senden</a> oder <a href="/termin-melden/">Termin melden</a> einreichen, macht die Seite besser.</li>'
        . '<li><strong>Als Betrieb sichtbar werden.</strong> Eine gekennzeichnete Anzeige, ein Tipp oder ein Unternehmensprofil trägt die lokale Berichterstattung. Alle Formate stehen unter <a href="/werben/">Werben</a>. Wer wirbt, bekommt keinen Einfluss auf redaktionelle Inhalte.</li></ol>'
        . '<p>Fehler gefunden? Über <a href="/korrekturen/">Korrekturen</a> erreichen Hinweise direkt die Redaktion.</p>';
}

function ma_seite_anzeigen(): string {
    return '<p><a class="btn" href="/anzeigen/aufgeben/">Anzeige aufgeben</a> Werbung, Immobilie, Traueranzeige oder Familienanzeige: Angaben, Bild und Kontakt in einem Formular.</p>'
        . '<h2 id="immobilienmarkt">Immobilienmarkt</h2><p><a href="/immobilien/">Immobilien ansehen</a></p>'
        . '<h2 id="stellenmarkt">Stellenmarkt</h2><p><a href="/jobs/">Stellen ansehen</a></p>'
        . '<h2 id="traueranzeigen">Traueranzeigen</h2><p><a href="/traueranzeigen/">Traueranzeigen ansehen</a></p>'
        . '<h2 id="familienanzeigen">Familienanzeigen</h2><p><a href="/familienanzeigen/">Familienanzeigen ansehen</a></p>'
        . '<h2 id="werbung">Werbung</h2><p>Banner, Tipp und Sponsoring, Unternehmensprofil: <a href="/werben/">Werben auf Merzenich Aktuell</a>.</p>';
}

function ma_seite_aufgeben(): string {
    // Seit 1.24.0 erst die Art wählen, dann ein einziges Formular (forms.php, ma_aufgeben_shortcode).
    return '<p>Jede Einsendung wird redaktionell geprüft und nicht automatisch veröffentlicht; wir melden uns mit Rückfragen, Format und Preis. Bilder nur mit bestätigten Bildrechten.</p>'
        . '[ma_anzeige_aufgeben]';
}

function ma_seite_archiv(): string {
    return '<p>Alle veröffentlichten Meldungen, nach Monat geordnet, die jüngste zuerst. Ressorts: <a href="/blaulicht/">Blaulicht</a>, <a href="/sport/">Sport</a>, <a href="/rathaus/">Rathaus &amp; Politik</a>, <a href="/leben/">Leben</a>, <a href="/wirtschaft/">Wirtschaft</a>, <a href="/vereine/">Vereine</a>; alle Meldungen chronologisch unter <a href="/nachrichten/">Nachrichten</a>.</p>[ma_archiv]';
}

function ma_seite_diskussion(): string {
    return '<p>Kommentare gibt es unter jeder Meldung; sie erscheinen nach Prüfung durch die Redaktion (<a href="/kommentarregeln/">Kommentarrichtlinien</a>). Hier stehen die jüngsten freigegebenen Kommentare aller Meldungen.</p>[ma_diskussion]';
}

function ma_seite_themen(): string {
    return '<p>Jede Meldung trägt Schlagworte. Hier stehen alle Themen mit der Zahl ihrer Meldungen; die Ortsteile haben eigene Seiten: <a href="/ort/merzenich/">Merzenich</a>, <a href="/ort/golzheim/">Golzheim</a>, <a href="/ort/girbelsrath/">Girbelsrath</a>, <a href="/ort/morschenich/">Morschenich</a>, <a href="/ort/buergewald/">Bürgewald</a>.</p>[ma_themen]';
}

function ma_seite_whatsapp(): string {
    return '<h2 id="was-im-kanal-geplant-ist">Was der Kanal bringt</h2>'
        . '<p>Der WhatsApp-Kanal wird gerade eingerichtet. Sobald er bereitsteht, finden Sie hier den Link zum Abonnieren. Er bringt:</p><ul>'
        . '<li>Eilmeldungen und Einsätze der Feuerwehr, sobald die Meldung geprüft ist.</li><li>Morgens die wichtigsten Meldungen des Vortags.</li><li>Freitags die Termine des Wochenendes aus allen fünf Ortsteilen.</li><li>Ergebnisse des SC 1919 Merzenich nach dem Spieltag.</li></ul>'
        . '<p>Höchstens zwei bis drei Nachrichten am Tag. Werbung nur gekennzeichnet und höchstens einmal pro Woche.</p>'
        . '<h2 id="so-funktioniert-der-kanal">So funktioniert der Kanal</h2>'
        . '<p>Ein WhatsApp-Kanal ist ein Broadcast: Sie sehen unsere Meldungen, niemand sieht Ihre Nummer, niemand kann Ihnen über den Kanal schreiben. Abbestellen jederzeit über „Kanal verlassen“.</p>'
        . '<h2 id="lieber-rss">Bis dahin und auch danach</h2>'
        . '<p>Neues steht immer zuerst auf der <a href="/">Startseite</a>.</p>'
        . '<p>Alle Meldungen: <a href="/feed/">/feed/</a>. Nur Blaulicht: <a href="/blaulicht/feed/">/blaulicht/feed/</a>. Termine: <a href="/termine/feed/">/termine/feed/</a>.</p>';
}

/* ------------------------------------------------------------ Shortcodes: Archiv, Diskussion, Redaktionsmeldungen */

add_action('init', function (): void {
    add_shortcode('ma_archiv', 'ma_seite_archiv_liste');
    add_shortcode('ma_diskussion', 'ma_seite_diskussion_liste');
    add_shortcode('ma_redaktion_meldungen', 'ma_seite_redaktion_liste');
    add_shortcode('ma_themen', 'ma_seite_themen_liste');
});

/** Monatsliste aller Meldungen: Datum, Titel, Ressort. */
function ma_seite_archiv_liste(): string {
    $posts = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 2000, 'orderby' => 'date', 'order' => 'DESC']);
    if (!$posts) return '<p>Noch keine Meldungen.</p>';
    $monate = [];
    foreach ($posts as $p) $monate[get_post_time('Y-m', false, $p)][] = $p;
    $namen = ['01' => 'Januar', '02' => 'Februar', '03' => 'März', '04' => 'April', '05' => 'Mai', '06' => 'Juni', '07' => 'Juli', '08' => 'August', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember'];
    $h = '';
    foreach ($monate as $ym => $liste) {
        [$jahr, $monat] = explode('-', $ym);
        $h .= '<h2 id="' . esc_attr($ym) . '">' . esc_html($namen[$monat] . ' ' . $jahr) . '</h2><ul class="archive-list">';
        foreach ($liste as $p) {
            $ressort = function_exists('ma_seo_ressort_von') ? ma_seo_ressort_von($p)[1] : '';
            $h .= '<li><time datetime="' . esc_attr(get_post_time('c', false, $p)) . '">' . esc_html(get_post_time('d.m.', false, $p)) . '</time> <a href="' . esc_url(get_permalink($p)) . '">' . esc_html(get_the_title($p)) . '</a>' . ($ressort ? ' <span class="rs">' . esc_html($ressort) . '</span>' : '') . '</li>';
        }
        $h .= '</ul>';
    }
    return $h;
}

/** Die jüngsten freigegebenen Kommentare aller Meldungen. */
function ma_seite_diskussion_liste(): string {
    $kommentare = get_comments(['status' => 'approve', 'number' => 30, 'post_status' => 'publish', 'type' => 'comment']);
    if (!$kommentare) return ma_seite_diskussion_einladung();
    $h = '<ul class="diskussion-liste">';
    foreach ($kommentare as $c) {
        $h .= '<li><p><strong>' . esc_html($c->comment_author) . '</strong> <time datetime="' . esc_attr(get_comment_date('c', $c)) . '">' . esc_html(get_comment_date('d.m.Y · H:i', $c)) . ' Uhr</time> zu <a href="' . esc_url(get_comment_link($c)) . '">' . esc_html(get_the_title((int) $c->comment_post_ID)) . '</a></p>'
            . '<p>' . esc_html(wp_trim_words(wp_strip_all_tags($c->comment_content), 60, ' …')) . '</p></li>';
    }
    return $h . '</ul>';
}

/**
 * Noch kein freigegebener Kommentar: statt einer leeren Seite die jüngsten Meldungen
 * mit offener Kommentarfunktion, jeweils mit Sprung zum Kommentarfeld (1.20.6,
 * Vorgabe Betreiber 05.10.2026: keine leeren Seiten).
 */
function ma_seite_diskussion_einladung(): string {
    $posts = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 8, 'orderby' => 'date', 'order' => 'DESC', 'comment_status' => 'open']);
    $h = '<p>Noch hat niemand kommentiert. Schreiben Sie den ersten Kommentar: Jede Meldung hat am Ende ein Kommentarfeld, die Redaktion schaltet Beiträge nach den <a href="' . esc_url(home_url('/kommentarregeln/')) . '">Kommentarregeln</a> frei.</p>';
    if (!$posts) return $h;
    $h .= '<h2>Diskutieren Sie mit</h2><ul class="diskussion-liste diskussion-einladung">';
    foreach ($posts as $p) {
        $ressort = function_exists('ma_seo_ressort_von') ? ma_seo_ressort_von($p)[1] : '';
        $h .= '<li><p><a href="' . esc_url(get_permalink($p)) . '"><strong>' . esc_html(get_the_title($p)) . '</strong></a></p>'
            . '<p><time datetime="' . esc_attr(get_post_time('c', false, $p)) . '">' . esc_html(get_post_time('d.m.Y', false, $p)) . '</time>' . ($ressort !== '' ? ' · ' . esc_html($ressort) : '')
            . ' · <a href="' . esc_url(get_permalink($p)) . '#respond">Kommentar schreiben</a></p></li>';
    }
    return $h . '</ul>';
}

/** Alle Schlagworte mit Anzahl, die häufigsten zuerst (Seite /thema/). */
function ma_seite_themen_liste(): string {
    $tags = get_terms(['taxonomy' => 'post_tag', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 300]);
    if (!is_array($tags) || !$tags) return '<p>Noch keine Themen.</p>';
    $h = '<ul class="themen-liste">';
    foreach ($tags as $t) $h .= '<li><a href="' . esc_url(get_term_link($t)) . '">' . esc_html($t->name) . '</a></li>';
    return $h . '</ul>';
}

/** Die jüngsten Meldungen der Redaktion (Seite /redaktion/). */
function ma_seite_redaktion_liste(): string {
    $posts = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 12]);
    if (!$posts) return '<p>Noch keine Meldungen.</p>';
    $h = '<ul class="archive-list">';
    foreach ($posts as $p) {
        $ressort = function_exists('ma_seo_ressort_von') ? ma_seo_ressort_von($p)[1] : '';
        $h .= '<li><time datetime="' . esc_attr(get_post_time('c', false, $p)) . '">' . esc_html(get_post_time('d.m.Y', false, $p)) . '</time> <a href="' . esc_url(get_permalink($p)) . '">' . esc_html(get_the_title($p)) . '</a>' . ($ressort ? ' <span class="rs">' . esc_html($ressort) . '</span>' : '') . '</li>';
    }
    return $h . '</ul><p><a href="/nachrichten/">Alle Meldungen</a> · <a href="/archiv/">Archiv nach Monat</a></p>';
}

/* ------------------------------------------------------------ Anlegen und Pflegen (wie includes/rechtstexte.php) */

function ma_seite_html(string $slug): string {
    $f = ma_seiten()[$slug]['html'] ?? '';
    return $f !== '' && function_exists($f) ? $f() : '';
}

/** Pfad der Seite (mit Elternseite). */
function ma_seite_pfad(string $slug): string {
    $s = ma_seiten()[$slug] ?? [];
    return isset($s['eltern']) ? $s['eltern'] . '/' . $slug : $slug;
}

function ma_seite_seite(string $slug): ?WP_Post {
    $p = get_page_by_path(ma_seite_pfad($slug), OBJECT, 'page');
    return $p instanceof WP_Post && $p->post_status !== 'trash' ? $p : null;
}

function ma_seiten_stand(): array {
    $s = get_option('ma_seiten_stand', []);
    return is_array($s) ? $s : [];
}

/** fehlt | aktuell | veraltet | manuell */
function ma_seite_status(string $slug): string {
    $p = ma_seite_seite($slug);
    if (!$p) return 'fehlt';
    $stand = ma_seiten_stand()[$slug] ?? null;
    if (!$stand || md5((string) $p->post_content) !== ($stand['hash'] ?? '')) return 'manuell';
    return ($stand['version'] ?? '') === MA_SEITEN_VERSION ? 'aktuell' : 'veraltet';
}

function ma_seite_schreiben(string $slug): int {
    $t = ma_seiten()[$slug] ?? null;
    if (!$t) return 0;
    $html = ma_seite_html($slug);
    $p = ma_seite_seite($slug);
    $daten = ['post_type' => 'page', 'post_status' => 'publish', 'post_title' => $t['titel'], 'post_name' => $slug, 'post_excerpt' => $t['anriss'], 'post_content' => $html, 'comment_status' => 'closed', 'ping_status' => 'closed'];
    if (isset($t['eltern'])) { $e = ma_seite_seite($t['eltern']); if (!$e) { ma_seite_schreiben($t['eltern']); $e = ma_seite_seite($t['eltern']); } $daten['post_parent'] = $e ? $e->ID : 0; }
    if ($p) { $daten['ID'] = $p->ID; $id = wp_update_post($daten, true); }
    else $id = wp_insert_post($daten, true);
    if ($id instanceof WP_Error || !$id) return 0;
    update_post_meta($id, 'ma_eyebrow', $t['eyebrow']);
    $stand = ma_seiten_stand();
    $stand[$slug] = ['version' => MA_SEITEN_VERSION, 'hash' => md5($html), 't' => time()];
    update_option('ma_seiten_stand', $stand, false);
    return (int) $id;
}

function ma_seiten_abgleichen(): void {
    if (get_option('ma_seiten_geprueft') === MA_SEITEN_VERSION && (int) get_option('ma_seiten_anzahl', 0) === count(ma_seiten())) return;
    foreach (array_keys(ma_seiten()) as $slug) {
        $status = ma_seite_status($slug);
        if ($status === 'fehlt' || $status === 'veraltet') ma_seite_schreiben($slug);
    }
    update_option('ma_seiten_geprueft', MA_SEITEN_VERSION, false);
    update_option('ma_seiten_anzahl', count(ma_seiten()), false);
}
add_action('admin_init', 'ma_seiten_abgleichen');

add_action('admin_menu', function (): void {
    add_submenu_page('merzenich-aktuell', 'Redaktionsseiten', 'Redaktionsseiten', 'manage_options', 'ma-seiten', 'ma_seiten_seite_admin');
}, 30);

add_action('admin_post_ma_seite_uebernehmen', function (): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.', 403);
    check_admin_referer('ma_seite_uebernehmen');
    $slug = sanitize_key(wp_unslash($_POST['slug'] ?? ''));
    $id = isset(ma_seiten()[$slug]) ? ma_seite_schreiben($slug) : 0;
    wp_safe_redirect(admin_url('admin.php?page=ma-seiten&uebernommen=' . ($id ? $slug : '0'))); exit;
});

add_action('admin_notices', function (): void {
    if (!current_user_can('manage_options') || (sanitize_key(wp_unslash($_GET['page'] ?? '')) === 'ma-seiten')) return;
    $manuell = array_filter(array_keys(ma_seiten()), fn($s) => ma_seite_status($s) === 'manuell' && (ma_seiten_stand()[$s]['version'] ?? '') !== MA_SEITEN_VERSION);
    if (!$manuell) return;
    printf('<div class="notice notice-warning"><p>Redaktionsseiten: %s wurde von Hand geändert; die Fassung %s des Plugins ist nicht eingespielt. <a href="%s">Redaktionsseiten prüfen</a></p></div>',
        esc_html(implode(', ', array_map(fn($s) => ma_seiten()[$s]['titel'], $manuell))), esc_html(MA_SEITEN_VERSION), esc_url(admin_url('admin.php?page=ma-seiten')));
});

function ma_seiten_seite_admin(): void {
    if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
    $ue = sanitize_key(wp_unslash($_GET['uebernommen'] ?? ''));
    echo '<div class="wrap"><h1>Redaktionsseiten</h1>';
    if ($ue !== '') echo '<div class="notice notice-' . ($ue === '0' ? 'error' : 'success') . ' is-dismissible"><p>' . ($ue === '0' ? 'Nicht übernommen.' : 'Fassung ' . esc_html(MA_SEITEN_VERSION) . ' für „' . esc_html(ma_seiten()[$ue]['titel'] ?? $ue) . '“ eingespielt.') . '</p></div>';
    echo '<p>Diese Seiten verlinken Fußzeile und Meldungen. Das Plugin legt sie an und pflegt sie als normale Seiten (Fassung ' . esc_html(MA_SEITEN_VERSION) . '). Eine neue Fassung wird nur eingespielt, wenn der Text nicht von Hand geändert wurde; sonst entscheiden Sie hier. Eigene Ergänzungen (etwa die Korrekturliste oder der Link zum WhatsApp-Kanal) bleiben so erhalten.</p>';
    echo '<table class="widefat striped" style="max-width:900px"><thead><tr><th>Seite</th><th>Status</th><th></th></tr></thead><tbody>';
    $namen = ['fehlt' => 'fehlt (wird beim nächsten Admin-Aufruf angelegt)', 'aktuell' => 'aktuell, Fassung ' . MA_SEITEN_VERSION, 'veraltet' => 'ältere Fassung', 'manuell' => 'von Hand geändert'];
    foreach (ma_seiten() as $slug => $t) {
        $p = ma_seite_seite($slug); $status = ma_seite_status($slug);
        echo '<tr><td><strong>' . esc_html($t['titel']) . '</strong><br><code>/' . esc_html(ma_seite_pfad($slug)) . '/</code></td><td>' . esc_html($namen[$status]) . ($p ? '<br><span class="description">zuletzt geändert ' . esc_html(get_the_modified_date('d.m.Y H:i', $p)) . '</span>' : '') . '</td><td>';
        if ($p) echo '<a class="button" href="' . esc_url(get_permalink($p)) . '" target="_blank">Ansehen</a> <a class="button" href="' . esc_url(get_edit_post_link($p->ID)) . '">Bearbeiten</a> ';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline" onsubmit="return confirm(\'Den Text der Seite durch die Fassung des Plugins ersetzen? Eigene Änderungen gehen verloren (Revisionen bleiben).\')"><input type="hidden" name="action" value="ma_seite_uebernehmen"><input type="hidden" name="slug" value="' . esc_attr($slug) . '">';
        wp_nonce_field('ma_seite_uebernehmen');
        echo '<button class="button' . ($status === 'aktuell' ? '' : ' button-primary') . '">Fassung ' . esc_html(MA_SEITEN_VERSION) . ' einspielen</button></form></td></tr>';
    }
    echo '</tbody></table></div>';
}
