<?php

namespace Pbdkn\ContaoBesslichschmuck\Resources\contao\modules;
use Contao\Input;
use Contao\Module;
use Contao\ContentModel;
use Markocupic\GalleryCreatorBundle\Model\GalleryCreatorAlbumsModel;
use Markocupic\GalleryCreatorBundle\Model\GalleryCreatorPicturesModel;
use Markocupic\GalleryCreatorBundle\Util\GalleryCreatorUtil;

class DetailArtikelDisplay extends Module
{
    protected $strTemplate = 'fe_detail_artikel_module';

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
        if ($objFile !== null) $arrPiclistPaths[] = $objFile->path;
        else $debugtxt.='Pfad Original: nicht vorhanden <br>';
        $this->Template->text=$schmuckArtikel->text;
        $this->Template->name=$name;
        $this->Template->artikelzusatz=$schmuckArtikel->artikelzusatz;

//        $spalte.="<div class='col-md-6 detailanfrage'>";
        // detailAlbum anhand des Alias suchen
        $albumAlias = "$name-detail";
        $album = GalleryCreatorAlbumsModel::findByAlias($albumAlias);
        if ($album !== null) {
//          $debugtxt .="Album mit alias gefunden: $albumAlias Titel: " . $album->name." gefunden<br>";
          $pictures = GalleryCreatorPicturesModel::findBy('pid', $album->id);
//          $debugtxt.="findby pid: ".$album->id."<br>";
          
          $this->Template->detailanfrage="start detailanfrage<br>";
          if ($pictures !== null) {    // Pfade detailbilder nach $arrPiclistPaths
            $anzPic = count($pictures);
//          $debugtxt.="anzahl Bilder $anzPic<br>";
            foreach ($pictures as $picture) { // pictures pfade in array übernehmen
              // besorge abs. Pathname
              $objFile = \FilesModel::findByUuid($picture->uuid);
              if ($objFile !== null) {
                $arrPiclistPaths[] = $objFile->path;
//                $debugtxt.="add Path: ".$objFile->path."<br>";
              } else $debugtxt.='Pfad Original: nicht vorhanden <br>';
            }
          }   // pictures !== null
        }     // $album !== null
        $cntPiclist=count($arrPiclistPaths);
          
        // scrollcontainer aufbauen
        $spalte.="\n<div id='carouselDetailIndicators' class='carousel slide' data-bs-ride='carousel' >";
        if ($cntPiclist > 1) {
          $spalte.="<ol class='carousel-indicators'>";
          $first=true;
          $i=0;
          foreach ($arrPiclistPaths as $picture) { // pictures in carousell buttons uebernehmen
            if ($first) {
              $first=false;
              $spalte.="<button type='button' data-bs-target='#carouselDetailIndicators' data-bs-slide-to='$i' class='active' aria-current='true' title='..$i'>";
            } else {
              $spalte.="<button type='button' data-bs-target='#carouselDetailIndicators' data-bs-slide-to='$i' title='..$i'>";
            }
            $spalte.="<img src=".$picture." class='d-block w-100'>";
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
          $debugtxt.= "bearbeiten $picture <br>";
          if (strpos($picture, '.mp3') !== false) {
            $debugtxt.= "Die Datei $picture enthält '.mp3<br>";
            continue;  // detaildarstellung in Gallery creator geht nicht
            $spalte.='<div class="ratio ratio-1x1">';
              $spalte.='<video controls>';
                $spalte.='<source src="'.$picture.'" type="audio/mpeg">';
                $spalte.='Ihr Browser unterstützt kein HTML5-Video.';
              $spalte.='</video>';
            $spalte.='</div>';
          } else if (strpos($picture, '.mp4') !== false) {
            continue;  // detaildarstellung in Gallery creator geht nicht
            $debugtxt.= "Die Datei $picture enthält '.mp4 kann noch nicht ausgewertet werden<br>"; 
            $spalte.='<div class="ratio ratio-16x9">';
              $spalte.='<video controls>';
                $spalte.='<source src="video2.mp4" type="video/mp4">';
                $spalte.='Ihr Browser unterstützt kein HTML5-Video.';
              $spalte.='</video>';
            $spalte.='</div>';
          } else {
            $spalte.= "<img src=".$picture." class='d-block w-100' alt='Bild: $i' title='tit $i'>";
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
        $spalte.=    "var carouselInstance = new bootstrap.Carousel(myCarousel,{ interval: 500000 , ride: 'carousel'});\n";
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
      $this->Template->debugtxt = $debugtxt;
    }
}
