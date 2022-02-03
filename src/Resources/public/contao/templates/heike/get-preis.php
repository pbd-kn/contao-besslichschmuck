<?php

$artikel = trim(\Input::get('artikel'));
$name = trim(\Input::get('name'));  
$preiskategorie = trim(\Input::get('preiskategorie'));
//echo "artikel: $artikel name: $name<br>";
if ($artikel == "") $artikel = $name;
if ($artikel == "") return;
$arr = explode(':', $artikel);
$tableexist=false;


// voreinstellung
  $Paarpreis = "paarpreis";
  $Stückpreis = "stückpreis";
  $Paarpreistxt = "paarpreis";
  $Stückpreistxt = "stückpreis";
if ($preiskategorie == "2.3") {
  $Paarpreis = "paarpreis2.3";
  $Stückpreis = "stückpreis2.3";
  $Paarpreistxt = "paarpr2.3";
  $Stückpreistxt = "stückpr2.3";
}
elseif ($preiskategorie == "EK") {
  $Paarpreis = "paarpreisEK";
  $Stückpreis = "stückpreisEK";
  $Paarpreistxt = "paarprEK";
  $Stückpreistxt = "stückprEK";
}

echo "<div class='preistabelle'>";
    $cnt=0;
    $paarda=false;
    $gesamt = 0;
    foreach ($arr as $k=>$n) {
      $e =  $this->replaceInsertTags("{{efg_insert::artikelliste::$n::$Paarpreis}}");
      $pos = strpos($e, "error");
      if($pos !== FALSE) {
      } else {
        if ($e != "")  {
          $e = sprintf("%1\$.2f",$e); 
          $e = str_replace(".",",",$e);
          if ($tableexist == false) {
            echo " <table class='tablepreistabelle'>";
            $tableexist=true;
          }
          echo "<tr><td width='80px' data-toggle='tooltip' title='detailinformation erhalten sie unter<br>info -> preise'>$Paarpreistxt<sup style='font-size:.7em; line-height:2em;'>*</sup></td><td width='auto'>$n</td><td align='right' width='66px'>$e €</td></tr>";
          $cnt++;
          $paarda=true;
          $gesamt += $e;
        }
      }
    }
/*  Gesamtpreis nicht ausgeben
    if ($cnt > 1) { // gesamtpreis ausgeben
          $gesamt = sprintf("%1\$.2f",$gesamt);
          $gesamt = str_replace(".",",",$gesamt);
          if ($tableexist == false) {
            echo " <table class='tablepreistabelle'>";
            $tableexist=true;
          }
          echo "<tr><td width='80px' valign='bottom'>Summe</td><td width='40px'>&nbsp;</td><td align='right' width='66px'>$gesamt €</td></tr>";
    }
*/
    // einzelpreise aus Formular auslesen nur wenn kein Paarpreis da ist
    if (!$paarda) {
      //echo "<tr><td>kein paarpreis</td></tr>";
      $cnt=0;
      $gesamt = 0;
      foreach ($arr as $k=>$n) {
        $e =  $this->replaceInsertTags("{{efg_insert::artikelliste::$n::$Stückpreis}}");
        $pos = strpos($e, "error");
        if($pos !== FALSE) {
          if ($tableexist == false) {
            echo " <table class='tablepreistabelle'>";
            $tableexist=true;
          }
          echo "<tr><td>$n error: $e </td></tr>";
        } else {
          if ($e != "")  {
            $e = sprintf("%1\$.2f",$e); 
            $e = str_replace(".",",",$e);
            if ($tableexist == false) {
              echo " <table class='tablepreistabelle'>";
              $tableexist=true;
            }
            echo "<tr>\n<td width='80px' data-toggle='tooltip' title='detailinformation erhalten sie unter info -> preise' >$Stückpreistxt<sup style='font-size:.7em; line-height:2em;'>*</sup></td>\n<td width='auto'>$n</td><td align='right' width='66px'>$e €</td>\n</tr>\n";
            $cnt++;
            $gesamt += $e;
          }
        }
      }
/* Gesamtpreis nicht ausgeben
      if ($cnt > 1) { // gesamtpreis ausgeben
            $gesamt = sprintf("%1\$.2f",$gesamt);
            $gesamt = str_replace(".",",",$gesamt);
            if ($tableexist == false) {
              echo " <table class='tablepreistabelle'>";
              $tableexist=true;
            }
            echo "<tr><td width='80px' valign='bottom'>Summe</td><td width='40px'>&nbsp;</td><td align='right' width='66px'>$gesamt €</td></tr>";
      }
*/
    }

    if ($tableexist == true) {
      echo " </table>";
      $tableexist=false;
    }
    echo "</div>";
?>
