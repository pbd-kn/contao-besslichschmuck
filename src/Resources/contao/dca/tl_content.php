<?php

// dieses php !!! muss !!! in der 1. Zeile stehen
// am ende darf kein schliessendes php stehen (warum auch immer)
// dca/tl_content.php
use Markocupic\GalleryCreatorBundle\Model\GalleryCreatorAlbumsModel;
use Contao\Message;
use Contao\System;
use Pbdkn\ContaoBesslichschmuck\Util\BesslichUtil;

/**
 * Table tl_content
 */
$strName = 'tl_content';
 
 
/* Palettes 
   Doku siehe https://docs.contao.org/books/api/dca/palettes.html
   liefert die Darstellung im BE die einzelen Teile werden durch ; getrennt
   danach folgen die Spaltenbezeichnungen aus der Tabelle hier tl_content
   z.B. type und headline sind in der Tabelle tl_content schon enthalten
   artikelbeschreibung, preisartikel_endpreis, 'preisartikel_vkpreis werden hier definiert
*/
$GLOBALS['TL_CONFIG']['displayErrors'] = true;
$GLOBALS['TL_DCA'][$strName]['palettes']['schmuckartikel'] = '{mytype_legend:hide},type;{schmuckartikel_legend},schmuckartikelname,singleSRC;{beschreibung_legend},text,artikelzusatz,preisliste;{invisible_legend:hide},invisible,start,stop;{template_legend:hide},customTpl,zusatzinfo;';
//$GLOBALS['TL_DCA'][$strName]['palettes']['__selector__'][] = 'myaddImage';  // Selector für checkbox hinzufügen

// wird mit der Checkbox dargestellt als subpalette vom selector  myaddImage
//$GLOBALS['TL_DCA'][$strName]['subpalettes']['myaddImage'] = 'singleSRC';

  
//$GLOBALS['TL_DCA'][$strName]['fields']['type']['default'] = 'Schmuckartikel';

/* Fields */


