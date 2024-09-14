<?php

array_insert($GLOBALS['TL_CTE'], 2, array('besslichschmuck' => array('schmuckartikel' => 'Pbdkn\ContaoBesslichschmuck\Resources\contao\classes\elements\ContentSchmuckartikel')));


/* festlegen von inputtypes (klassen) die bei DCA als inputtype angegeben werden können */
$GLOBALS['BE_FFL']['schmuckartikel'] = 'schmuckartikel';
$GLOBALS['BE_FFL']['comment'] = 'comment';


if (TL_MODE == 'BE') {
array_insert($GLOBALS['BE_MOD'], 1, array('Tabellen' => array()));
$GLOBALS['BE_MOD']['Tabellen']['Preisliste'] = array
   (
	'tables'     => ['tl_heike_preisliste'],
	'icon'       => 'bundles/contaobesslichschmuck/icons/formdata_all.gif',
    'stylesheet' => 'bundles/contaobesslichschmuck/css/style.css',
    );
}

/**
 * -------------------------------------------------------------------------
 * FRONT END MODULES
 * -------------------------------------------------------------------------
 */
/*  module einbinden ??
array_insert($GLOBALS['FE_MOD']['application'], count($GLOBALS['FE_MOD']['application']), array
(
	'formdatalisting' => 'Pbdkn\Efgco4\Resources\contao\modules\ModuleFormdataListing'
));
*/
// config/config.php


/* css einbinden */
if (TL_MODE == 'FE') {
    $GLOBALS['TL_CSS'][] = '/bundles/contaobesslichschmuck/css/festyle.css|static';
}



?>