# Preisliste in der Tabellenverwaltung

Die Preisausgabe liest ausschließlich veröffentlichte Einträge der aktiven Tabelle in der Tabellenverwaltung. Die bisherige Preisquelle, der Umschalter und die alten Import-Weiterleitungen sind entfernt. Der Übernahme-Menüpunkt des Tabellenverwaltungs-Bundles wird in dieser Installation ausgeblendet.

`besslichPriceActive` kennzeichnet die verwendete Tabelle. `besslichPricePreferred` erhält die bisherige Auswahl bei mehrfachen Artikelbezeichnungen. Diese beiden Felder werden weiterhin benötigt. Technische Herkunfts-IDs in den übernommenen Einträgen sichern die Reihenfolge und die Zuordnung doppelter Artikel; sie greifen nicht auf eine alte Tabelle zu.

Preise werden unter Tabellenverwaltung → Tabellen → Heike Preisliste bearbeitet. Der Preislisten-CSV-Import ergänzt oder ersetzt die veröffentlichte Preisliste nach Vorschau und Bestätigung.

## Insert-Tags der Tabellenverwaltung

- `{{direntry::Heike Preisliste::ARTIKELBEZEICHNUNG}}` liefert die interne Eintrags-ID, sofern genau ein veröffentlichter Eintrag mit dieser Bezeichnung existiert.
- `{{tabelle::17::PreisStueck2_3}}` liefert den Feldwert des veröffentlichten Eintrags mit ID 17.
- `{{tabelle::17::_title}}` liefert dessen Bezeichnung.

17 ist nur ein Beispiel und muss durch eine echte Eintrags-ID ersetzt werden. Die Feldschlüssel sind exakt zu schreiben. Unbekannte, nicht veröffentlichte oder mehrdeutige Einträge liefern leere Werte. Feldwerte werden HTML-geschützt ausgegeben. Ein CSV-Ersetzen vergibt neue Eintrags-IDs; feste ID-Tags müssen dann angepasst werden. Die beiden Tags sind nicht automatisch ineinander verschachtelbar.

## Preiszugriff im Schmuck-Bundle

`BesslichUtil::getPreis($artikel)` ruft `PriceDirectory::get($artikel)` auf. Dieser lädt die veröffentlichte Preisliste aus `tl_directory_entry`, dekodiert deren Feldwerte und wählt den Artikel anhand des Feldes `Artikel`. Bei doppelten Artikeln wird die gespeicherte bevorzugte Zuordnung verwendet.

`createPreislisteRender()` wählt nach Preisart die Felder `PreisStueck2_3`/`PreisPaar2_3`, `PreisStueck2_5`/`PreisPaar2_5` oder `PreisStueckEK`/`PreisPaarEK` und rendert daraus die Ausgabe. Artikeldetails, Galerie, Zusatzartikel, Preislistenmodul und AJAX-Abfragen verwenden diese zentrale Datenquelle. Dafür werden keine eigenen Preis-Insert-Tags benötigt.
