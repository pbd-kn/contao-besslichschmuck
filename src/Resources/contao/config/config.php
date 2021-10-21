<?php

array_insert($GLOBALS['TL_CTE'], 2, array('besslichschmuck' => array('gallery_creator_ce_news' => 'Markocupic\GalleryCreatorBundle\ContentGalleryCreatorNews')));
array_insert($GLOBALS['TL_CTE'], 2, array('besslichschmuck' => array('schmuckartikel' => 'PBDKN\ContaoBesslichschmuck\Resources\contao\classes\elements\ContentSchmuckartikel')));


/* festlegen von inputtypes (klassen) die bei DCA als inputtype angegeben werden können */
$GLOBALS['BE_FFL']['schmuckartikel'] = 'schmuckartikel';
$GLOBALS['BE_FFL']['comment'] = 'comment';


?>