$GLOBALS['TL_DCA'][$strName]['fields']['schmuckartikelname'] = array
(
			'label'                   => &$GLOBALS['TL_LANG'][$strName]['schmuckartikelname'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'schmuckartikel',
            'eval'                    => array('submitOnChange'=>true, 'maxlength'=>32, 'tl_class'=>'long','mandatory'=>true),
            'save_callback' => array(array('checkSchmuck', 'checkSchmuckartikel')),
            'load_callback' => array(array('checkSchmuck', 'loadSchmuckartikel')),    
			'sql'                     => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA'][$strName]['fields']['singleSRC'] = array
        (
            'label'                   => " ",
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => array('filesOnly'=>true, 'fieldType'=>'radio', 'tl_class'=>'clr', 'extensions' => 'jpg,jpeg,png,gif,bmp,svg'),
            'sql'                     => "binary(16) NULL",

        );


$GLOBALS['TL_DCA'][$strName]['fields']['artikelzusatz'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$strName]['artikelzusatz'],
    'exclude'                 => true,
    'inputType'               => 'textarea',
    'eval'                    => array('rte' => 'tinyMCE', 'helpwizard'=>true),
    'explanation'             => 'insertTags',
		'sql'                     => "mediumtext NULL"
);

 
$GLOBALS['TL_DCA'][$strName]['fields']['preisliste'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][$strName]['preisliste'],
    'exclude'                 => true,
    'inputType'               => 'listWizard',
    'eval'                    => array(   'maxlength'=>32),
    'save_callback' => array(array('checkSchmuck', 'checkPreise')),
    'sql'                     => "mediumtext NULL"
);
$GLOBALS['TL_DCA'][$strName]['fields']['customTpl'] = array
(
			'label'                   => &$GLOBALS['TL_LANG'][$strName]['customTpl'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('article_template_listing', 'getArticleTemplates'),
			'eval'                    => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
			'sql'                     => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA'][$strName]['fields']['zusatzinfo'] = array
(
	'label'                   => &$GLOBALS['TL_LANG'][$strName]['zusatzinfo'],
	'exclude'                 => true,
	'search'                  => true,
//	'inputType'               => 'text',
			'inputType'               => 'select',
      'options_callback'           => array('checkSchmuck', 'getzusatzlist'),
			'selectKey'              => 'alias,title',
      'select_where' => 'alias like \'%popup%\'',
      'eval'                    => array(  'decodeEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50 wizard'),
	'sql'                     => "varchar(255) NOT NULL default ''"
);
/*
$GLOBALS['TL_DCA'][$strName]['fields']['zusatzinfo'] = array
(
  'label' => &$GLOBALS['TL_LANG'][$strName]['zusatzinfo'],
  'inputType' => 'efgLookupSelect',
  'formfieldType' => 'efgLookupSelect',
  'exclude' => false,
  'search' => true,
  'sorting' => true,
  'filter' => false,
  'efgLookupOptions'  => array (
    'lookup_field' => 'tl_article.alias',
    'lookup_sort' => '',
    'lookup_val_field' => 'tl_article.alias',
    'lookup_where' => 'alias like \'%popup%\''
  ),
  'eval' => array (
    'mandatory' => true,
    'chosen' => true
  )
//  'ff_id'] = 68;
//  'f_id'] = 9;
);
*/
/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class article_template_listing extends Backend
{

	/**
	 * Get all tables and return them as array
	 *
	 * @return array
	 */
	public function getAllTables()
	{
		return $this->Database->listTables();
	}


	/**
	 * Return all list templates as array
	 *
	 * @return array
	 */
	public function getArticleTemplates()
	{
		return $this->getTemplateGroup('ce_bess');
	}


	/**
	 * Return all info templates as array
	 *
	 * @return array
	 */
	public function getInfoTemplates()
	{
		return $this->getTemplateGroup('info_');
	}
}

class checkSchmuck extends Backend {

  public  $schmuckartikelname = '';  
  public  $uuid = '';  
  public static $schmuckpath = '';  
  // prueft ob value in artikelliste und in Gallery Creator
  public function loadSchmuckartikel ($varValue, DataContainer $dc) {

    $strName = 'tl_content';
    // dca element singleSRC aus test dynamisch anpassen
    $GLOBALS['TL_DCA'][$strName]['fields']['singleSRC']['inputType'] = 'comment';   // Ändere Dynamisch den InputType, damit kein Bild ausgewählt werden kann
    $GLOBALS['TL_DCA'][$strName]['fields']['singleSRC']['save_callback'] = array(array('checkSchmuck', 'setsingleSRC'));
    $GLOBALS['TL_DCA'][$strName]['fields']['singleSRC']['load_callback'] = array(array('checkSchmuck', 'loadsingleSRC'));

    if ($varValue) {
      \System::log("PBD Besslich loadSchmuckartikel Value $varValue", __METHOD__, TL_GENERAL);
    } else {
      \System::log("PBD Besslich loadSchmuckartikel no Value", __METHOD__, TL_GENERAL);
    }
    $gch=new Pbdkn\ContaoBesslichschmuck\Resources\contao\classes\GC_Helper();
    $objPicElement=$gch->getPicture($varValue);
    if ($objPicElement === null){
        //throw new Exception(sprintf($GLOBALS['TL_LANG'][$strName]['no_image'], $varValue));  
        $varValue="";     
    } else {
      // schmuckartikelname merken
      $this->schmuckartikelname=$varValue;
      if ($varValue=="") {
        $this->uuid='';
      }
      //\System::log("PBD Besslich loadSchmuckartikel pic " . $objPicElement->path, __METHOD__, TL_GENERAL);
    }
    return $varValue;
  }
  
  /*
   * Ueberpruefung des eingegebenen Names ob ein Bild und ein Preis vorhanden ist
   * Die uuid des Bildes wird in Public uuid gespeichert.
   */
  public function checkSchmuckartikel ($varValue, DataContainer $dc) {
    $strName = 'tl_content';
    $retVal=$varValue;
    $id = $dc->id;
    // prüfen ob Bild im Galery Creator vorhanden
//\System::log("PBD Besslich checkSchmuckartikel Value $varValue", __METHOD__, TL_GENERAL);

    $gch=new Pbdkn\ContaoBesslichschmuck\Resources\contao\classes\GC_Helper();
    $objPicElement=$gch->getPicture($varValue);
    if ($objPicElement === null) { 
      $retVal="";     // keinen wert speichern
      return $retval;
    } 
    
    if(!empty($objPicElement['uuid'])){   // uuid des namens der Bilddatei merken wird dann beim save in singleSRC uebernommen
        $this->uuid=$objPicElement['uuid'];
    } else {
        throw new Exception(sprintf($GLOBALS['TL_LANG'][$strName]['invalid_uuid'], $varValue));
    }
    // Prüfen ob in der Preisliste vorhanden    
    $res=$this->checkPreis ($varValue);
    return $retVal;
  }

  /*
   * Ueberprueft ob zu den eingegebenen Zusatz Artikeln Preise existieren
   * wird als save_callback in preisliste gerufen
   */
   
  public function checkPreise ($varValue, DataContainer $dc) {  
    $pr = deserialize($varValue, true);           // alle Eintraege dews listwizards
    if (count($pr) > 0) {
      foreach ($pr as $a) {
          $res=$this->checkPreis ($a);   // evtl. exception.
      }
    }
    return $varValue; 
  }
  public function getPreisFromPreisliste($varValue) 
  {
  
  // eigentlich muesste man das ueber die utils holen und nicht ueber einen route aufruf
  // einführen des construktors wie bei class schmuckartikel
  // return $this->besslichUtil->getPreis($varValue);

    $strName = 'tl_content';   // fuer text aus language  
    $relativeUrl = '/besslich/getPreisliste?schmuckartikel='.$varValue;

    // Dynamische Erstellung der vollständigen URL

    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $url = $scheme . '://' . $host . $relativeUrl;

    // Initialisieren Sie eine cURL-Session
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json'
     ));
     // request
     $response = curl_exec($ch);
     // Überprüfen Sie auf Fehler
     $error="";
     if(curl_errno($ch)) {
        $error.='Request Error:' . curl_error($ch);
     }
     // Ueberprüfen Sie den HTTP-Statuscode
     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
     if ($httpCode != 200) {
       $error.=('Request Error: HTTP Statuscode ' . $httpCode." url $url");
     }
     
     // Schließen cURL-Session
     curl_close($ch);
     //throw new Exception("url: $url hallo anton ");
     if ($error !="") throw new Exception(sprintf($GLOBALS['TL_LANG'][$strName]['no_artikellist'], 'preisliste')." $error url: $url");
//throw new Exception("url $url");
     // JSON-Antwort dekodieren
     $data = json_decode($response, true);
     return $data['data'];  
  }

  public function checkPreis ($varValue) {  
      // Prüfen ob in der Preisliste vorhanden  
    $strName = 'tl_content';   // fuer text aus language 
    if (isset($varValue) && strlen(trim($varValue)) !=0 ) {
      $resArr=$this->getPreisFromPreisliste($varValue);
      if (count($resArr)==0) {
        throw new Exception(sprintf($GLOBALS['TL_LANG'][$strName]['no_artikelexist'], $varValue)." anton");
      }
     }
     return "";    
  }
  
  /* 
   * setzt die UUId zu singleSRC
   * und waehlt demit das Bild als Voreinstellung aus
   */
   public function setsingleSRC ($varValue, DataContainer $dc) {
    //$id = $dc->id;  
    //$text = $dc->text; 
    if ($this->uuid != "") { 
      //$schmuckartikelname = $this->schmuckartikelname;
      //$uu= \StringUtil::binToUuid($this->uuid);
      //\System::log("PBD Besslich setsingleSRC varValue $varValue  schmuckartikelname $schmuckartikelname uu $uu", __METHOD__, TL_GENERAL);
      return $this->uuid;
    } else {
      //\System::log("PBD Besslich no uuid setsingleSRC varValue $varValue  schmuckartikelname $schmuckartikelname", __METHOD__, TL_GENERAL);
     return $varValue;
    }
   }
  public function loadsingleSRC ($varValue, DataContainer $dc) {
    //$strName = 'tl_content';
    //\System::log("PBD Besslich loadsingleSRC varValue", __METHOD__, TL_GENERAL);
/*
    $image = new \stdClass();
    \Controller::addImageToTemplate($image, [
        'singleSRC' => $varValue,
        'size' => [100, 100, 'crop']
    ]);
    //$this->insert('image', (array) $image); 
*/

     return $varValue;
   }
   
   
  public function getzusatzlist ( DataContainer $dc) { 
    $strName = 'tl_content';
    $where=$GLOBALS['TL_DCA'][$strName]['fields']['zusatzinfo']['select_where'];
    if (!empty($where)) {
      $where = 'WHERE ' . $where;
    } else {
      $where="";
    }
    $select=$GLOBALS['TL_DCA'][$strName]['fields']['zusatzinfo']['selectKey'];

    $values = array("");
    $objArticle = $this->Database->prepare("SELECT $select FROM tl_article $where ORDER BY title")->execute();
    while($objArticle ->next()){
//\System::log("PBD Besslich getzusatzlist add alias " . $objArticle->alias, __METHOD__, TL_GENERAL);
      $values[$objArticle->alias] = $objArticle->alias;     //[Rueckgabewert] angezeigter wert
    }
    return $values; 
  } 
}

