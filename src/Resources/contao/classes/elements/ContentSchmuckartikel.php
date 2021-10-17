<?php

namespace PBDKN\ContaoBesslichschmuck\Resources\contao\classes\elements;
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
class ContentSchmuckartikel extends Contao\ContentElement
{
 
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_besslichschmuck';
 
    /**
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
     * Erzeugt die Ausgabe für das Backend.
     * @return string
     */
    private function genBeOutput()
    {
    //\System::log('PBD Besslich Artikel compile gerufen singleSrc ' . $this->singleSRC , __METHOD__, TL_GENERAL);
    //\System::log('PBD Besslich Artikel compile gerufen singleSrc path ' . checkSchmuck::$schmuckpath , __METHOD__, TL_GENERAL);
        $this->strTemplate          = 'be_wildcard';
        $this->Template             = new \BackendTemplate($this->strTemplate);
        //$this->Template->title      = $this->headline;
        //$objFile = \FilesModel::findByUuid($this->singleSRC);
        $imgtxt="";
        $objPicElement = \GalleryCreatorPicturesModel::findOneBy(
          array('column' => "tl_gallery_creator_pictures.name like '" . $this->schmuckartikelname . ".%'"),"" 
        );
        
        $wi = "";
        
        if ($objPicElement !== null)
			  {
				  $path = $objPicElement->path;
          //$wi .= $this->Template->wildcard   = "single: " . $path . "<br>";
          //$imgtxt="";
          $imgtxt .= "Schmuckartikel: " . $this->schmuckartikelname . "<br>";
          $imgtxt .= "Pfad: $path<br>";
          $objAlbumlement = \GalleryCreatorAlbumsModel::findOneBy('id',$objPicElement->pid);
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
     * Erzeugt die Ausgebe für das Frontend.
     * @return string
     */
    private function genFeOutput()
    {
        if ($this->preisliste != '') {
            // prüfen ob inhalt da
            $arr =  deserialize($this->preisliste, true);
            foreach ($arr as $str) {
              if ($str != "") {
                $this->Template->arrpreisliste = deserialize($this->preisliste, true);
              }
            }
        }
    }
}

?>