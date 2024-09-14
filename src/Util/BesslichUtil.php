<?php

declare(strict_types=1);


namespace Pbdkn\ContaoBesslichschmuck\Util;

use Doctrine\DBAL\Connection;
use Pbdkn\ContaoBesslichschmuck\Util\CgiUtil;


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
  public function getSchmuckArtikel( string $sql, array $params, array $ignoreFields=[]): array
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
      //$html.="indStueck $indStueck indPaar $indPaar<br>";
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

}
 
 