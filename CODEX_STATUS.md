# Codex Status

Stand: Preislisten-Backend im Contao-Bundle `contao-besslichschmuck`.

## Bisher gemacht

- Backend-Modul `PreislisteKonfiguration` in `BE_MOD` registriert.
- Backend-Routen auf Annotation-Routing mit Backend-Scope umgestellt.
- Preislisten-Backend-Templates optisch vereinheitlicht:
  - `be_preisliste_config.html5`
  - `be_preisliste_edit.html5`
  - `be_preisliste_catalog.html5`
  - `be_preisliste_calculation.html5`
  - `be_preisliste_globals.html5`
  - `be_preisliste_formula_rules.html5`
  - `be_import_csv.html5`
  - `be_import_csv_checkbox.html5`
- CSV-Import-Templates von eigener HTML-Seite auf Contao `be_main` umgestellt.
- In `PreislisteConfigController` wurde begonnen, die alte Berechnungs-/Erklaerlogik aus `web/calc_explain.php` nach Contao zu portieren.

## Aktueller Fehler

Die Berechnungen wurden im Backend nicht angezeigt.

Wahrscheinliche Ursache:

- Die alte Berechnungslogik basiert auf der Tabelle `catalog_items` mit technischen Spalten wie:
  - `artikel_code`
  - `ag_in_g`
  - `au_in_g`
  - `zeit_in_h`
  - `stueck_1`
  - `steine_perlen_ek`
  - `steine_messe`
  - `furnituren_steine_ek`
  - `plattierung_oxidation`
  - `schnur_2`
- Die aktuelle Contao-DCA `tl_heike_preisliste` hat bisher vor allem Anzeige-/Preisfelder:
  - `Artikel`
  - `Kategorie`
  - `Subkategorie`
  - `Beschreibung`
  - `PreisStueck2_5`
  - `PreisPaar2_5`
  - `PreisStueck2_3`
  - `PreisPaar2_3`
  - `PreisStueckEK`
  - `PreisPaarEK`

Damit fehlen der Berechnung die Eingabewerte. Die Erklaeransicht kann deshalb keine sinnvollen Berechnungen anzeigen.

## Update 2026-05-23

- `tl_heike_preisliste.php` wurde um die technischen Berechnungsspalten aus der alten `catalog_items`-Struktur erweitert.
- Die Bearbeiten-Maske zeigt jetzt Feldgruppen fuer Stammdaten, Kalkulationsbasis, Steine/Furnituren/Zuschlaege, Preise/Zwischenergebnisse und Import/Reserve.
- `PreislisteConfigController` legt fehlende technische Spalten in `tl_heike_preisliste` beim Oeffnen der Preislisten-Backendseiten automatisch nach.
- `global_params` wird mit den alten Defaultwerten initialisiert, falls Eintraege fehlen.
- `catalog_formula_rules` wird mit Default-Formelregeln initialisiert.
- Die Berechnungsansicht versucht als Fallback, technische Werte aus einer vorhandenen Tabelle `catalog_items` anhand von `artikel_code`/`Artikel` zu holen.

Nach dem Deployment/Cache-Leeren sollte einmal die Preislisten-Konfiguration oder Datensatz-Bearbeitung geoeffnet werden, damit die Auto-Migration laeuft. Danach sollte ein Reimport der CSV mit vollstaendigen Spalten die neuen Felder fuellen.

## Naechster sinnvoller Schritt

1. Entscheiden, ob `tl_heike_preisliste` die komplette neue CSV-Struktur aus `catalog_items` bekommen soll.
2. Falls ja: DCA `tl_heike_preisliste.php` um die technischen Berechnungsspalten erweitern.
3. CSV-Import so anpassen, dass diese Spalten importiert werden.
4. Berechnungsansicht danach gegen echte Datensaetze testen.
5. Optional: alte Feldnamen (`PreisStueck2_5`) und neue technische Feldnamen (`preis_stueck_2_5`) sauber mappen oder vereinheitlichen.

## Wichtige Dateien

- `src/Controller/BackendController/PreislisteConfigController.php`
- `src/Resources/contao/templates/be_preisliste_calculation.html5`
- `src/Resources/contao/dca/tl_heike_preisliste.php`
- Alte Referenz:
  - `C:\wampneu\www\co5\websites\HeikePreisliste\web\calculate.php`
  - `C:\wampneu\www\co5\websites\HeikePreisliste\web\calc_explain.php`
  - `C:\wampneu\www\co5\websites\HeikePreisliste\web\catalog_columns_rename.sql`
