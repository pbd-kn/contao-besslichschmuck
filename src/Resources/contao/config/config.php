<?php

array_insert($GLOBALS['TL_CTE'], 2, array('besslichschmuck' => array('schmuckartikel' => 'Pbdkn\ContaoBesslichschmuck\Resources\contao\classes\elements\ContentSchmuckartikel')));


/* festlegen von inputtypes (klassen) die bei DCA als inputtype angegeben werden können */
$GLOBALS['BE_FFL']['schmuckartikel'] = 'schmuckartikel';
$GLOBALS['BE_FFL']['comment'] = 'comment';


/*
$GLOBALS['BE_MOD']['system']['Preisliste'] = array(
    'tables' => array('tl_heike_preisliste')
);
*/
// Optional: Konfiguration der Menüreihenfolge
array_insert($GLOBALS['BE_MOD'], 1, [
    'Tabellen' => [
        'Preisliste' => [
            'tables' => ['tl_heike_preisliste'],
        ],
    ],
]);


?>