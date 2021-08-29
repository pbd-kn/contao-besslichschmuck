<?php
// config/config.php
//$GLOBALS['TL_CTE']['esit']['myproduct'] = '\\esit\\esitcontent\\classes\\elements\\ContentProduct';
$GLOBALS['TL_CTE']['besslichschmuck']['schmuckartikel'] = 'PBDKN/ContaoBesslichschmuck/classes/elements/ContentSchmuckartikel';
/*
Der zweite Key des Arrays (besslichschmuck) ist die Gruppe, in der der Eintrag angezeigt wird. 
Der dritte Key gibt den Namen des objektes an (schmuckartikel).
Der Wert ist die virtuelle Contao-Klasse.
welche Datei die Klasse realisiert wird in autoload.php festgelegt
ClassLoader::addClasses ... 
*/

/* festlegen von inputtypes (klassen) die bei DCA als inputtype angegeben werden können */
$GLOBALS['BE_FFL']['schmuckartikel'] = 'schmuckartikel';
$GLOBALS['BE_FFL']['comment'] = 'comment';


?>