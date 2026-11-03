<?php

declare(strict_types=1);
namespace Pbdkn\ContaoBesslichschmuck\Util;
use Doctrine\DBAL\Connection;
use PBDKN\TabellenverwaltungBundle\Backend\CsvTransfer;

/** Price-list specific CSV import: append or replace, without CSV IDs. */
final class PriceCsvImport
{
    private Connection $db;
    private const FIELDS = ['Artikel','Kategorie','Subkategorie','PreisStueck2_5','PreisPaar2_5','PreisStueck2_3','PreisPaar2_3','PreisStueckEK','PreisPaarEK','Beschreibung','listTpl'];
    public function __construct(Connection $db) { $this->db = $db; }

    private function target(): array
    {
        $found = [];
        foreach ($this->db->fetchAllAssociative('SELECT * FROM tl_directory') as $directory) {
            $columns = $this->decode($directory['columns']);
            if (in_array('HeikeQuellId', array_column($columns,'key'), true)) { $found[] = $directory; }
        }
        if (count($found) !== 1) { throw new \RuntimeException('Es muss genau eine übernommene Heike-Preisliste existieren.'); }
        return $found[0];
    }
    private function decode($blob): array
    {
        if (is_resource($blob)) { $blob = stream_get_contents($blob); }
        $data = @unserialize((string)$blob, ['allowed_classes'=>false]);
        if (!is_array($data)) { throw new \RuntimeException('Ungültige Tabellendaten.'); }
        return $data;
    }
    private function snapshot(int $id): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM tl_directory_entry WHERE pid=? ORDER BY id', [$id]);
    }
    public function preview(string $contents, string $mode, string $delimiter = 'auto'): array
    {
        if (!in_array($mode, ['append','replace'], true)) { throw new \InvalidArgumentException('Ungültiger Importmodus.'); }
        $directory = $this->target();
        $active = (new PriceDirectory($this->db))->activeDirectory();
        if ($active === null || (int)$active['id'] !== (int)$directory['id']) { throw new \RuntimeException('Bitte zuerst die übernommene Preisliste unter Preisquelle umstellen prüfen und aktivieren.'); }
        $csv = (new CsvTransfer($this->db))->parse($contents, $delimiter);
        if (!in_array('Artikel', $csv['header'], true)) { throw new \RuntimeException('Die CSV-Kopfzeile muss Artikel enthalten (Schreibweise beachten).'); }
        if ($mode === 'replace') {
            $required = ['Kategorie','Subkategorie','Beschreibung','PreisStueck2_5','PreisPaar2_5','PreisStueck2_3','PreisPaar2_3','PreisStueckEK','PreisPaarEK'];
            $missing = array_diff($required, $csv['header']);
            if ($missing) { throw new \RuntimeException('Zum vollständigen Ersetzen fehlen CSV-Spalten: '.implode(', ', $missing)); }
        }
        $columns = array_column($this->decode($directory['columns']), 'key');
        $before = $this->snapshot((int)$directory['id']);
        $legacy = $sorting = 0;
        $counts = [];
        foreach ($before as $entry) {
            $legacy = max($legacy, (int)$entry['legacyId']);
            $sorting = max($sorting, (int)$entry['sorting']);
            $data = array_column($this->decode($entry['data']), 'value', 'key');
            $key = mb_strtolower(trim((string)($data['Artikel'] ?? '')));
            if ($mode === 'append') { $counts[$key] = ($counts[$key] ?? 0) + 1; }
        }
        $rows = [];
        $skipped = [];
        foreach ($csv['rows'] as $index => $source) {
            $article = trim($source['Artikel']);
            $missing = [];
            foreach (['Artikel','Kategorie','Subkategorie'] as $required) {
                if (trim((string)($source[$required] ?? '')) === '') { $missing[] = $required; }
            }
            if ($missing) { $skipped[] = ['record'=>$index+1,'missing'=>$missing]; continue; }
            if (mb_strlen($article) > 255) { throw new \RuntimeException('Datensatz '.($index+1).': Artikel ist länger als 255 Zeichen.'); }
            ++$legacy; $sorting += 128;
            $values = array_fill_keys($columns, '');
            foreach (self::FIELDS as $key) { if (array_key_exists($key, $source)) { $values[$key] = $source[$key]; } }
            $values['Artikel'] = $article;
            $values['HeikeQuellId'] = (string)$legacy;
            $values['id'] = (string)$legacy;
            $values['pid'] = '0'; $values['sorting'] = (string)$sorting; $values['tstamp'] = (string)time();
            if (array_key_exists('import_source_base64', $values)) { $values['import_source_base64'] = null; }
            $data = [];
            foreach ($values as $key=>$value) { $data[] = ['key'=>$key,'value'=>$value]; }
            $rows[] = ['legacyId'=>$legacy,'sorting'=>$sorting,'title'=>$article,'data'=>serialize($data),'published'=>'1'];
            $key = mb_strtolower($article);
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }
        if (!$rows) { throw new \RuntimeException('Die CSV enthält keine importierbaren Datensätze mit Artikel, Kategorie und Subkategorie.'); }
        $duplicateNames = array_keys(array_filter($counts, static fn(int $count): bool => $count > 1));
        return [
            'directory'=>(int)$directory['id'], 'title'=>$directory['title'], 'mode'=>$mode,
            'schema'=>hash('sha256', serialize($directory)), 'before'=>hash('sha256', serialize($before)),
            'oldCount'=>count($before), 'rows'=>$rows, 'ignored'=>array_values(array_diff($csv['header'], self::FIELDS)),
            'duplicateNames'=>$duplicateNames, 'skipped'=>$skipped, 'csvCount'=>count($csv['rows']),
        ];
    }
    public function execute(array $plan): int
    {
        return $this->db->transactional(function (Connection $db) use ($plan): int {
            if (!in_array($plan['mode'], ['append','replace'], true) || !$plan['rows']) { throw new \RuntimeException('Ungültige Importvorschau.'); }
            $lock = $db->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform ? '' : ' FOR UPDATE';
            $db->fetchOne('SELECT id FROM tl_directory WHERE id=?'.$lock, [$plan['directory']]);
            $directory = $this->target();
        $active = (new PriceDirectory($this->db))->activeDirectory();
        if ($active === null || (int)$active['id'] !== (int)$directory['id']) { throw new \RuntimeException('Bitte zuerst die übernommene Preisliste unter Preisquelle umstellen prüfen und aktivieren.'); }
            $db->fetchAllAssociative('SELECT id FROM tl_directory_entry WHERE pid=?'.$lock, [$plan['directory']]);
            if ((int)$directory['id'] !== $plan['directory'] || !hash_equals($plan['schema'], hash('sha256',serialize($directory))) || !hash_equals($plan['before'],hash('sha256',serialize($this->snapshot($plan['directory']))))) {
                throw new \RuntimeException('Die Preisliste wurde seit der Vorschau geändert. Bitte eine neue Vorschau erstellen.');
            }
            if ($plan['mode'] === 'replace') {
                $db->executeStatement('DELETE FROM tl_directory_entry WHERE pid=?', [$plan['directory']]);
                $db->update('tl_directory',['besslichPricePreferred'=>serialize([]),'tstamp'=>time()],['id'=>$plan['directory']]);
            }
            foreach ($plan['rows'] as $row) {
                $db->insert('tl_directory_entry', $row + ['pid'=>$plan['directory'],'tstamp'=>time()]);
                $stored = $db->fetchAssociative('SELECT title,data FROM tl_directory_entry WHERE id=?', [$db->lastInsertId()]);
                $storedData = $stored['data'];
                if (is_resource($storedData)) { $storedData = stream_get_contents($storedData); }
                if ($storedData !== $row['data'] || $stored['title'] !== $row['title']) { throw new \RuntimeException('Gespeicherte CSV-Werte stimmen nicht überein. Import zurückgerollt.'); }
            }
            $expected = count($plan['rows']) + ($plan['mode'] === 'append' ? $plan['oldCount'] : 0);
            if ((int)$db->fetchOne('SELECT COUNT(*) FROM tl_directory_entry WHERE pid=?',[$plan['directory']]) !== $expected) { throw new \RuntimeException('Datensatzanzahl stimmt nicht überein. Import zurückgerollt.'); }
            return count($plan['rows']);
        });
    }
}
