<?php

namespace Pbdkn\ContaoBesslichschmuck\Resources\contao\modules;
use Contao\System;

use Contao\Input;
use Contao\Module;
use Contao\ContentModel;
use Contao\FilesModel;
use Markocupic\GalleryCreatorBundle\Model\GalleryCreatorAlbumsModel;
use Markocupic\GalleryCreatorBundle\Model\GalleryCreatorPicturesModel;
use Markocupic\GalleryCreatorBundle\Util\GalleryCreatorUtil;

class DetailArtikelDisplay extends Module
{
    protected $strTemplate = 'fe_detail_artikel_module';

    private $besslichUtil;

    /**
     * Modul-Konstruktor
     */
     /*
    public function __construct($objModule, $strColumn = 'main')
    {
        // Elternkonstruktor aufrufen
        parent::__construct($objModule, $strColumn);
    }
    */
    /**
     * Modul initialisieren
     */
    public function generate()
    {

        $container = System::getContainer();
        $this->besslichUtil=$besslichUtil = $container->get('Pbdkn.ContaoBesslichschmuck.util.besslich_util');

        return parent::generate();
    }
    /**
     * Verarbeite die Daten und fülle das Template
     */
    protected function compile(): void
    {
        if (TL_MODE == 'BE') {
            // Backend-Ausgabe definieren
            $this->Template = new \BackendTemplate('be_detail_artikel_module');
            $this->Template->message = 'Detaildarstellung Schmuckartikel';
            return;
        }

      $debugtxt="";
      $name = Input::get('name') ?: 'Standardwert 1';
      $page = Input::get('page') ?: 'Standardwert 2';
//      $debugtxt.="InputParameter Name: " . $name . "<br>Page: " . $page . "<br>";
      /* 
       *  erstellung linke column
       */
      $spalte="<div class='box'></div>";             // nimmt den Text für Spalte auf
      $spalte="";             // nimmt den Text für Spalte auf
      if ($page=="full") {
        $schmuckArtikel = ContentModel::findBy(
          ['type=?', 'schmuckartikelname=?'], 
          ['schmuckartikel', $name]
        );
/*
        if ($schmuckArtikel !== null) $debugtxt.='schmuckartikel gefunden: ' . $schmuckArtikel->schmuckartikelname.'<br>Text'.$schmuckArtikel->text.'<br>';
        else $debugtxt.='schmuckartikel Artikel nicht gefunden.<br>';     
*/        
        // Pfad original Bild
        $arrPiclistPaths = array();
        $objFile = \FilesModel::findByUuid($schmuckArtikel->singleSRC);
        $schmuckArtikelPfad="";
        if ($objFile !== null) {
          $arrPiclistPaths[] = $objFile->path;
          $schmuckArtikelPfad=$objFile->path;    // dies wird als ersatz im Button bei videos angezeigt
        } else $debugtxt.="Bild zum Original: $name nicht vorhanden <br>";
        $this->Template->text=$schmuckArtikel->text;
        $this->Template->name=$name;
        $this->Template->artikelzusatz=$schmuckArtikel->artikelzusatz;

        // detailAlbum anhand des Alias suchen
        $albumAlias = "$name-detail";
          // suchen ob ein detail-directory existiert
        $directory = 'files/DetailSchmuckartikel/'.$albumAlias.'/';
        $files = FilesModel::findBy(
          ['path LIKE ?', 'type="file"'], 
          [$directory . '%'], 
          ['order' => 'name ASC'] );
        
        if ($files === null) {
          //$debugtxt.="Das Verzeichnis '$directory' existiert nicht oder ist leer.<br>";
        } else {
          //$debugtxt.="Dateien im Verzeichnis $directory:<br>";
          // Dateien durchlaufen
          foreach ($files as $file) {
              $arrPiclistPaths[] = $file->path;
              //$debugtxt.="add Path: ".$file->path."<br>";
          }          
        }        
        $cntPiclist=count($arrPiclistPaths);
          
        // scrollcontainer aufbauen
        $spalte.="\n<div id='carouselDetailIndicators' class='carousel slide' data-bs-ride='carousel' >";
        if ($cntPiclist > 1) {
          $spalte.="<ol class='carousel-indicators'>";
          $first=true;
          $i=0;
          // beispiel eigens icon in button
          //<button type="button" data-bs-target="#customCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1">
          //  <img src="thumb1.jpg" class="img-fluid" alt="Thumbnail 1">
// ich schreibe es mal hier auf wie mov dateiewn verkleinert werden
// ffmpeg -i "Inputfile.mov" -vf "scale=640:-1" -c:v libx264 -b:v 500k -crf 30 -c:a libopus -b:a 64k -an -pix_fmt yuv420p anton1.mp4
          foreach ($arrPiclistPaths as $picture) { // pictures in carousell buttons uebernehmen
            if ($first) {
              $first=false;
              $spalte.="<button type='button' data-bs-target='#carouselDetailIndicators' data-bs-slide-to='$i' class='active' aria-current='true' title='..$i'>";
            } else {
              $spalte.="<button type='button' data-bs-target='#carouselDetailIndicators' data-bs-slide-to='$i' title='..$i'>";
            }
            //$debugtxt.="einfügen $picture:<br>";

            if (strpos($picture, '.mp4') !== false ||
                     strpos($picture, '.webm') !== false ||
                     strpos($picture, '.mp3') !== false
             ) {   // video
              $spalte.="<div class='play-icon-wrapper'>";
              $spalte.="<img src='".$schmuckArtikelPfad."'' class='d-block w-100 play-icon'>";
              $spalte.="</div>";
//              $spalte.="<span class='play-icon'></span>";
            } else { 
              $spalte.="<img src='".$picture."' class='d-block w-100'>";
            }
            $spalte.="</button>\n";
            $i++;
          }
          $spalte.= "</ol>\n";
        }
        $spalte.= "<div class='carousel-inner'>";
        $first=true;
        $i=0;
        foreach ($arrPiclistPaths as $picture) {    // pictures in carousell carousel-item uebernehmen
          if ($first) {
            $first=false;
            $spalte.= "<div class='carousel-item active'>";
          } else {
            $spalte.= "<div class='carousel-item'>";
          }
          // auf mp4 
          //$debugtxt.= "bearbeiten $picture <br>";
          if (strpos($picture, '.mp4') !== false ||
                     strpos($picture, '.webm') !== false ||
                     strpos($picture, '.mp3') !== false
             ) {
            $spalte.='<a href="'.$picture.'" class="glightbox2" data-lightbox="box">';
            $spalte.='<div class="ratio ratio-1x1">';
              //$debugtxt.="!!MP4 Picture: $picture<br>";
              $spalte.='<video  autoplay muted loop playsinline>';
                $spalte.='<source src="'.$picture.'"  type="video/mp4">';
                $spalte.='<source src="'.$picture.'" type="audio/mpeg">';
                $spalte.='<source src="'.$picture.'" type="video/webm">';
                $spalte.='Ihr Browser unterstützt dieses Format nicht';
              $spalte.='</video>';
            $spalte.='</div>';
            $spalte.='</a>';
          } else {
            $spalte.='<a href="'.$picture.'" class="glightbox" data-lightbox="box" >';
            $spalte.= '<img src="'.$picture.'" class="d-block w-100" alt=" Bild: '.$i.' title="tit $i">';
            $spalte.='</a>';
          }
          $spalte.= "</div>\n";
          $i++;
        }
        $spalte.= "</div>\n";    // ende carousel-inner
        if ($cntPiclist > 1) {
          $spalte.= "<a class='carousel-control-prev' href='#carouselDetailIndicators' role='button' data-bs-slide='prev'>";
            $spalte.= "<span class='carousel-control-prev-icon' aria-hidden='true'></span>";
            $spalte.= "<span class='visually-hidden'>b</span>";
          $spalte.= "</a>";
          $spalte.= "<a class='carousel-control-next' href='#carouselDetailIndicators' role='button' data-bs-slide='next'>";
            $spalte.= "<span class='carousel-control-next-icon' aria-hidden='true'></span>";
            $spalte.= "<span class='visually-hidden'>v</span>";
          $spalte.= "</a>";
        }            
        $spalte.= "</div>\n";  // ende div carouselDetailIndicators
        // caruosell starten wenn alles geladen
        $spalte.="\n<script>";
        $spalte.="document.addEventListener('DOMContentLoaded', function () { \n";
        $spalte.=   "var myCarousel = document.querySelector('#carouselDetailIndicators');\n";
        $spalte.=   "if (myCarousel) {\n";
        $spalte.=    "console.log('#carouselDetailIndicators gefunden:' + myCarousel);\n";
        $spalte.=    "var carouselInstance = new bootstrap.Carousel(myCarousel,{ interval: 3000 , ride: 'carousel'});\n";
        $spalte.=    "console.log('Bootstrap Carousel-Instanz: ', carouselInstance);\n";
        $spalte.="  } else {\n";
        $spalte.=     "console.error('#carouselDetailIndicators wurde nicht gefunden!');\n";
        $spalte.=  "}\n";
        $spalte.="});\n";
        $spalte.="</script>\n";
        $this->Template->bild=$spalte;
      }  // ende page full
      // Kontakt ausgeben
      $spalte="";
      $m =  $this->replaceInsertTags("{{insert_form::1}}");      // artikel anfrage formular
      if ($m == "") {
        $spalte.="<p>FormulR für Konaktanfrage derzeit nicht vorhanden</p>";
      } else {
        $spalte.="<h3 style='margin-top:0px;'>anfrage zu artikel $name</h3>";
        $spalte.="<p>füllen sie <strong>alle mit * </strong> bezeichneten felder aus</p>";
        $spalte.=" $m";
      }
      $this->Template->kontaktanfrage=$spalte;
//      $debugtxt.="kontaktanfrage ok<br>";  
/*        $preisListenArray=$schmuckArtikel->preisliste;
      $preisArtikelNamen=[];
      $preisArtikelNamen[]=$name;
      
      if (isset($preisListenArray) &&  ($preisListenArray!= '')) {  // prüfen ob zusaetzliche Preise  da  
            $arr =  deserialize($preisListenArray, true);  // preisliste ist die eingabe von mehreren Namen fuer die die Preise angezeigtwerden soll
            foreach ($arr as $k=>$v) {
              $preisArtikelNamen[]=$v;
            }   
      }
      //$this->Template->divpreisliste='<strong>preise</strong><br>'.$this->besslichUtil->createPreislisteRender('23',$preisArtikelNamen);
*/
      $this->Template->divpreisliste='<strong>preise</strong><br>'.$this->besslichUtil->createFullPreislisteRender('23',$name,$schmuckArtikel->preisliste);
        
        //echo 'preisListenArray count: '.$preisListenArray.'<br>';                                            

      $this->Template->debugtxt = $debugtxt;
    }
}
