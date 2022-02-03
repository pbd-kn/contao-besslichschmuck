<?php
$name[]= \Input::get('name');
$name[]= \Input::get('name1');
$name[]= \Input::get('name2');
$name[]= \Input::get('name3');
$name[]= \Input::get('name4');
$name[]= \Input::get('name5');
$name[]= \Input::get('name6');
$name[]= \Input::get('name7');
$name[]= \Input::get('name8');
$name[]= \Input::get('name9');
$name[]= \Input::get('name10');
/*
echo "pid: $pid<br>";
echo "name: $name<br>";
echo "alias: $alias<br>";
echo "imgurl: $imgurl<br>";

$ps="{{link::" . $name . "}}";                    // inserttag davon bilden
$p = $this->replaceInsertTags( $ps );           // insertag umwandeln
echo "link: $p<br>";
*/

if (count($name) > 0) {
  // detail ausgeben
  //echo "<div class='row'>";
  echo "<div class='detailanfrage-kontakt'>";
echo "PBD----------------------------------------<br>";
  $n = strtolower($name[0]);
  $m =  $this->replaceInsertTags("{{insert_article::$n}}",false);
  $pos = strpos($m, "error");
  if($pos !== FALSE) {
    echo "<p>Für Artikel <strong>$n</strong> ist keine Detailbeschreibung vorhanden<br>$m  </p>";
  } else {
    echo "$m";
  }
  //  foreach ($name as $k=>$n) { echo "<br> n $n";}
  // paarpreise aus Formular auslesen
  echo "<table>";
    $cnt=0;
    $paarda=false;
    $gesamt = 0;
    foreach ($name as $k=>$n) {
      $e =  $this->replaceInsertTags("{{efg_insert::artikelliste::$n::Paarpreis}}");
      $pos = strpos($e, "error");
      if($pos !== FALSE) {
      } else {
        if ($e != "")  {
          $e = sprintf("%1\$.2f",$e); 
          $e = str_replace(".",",",$e);
          echo "<tr><td width='80px'>paarpreis</td><td width='40px'>$n</td><td align='right' width='66px'>$e €</td></tr>";
          $cnt++;
          $paarda=true;
          $gesamt += $e;
        }
      }
    }
    /*
    if ($cnt > 1) { // gesamtpreis ausgeben
          $gesamt = sprintf("%1\$.2f",$gesamt);
          $gesamt = str_replace(".",",",$gesamt);
          echo "<tr><td width='80px' valign='bottom'>Summe</td><td width='40px'>&nbsp;</td><td align='right' width='66px'>$gesamt €</td></tr>";
    }
    */
    // einzelpreise aus Formular auslesen nur wenn kein Paarpreis da ist
    if (!$paarda) {
      //echo "<tr></tr>";
      $cnt=0;
      $gesamt = 0;
      foreach ($name as $k=>$n) {
        $e =  $this->replaceInsertTags("{{efg_insert::artikelliste::$n::Stückpreis}}");
        $pos = strpos($e, "error");
        if($pos !== FALSE) {
        echo "<tr><td>$n error: $e </td></tr>";
        } else {
          if ($e != "")  {
            $e = sprintf("%1\$.2f",$e); 
            $e = str_replace(".",",",$e);
            echo "<tr><td width='80px'>einzelpreis</td><td width='80px'>$n</td><td align='right' width='66px'>$e €</td></tr>";
            $cnt++;
            $gesamt += $e;
          }
        }
      }
      /*
      if ($cnt > 1) { // gesamtpreis ausgeben
            $gesamt = sprintf("%1\$.2f",$gesamt);
            $gesamt = str_replace(".",",",$gesamt);
            echo "<tr><td width='80px' valign='bottom'>Summe</td><td width='40px'>&nbsp;</td><td align='right' width='66px'>$gesamt €</td></tr>";
      }
      */
    }
  echo "</table>";
  echo "</div>";
}

//echo "</div>";

?>