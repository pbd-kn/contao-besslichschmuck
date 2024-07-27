<?php

declare(strict_types=1);

namespace Pbdkn\ContaoBesslichschmuck\Controller;

use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Database;
use Doctrine\DBAL\Connection;
use Contao\CoreBundle\Framework\Adapter;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Contao\FilesModel;
use Contao\StringUtil;
use Doctrine\DBAL\Driver\Exception as DoctrineDBALDriverException;
use Doctrine\DBAL\Exception as DoctrineDBALException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Pbdkn\ContaoBesslichschmuck\Util\BesslichUtil;
use Pbdkn\ContaoBesslichschmuck\Util\CgiUtil;


class BesslichAjaxController extends AbstractController
{
    private ContaoFramework $framework;
    private Connection $connection; 
    private InsertTagParser $insertTagParser; 
    private TranslatorInterface $translator;
    private CgiUtil $cgiUtil; 
    private BesslichUtil $besslichUtil; 
    // Adapters
    private Adapter $config;
    private Adapter $environment;
    private Adapter $input;
    private Adapter $stringUtil;    

    public function __construct(
      ContaoFramework $framework,
      Connection $connection,
      InsertTagParser $insertTagParser,
      TranslatorInterface $translator,
      CgiUtil $cgiUtil,
      BesslichUtil $besslichUtil)
    {
        $this->framework = $framework;
        $this->connection = $connection;
        $this->insertTagParser=$insertTagParser; 
        $this->translator=$translator;
        $this->cgiUtil=$cgiUtil;
        $this->besslichUtil=$besslichUtil;
        // Adapters
        $this->config = $this->framework->getAdapter(Config::class);
        $this->environment = $this->framework->getAdapter(Environment::class);
        $this->input = $this->framework->getAdapter(Input::class);
        $this->stringUtil = $this->framework->getAdapter(StringUtil::class);

    }
    /**
     * @Route("/besslich/getPreislistePath/{schmuckartikel}/{kategorie}/{subkategorie}", 
     * name="BesslichAjaxController::getPreislistePath",
     * defaults={"kategorie": "none","subkategorie":"none"})
     * @throws \Exception
     */
          
     /*
      * wertet die Angaben des get request aus
      * Parameter schmuckartikel,kategorie,subkategorie
      * der Datenbankaufruf wird mit like ausgeführt.
      * Beispiel: /besslich/getPreislistePath/ab1s führt zum like %ab1s%
      */

    public function getPreislistePath(string $schmuckartikel,string $kategorie, string $subkategorie ): JsonResponse
    {
      $debugArr[]=utf8_encode("debug von getPreisliste like (% $schmuckartikel  %)");
      $errArr=[];

      //$db = Database::getInstance();
//      $db->query("SET NAMES 'utf8mb4'");
      // Baue die SQL-Abfrage dynamisch auf
      $sql = "SELECT * FROM tl_heike_preisliste WHERE Artikel LIKE ?";
      $params = ['%' . $schmuckartikel . '%'];

      if ($kategorie !== 'none') {
            $sql .= " AND Kategorie LIKE ?";
            $params[] = '%' . $kategorie . '%';
      }

      if ($subkategorie !== 'none') {
            $sql .= " AND Subkategorie ?";
            $params[] = '%' . $subkategorie . '%';
      }
       $resarr=$this->besslichUtil->getSchmuckArtikel($sql,$params);
       $arr['data']=$resarr;
       $arr['error']=$errArr;
       $arr['debug']=$debugArr;
       return new JsonResponse($arr);
             
    }
    
    
    /**
     * @Route("/besslich/getPreisliste", 
     * name="BesslichAjaxController::getPreisliste")
     * @throws \Exception
     */
     
     /*
      * wertet die Angaben des get request aus
      * Parameter schmuckartikel,kategorie,subkategorie
      * der Datenbankaufruf wird mit like ausgeführt.
      * Beispiel: /besslich/getPreisliste&schmuckartikel=ab1s
      * /besslich/getPreisliste&schmuckartikel=%25ab1s%25  führt zum like %ab1s%
      * /besslich/getPreisliste?schmuckartikel=&subkategorie=   liefert alle bei denen Schmuckartikel und subkategorie leer ist
      */
    public function getPreisliste(Request $request)
    {
      $debugArr[]=utf8_encode("debug von getPreisliste");
      $debugArr[]=utf8_encode("Help: besslich/getPreisliste?schmuckartikel=artikel&kategorie=kategorie&subkategorie=subkategorie");
      $debugArr[]=utf8_encode("Help: die einzelnen Attribute werden mit like gesucht. Keine Angabe führt zur Ausgabe aller");
      $debugArr[]=utf8_encode("Help: %25name%25. Keine Angabe führt zuzum likde %name%");
      $errArr=[];
        // Erhalte die Datenbankverbindung
        
        // Hol die optionalen Parameter aus der Anfrage
        $name = $request->query->get('schmuckartikel', 'none');
        $kategorie = $request->query->get('kategorie', 'none');
        $subkategorie = $request->query->get('subkategorie', 'none');
        $debugArr[]=utf8_encode("name $name kategorie $kategorie subkategorie $subkategorie");

        // Baue die SQL-Abfrage dynamisch auf
        $sql = "SELECT * FROM tl_heike_preisliste WHERE 1=1";
        $params = [];
        if ($name === '') {
            $sql .= " AND (Artikel IS NULL OR Artikel = '')";
        } else if ($name !== 'none') {
            $sql .= " AND Artikel LIKE ?";
            $params[] = $name;
        } 
        if ($subkategorie === '') {
            $sql .= " AND (Subkategorie IS NULL OR Subkategorie = '')";
        } else if ($subkategorie !== 'none') {
            $sql .= " AND Subkategorie LIKE ?";
            $params[] = $subkategorie;
        }
        if ($kategorie === '') {
            $sql .= " AND (Kategorie IS NULL OR Kategorie = '')";
        } else if ($kategorie !== 'none') {
            $sql .= " AND Kategorie LIKE ?";
            $params[] = $kategorie;
        }
        $debugArr[]=utf8_encode("debug von getPreisliste sql $sql");
       // Führe die Abfrage aus
       $resarr=$this->besslichUtil->getSchmuckArtikel($sql,$params);
       $arr['data']=$resarr;
       $arr['error']=$errArr;
       $arr['debug']=$debugArr;
       return new JsonResponse($arr);
   }
}
