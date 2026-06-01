<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   Efg
 * @author    P.Broghammer
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * @copyright P.Broghammer 2024-
 *
 */
use Contao\DC_Table;
use Contao\DataContainer;
use Pbdkn\ContaoBesslichschmuck\Resources\contao\dataContainer\tableList;
use Contao\Backend;
use Contao\System;
use Contao\Image;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

\Contao\System::loadLanguageFile('tl_heike_preisliste_odt');

/**
 * Table tl_heike_preisliste_odt 
 */
// config/dca/tl_heike_preisliste_odt.php

$GLOBALS['TL_DCA']['tl_heike_preisliste_odt'] = [
    // Config
    'config' => [
        'dataContainer'               => 'Table',  // DC_Table verwenden
         'enableVersioning'            => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'Artikel' => 'index'
            ]
        ],
        'onsubmit_callback' => [
            ['tl_heike_preisliste_odt', 'recalculatePairPrices'],
        ],

    ],

    // List
    'list' => [
        'sorting' => [
            'mode'        => DataContainer::MODE_SORTABLE,
            'fields'      => ['Artikel ASC'],
            'panelLayout' => 'filter;search,sort,limit',
        ],
        'label' => [
            'fields' => [
                'Artikel',
                'PreisStueck2_5',
                'PreisPaar2_5',                
            ],
            'format' => '%s [StückPr: %s, PaarPr: %s]',
        ],
        'global_operations' => [
            'csvimport' => [
            'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['importCSV'][0], 
            //'href' => 'key=importCSV', 
			'href'                => '',
            'class' => 'header_csv_import', 
            'attributes' => 'onclick="Backend.getScrollOffset();"',
                'button_callback' => ['tl_heike_preisliste_odt', 'importCsvButton'],

            ],            
        
            'all' => [
                'label'        => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'         => 'act=select',
                'class'        => 'header_edit_all',
                'attributes'   => 'onclick="Backend.getScrollOffset();"'
            ]
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['editheader'],
                'href'  => 'act=editheader',
                'icon'  => 'header.gif'
            ],
            'delete' => [
                'label'       => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['delete'],
                'href'        => 'act=delete',
                'icon'        => 'delete.gif',
                'attributes'  => 'onclick="if (!confirm(\'' . 'Wirklich löschen?' . '\')) return false; Backend.getScrollOffset();"'
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ],
        ]
    ],

    // Palettes
    'palettes' => [
        'default' => 'Artikel,Kategorie,Subkategorie,Beschreibung,PreisStueck2_5,PreisPaar2_5,PreisStueck2_3,PreisPaar2_3,PreisStueckEK,PreisPaarEK;{template_legend},listTpl'
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['sorting'],
            'exclude' => true,
            'filter'  => false,
            'inputType' => 'text',
            'eval'    => ['rgxp'=>'digit', 'maxlength'=>10, 'tl_class'=>'w50'],
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
		'import_source' => array
		(
			//'label'                   => @$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['import_source'],
			'label'                   => 'PBD importsource CSV',
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => [
               'filesOnly'  => true,
                'fieldType'  => 'radio',
                'extensions' => '',
            ],
            'sql'       => [
              'type' => 'binary',
              'length' => 16,
              'fixed' => true,
              'notnull' => false,
            ],		
       ),
        
        
        'Artikel' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['Artikel'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => true,
            'eval'    => ['mandatory'=>true],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        
        'Kategorie' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['Kategorie'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => true,
            'eval'    => ['mandatory'=>true],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'Subkategorie' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['Subkategorie'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => true,
            'eval'    => ['mandatory'=>true],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'PreisStueck2_5' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['PreisStueck25'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => false,
            'eval'    => ['tl_class'=>'w50'],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'PreisPaar2_5' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['PreisPaar25'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => false,
            'eval'    => ['tl_class'=>'w50'],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'PreisStueck2_3' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['PreisStueck23'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => false,
            'eval'    => ['tl_class'=>'w50'],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'PreisPaar2_3' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['PreisPaar23'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => false,
            'eval'    => ['tl_class'=>'w50'],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'PreisStueckEK' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['PreisStueckEK'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => false,
            'eval'    => ['tl_class'=>'w50'],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'PreisPaarEK' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['PreisPaarEK'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => false,
            'eval'    => ['tl_class'=>'w50'],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'Beschreibung' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['Beschreibung'],
            'inputType' => 'textarea',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => true,
            'eval'    => ['mandatory'=>false, 'default'=>'Initialer Wert'], // Set default value
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        // Neues Feld für die Template-Auswahl
        'listTpl' => [
            'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_odt']['listTpl'],
            'default' => 'mod_list_temp',
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => function () {
                return Contao\Controller::getTemplateGroup('mod_');
            },
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''"
        ],
        
    ],
];

$GLOBALS['TL_DCA']['tl_heike_preisliste_odt']['palettes']['default'] =
    '{stammdaten_legend},Artikel,Kategorie,Subkategorie,Beschreibung;'
    .'{kalkulation_basis_legend},ag_in_g,ag_incl_verlust,au_in_g,au_incl_verlust,zeit_in_h,artikel_zusatz,ist_paar;'
    .'{steine_legend},stueck_1,steine_perlen_ek,steine_messe,artikel_2,stueck_2,furnituren_steine_ek,steine_messe_2,plattierung_oxidation,schnur_2;'
    .'{preise_legend},ek,PreisStueckEK,PreisPaarEK,PreisStueck2_5,PreisPaar2_5,PreisStueck2_3,PreisPaar2_3;'
    .'{import_legend:hide},artikelnr,nochmals_artikel,leer_1,leer_2,reserve_1,reserve_2,reserve_3,reserve_4;'
    .'{template_legend:hide},listTpl';

$technicalFields = array(
    'ag_in_g' => 'AG in g',
    'ag_incl_verlust' => 'AG inkl. Verlust',
    'au_in_g' => 'AU in g',
    'au_incl_verlust' => 'AU inkl. Verlust',
    'zeit_in_h' => 'Zeit in h',
    'artikel_zusatz' => 'Artikel Zusatz',
    'ist_paar' => 'Als Paar verkauft',
    'stueck_1' => 'Stueck 1',
    'steine_perlen_ek' => 'Steine/Perlen EK',
    'steine_messe' => 'Steine Messe',
    'artikel_2' => 'Artikel 2',
    'stueck_2' => 'Stueck 2',
    'furnituren_steine_ek' => 'Furnituren/Steine EK',
    'steine_messe_2' => 'Steine Messe 2',
    'plattierung_oxidation' => 'Plattierung/Oxidation',
    'schnur_2' => 'Schnur 2',
    'leer_1' => 'Leer 1',
    'leer_2' => 'Leer 2',
    'artikelnr' => 'Artikelnummer',
    'ek' => 'EK',
    'nochmals_artikel' => 'Nochmals Artikel',
    'reserve_1' => 'Reserve 1',
    'reserve_2' => 'Reserve 2',
    'reserve_3' => 'Reserve 3',
    'reserve_4' => 'Reserve 4',
);

foreach ($technicalFields as $fieldName => $label) {
    if (!isset($GLOBALS['TL_DCA']['tl_heike_preisliste_odt']['fields'][$fieldName])) {
        $GLOBALS['TL_DCA']['tl_heike_preisliste_odt']['fields'][$fieldName] = array(
            'label' => array($label, $fieldName),
            'exclude' => false,
            'search' => true,
            'sorting' => true,
            'filter' => false,
            'inputType' => 'text',
            'eval' => array('tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''",
        );
    }
}

$GLOBALS['TL_DCA']['tl_heike_preisliste_odt']['fields']['ist_paar']['inputType'] = 'checkbox';
$GLOBALS['TL_DCA']['tl_heike_preisliste_odt']['fields']['ist_paar']['eval'] = array('tl_class' => 'w50');
$GLOBALS['TL_DCA']['tl_heike_preisliste_odt']['fields']['ist_paar']['sql'] = "char(1) NOT NULL default ''";

$formulaPlaceholders = array(
    'ag_incl_verlust' => 'ag_in_g * (1 + C3)',
    'au_incl_verlust' => 'au_in_g * (1 + C5)',
    'stueck_1' => 'Anzahl Steine/Furnituren Stück 1',
    'steine_messe' => 'stueck_1 * steine_messe, sonst steine_perlen_ek * 1.5',
    'stueck_2' => 'Anzahl Steine/Furnituren Stück 2',
    'furnituren_steine_ek' => 'Basis für Stück 2, wenn steine_messe_2 leer ist',
    'steine_messe_2' => 'stueck_2 * steine_messe_2, sonst furnituren_steine_ek * 1.5',
    'plattierung_oxidation' => 'z.B. D13 oder fester Betrag',
    'schnur_2' => 'z.B. H5 oder fester Betrag',
    'ek' => 'Material + Arbeitszeit + Steine/Furnituren + Plattierung + Schnur + Zuschläge',
    'PreisStueckEK' => 'ceil(ek), wenn kein importierter Stück-EK vorhanden ist',
    'PreisPaarEK' => 'Nur befüllen, wenn der Artikel als Paar verkauft wird',
    'PreisStueck2_5' => 'ceil(PreisStueckEK * 2.5)',
    'PreisPaar2_5' => 'ceil(PreisPaarEK * 2.5)',
    'PreisStueck2_3' => 'ceil(PreisStueckEK * 2.3)',
    'PreisPaar2_3' => 'ceil(PreisPaarEK * 2.3)',
);

foreach ($formulaPlaceholders as $fieldName => $placeholder) {
    if (isset($GLOBALS['TL_DCA']['tl_heike_preisliste_odt']['fields'][$fieldName])) {
        unset($GLOBALS['TL_DCA']['tl_heike_preisliste_odt']['fields'][$fieldName]['eval']['placeholder']);

        if (!isset($GLOBALS['TL_DCA']['tl_heike_preisliste_odt']['fields'][$fieldName]['label']) || !is_array($GLOBALS['TL_DCA']['tl_heike_preisliste_odt']['fields'][$fieldName]['label'])) {
            $GLOBALS['TL_DCA']['tl_heike_preisliste_odt']['fields'][$fieldName]['label'] = array($fieldName, '');
        }

        $GLOBALS['TL_DCA']['tl_heike_preisliste_odt']['fields'][$fieldName]['label'][1] = 'Default: '.$placeholder;
    }
}

$GLOBALS['TL_DCA']['tl_heike_preisliste_odt']['fields']['Beschreibung']['sql'] = "text NULL";



// src/Resources/contao/dca/tl_heike_preisliste_odt.php

// diese Klasse wird benötigt um den Button Callback auf eine route umzulenktn
class tl_heike_preisliste_odt extends Backend
{
    public function recalculatePairPrices(DataContainer $dc): void
    {
        if (!$dc->id) {
            return;
        }

        $db = \Contao\Database::getInstance();
        $row = $db
            ->prepare('SELECT ist_paar, PreisStueckEK FROM tl_heike_preisliste_odt WHERE id=?')
            ->execute($dc->id);

        if (!$row->numRows) {
            return;
        }

        if ((string) $row->ist_paar !== '1') {
            $db
                ->prepare("UPDATE tl_heike_preisliste_odt SET PreisPaarEK='', PreisPaar2_5='', PreisPaar2_3='' WHERE id=?")
                ->execute($dc->id);

            return;
        }

        $preisStueckEk = $this->parseDecimal((string) $row->PreisStueckEK);
        if ($preisStueckEk <= 0) {
            return;
        }

        $preisPaarEk = $preisStueckEk * 2;
        $db
            ->prepare('UPDATE tl_heike_preisliste_odt SET PreisPaarEK=?, PreisPaar2_5=?, PreisPaar2_3=? WHERE id=?')
            ->execute(
                number_format($preisPaarEk, 2, '.', ''),
                number_format(ceil($preisPaarEk * 2.5), 2, '.', ''),
                number_format(ceil($preisPaarEk * 2.3), 2, '.', ''),
                $dc->id
            );
    }

    private function parseDecimal(string $value): float
    {
        $value = trim($value);

        if ($value === '') {
            return 0.0;
        }

        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : 0.0;
    }

    /**
     * Erzeugt den CSV-Import-Button mit einer Route.
     */
    public function importCsvButton($row, $href, $label, $title, $icon, $attributes)
    {
        // Den Symfony-Router verwenden, um die URL zu generieren 
        $router = System::getContainer()->get('router');
        //$url = $router->generate('ImportHeikePreislisteController::importAction', [], UrlGeneratorInterface::ABSOLUTE_URL);  // für filetree geht aber nicht
        $url = $router->generate('ImportHeikePreislisteController::importFromCheckbox', [], UrlGeneratorInterface::ABSOLUTE_URL);  // erzeugt wohl die url fuer den Namen
        $class = 'header_csv_import';
        $strRet='<a href="'.$url.'?table=tl_heike_preisliste_odt"  class="' . $class .'" title="' . $title . '"' . $attributes . '>' . $label . '</a> ';
        return $strRet;
    }
    
}



