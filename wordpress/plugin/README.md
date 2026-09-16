# Core Plugin Source

Hier gehört der vollständige Quellcode des WordPress-Plugins **Merzenich Aktuell Core** hinein.

Dokumentierter Lieferstand: Core 1.0.0.

Dauerhafte Funktionen wie CPTs, Taxonomien, Orte, Veranstaltungen, Märkte, Werbung, Wetter, KI-/Human-Review-Metadaten, Quellenradar und Formulare gehören in dieses Plugin und nicht ins Theme.

Nicht aus Dokumentation rekonstruieren oder neu erfinden. Sobald der echte Plugin-Quellordner bzw. das originale ZIP verfügbar ist, den Originalstand hier einbringen und danach nur nachvollziehbar per Git weiterentwickeln.

## Stand

Der Quellcode liegt seit dem Auspacken der Lieferzips hier im Klartext. Er
stammt Byte für Byte aus `wordpress-delivery/`; beide Archive wurden vorher
gegen `docs/SHA256SUMS.txt` geprüft und stimmten überein. Nichts daran ist
rekonstruiert.

Ab hier gilt: Änderungen nur hier im Klartext, nachvollziehbar per Git. Die
Archive unter `wordpress-delivery/` bleiben als ausgelieferter Stand liegen
und werden neu gepackt, wenn eine neue Lieferung ansteht. Wer das tut, muss
`docs/SHA256SUMS.txt` mitziehen, sonst schlägt `node qa/pruefung.mjs` fehl.
