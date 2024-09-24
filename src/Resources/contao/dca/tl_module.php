<?php


// der PalettenName muss mit dem Namen in der config übereinstimmen
// 'Heike_Preisliste' => ModuleHeikePreisliste::class
$GLOBALS['TL_DCA']['tl_module']['palettes']['Heike_Preisliste'] = 
'{title_legend},name,headline,type;{config_legend},heike_preislisteTpl,heike_preislisteKategorie,perPage;';

// Template-Auswahlfeld für die Standarddarstellung
$GLOBALS['TL_DCA']['tl_module']['fields']['heike_preislisteTpl'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['heike_preislisteTpl'],
    'default'                 => 'list_heike_default',
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback'        => function () {
        return \Contao\Controller::getTemplateGroup('list_heike_');
    },
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['heike_preislisteKategorie'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['heike_preislisteKategorie'],
    'default'                 => 10,
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => array('EK' => 'EK', '23' => '23', '25' => '25'),
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(64) NOT NULL default ''"
);





