<?php
declare(strict_types=1);
namespace Pbdkn\ContaoBesslichschmuck\Util;

/** Reads only saved cell results. Does not evaluate formulas or execute macros. */
final class PriceOdsReader
{
    public const RANGE = '$PreislisteI_2006.$T$66:AJ1500';
    private const T = 'urn:oasis:names:tc:opendocument:xmlns:table:1.0';
    private const O = 'urn:oasis:names:tc:opendocument:xmlns:office:1.0';
    private const X = 'urn:oasis:names:tc:opendocument:xmlns:text:1.0';
    private const FIELDS = ['Artikel','Kategorie','Subkategorie','Beschreibung','PreisStueckEK','PreisPaarEK','PreisStueck2_5','PreisPaar2_5','PreisStueck2_3','PreisPaar2_3'];

    public static function read(string $contents, string $range = self::RANGE): array
    {
        if (!preg_match('/^\$?PreislisteI_2006\.\$?T\$?(\d+):\$?AJ\$?(\d+)$/D', $range, $m) || (int)$m[1] < 1 || (int)$m[2] < (int)$m[1] || (int)$m[2] > 10000) {
            throw new \RuntimeException('Bitte einen Bereich wie '.self::RANGE.' angeben (Spalten T bis AJ, höchstens Zeile 10000).');
        }
        $first = (int)$m[1]; $last = (int)$m[2];
        if (!class_exists(\ZipArchive::class)) { throw new \RuntimeException('Die PHP-Erweiterung zip wird zum Lesen von ODS benötigt.'); }
        $temp = tempnam(sys_get_temp_dir(), 'heike_ods_');
        if ($temp === false) { throw new \RuntimeException('ODS-Zwischendatei konnte nicht angelegt werden.'); }
        try {
            $zip = new \ZipArchive();
            if (file_put_contents($temp, $contents) === false || $zip->open($temp) !== true) { throw new \RuntimeException('Ungültige ODS-Datei.'); }
            try {
                $stat = $zip->statName('content.xml');
                if (!$stat || $stat['size'] > 52428800) { throw new \RuntimeException('ODS-Inhalt fehlt oder ist größer als 50 MB.'); }
                $xml = $zip->getFromName('content.xml');
            } finally { $zip->close(); }
        } finally { unlink($temp); }
        $doc = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try { $ok = is_string($xml) && $doc->loadXML($xml, LIBXML_NONET); }
        finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
        if (!$ok || $doc->doctype) { throw new \RuntimeException('Ungültiger ODS-XML-Inhalt.'); }
        $xpath = new \DOMXPath($doc); $xpath->registerNamespace('t', self::T);
        $sheet = $xpath->query('//t:table[@t:name="PreislisteI_2006"]')->item(0);
        if (!$sheet) { throw new \RuntimeException('Tabellenblatt PreislisteI_2006 fehlt.'); }
        $stream = fopen('php://temp', 'w+');
        try {
            fputcsv($stream, self::FIELDS, ';', '"', '');
            $number = 1; $mapping = null; $warnings = []; $articleRows = [];
            foreach ($xpath->query('.//t:table-row', $sheet) as $row) {
                if ($number > $last) { break; }
                $repeat = max(1, (int)$row->getAttributeNS(self::T, 'number-rows-repeated'));
                // Column A is checked independently of the selected export range.
                $columnA = $xpath->query('./t:table-cell | ./t:covered-table-cell', $row)->item(0);
                if ($columnA && preg_match('/\betsy\b/iu', trim($columnA->textContent))) {
                    $warnings[] = 'ODS ab Zeile '.$number.' nicht berücksichtigt: Etsy-Markierung in Spalte A.';
                    break;
                }
                if ($number + $repeat > $first) {
                    $values = array_fill(19, 17, ''); $column = 0;
                    foreach ($row->childNodes as $cell) {
                        if (!$cell instanceof \DOMElement || !in_array($cell->localName, ['table-cell','covered-table-cell'], true)) { continue; }
                        if ($column > 35) { break; }
                        $columns = max(1, (int)$cell->getAttributeNS(self::T, 'number-columns-repeated'));
                        $parts = [];
                        foreach ($cell->getElementsByTagNameNS(self::X, 'p') as $p) { $parts[] = $p->textContent; }
                        $value = implode("\n", $parts);
                        if (in_array($cell->getAttributeNS(self::O, 'value-type'), ['float','currency','percentage'], true)) { $value = str_replace('.', ',', $cell->getAttributeNS(self::O, 'value')); }
                        for ($c = max(19, $column); $c <= min(35, $column + $columns - 1); ++$c) { $values[$c] = $value; }
                        $column += $columns;
                    }
                    if (in_array('Artikel', $values, true) && in_array('Kategorie', $values, true)) {
                        if ($mapping === null) {
                            $mapping = [];
                            foreach (self::FIELDS as $field) {
                                $key = array_search($field, $values, true);
                                if ($key === false) { throw new \RuntimeException('ODS-Kopfzeile: '.$field.' fehlt.'); }
                                $mapping[$field] = $key;
                            }
                        }
                    } elseif ($mapping !== null && count(array_filter($values, static fn($v) => trim($v) !== ''))) {
                        $record = []; $invalid = [];
                        foreach ($mapping as $field => $key) {
                            $value = $values[$key];
                            if (str_starts_with($field, 'Preis') && trim($value) !== '' && !preg_match('/^-?\d+(?:,\d+)?$/D', $value)) { $invalid[] = $field.': '.$value; }
                            $record[] = $value;
                        }
                        if ($invalid) { $warnings[] = 'ODS-Zeile '.max($first, $number).' ausgelassen – kein gültiger gespeicherter Preis ('.implode(', ', $invalid).').'; }
                        else { for ($r = max($first, $number); $r <= min($last, $number + $repeat - 1); ++$r) { $articleRows[mb_strtolower(trim($values[$mapping["Artikel"]]))][] = $r; fputcsv($stream, $record, ';', '"', ''); } }
                    }
                }
                $number += $repeat;
            }
            if ($mapping === null) { throw new \RuntimeException('Keine Preislisten-Kopfzeile im gewählten Bereich gefunden.'); }
            rewind($stream);
            return ['csv'=>stream_get_contents($stream), 'warnings'=>$warnings, 'range'=>$range, 'articleRows'=>$articleRows];
        } finally { fclose($stream); }
    }
}
