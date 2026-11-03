<?php
/** Transitional schema only: preserves the old table for verification and rollback.
 * No backend module, edit palette or old import is registered for this table.
 */
$GLOBALS['TL_DCA']['tl_heike_preisliste'] = [
    'config' => [
        'dataContainer' => 'Table', 'closed' => true, 'notEditable' => true,
        'notCreatable' => true, 'notDeletable' => true,
        'sql' => ['keys' => ['id'=>'primary','Artikel'=>'index']],
    ],
    'fields' => [
        'id' => ['sql'=>'int(10) unsigned NOT NULL auto_increment'],
        'pid' => ['sql'=>"int(10) unsigned NOT NULL default '0'"],
        'sorting' => ['sql'=>"int(10) unsigned NOT NULL default '0'"],
        'tstamp' => ['sql'=>"int(10) unsigned NOT NULL default '0'"],
        'import_source' => ['sql'=>['type'=>'binary','length'=>16,'fixed'=>true,'notnull'=>false]],
        'listTpl' => ['sql'=>"varchar(64) NOT NULL default ''"],
    ],
];
foreach (['Artikel','Kategorie','Subkategorie','PreisStueck2_5','PreisPaar2_5','PreisStueck2_3','PreisPaar2_3','PreisStueckEK','PreisPaarEK','Beschreibung'] as $priceField) {
    $GLOBALS['TL_DCA']['tl_heike_preisliste']['fields'][$priceField] = ['sql'=>"varchar(255) NOT NULL default ''"];
}
unset($priceField);
