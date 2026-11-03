<?php

declare(strict_types=1);
namespace Pbdkn\ContaoBesslichschmuck\Util;

/** Website files, independent of the browser's local upload dialog. */
final class WebsiteCsvFiles
{
    public static function listing(string $projectDir): array
    {
        $root = realpath($projectDir.'/files');
        if ($root === false) { return []; }
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->isLink() || strtolower($file->getExtension()) !== 'csv') { continue; }
            $path = 'files/'.str_replace('\\','/',substr($file->getPathname(),strlen($root)+1));
            $files[] = $path;
        }
        usort($files, static function (string $a, string $b): int {
            $priority = static fn(string $path): int => str_starts_with(strtolower($path),'files/heike-files/downloads/csv/') ? 0 : 1;
            return ($priority($a)<=>$priority($b)) ?: strnatcasecmp($a,$b);
        });
        return $files;
    }
    public static function read(string $projectDir, string $path): string
    {
        if (!str_starts_with($path,'files/') || !in_array($path,self::listing($projectDir),true)) { throw new \RuntimeException('Bitte eine vorhandene CSV-Datei aus dem files-Verzeichnis auswählen.'); }
        $root = realpath($projectDir.'/files');
        $file = realpath($projectDir.'/'.$path);
        if ($root === false || $file === false || !str_starts_with(str_replace('\\','/',$file),str_replace('\\','/',$root).'/') || !is_readable($file)) { throw new \RuntimeException('Die CSV-Datei liegt nicht im files-Verzeichnis oder ist nicht lesbar.'); }
        $handle = fopen($file,'rb');
        if ($handle === false) { throw new \RuntimeException('Die CSV-Datei konnte nicht geöffnet werden.'); }
        try { $contents = stream_get_contents($handle,5242881); } finally { fclose($handle); }
        if ($contents === false || strlen($contents)>5242880) { throw new \RuntimeException('Die CSV darf höchstens 5 MB groß sein.'); }
        return $contents;
    }
}
