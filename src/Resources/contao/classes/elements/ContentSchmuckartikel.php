<?php

/**
 * @package   Besslich-Schmuck
 * @author    Peter Broghammer
 * @license   LGPL
 * @copyright Peter Broghammer
 */
 
/**
 * Da die Klasse von \Contao\ContentElement erbt muss sie eine Methode mit dem Namen compile implementieren. 
 * Diese ist für die Ausgabe zuständig. 
 * Ich splitte an dieser Stelle immer in zwei Methoden auf, eine für die Backend- und eine für die Frontend-Ausgabe. 
 * Die Methode genBeOutput ist für die Ausgabe im Backend zuständig. 
 * Es interessiert aber mehr die Methode genFeOutput. 
 * Diese erstellt die Ausgabe für das Frontend. 
 * Die Eigenschaften werden von Contao als serialisiertes Array gespeichert. 
 * In Zeile 60 werden sie deshalb deserialisiert, damit wir sie nutzen können. 
 * Wir speichern das Array in $this->Template->arrProperties und haben dann im Template über $this->arrProperties zugriff darauf.
 */
 
namespace Pbdkn\ContaoBesslichschmuck\Resources\contao\classes\elements;

use Contao\System;
use Pbdkn\ContaoBesslichschmuck\Resources\contao\classes\GC_Helper;
use Pbdkn\ContaoBesslichschmuck\Util\BesslichUtil;
use Pbdkn\ContaoBesslichschmuck\Util\CgiUtil;
use Markocupic\GalleryCreatorBundle\Model\GalleryCreatorAlbumsModel;


class ContentSchmuckartikel extends \Contao\ContentElement
{
 
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_besslichschmuck';
 
    private BesslichUtil $besslichUtil;
    private CgiUtil $cgi;

    public function __construct($arrAttributes = null)
    {
        parent::__construct($arrAttributes);
        $this->besslichUtil = System::getContainer()->get(BesslichUtil::class);
        $this->cgi = System::getContainer()->get(CgiUtil::class);
    }    /**
     * Compile the content element
     */
    protected function compile()
    {
 //\System::log('PBD Besslich Artikel compile gerufen', __METHOD__, TL_GENERAL);
        if (TL_MODE == 'BE') {
            $this->genBeOutput();
        } else {
            $this->genFeOutput();
        }
    }
 
    /**
     * Erzeugt die Ausgabe für das Backend. zur Uebersichtsdarstellung des ce Elements
     * @return string
     */
    private function genBeOutput()
    {
    //\System::log('PBD Besslich Artikel compile gerufen singleSrc ' . $this->singleSRC , __METHOD__, TL_GENERAL);
    //\System::log('PBD Besslich Artikel compile gerufen singleSrc path ' . checkSchmuck::$schmuckpath , __METHOD__, TL_GENERAL);
        $this->strTemplate          = 'be_wildcard';
        $this->Template             = new \BackendTemplate($this->strTemplate);
        $imgtxt="";
        $gch=new GC_Helper();
        $objPicElement=$gch->getPicture($this->schmuckartikelname);

        $wi = "";
        
        if ($objPicElement !== null) {
		  $path = $objPicElement['path'];
          $imgtxt .= "Schmuckartikel: " . $this->schmuckartikelname . "<br>";
          $imgtxt .= "Pfad: $path<br>";
          $objAlbumlement = GalleryCreatorAlbumsModel::findOneBy('id',$objPicElement['pid']);
          if ($objAlbumlement !== null) {
            $imgtxt .=  "Album: " .  $objAlbumlement->name . "<br>";
          } else {
            $imgtxt .= "kein Album da pid " . $objPicElement->pid . "<br>";
          }
          $wi .= "<img src='$path' width='50px' height='50px' style='float:left'></img>";
          $wi .= "<span style='float:left;padding-left: 10px;'>$imgtxt</span>";
		} else {
          $wi .= $this->Template->wildcard   = "<strong> !!! Kein Bild vorhanden</strong><br>";
        }
        $this->Template->wildcard   = $wi;
    }
 
    /**
     * Erzeugt die Ausgabe für das Frontend.
     * mittels protected $strTemplate = 'ce_besslichschmuck'; template ce_besslichschmuck
     * es werden templatevariable erzeugt
     * schmuckImage:    gerednertes image
     * preisArikel:     array das den gesamten Datensatz des schuckartikels enthaelt
     * arrpreisliste:   array mit schluessel des elments aller vorhandenen artikel name + preisliste
     * divpreisliste:   gerenderte Preisliste
     * @return string
     */
    private function genFeOutput()
    {
      // Bild erzeugen
      $c=$this->cgi;                    // create cgi
      $objFile = \FilesModel::findByUuid($this->singleSRC);
      if ($objFile !== null)
	  {
	    $path = $objFile->path;
        $im = "<figure class='image_container'>";
        $im .=  "<img src='$path'>";
        $im .= "</figure>";
        $this->Template->schmuckImage=$im;
        //echo "<br>" . $this->caption;
      } else {
        $this->Template->schmuckImage="";
      }
/*
      $preisArtikelNamen=[];
      $preisArtikelNamen[]=$this->schmuckartikelname;
      
      if (isset($this->preisliste) &&  ($this->preisliste!= '')) {  // prüfen ob zusaetzliche Preise  da  
            $arr =  deserialize($this->preisliste, true);  // preisliste ist die eingabe von mehreren Namen fuer die die Preise angezeigtwerden soll
            foreach ($arr as $k=>$v) {
              $preisArtikelNamen[]=$v;
            }   
      }
      //$this->Template->divpreisliste=$this->besslichUtil->createPreislisteRender('23',$preisArtikelNamen);
*/
      $this->Template->divpreisliste='<strong>preise</strong><br>'.$this->besslichUtil->createFullPreislisteRender('23',$this->schmuckartikelname,$this->preisliste);
    }
}

?>