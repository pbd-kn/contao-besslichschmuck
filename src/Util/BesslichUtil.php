<?php

declare(strict_types=1);


namespace Pbdkn\ContaoBesslichschmuck\Util;

use Doctrine\DBAL\Connection;
use Pbdkn\ContaoBesslichschmuck\Util\CgiUtil;
use Contao\FilesModel;
use Pbdkn\ContaoBesslichschmuck\Model\SchmuckartikelModel;



class BesslichUtil
{
    private Connection $connection;
    private CgiUtil $cgiUtil;
    public function __construct( Connection $connection, CgiUtil $cgiUtil,)
    {
            $this->connection = $connection;
            $this->cgiUtil=$cgiUtil;
    }
    /**
     * @throws \Exception
     */
  /* liefert die Schmuckartikel aus der preisliste selektiert nach sql
   */
  public function getSchmuckArtikelFromPreisliste( string $sql, array $params, array $ignoreFields=[]): array
  {
    $stmt = $this->connection->prepare($sql);
    $stmt = $this->connection->executeQuery ($sql, $params);
    $num_rows = $stmt->rowCount();    
      
    $resarr=[];
    if ($num_rows > 0) {
      // Iteriere durch die Ergebnisse
      while (($row = $stmt->fetchAssociative()) !== false) $resarr[]=$row;
    }
    return $resarr;  
  }
  /* liefert die Preisentry zu einem artikel aus der preisliste selektiert nach sql
   */
  public function getPreis( string $artikel): array
  {
    $resarr=[];
    $sql = "SELECT * FROM tl_heike_preisliste WHERE Artikel ='$artikel'";
    $stmt = $this->connection->executeQuery ($sql);

    $num_rows = $stmt->rowCount();    
      
    $resarr=[];
    if ($num_rows > 0) {
      $resarr = $stmt->fetchAssociative();
    }
    return $resarr;
  }  
 /* 
   * erzeugt eine gerenderte Preisliste 
   *              für den $schmuckartikel
   *              und macht evtl. die deserialize für die zusatzartikel
   * Art = EK Einkauf
   *     = 23 VK 2.3
   *     = 25 VK 2.5
   *  Default bei falscher eingabe 23
   */
   public function createFullPreislisteRender (string $art, $schmuckartikel,  $serialarrNamen): string
  {
      $preisArtikelNamen=[];
      $preisArtikelNamen[]=$schmuckartikel;  // preis des schmuckartikel selbst
      
      if (isset($serialarrNamen) &&  ($serialarrNamen!= '')) {  // prüfen ob zusaetzliche Preise  da  
            $arr =  deserialize($serialarrNamen, true);  // preisliste ist die eingabe von mehreren Namen fuer die die Preise angezeigtwerden soll
            foreach ($arr as $k=>$v) {
              $preisArtikelNamen[]=$v;  // zusatzartikel übenehmen
            }   
      }
      return $this->createPreislisteRender($art,$preisArtikelNamen);  
  }
  
