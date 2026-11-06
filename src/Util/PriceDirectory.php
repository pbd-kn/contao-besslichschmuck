<?php

declare(strict_types=1);
namespace Pbdkn\ContaoBesslichschmuck\Util;
use Doctrine\DBAL\Connection;

/** Reads published prices exclusively from the configured directory. */
final class PriceDirectory
{
    private Connection $db;
    private ?array $rows = null;
    private ?array $source = null;
    public function __construct(Connection $db) { $this->db = $db; }
    public function activeDirectory(): ?array
    {
        if ($this->source !== null) { return $this->source ?: null; }
        $tables = $this->db->createSchemaManager()->listTableNames();
        if (!in_array('tl_directory',$tables,true)) { $this->source=[]; return null; }
        $columns = $this->db->createSchemaManager()->listTableColumns('tl_directory');
        if ((!isset($columns['besslichpriceactive']) && !isset($columns['besslichPriceActive'])) || (!isset($columns['besslichpricepreferred']) && !isset($columns['besslichPricePreferred']))) { $this->source=[]; return null; }
        $active = $this->db->fetchAllAssociative('SELECT * FROM tl_directory WHERE besslichPriceActive=?',['1']);
        if (count($active)>1) { throw new \RuntimeException('Mehrere Preislisten sind aktiv. Bitte die Preislisten-Konfiguration prüfen.'); }
        $this->source=$active[0]??[];
        return $this->source ?: null;
    }
    public function all(): array
    {
        if ($this->rows !== null) { return $this->rows; }
        $directory = $this->activeDirectory();
        if ($directory === null) {
            throw new \RuntimeException('Keine Tabellenverwaltung als Preisliste eingerichtet.');
        }
        $rows = [];
        foreach ($this->db->fetchAllAssociative('SELECT legacyId,data FROM tl_directory_entry WHERE pid=? AND published=? ORDER BY legacyId,id', [$directory['id'], '1']) as $entry) {
            $data = unserialize((string)$entry['data'], ['allowed_classes'=>false]);
            if (!is_array($data)) { throw new \RuntimeException('Ungültige Preislistendaten.'); }
            $row = array_column($data,'value','key');
            if (!isset($row['Artikel'], $row['HeikeQuellId']) || (string)$row['HeikeQuellId'] !== (string)$entry['legacyId']) { throw new \RuntimeException('Die Herkunfts-ID eines Preiseintrags ist ungültig.'); }
            $row['id'] = (int)$entry['legacyId'];
            if (array_key_exists('import_source_base64',$row)) {
                $row['import_source'] = $row['import_source_base64'] === null ? null : base64_decode($row['import_source_base64'], true);
                unset($row['import_source_base64']);
            }
            unset($row['HeikeQuellId']);
            $rows[] = $row;
        }
        return $this->rows = $rows;
    }
    public function get(string $article): array
    {
        $directory = $this->activeDirectory();
        if ($directory === null) {
            throw new \RuntimeException('Keine Tabellenverwaltung als Preisliste eingerichtet.');
        }
        $matches = array_values(array_filter($this->all(), static fn(array $row): bool => mb_strtolower(rtrim((string)$row['Artikel'])) === mb_strtolower(rtrim($article))));
        $preferred = @unserialize((string)($directory['besslichPricePreferred']??''), ['allowed_classes'=>false]);
        $selected = is_array($preferred) ? ($preferred[mb_strtolower(rtrim($article))]??null) : null;
        foreach ($matches as $row) { if ($selected !== null && (int)$row['id'] === (int)$selected) { return $row; } }
        return $matches[0] ?? [];
    }
    public function filter(array $filters = []): array
    {
        $rows = $this->all();
        foreach ($filters as $key => $pattern) {
            if (!in_array($key, ['Artikel','Kategorie','Subkategorie'], true) || !is_string($pattern)) {
                throw new \InvalidArgumentException('Ungültiger Preislistenfilter.');
            }
            if ($pattern === '') {
                $rows = array_values(array_filter($rows, static fn(array $row): bool => ($row[$key] ?? null) === null || $row[$key] === ''));
                continue;
            }
            // Preserve the MySQL collation, including accent-insensitive LIKE matching.
            $values = array_values(array_unique(array_map(static fn(array $row) => $row[$key] ?? null, $rows), SORT_REGULAR));
            $expressions = $params = [];
            foreach ($values as $index => $value) {
                $expressions[] = 'CAST(? AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci LIKE ? AS m'.$index;
                $params[] = $value; $params[] = $pattern;
            }
            if (!$expressions) { return []; }
            $matches = $this->db->fetchAssociative('SELECT '.implode(',', $expressions), $params);
            $accepted = [];
            foreach ($values as $index => $value) { if ($matches['m'.$index]) { $accepted[] = $value; } }
            $rows = array_values(array_filter($rows, static fn(array $row): bool => in_array($row[$key] ?? null, $accepted, true)));
        }
        return $rows;
    }
}