<?php
$pid= \Input::get('pid');
$name= trim(\Input::get('name'));
$alias= \Input::get('alias');
$imgurl= \Input::get('imgurl');
$page= trim(\Input::get('page'));
/*
echo "pid: $pid<br>";
echo "name: $name<br>";
echo "alias: $alias<br>";
echo "imgurl: $imgurl<br>";
echo "page: $page<br>";

$ps="{{link::" . $name . "}}";                    // inserttag davon bilden
$p = $this->replaceInsertTags( $ps );           // insertag umwandeln
echo "link: $p<br>";
*/
  $mein_init_txt = <<< EOT
<script>
jQuery(document).ready(function () {
    // options allgemein s. https://api.jqueryui.com/1.10/tooltip/
    // options position siehe https://api.jqueryui.com/1.10/position/
    var options = {
      html:true, 
      placement:"left", 
      show: { effect: "drop", duration: 800, direction:"down" },
      position: { my: "right bottom", at: "left bottom" }
      }; 
    jQuery('[data-toggle="tooltip"]').tooltip(options);       // tooltipp jQuery einschalten  (geht nur wenn jQuery ui geladen ist)
    });
</script>
EOT;
echo  $mein_init_txt;
if ($page=="full") {
echo "<div class='row'>";
  // navigation ausgeben   geänderrt 25.01.2017
  // nur eine Spalte mit mod 2 ausgeben
  /*
  $m =  $this->replaceInsertTags("{{insert_article::navigation-ebene-3-links}}");
  if ($m == "") {
    //echo "<p class='col-md-3'>m ist leer</p>";
    echo "<p class='col-md-3'>&nbsp;</p>";
  } else {
    echo "$m";
  }
  */
  echo "<p class='col-md-1'>&nbsp;</p>";

  // detail ausgeben
  $m =  $this->replaceInsertTags("{{insert_article::$name}}",false);
  $pos = strpos($m, "error");
  if($pos !== FALSE) {
    echo "<p class='col-md-5'>Für Artikel <strong>$name</strong> ist keine Detailbeschreibung vorhanden<br>$m  </p>";
  } else {
    echo "<div class='col-md-5 detailanfrage'>";
    echo "<h3 style='margin-top:0px;'>artikelbeschreibung $name</h3>";
    echo "$m";
    echo "</div>\n";
  }

  // 
  //$m =  $this->replaceInsertTags("{{insert_article::logorawbild}}");
  $m =  $this->replaceInsertTags("{{insert_form::1}}");      // artikel anfrage formular
  if ($m == "") {
    //echo "<p class='col-md-4'>m ist leer</p>";
  } else {
      echo "<div class='col-md-4'> ";
        echo "<h3 style='margin-top:0px;'>anfrage zu artikel $name</h3>";
        //echo "<p>füllen sie <strong>alle mit * </strong> bezeichneten felder aus</p>";
        //echo " $m";
        echo "liebe kundin, liebe kunde,";
        echo "<br>die neue datenschutzverordnung hat mich etwas überrascht. damit sie ganz sicher sind, das keine daten von ihnen bei mir gespeichert werden, habe ich die kontaktaufnahme kurzfristig außer funktion gesetzt.";
        echo "<br>ihre anfrage schicken sie mir bitte per normaler post zu. darin können sie mir auch ihre e-mail adresse mitteilen.";
        echo "<strong>";
        echo "<br>heike besslich<br>";
        echo "<br>josef-martin-bauer-ring 56";
        echo "<br>84416 taufkirchen / vils</strong>";
        echo "<br><br>ich werde wie üblich so schnell wie möglich antworten.";
        echo "<br>sobald ich die entstsprechende datenschutzverordnung im impressum aufgenommen habe, werde ich den e-mail kontakt wieder freischalten.";
        echo "<br>ich bitte sie die unannehmlichkeiten zu entschuldigen.";
      echo "</div>";
  }
echo "</div>";   // div row
} else  {
// nur artikel ausgeben
  // detail ausgeben
  echo "<div class=insidelightbox>";  
  $m =  $this->replaceInsertTags("{{insert_article::$name}}",false);
  $pos = strpos($m, "error");
  if($pos !== FALSE) {
    echo "<p>Für Artikel <strong>$name</strong> ist keine Detailbeschreibung vorhanden<br>$m  </p>";
  } else {
    echo "$m\n";
  }
  echo "</div>";
}

?>