  /* 
   * erzeugt eine gerenderte Preisliste aller in array $arrNamen enthaltenen Felder
   * Art = EK Einkauf
   *     = 23 VK 2.3
   *     = 25 VK 2.5
   *  Default bei falscher eingabe 23
   */
  public function createPreislisteRender (string $art, array $arrNamen): string
  {
      $c=$this->cgiUtil;
      $indStueck='PreisStueck2_3';
      $indPaar='PreisPaar2_3';
      $art = strtolower($art);
      if ($art == '25') {
        $indStueck='PreisStueck2_5';
        $indPaar='PreisPaar2_5';
      } else if ($art == 'ek') {
        $indStueck='PreisStueckEK';
        $indPaar='PreisPaarEK';
      }
      $html=$c->div(array("class"=>"preistabelle"));
      $html.=$c->table(array("class"=>"tablepreistabelle"));
      $html.=$c->tbody();      
      foreach ($arrNamen as $name) {
        $arrPr=$this->getPreis($name);
        $PreisStueck=@$arrPr[$indStueck]; 
        $PreisPaar=@$arrPr[$indPaar];
        if ((isset($PreisStueck) && strlen(trim($PreisStueck))!=0)) $einzelDa=true; else $einzelDa=false;
        if ((isset($PreisPaar) && strlen(trim($PreisPaar))!=0)) $paarDa=true; else $paarDa=false;
        //$html.="name $name PreisStueck $PreisStueck PreisPaar $PreisPaar<br>";
        $html.=$c->tr();
        if ($einzelDa ||$paarDa)           
        {
          $html.=$c->td(array('class'=>'tl_preisliste tl_preislisteName '),"$name:");
          if ($einzelDa) {
            $cl="tl_preisliste tl_preislisteStueckPr";
            if ($paarDa) $cl.=" tl_preislisteEinzelBefore";
            $html.=$c->td(array('class'=>$cl),"$PreisStueck €");
          }
          if ($paarDa) {
            $cl="tl_preisliste tl_preislistePaarPr";
            if ($einzelDa) $cl.=" tl_preislistePaarBefore";
            $html.=$c->td(array('class'=>$cl),"$PreisPaar €");
          }
//          $html.='<br>';
        }
        $html.=$c->end_tr();
      }
      $html.=$c->end_tbody();
      $html.=$c->end_table();
      $html.=$c->end_div(); 
      return $html;
  }
  /* 
   * erzeugt ein Array eines Schmuckartikels aus dem artikel mit dem aliasnamen
   * Alias alias name
   * Return
   *  $arr['data']=$dataArr;
   *     $dataArr['schmuckartikelname']=schmuckartikelname;
   *     $dataArr['text']=text;
   *     $dataArr['singleSRC']=utf8_encode($schmuckartikel->singleSRC); wohl irrelevant
   *     $dataArr['imgPath']
   *     $dataArr['artikelzusatz']
   *     $dataArr['preisliste']=utf8_encode($schmuckartikel->preisliste); namen der in der Preisliste vorhanden
   *     $dataArr['customTpl']=utf8_encode($schmuckartikel->customTpl); für popup
   *     $dataArr['zusatzinfo']=utf8_encode($schmuckartikel->zusatzinfo); für popup
   *  $arr['error']=$errArr;
   *  $arr['debug']=$debArr;
   */
  public function getSchmuckartikelFromAlias (string $alias): array
  {
      $resArr['data']=[];
      $resArr['error']=[];
      $resArr['debug']=[];
      $dataArr=[];
      $errArr=[];
      $debArr=[];
      $schmuckartikel = SchmuckartikelModel::findOneBy('schmuckartikelname', $alias);

      if ($schmuckartikel === null) {
          // Wenn kein Artikel gefunden wurde, gib Error zurück
        $errArr[]='Artikel Alias: "'.$alias.'" nicht gefunden';
      } else {
        $dataArr['schmuckartikelname']=$schmuckartikel->schmuckartikelname;
        //$text = preg_replace('/^\x{EF}\x{BB}\x{BF}/u', '', $schmuckartikel->text);
        $dataArr['text']=$schmuckartikel->text;
        $dataArr['singleSRC']=utf8_encode($schmuckartikel->singleSRC);
        // Suche das FileModel anhand der UUID
        $fileModel = FilesModel::findByUuid($schmuckartikel->singleSRC);
        if ($fileModel === null) {
            // Wenn die UUID nicht gefunden wurde, gib eine Fehlermeldung aus 
          $errArr[]='uuid von singleSRC nicht im FileModel';
          $dataArr['imgPath']="";
        } else {
          $dataArr['imgPath']=$fileModel->path;
        }
        //$dataArr['artikelzusatz']=utf8_encode($schmuckartikel->artikelzusatz);
        //if (isset($schmuckartikel->artikelzusatz))$dataArr['artikelzusatz']=preg_replace('/^\x{EF}\x{BB}\x{BF}/u', '', $schmuckartikel->artikelzusatz);
        if (isset($schmuckartikel->artikelzusatz))$dataArr['artikelzusatz']=$schmuckartikel->artikelzusatz;
        else $dataArr['artikelzusatz']='';
        $dataArr['preisliste']=$schmuckartikel->preisliste;
        $dataArr['customTpl']=$schmuckartikel->customTpl;
        $dataArr['zusatzinfo']=$schmuckartikel->zusatzinfo;
      }
      $arr['data']=$dataArr;
      $arr['error']=$errArr;
      $arr['debug']=$debArr;
      return $arr;
  }

}
 
 