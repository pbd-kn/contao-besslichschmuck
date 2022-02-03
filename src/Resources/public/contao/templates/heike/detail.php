<?php
$name= trim(\Input::get('name'));
$page= trim(\Input::get('page'));
//echo "name: $name<br>";
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
  // navigation ausgeben   geändert 25.01.2017
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
        echo "<p>füllen sie <strong>alle mit * </strong> bezeichneten felder aus</p>";
        echo " $m";
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