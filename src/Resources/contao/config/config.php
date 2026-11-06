<?php

\Contao\ArrayUtil::arrayInsert($GLOBALS['TL_CTE'], 2, array('besslichschmuck' => array('schmuckartikel' => 'Pbdkn\ContaoBesslichschmuck\Resources\contao\classes\elements\ContentSchmuckartikel')));


/* festlegen von inputtypes (klassen) die bei DCA als inputtype angegeben werden können */
$GLOBALS['BE_FFL']['schmuckartikel'] = 'schmuckartikel';
$GLOBALS['BE_FFL']['comment'] = 'comment';


unset($GLOBALS['BE_MOD']['table_management']['directory_heike_import']);
if (defined('TL_MODE') && TL_MODE === 'BE') {
$GLOBALS['BE_MOD']['table_management']['heike_price_csv'] = ['callback' => \Pbdkn\ContaoBesslichschmuck\Backend\PriceCsvBackend::class];
}

/**
 * -------------------------------------------------------------------------
 * FRONT END MODULES
 * -------------------------------------------------------------------------
 */

// Auflistung schmuckartikel nach preiskategorie
// Dieser Name Heike_Preisliste wird in der Palettenbeschreibung verwendet
/*
array_insert($GLOBALS['FE_MOD']['application'], count($GLOBALS['FE_MOD']['application']), array
(
	'Heike_Preisliste' => ModuleHeikePreisliste::class
));
*/
$GLOBALS['FE_MOD']['application']['Heike_Preisliste'] = \Pbdkn\ContaoBesslichschmuck\Resources\contao\modules\ModuleHeikePreisliste::class;
$GLOBALS['FE_MOD']['BesslichSchmuck']['displayArikelDetail'] = '\Pbdkn\ContaoBesslichschmuck\Resources\contao\modules\DetailArtikelDisplay';


/* css js einbinden */
if (defined('TL_MODE') && TL_MODE === 'FE') {
/*    $GLOBALS['TL_CSS'][] = '/bundles/contaobesslichschmuck/css/print2.css|static'; nach festyle migriert */
    $GLOBALS['TL_CSS'][] = '/bundles/contaobesslichschmuck/css/festyle.css|static';
    $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaobesslichschmuck/js/gallery-touch.js|static|v=' . time();
/*    $GLOBALS['TL_JAVASCRIPT'][] = '/bundles/contaobesslichschmuck/js/schriftart-select.js'; */
}
