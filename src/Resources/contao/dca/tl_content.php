<?php

// dieses php !!! muss !!! in der 1. Zeile stehen
// am ende darf kein schliessendes php stehen (warum auch immer)
// dca/tl_content.php
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
            'eval'                    => array('filesOnly'=>true, 'fieldType'=>'radio', 'tl_class'=>'clr', 'extensions'=>$GLOBALS['TL_CONFIG']['validImageTypes']),
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
  // prüft ob value in artikelliste und in Gallery Creator
  public function loadSchmuckartikel ($varValue, DataContainer $dc) {

    $strName = 'tl_content';
    // dca element singleSRC aus test dynamisch anpassen
    $GLOBALS['TL_DCA'][$strName]['fields']['singleSRC']['inputType'] = 'comment';   // Ändere Dynamisch den InputType, damit kein Bild ausgewählt werden kann
    //$GLOBALS['TL_DCA'][$strName]['fields']['singleSRC']['displaylabel'] = "Mein label";
    //$GLOBALS['TL_DCA'][$strName]['fields']['singleSRC']['displaytext'] = "Mein textle";
    $GLOBALS['TL_DCA'][$strName]['fields']['singleSRC']['save_callback'] = array(array('checkSchmuck', 'setsingleSRC'));
    $GLOBALS['TL_DCA'][$strName]['fields']['singleSRC']['load_callback'] = array(array('checkSchmuck', 'loadsingleSRC'));

    if ($varValue) {
      \System::log("PBD Besslich loadSchmuckartikel Value $varValue", __METHOD__, TL_GENERAL);
    } else {
      \System::log("PBD Besslich loadSchmuckartikel no Value", __METHOD__, TL_GENERAL);
    }
    $gch=new PBDKN\ContaoBesslichschmuck\Resources\contao\classes\GC_Helper();
    $objPicElement=$gch->getPicture($varValue);
/*    PBD
    $objPicElement = \GalleryCreatorPicturesModel::findOneBy(
      array('column' => "tl_gallery_creator_pictures.name like '$varValue.%'"),"" 
      );
*/
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
   * Ueberpruefung des einegegenen Names ob ein Bild und ein Preis vorhanden ist
   * Die uuid des Bildes wird in Public uuid gespeichert.
   */
  public function checkSchmuckartikel ($varValue, DataContainer $dc) {
    $strName = 'tl_content';
    $id = $dc->id;
    // prüfen ob Bild im Galery Creator vorhanden
\System::log("PBD Besslich checkSchmuckartikel Value $varValue", __METHOD__, TL_GENERAL);

    $gch=new PBDKN\ContaoBesslichschmuck\Resources\contao\classes\GC_Helper();
    $objPicElement=$gch->getPicture($varValue);
/*    
    $objPicElement = \GalleryCreatorPicturesModel::findOneBy(
      array('column' => "tl_gallery_creator_pictures.name like '$varValue.%'"),"" 
      );
*/      
    if ($objPicElement === null){
        //throw new Exception(sprintf($GLOBALS['TL_LANG'][$strName]['no_image'], $varValue));  
        $varValue="";     
    } 
    
    // Prüfen ob in der Preisliste vorhanden    
    $this->checkPreis ($varValue);
    /*
    $objForm = $this->Database->execute( "SELECT alias,efgAliasField FROM tl_form where alias='artikelliste'");
    if($objForm->numRows == 0 ) {
        throw new Exception(sprintf($GLOBALS['TL_LANG'][$strName]['no_artikellist'], 'artikelliste'));       
    }
    $tablealias = $objForm->alias;
    $tableEfgAliasField = $objForm->efgAliasField;

    $objPid = @$this->Database->execute( "SELECT pid FROM tl_formdata_details where ff_name='$tableEfgAliasField' AND value='$varValue'");
    if( $objPid->numRows == 0 ) {
        throw new Exception(sprintf($GLOBALS['TL_LANG'][$strName]['no_artikelexist'], $varValue));
    }
    //\System::log("PBD Besslich checkSchmuckartikel varValue $varValue id $id path $schmuckartikelname " . $objPicElement->path, __METHOD__, TL_GENERAL);
    */

//    $objFile = \FilesModel::findByPath($objPicElement->path);
//    $uuid="";
//    if ($objFile !== null) {
//      if ($objFile->uuid) {
      if(!isempty($objPicElement['uuid']){
        $this->uuid=$objPicElement['uuid'];
      } else {
        throw new Exception(sprintf($GLOBALS['TL_LANG'][$strName]['invalid_uuid'], $varValue));
      }
//    } else {
//      throw new Exception(sprintf($GLOBALS['TL_LANG'][$strName]['invalid_uuid'], $varValue));
//    }
    return $varValue;
  }

  /*
   * Ueberprueft ob zu den eingegebenen Zusatz Artikeln Preise existieren
   */
   
  public function checkPreise ($varValue, DataContainer $dc) {  
    $pr = deserialize($varValue, true);
    $l=  count($pr);
    //\System::log("PBD Besslich checkPreise checkSchmuckartikel gerufen count $l", __METHOD__, TL_GENERAL);

    if (count($pr) > 0) {
      foreach ($pr as $a) {
          $res=$this->checkPreis ($a);
      }
    }
    return $varValue; 
  }
  
  public function checkPreis ($varValue) {  
      // Prüfen ob in der Preisliste vorhanden  
    if ($varValue=="") return $varValue;
    $strName = 'tl_content';  
    $objForm = $this->Database->execute( "SELECT alias,efgAliasField FROM tl_form where alias='artikelliste'");
    if($objForm->numRows == 0 ) {
        throw new Exception(sprintf($GLOBALS['TL_LANG'][$strName]['no_artikellist'], 'artikelliste'));       
    }
    $tablealias = $objForm->alias;
    $tableEfgAliasField = $objForm->efgAliasField;

    $objPid = @$this->Database->execute( "SELECT pid FROM tl_formdata_details where ff_name='$tableEfgAliasField' AND value='$varValue'");
    if( $objPid->numRows == 0 ) {
     throw new Exception(sprintf($GLOBALS['TL_LANG'][$strName]['no_artikelexist'], $varValue));
    }
    return "";
  }
  
  /* 
   * setzt die UUId zu singleSRC
   * und waehlt demit das Bild als Voreinstellung aus
   */
   public function setsingleSRC ($varValue, DataContainer $dc) {
    $id = $dc->id;  
    $text = $dc->text; 
    if ($this->uuid != "") { 
      //$schmuckartikelname = $this->schmuckartikelname;
      //$uu= \StringUtil::binToUuid($this->uuid);
      //\System::log("PBD Besslich setsingleSRC varValue $varValue id $id schmuckartikelname $schmuckartikelname uu $uu", __METHOD__, TL_GENERAL);
      return $this->uuid;
    } else {
      //\System::log("PBD Besslich no uuid setsingleSRC varValue $varValue id $id schmuckartikelname $schmuckartikelname", __METHOD__, TL_GENERAL);
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
    public function generate()
    {
      if ($this->varValue != "") {
        $albumname="Kein Album vorhanden";
        $imgpath="Kein Image Pfad vorhanden";
        $txt .= "<div><table style='border-collapse: collapse'>";
        $varValue =  $this->varValue;
System::log("PBD besslich tl_content.php generate $varValue", __METHOD__, TL_GENERAL); 

        $gch=new PBDKN\ContaoBesslichschmuck\Resources\contao\classes\GC_Helper();
        $objPicElement=$gch->getPicture($varValue);
/*
        $objPicElement = \GalleryCreatorPicturesModel::findOneBy(
          array('column' => "tl_gallery_creator_pictures.name like '$varValue.%'"),"" 
        );
*/
        if ($objPicElement === null){
System::log("PBD besslich tl_content.php generate picelement NULL", __METHOD__, TL_GENERAL); 
          $varValue="";
        } else {
          $imgpath = $objPicElement['path'];
          $objAlbumlement = \GalleryCreatorAlbumsModel::findOneBy('id',$objPicElement['pid']);
          if ($objAlbumlement !== null) {
           $albumname =   $objAlbumlement->name;
          }
        } 
             // Prüfen ob in der Preisliste vorhanden 
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
      // Aufbau des Ergebnisses
      // schmuckartikel
      $txt = parent::generate();
/*
           '<input type="text" name="%s" id="ctrl_%s" value="%s">',
            $this->strName,
            $this->strId,
            $this->varValue

        );
*/
      if ($this->varValue != "" && $objPicElement !== null) {
        $tdstyle= "style='border: 1px solid #dddddd;text-align:left;padding:2px;'";
        // Bild ausgeben
        $txt .= "<table>";
        $txt .= "<tr><td colspan='2' $tdstyle><img class='gimage' src='$imgpath' width='80' height='60' ></td></tr>";
        $txt .= "<tr><td $tdstyle>Album</td><td $tdstyle>$albumname</td></tr>";
        $txt .= "<tr><td $tdstyle>Pfad</td><td $tdstyle>$imgpath</td></tr>";
        if ($err_result == "") {
          $txt .= "<tr><td $tdstyle>Artikelbeschreibung</td><td $tdstyle>nicht vorhanden</td></tr>";
        } else {
          $pid = $rowPrice->pid;
          //$txt .= "<tr><td $tdstyle>Id</td><td $tdstyle>" . $rowPrice->id . "</td></tr>";
          //$txt .= "<tr><td $tdstyle>pid</td><td $tdstyle>" . $rowPrice->pid . "</td></tr>";
System::log("PBD besslich tl_content.php generate query SELECT * FROM tl_formdata_details where ff_name='Kategorie' AND pid='$pid'", __METHOD__, TL_GENERAL); 
          $rowval = @$this->Database->execute( "SELECT * FROM tl_formdata_details where ff_name='Kategorie' AND pid='$pid'");
          $txt .= "<tr><td $tdstyle>Kategorie</td><td $tdstyle>" . $rowval->value . "</td></tr>";
          $rowval = @$this->Database->execute( "SELECT * FROM tl_formdata_details where ff_name='Subkategorie' AND pid='$pid'");
          $txt .= "<tr><td $tdstyle>Subkategorie</td><td $tdstyle>" . $rowval->value . "</td></tr>";
          $rowval = @$this->Database->execute( "SELECT * FROM tl_formdata_details where ff_name='Beschreibung' AND pid='$pid'");
          $txt .= "<tr><td $tdstyle>Beschreibung</td><td $tdstyle>" . $rowval->value . "</td></tr>";
          $rowval = @$this->Database->execute( "SELECT * FROM tl_formdata_details where ff_name='Preis1' AND pid='$pid'");
          $txt .= "<tr><td $tdstyle>Stückpreis</td><td $tdstyle>" . $rowval->value . "</td></tr>";
          $rowval = @$this->Database->execute( "SELECT * FROM tl_formdata_details where ff_name='Preis2' AND pid='$pid'");
          $txt .= "<tr><td $tdstyle>Paarpreis</td><td $tdstyle>" . $rowval->value . "</td></tr>";
          $rowval = @$this->Database->execute( "SELECT * FROM tl_formdata_details where ff_name='Preis3' AND pid='$pid'");
          $txt .= "<tr><td $tdstyle>Stückpreis2.3</td><td $tdstyle>" . $rowval->value . "</td></tr>";
          $rowval = @$this->Database->execute( "SELECT * FROM tl_formdata_details where ff_name='Preis4' AND pid='$pid'");
          $txt .= "<tr><td $tdstyle>Paarpreis2.3</td><td $tdstyle>" . $rowval->value . "</td></tr>";
        }
         
        $txt .= "</table>";
      }
      return  $txt;

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
         $displaytext = $GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['displaytext'];
         $displaylabel = $GLOBALS['TL_DCA']['tl_content']['fields']['singleSRC']['displaylabel'];
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