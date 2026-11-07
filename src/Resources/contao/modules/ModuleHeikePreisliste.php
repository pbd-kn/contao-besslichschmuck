<?php
/*
 * Auflistung der Schmuckelemente nach der Preisliste
 * Vorgabe der Preiskategorie
 * Vorlage aus \vendor\contao\listing-bundle\src\Resources\contao\modules
 */
namespace Pbdkn\ContaoBesslichschmuck\Resources\contao\modules;

use Contao\System;
use Contao\Database;
use Contao\Pagination;
use Contao\StringUtil;
use Contao\BackendTemplate;
use Contao\Config;


use Contao\Module;
use Contao\ModuleModel;
use Contao\Input;
//use Contao\CoreBundle\Framework\ContaoFramework;
//use Doctrine\DBAL\Connection;
//use Symfony\Contracts\Translation\TranslatorInterface;
//use Contao\InsertTagParser;
use Pbdkn\ContaoBesslichschmuck\Util\CgiUtil;
use Pbdkn\ContaoBesslichschmuck\Util\BesslichUtil;

class ModuleHeikePreisliste extends Module
{
	/**
	 * Primary key
	 * @var string
	 */
	protected $strPk = 'id';

    // Standard-Template
    protected $strTemplate = 'list_heike_preisliste';

    private $cgiUtil;
    private $besslichUtil;
    private $heike_preislisteKategorie='23';
    private $perPage=10;
    private $list_fields='Kategorie,Subkategorie,Artikel';    // Felder die aus der Preisliste gelesen werden
    private $list_search='Artikel, Kategorie, Subkategorie'; // Felder die als Suche verwendet werden können



    // Der Konstruktor
    public function __construct(
      ModuleModel $moduleModel,                   // Contao\ModuleModel muss als erstes Argument kommen
    ) {
        parent::__construct($moduleModel); // ModuleModel wird von Contao automatisch gesetzt
    }