/**
 * Class schmuckartikel (Inputtype)
 */

class schmuckartikel extends Contao\TextField
{
    /**
     * @var bool
     */
    protected $blnSubmitInput = true;

    /**
     * @var string
     */
    protected $strTemplate = 'be_widget';
     /**
     * @return string
     */
    private BesslichUtil $besslichUtil; 

    public function __construct($arrAttributes = null)
    {
        parent::__construct($arrAttributes);
        $this->besslichUtil = System::getContainer()->get(BesslichUtil::class);
    }     
    public function generate()
    {
      $txt="";
      if ($this->varValue != "") {              // aus Contao\Textfield  
        // validiere den Weet
        $albumname="Kein Album vorhanden";
        $imgpath="Kein Image Pfad vorhanden";
        $txt .= "<div><table style='border-collapse: collapse'>";
        $varValue =  $this->varValue;
System::log("PBD besslich tl_content.php generate $varValue", __METHOD__, TL_GENERAL); 

        $gch=new Pbdkn\ContaoBesslichschmuck\Resources\contao\classes\GC_Helper();
        $objPicElement=$gch->getPicture($varValue);
        if ($objPicElement === null){
System::log("PBD besslich tl_content.php generate picelement NULL", __METHOD__, TL_GENERAL); 
          $varValue="";
        } else {
          $imgpath = $objPicElement['path'];
          $objAlbumlement = GalleryCreatorAlbumsModel::findOneBy('id',$objPicElement['pid']);
          if ($objAlbumlement !== null) {
           $albumname =   $objAlbumlement->name;
          }
        } 

             // Prüfen ob in der Preisliste vorhanden und werte liefern
        $preisArr=$this->besslichUtil->getPreis($varValue);
/*
        $err_result = ""; 
        $rowPrice = ""; 
        $this->import('Database'); 
        $objForm = $this->Database->prepare( "SELECT alias,efgAliasField FROM tl_form where alias='artikelliste'")->execute();
        if($objForm->numRows == 0 ) {
           $err_result = sprintf($GLOBALS['TL_LANG'][$strName]['no_artikellist'], 'artikelliste') . "<br>";       
        } else {
          $tablealias = $objForm->alias;
          $tableEfgAliasField = $objForm->efgAliasField;
          $rowPrice = @$this->Database->execute( "SELECT * FROM tl_formdata_details where ff_name='$tableEfgAliasField' AND value='$varValue'");
          if( $rowPrice->numRows == 0 ) {
            //$txt .= sprintf($GLOBALS['TL_LANG'][$strName]['no_artikelexist'], $varValue) . "<br>";
          } else {
            $err_result = "<strong>$varValue</strong> in Preisliste vorhanden<br>"; 
          }
        }
      }
*/
      // Aufbau des Ergebnisses
      // schmuckartikel
      $txt = parent::generate();
      if ($this->varValue != "" && $objPicElement !== null) {
        $tdstyle= "style='border: 1px solid #dddddd;text-align:left;padding:2px;'";
        // Bild ausgeben
        $txt .= "<table>";
        $txt .= "<tr><td colspan='2' $tdstyle><img class='gimage' src='$imgpath' width='80' height='60' ></td></tr>";
        $txt .= "<tr><td $tdstyle>Album</td><td $tdstyle>$albumname</td></tr>";
        $txt .= "<tr><td $tdstyle>Pfad</td><td $tdstyle>$imgpath</td></tr>";
        $l=count($preisArr);
        if ($l == 0) {
          $txt .= "<tr><td $tdstyle>Artikelbeschreibung</td><td $tdstyle>nicht vorhanden</td></tr>";
        } else {
          $pid = $preisArr['pid'];
          $txt .= "<tr><td $tdstyle>Kategorie</td><td $tdstyle>" . $preisArr['Kategorie'] . "</td></tr>";
          $txt .= "<tr><td $tdstyle>Subkategorie</td><td $tdstyle>" . $preisArr['Subkategorie'] . "</td></tr>";
          $txt .= "<tr><td $tdstyle>Beschreibung</td><td $tdstyle>" . $preisArr['Beschreibung'] . "</td></tr>";
          $txt .= "<tr><td $tdstyle>Stückpreis(2.5)</td><td $tdstyle>" . $preisArr['PreisStueck2_5'] . "</td></tr>";
          $txt .= "<tr><td $tdstyle>Paarpreis(2.5)</td><td $tdstyle>" . $preisArr['PreisPaar2_5'] . "</td></tr>";
          $txt .= "<tr><td $tdstyle>Stückpreis2.3</td><td $tdstyle>" . $preisArr['PreisStueck2_3'] . "</td></tr>";
          $txt .= "<tr><td $tdstyle>Paarpreis2.3</td><td $tdstyle>" . $preisArr['PreisPaar2_3'] . "</td></tr>";
        }
         
        $txt .= "</table>";
      }
      return  $txt;

    }
  }

}
/**
 * Class noinput (Inputtype)
 * Die Klasse dient dazu ein leeres EingabeFeld
 */

class comment extends Contao\FileTree  
{
    /**
     * @var bool
     */
    protected $blnSubmitInput = true;

    /**
     * @var string
     */
    protected $strTemplate = 'be_widget';
     /**
     * @return string
     */
    public function generate()
    {
       $txt = "";
       $arrData = $GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['inputType'];
//System::log("PBD besslich tl_content.php 362 arrData $arrData", __METHOD__, TL_GENERAL); 
       if ($arrData == 'comment') {
         // Kommentar ausgeben
         $displaytext = @$GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['displaytext'];
         $displaylabel = @$GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['displaylabel'];
         if ($displaytext == "") $displaytext = " ";
         if ($displaylabel != "") {
           $txt .= "<fieldset><legend>$displaylabel</legend>$displaytext</fieldset>";
         } else {
           $txt .= "<div>$displaytext</div>";
         }
         return $txt;
       }
       return parent::generate();
       $myFiletree = new Contao\FileTree();
       return $myFiletree->generate();
       //return  "";

    }

}