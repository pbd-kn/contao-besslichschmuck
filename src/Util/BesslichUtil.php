<?php

declare(strict_types=1);


namespace Pbdkn\ContaoBesslichschmuck\Util;

use Doctrine\DBAL\Connection;


class BesslichUtil
{
    private Connection $connection;
    public function __construct( Connection $connection)
    {
            $this->connection = $connection;

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

}
 
 