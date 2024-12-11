<?php

namespace Pbdkn\ContaoBesslichschmuck\Resources\contao\modules;
use Contao\Input;
use Contao\Module;
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
      $this->Template->linkerRand="<p class='col-md-1'>&nbsp;</p>";
      /* 
       *  erstellung linke column
       */
      $spalte="";             // nimmt den Text für Spalte auf
      if ($page=="full") {
        $spalte.="<div class='col-md-5 detailanfrage'>";
        // detailAlbum anhand des Alias suchen
        $albumAlias = "$name-detail";
        $album = GalleryCreatorAlbumsModel::findByAlias($albumAlias);
        if ($album !== null) {
//          $debugtxt .="Album mit alias gefunden: $albumAlias Titel: " . $album->name." gefunden<br>";
          $pictures = GalleryCreatorPicturesModel::findBy('pid', $album->id);
          $this->Template->detailanfrage="start detailanfrage<br>";
          if ($pictures !== null) {
            $anzPic = count($pictures);
//            $debugtxt.="anzahl Bilder $anzPic<br>";
            $spalte.="<h3 style='margin-top:0px;'>artikelbeschreibung $name</h3>";
            // scrollcontainer
            $spalte.="\n<div id='carouselDetailIndicators' class='carousel slide' data-bs-ride='carousel' >";

            $spalte.="<ol class='carousel-indicators'>";
            $first=true;
            $i=0;
            foreach ($pictures as $picture) { // pictures in carousell buttons uebernehmen
              if ($first) {
                $first=false;
                $spalte.="<button type='button' data-bs-target='#carouselDetailIndicators' data-bs-slide-to='$i' class='active' aria-current='true' title='..$i'>";
              } else {
                $spalte.="<button type='button' data-bs-target='#carouselDetailIndicators' data-bs-slide-to='$i' title='..$i'>";
              }
              $spalte.="<img src=".$picture->path." class='d-block w-100'>";
              $spalte.="</button>\n";
              $i++;
            }
            $spalte.= "</ol>\n";
            $spalte.= "<div class='carousel-inner'>";
            $first=true;
            $i=0;
            foreach ($pictures as $picture) {    // pictures in carousell carousel-item uebernehmen
              if ($first) {
                $first=false;
                $spalte.= "<div class='carousel-item active'>";
              } else {
                $spalte.= "<div class='carousel-item'>";
              }
              $spalte.= "<img src=".$picture->path." class='d-block w-100' alt='Bild: $i' title='tit $i'>";
              $spalte.= "</div>\n";
              $i++;
            }
            $spalte.= "</div>\n";    // ende carousel-inner
//  Vorwärts rückwärts geht noch nicht
/*
<a class="carousel-control-prev" href="#carouselDetailIndicators" role="button" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
</a>
<a class="carousel-control-next" href="#carouselDetailIndicators" role="button" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
</a>
*/                    
            $spalte.= "<a class='carousel-control-prev' href='#carouselDetailIndicators' role='button' data-bs-slide='prev'>";
              $spalte.= "<span class='carousel-control-prev-icon' aria-hidden='true'></span>";
              $spalte.= "<span class='visually-hidden'>b</span>";
            $spalte.= "</a>";
            $spalte.= "<a class='carousel-control-next' href='#carouselDetailIndicators' role='button' data-bs-slide='next'>";
              $spalte.= "<span class='carousel-control-next-icon' aria-hidden='true'></span>";
              $spalte.= "<span class='visually-hidden'>v</span>";
            $spalte.= "</a>";
            
            $spalte.= "</div>\n";  // ende div carouselDetailIndicators

            $spalte.= "<div class='detail-linkback'><p>&nbsp;</p>";            
            $spalte.= "<p><a href='javascript:history.go(-1)' title='Zurück'>";
            $spalte.= "<span class='fa fa-chevron-left'>&nbsp;</span><span class=detail-linkback-txt>zurück</span>";
            $spalte.= "</a></p></div>";
            // caruosell starten wenn alles geladen
            $spalte.="\n<script>";
            $spalte.="document.addEventListener('DOMContentLoaded', function () { \n";
            $spalte.=   "var myCarousel = document.querySelector('#carouselDetailIndicators');\n";
            $spalte.=   "if (myCarousel) {\n";
            $spalte.=    "console.log('#carouselDetailIndicators gefunden:' + myCarousel);\n";
            $spalte.=    "var carouselInstance = new bootstrap.Carousel(myCarousel,{ interval: 5000 , ride: 'carousel'});\n";
            $spalte.=    "console.log('Bootstrap Carousel-Instanz: ', carouselInstance);\n";
            $spalte.="  } else {\n";
            $spalte.=     "console.error('#carouselDetailIndicators wurde nicht gefunden!');\n";
            $spalte.=  "}\n";
            $spalte.="});\n";
            $spalte.="</script>\n";
          }   // pictures !== null
        } else {
          $spalte.="<p>alte Darstellung</p>";
          // detail ausgeben
          $m =  $this->replaceInsertTags("{{insert_article::$name}}",false);
          $pos = strpos($m, "error");
          if($pos !== FALSE) {
            $spalte.="<p>Für Artikel <strong>$name</strong> ist keine Detailbeschreibung vorhanden<br>$m  </p>";
          } else {
            $spalte.="<h3 style='margin-top:0px;'>artikelbeschreibung $name</h3>";
            $spalte.="$m";
          }
          
        }
        $spalte.="</div>";     // Ende col-md-5 detailanfrage
        $this->Template->detailanfrage=$spalte;
      }  // ende page full
      // Kontakt ausgeben
      $spalte="";
      $m =  $this->replaceInsertTags("{{insert_form::1}}");      // artikel anfrage formular
      if ($m == "") {
        $spalte.="<p class='col-md-4'>m ist leer</p>";
      } else {
        $spalte.="<div class='col-md-4'> ";
          $spalte.="<h3 style='margin-top:0px;'>anfrage zu artikel $name</h3>";
          $spalte.="<p>füllen sie <strong>alle mit * </strong> bezeichneten felder aus</p>";
          $spalte.=" $m";
        $spalte.="</div>";
      }
      $this->Template->kontaktanfrage=$spalte;
//      $debugtxt.="kontaktanfrage ok<br>";  
      $this->Template->debugtxt = $debugtxt;
    }
}