    /**
     * Modul initialisieren
     */
    public function generate()
    {
    	$request = System::getContainer()->get('request_stack')->getCurrentRequest();

		if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request))
		{   // ausgabe im Backend
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . $GLOBALS['TL_LANG']['FMD']['listing'][0] . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = StringUtil::specialcharsUrl(System::getContainer()->get('router')->generate('contao_backend', array('do'=>'themes', 'table'=>'tl_module', 'act'=>'edit', 'id'=>$this->id)));

			return $objTemplate->parse();
		}

        $container = System::getContainer();
        $this->besslichUtil=$besslichUtil = $container->get('Pbdkn.ContaoBesslichschmuck.util.besslich_util');


        // Prüfe, ob im Backend ein Template für die Standarddarstellung ausgewählt wurde
        if ($this->heike_preislisteDisplayMode === 'default' && $this->heike_preislisteTpl != '') {
            $this->strTemplate = $this->heike_preislisteTpl;
        }

        // Wenn "Info" als Darstellungsart gewählt wurde, wähle das Template für die Info-Darstellung
        if ($this->heike_preislisteDisplayMode === 'info' && $this->heike_preislisteInfoTpl != '') {
            $this->strTemplate = $this->heike_preislisteInfoTpl;
        }

        return parent::generate();
    }

    /**
     * Template ausgeben
     */
    protected function compile()
    {
        // Lädt die Sprachdatei für 'tl_module'
        System::loadLanguageFile('tl_module');

        // Anzahl der Elemente pro Seite (aus dem Backend-Feld), 0 bedeutet alle
        $errArray=[];              // errors aufsammeln

		// Add the search menu


		$strOptions = '';
		$strSearch = Input::get('search');
		$strFor = Input::get('for');
		$strpreiskategorie = Input::get('per_kategorie') ?: $this->heike_preislisteKategorie;
		$arrFields = StringUtil::trimsplit(',', $this->list_fields);
		$arrSearchFields = StringUtil::trimsplit(',', $this->list_search);

		$this->Template->searchable = false;
		if (!empty($arrSearchFields) && \is_array($arrSearchFields))
		{
			$this->Template->searchable = true;
			if ($strSearch && !\in_array($strSearch, $arrSearchFields, true))
			{
				$strSearch = '';
				$strFor = '';
			}
			foreach ($arrSearchFields as $field)
			{
				$strOptions .= '  <option value="' . $field . '"' . ($field == $strSearch ? ' selected="selected"' : '') . '>' . $field . '</option>' . "\n";
			}
		}
		$this->Template->search_fields = $strOptions;     // fuer select im template

        $filters = [];
        if ($strSearch && $strFor) { $filters[$strSearch] = '%' . $strFor . '%'; }
        $preisListenArray = $this->besslichUtil->findPrices($filters);
        $total = count($preisListenArray);
        $id = 'page_l' . $this->id;
        $page = max(1, (int) (Input::get($id) ?? 1));
        $per_page = (int) Input::get('per_page') ?: $this->perPage;
        if ($per_page < -1) { $per_page = $this->perPage; }
        if ($per_page > 0 && $page > max(ceil($total / $per_page), 1)) {
            throw new \Contao\CoreBundle\Exception\PageNotFoundException('Page not found');
        }
        $order_by = Input::get('order_by');
        if (!in_array($order_by, $arrFields, true)) { $order_by = ''; }
        $sort = Input::get('sort');
        if (!in_array($sort, ['asc','desc'], true)) { $sort = 'asc'; }
        if ($order_by) {
            usort($preisListenArray, static function (array $a, array $b) use ($order_by, $sort): int {
                $comparison = strcasecmp((string)$a[$order_by], (string)$b[$order_by]);
                return ($sort === 'desc' ? -$comparison : $comparison) ?: ($a['id'] <=> $b['id']);
            });
        }
        if ($per_page > 0) { $preisListenArray = array_slice($preisListenArray, ($page - 1) * $per_page, $per_page); }
        // erzeuge BodyAray für Template
        $bodyArray=[];
        foreach ($preisListenArray as $k=>$row){
          $arr=[];
          $arr['Artikel']=$row['Artikel'];                  
          $artikelname=$arr['Artikel'];
          $arr['Kategorie']=$row['Kategorie'];                  
          $arr['Subkategorie']=$row['Subkategorie']; 
           
          // lese schmuckartikel
          $arrSchmuckartikel=$this->besslichUtil->getSchmuckartikelFromAlias($artikelname);
          $dataArr=$arrSchmuckartikel['data'];
          $errArr=$arrSchmuckartikel['error'];
          // auf Fehler prüfen
          if (count($errArr)>0) {  
            foreach($errArr as $v) $errArray[]=$v;
            continue;
          }   
          if (count($dataArr)==0){ 
            $errArray[]='Kein Alias zu Artikel '.$artikelname;continue;
          }
          if (isset($dataArr['imgPath'])) $arr['imgstr']=$dataArr['imgPath'];
          else { $errArray[]='Kein Bild zu '.$artikelname; continue; }
          if (isset($dataArr['text'])) {
            $arr['text']=$dataArr['text'];
          } else { 
            $errArray[]='Kein Artikeltext zu '.$artikelname;continue;
          }
          /*
          if (isset($dataArr['artikelzusatz']))$arr['artikelzusatz']=$dataArr['artikelzusatz'];
          else $arr['artikelzusatz']="";

        
          if (isset($dataArr['preisliste'])) { 
            $zusatzArtikel=$dataArr['preisliste'];
            $arrZus =  deserialize($zusatzArtikel, true);  // preisliste ist die eingabe von mehreren Namen fuer die die Preise angezeigt werden soll
          } else $arrZus=[];
          $artikelNmListe=[];              // artikelnamen für die die Preisliste angezeigt werden soll
          $artikelNmListe[]=$artikelname;
          foreach ($arrZus as $nm) $artikelNmListe[]=$nm;
          $arr['PreislisteRend']=$this->besslichUtil->createPreislisteRender ($strpreiskategorie, $artikelNmListe);
*/
          $arr['PreislisteRend']='<strong>preise</strong><br>'.$this->besslichUtil->createFullPreislisteRender('23',$artikelname,$dataArr['preisliste']);
          if (isset($dataArr['zusatzinfo']))$arr['zusatzinfo']=$dataArr['zusatzinfo'];
          else $arr['zusatzinfo']="";
          if (isset($dataArr['customTpl']))$arr['customTpl']=$dataArr['customTpl'];
          else $arr['customTpl']="";
          $bodyArray[]=$arr;
        }

        // Daten an das Template übergeben 
        if ($this->headline && $this->hl ) {
          $this->Template->headline=$this->headline;
          $this->Template->hl=$this->hl;
        }
        $this->Template->per_kategorie=$strpreiskategorie;
        $this->Template->tbody=$bodyArray;
        $this->Template->errArr=$errArray;
        		// Pagination
		$objPagination = new Pagination($total, $per_page, Config::get('maxPaginationLinks'), $id);
		$this->Template->pagination = $objPagination->generate("\n  ");
		$this->Template->per_page = $per_page;
		$this->Template->total = $total;

		// Template variables
		$this->Template->search_label = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['search']);
		$this->Template->per_page_label = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['list_perPage']);
		$this->Template->fields_label = $GLOBALS['TL_LANG']['MSC']['all_fields'][0];
		$this->Template->keywords_label = $GLOBALS['TL_LANG']['MSC']['keywords'];
        $this->Template->preisKategorie_label = $GLOBALS['TL_LANG']['tl_module']['heike_preislisteKategorie'][0];
        //$this->Template->preisKategorie_label = "hallo";
        $this->Template->preiskategorie=$strpreiskategorie;
		$this->Template->search = $strSearch;
		$this->Template->for = $strFor;
		$this->Template->order_by = $order_by;
		$this->Template->sort = $sort;
		$this->Template->no_results = sprintf($GLOBALS['TL_LANG']['MSC']['sNoResult'], $strFor);

        //$this->Template->prices = $objPrices->fetchAllAssoc();
    }
}
