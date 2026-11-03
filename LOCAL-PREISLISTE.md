# Lokale Preislisten-Umstellung und Hoster-Vorbereitung

## Aktueller lokaler Stand

Die Website verwendet die Tabellenverwaltung. Am 03.11.2026 enthält sie nach dem zwischenzeitlichen CSV-Import 815 veröffentlichte Einträge mit internen Herkunfts-IDs 838–1652. Die ursprüngliche Tabelle enthält weiterhin 837 Datensätze. Beide Bestände wurden bei dieser Bereinigung unverändert erhalten. Die Auswahl bei mehrfachen Artikelbezeichnungen entspricht weiterhin der CSV-Reihenfolge.

## Sichere Erstumschaltung auf einer anderen Installation

1. Vollständige Datenbank- und Dateisicherung erstellen.
2. Bundle installieren und Contao-Datenbankupdate ausführen. `tl_directory` erhält `besslichPriceActive` und `besslichPricePreferred`. Die alte Tabelle wird durch eine reine Schema-DCA weiterhin vor versehentlichem Entfernen geschützt.
3. Bis zur ausdrücklichen Aktivierung liest die Anwendung aus `tl_heike_preisliste`, auch wenn die Tabellenverwaltung noch nicht eingerichtet ist.
4. Unter **Tabellenverwaltung → Besslich-Preisliste übernehmen** den vorhandenen Serverbestand kopieren.
5. Unter **Tabellenverwaltung → Preisquelle umstellen** den Vergleich starten. Alle Datensätze, sämtliche Quellfelder einschließlich binärer Datei-UUIDs und die Herkunftszuordnung müssen übereinstimmen. Bei Abweichungen wird nicht aktiviert.
6. Bei doppelten Artikelbezeichnungen ermittelt die Anwendung die bisherigen Treffer anhand derselben SQL-Abfrage auf genau dieser Installation. Diese Zuordnungen werden in der Datenbank gespeichert; keine lokalen IDs stehen mehr im Quellcode.
7. Nach Vorschau und Bestätigung die Tabellenverwaltung aktivieren. Die kopierten Einträge werden dabei veröffentlicht. Übersicht, Details, Zusatzpreise und Preislistenmodul prüfen.

**Zurückschalten** ist im selben Backend-Bereich möglich, solange die ursprüngliche Tabelle vorhanden ist. Dabei wird deren ursprünglicher Stand verwendet: spätere CSV- oder Preisänderungen aus der Tabellenverwaltung werden nicht zurückkopiert. Nach abweichenden Änderungen ist der strenge Erstvergleich für eine erneute Aktivierung nicht mehr erfolgreich; deshalb die Rückkehr nur als Rückfallmaßnahme verwenden.

## Bereinigung

Der alte Backend-Menüpunkt „Tabellen → Preisliste“, dessen Bearbeitungs-DCA, die alten Import-Templates und die unbenutzten Sprachtexte sind entfernt. Die beiden bisherigen Import-URLs bleiben ausschließlich als Weiterleitungen auf den neuen Import bestehen. Die alte SQL-Tabelle und ihre reine Schema-DCA bleiben bis zum abgeschlossenen Hoster-Umzug erhalten. Sie werden nicht automatisch gelöscht.

Das Tabellenverwaltungs-Bundle wurde bei dieser Bereinigung nicht geändert. Die Zusatzfelder und beide neuen Backend-Bereiche gehören zum Besslichschmuck-Bundle. Die früheren EFG-Popup-Zuordnungen sind weiterhin nicht rekonstruiert; Zusatzinfo am Schmuckartikel funktioniert ohne EFG.

## CSV-Preispflege

**Tabellenverwaltung → Preisliste CSV importieren** liest CSV-Dateien aus dem `files`-Verzeichnis der Website. Der Download-/CSV-Ordner wird zuerst angezeigt. Neue Dateien über die Contao-Dateiverwaltung hochladen.

Die bisherige CSV benötigt keine ID-Spalte. **Ergänzen** hängt jede CSV-Zeile an, ohne vorhandene Preise zu aktualisieren. **Gesamte Preisliste ersetzen** übernimmt ausschließlich den neuen CSV-Bestand, nach Sicherung, Vorschau und Bestätigung. Artikel, Kategorie, Subkategorie, Beschreibung und alle sechs Preisfelder sind beim Ersetzen erforderlich; `listTpl` ist optional. Zeilen ohne Artikel, Kategorie oder Subkategorie werden wie bisher ausgelassen und in der Vorschau aufgelistet. Technische Quellspalten werden ignoriert; Preiszeichenfolgen bleiben unverändert.

Der Preislisten-CSV-Import ist erst nach Aktivierung dieser Tabelle als Preisquelle verfügbar. Bei vollständigem Ersetzen werden neue interne IDs vergeben und die bisherigen bevorzugten Zuordnungen zurückgesetzt. Bei mehrfachen Artikelbezeichnungen wird dann der erste Treffer in CSV-Reihenfolge verwendet. Auf numerischen Eintrags-IDs beruhende Links oder Insert-Tags müssen separat berücksichtigt werden. Neue Einträge sind sofort veröffentlicht.

Vorschau und Ausführung prüfen Schema und Bestandszustand; zwischenzeitliche Änderungen verhindern die Ausführung. Ersetzen, Einfügen und Kontrolle der gespeicherten Werte erfolgen in einer gemeinsamen Datenbanktransaktion.

## Prüfung

- `tests/price-source.php`: Übergang vor Schema/Installation, vollständiger Vergleich, dynamische Zuordnung bei Doppelungen, Veröffentlichung, veraltete Vorschau, atomare Aktivierung und Rückkehr zur alten Quelle.
- `tests/price-csv.php`: Vorschau, Ergänzen/Ersetzen ohne CSV-IDs, Auswahl bei Doppelungen, neue Artikel, Mehrzeiler und Preisformate, Schutz vor veralteter Vorschau, vollständiger Rollback.
- `tests/schmuck-popup.php`: Rendering ohne EFG, explizite Zusatzinfo und sicheres Encoding.

Für lokale Tests `BUNDLE_TEST_AUTOLOAD` auf den Autoloader der Contao-Installation setzen. Tests verwenden SQLite im Arbeitsspeicher; sie schreiben keine Website-Daten.

Die lokale Konfiguration wurde vor der Umschaltvorbereitung außerhalb des Repositories gesichert unter `C:/Users/pbd/.codex/visualizations/2026/11/02/01a19d1a-5f22-73b3-9b2e-ecd5bc255647/heike-before-source-switch-*.bin`. Kein Commit, Push oder Release für diese Vorbereitung ausgeführt.
