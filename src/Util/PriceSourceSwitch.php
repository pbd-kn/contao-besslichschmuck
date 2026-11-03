<?php

declare(strict_types=1);
namespace Pbdkn\ContaoBesslichschmuck\Util;
use Doctrine\DBAL\Connection;

final class PriceSourceSwitch
{
    public function __construct(private Connection $db) {}
    public function status(): array
    {
        $tables=$this->db->createSchemaManager()->listTableNames();
        $legacy=in_array('tl_heike_preisliste',$tables,true);
        $ready=false;
        if(in_array('tl_directory',$tables,true)) {
            $columns=$this->db->createSchemaManager()->listTableColumns('tl_directory');
            $ready=(isset($columns['besslichpriceactive']) || isset($columns['besslichPriceActive'])) && (isset($columns['besslichpricepreferred']) || isset($columns['besslichPricePreferred']));
        }
        return ['ready'=>$ready,'legacy'=>$legacy,'active'=>$ready ? (new PriceDirectory($this->db))->activeDirectory() : null];
    }
    public function preview(): array
    {
        $status=$this->status();
        if(!$status['ready']) {throw new \RuntimeException('Bitte zuerst die beiden neuen Preisquellen-Felder über das Contao-Datenbankupdate anlegen.');}
        if(!$status['legacy']) {throw new \RuntimeException('Die ursprüngliche Preisliste fehlt. Ein geprüfter Erstumstieg ist ohne sie nicht möglich.');}
        $directories=[];
        foreach($this->db->fetchAllAssociative('SELECT * FROM tl_directory') as $directory) {
            $columns=@unserialize((string)$directory['columns'],['allowed_classes'=>false]);
            if(is_array($columns)&&in_array('HeikeQuellId',array_column($columns,'key'),true)) {$directories[]=$directory;}
        }
        if(count($directories)!==1){throw new \RuntimeException('Bitte zuerst die Besslich-Preisliste einmalig in die Tabellenverwaltung übernehmen.');}
        $directory=$directories[0];
        $source=$this->db->fetchAllAssociative('SELECT * FROM tl_heike_preisliste ORDER BY id');
        $entries=$this->db->fetchAllAssociative('SELECT * FROM tl_directory_entry WHERE pid=? ORDER BY id',[$directory['id']]);
        if(!$source||count($source)!==count($entries)){throw new \RuntimeException('Die Datensatzanzahl stimmt nicht überein. Die Preisquelle wird nicht umgestellt.');}
        $original=array_column($source,null,'id');$seen=[];
        foreach($entries as $entry) {
            $data=@unserialize((string)$entry['data'],['allowed_classes'=>false]);
            if(!is_array($data)){throw new \RuntimeException('Ungültige kopierte Feldwerte.');}
            $values=array_column($data,'value','key');$id=$entry['legacyId'];
            if(!isset($original[$id])||isset($seen[$id])||(string)($values['HeikeQuellId']??'')!==(string)$id){throw new \RuntimeException('Die Zuordnung zu den ursprünglichen Datensätzen stimmt nicht überein.');}
            $seen[$id]=true;
            foreach($original[$id] as $key=>$value) {
                $target=$key==='import_source'?'import_source_base64':$key;
                if(!array_key_exists($target,$values)){throw new \RuntimeException('Feld fehlt: '.$id.' / '.$key);}
                $actual=$values[$target];
                if($key==='import_source' && $actual!==null){$actual=base64_decode((string)$actual,true);}
                if($actual!==$value){throw new \RuntimeException('Abweichender Feldwert bei Quell-ID '.$id.' / '.$key.'. Bitte vor der Umschaltung prüfen.');}
            }
        }
        $preferred=$duplicates=[];
        foreach($this->db->fetchFirstColumn('SELECT Artikel FROM tl_heike_preisliste GROUP BY Artikel HAVING COUNT(*)>1') as $name) {
            // Deliberately execute the same query as the legacy application on THIS installation.
            $selected=$this->db->fetchAssociative('SELECT * FROM tl_heike_preisliste WHERE Artikel=?',[$name]);
            $preferred[mb_strtolower(rtrim((string)$name))]=(int)$selected['id'];
            $duplicates[]=['article'=>$name,'sourceId'=>$selected['id']];
        }
        return ['directory'=>(int)$directory['id'],'title'=>$directory['title'],'count'=>count($source),'preferred'=>$preferred,'duplicates'=>$duplicates,'fingerprint'=>hash('sha256',serialize([$directory,$source,$entries]))];
    }
    public function activate(array $plan): void
    {
        $this->db->transactional(function(Connection $db) use($plan):void {
            $lock=$db->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\SqlitePlatform?'':' FOR UPDATE';
            $db->fetchAllAssociative('SELECT id FROM tl_directory ORDER BY id'.$lock);
            $db->fetchAllAssociative('SELECT id FROM tl_directory_entry WHERE pid=?'.$lock,[$plan['directory']]);
            $db->fetchAllAssociative('SELECT id FROM tl_heike_preisliste ORDER BY id'.$lock);
            $current=$this->preview();
            if(!hash_equals($plan['fingerprint'],$current['fingerprint'])||$plan['preferred']!==$current['preferred']){throw new \RuntimeException('Die Daten wurden seit der Vorschau geändert. Bitte erneut prüfen.');}
            $db->executeStatement('UPDATE tl_directory SET besslichPriceActive=?',['']);
            $db->update('tl_directory',['besslichPriceActive'=>'1','besslichPricePreferred'=>serialize($current['preferred']),'tstamp'=>time()],['id'=>$current['directory']]);
            $db->executeStatement('UPDATE tl_directory_entry SET published=?,tstamp=? WHERE pid=?',['1',time(),$current['directory']]);
        });
    }
    public function useLegacy(): void
    {
        $status=$this->status();
        if(!$status['legacy'] || !$status['ready']){throw new \RuntimeException('Die ursprüngliche Tabelle muss für die Rückkehr vorhanden sein.');}
        $this->db->executeStatement('UPDATE tl_directory SET besslichPriceActive=?',['']);
    }
}
