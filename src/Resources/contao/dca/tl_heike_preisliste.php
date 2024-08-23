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

\Contao\System::loadLanguageFile('tl_heike_preisliste');

/**
 * Table tl_heike_preisliste 
 */
// config/dca/tl_heike_preisliste.php

$GLOBALS['TL_DCA']['tl_heike_preisliste'] = [
    // Config
    'config' => [
        //'dataContainer'               => DC_Table::class,
        //'dataContainer'               => tableList::class,
        'dataContainer'               => 'Table',  // DC_Table verwenden
         'enableVersioning'            => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'Artikel' => 'index'
            ]
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
            'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['importCSV'][0], 
            //'href' => 'key=importCSV', 
			'href'                => '',
            'class' => 'header_csv_import', 
            'attributes' => 'onclick="Backend.getScrollOffset();"',
                'button_callback' => ['tl_heike_preisliste', 'importCsvButton'],

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
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['editheader'],
                'href'  => 'act=editheader',
                'icon'  => 'header.gif'
            ],
            'delete' => [
                'label'       => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['delete'],
                'href'        => 'act=delete',
                'icon'        => 'delete.gif',
                'attributes'  => 'onclick="if (!confirm(\'' . 'Wirklich löschen?' . '\')) return false; Backend.getScrollOffset();"'
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ],
        ]
    ],

    // Palettes
    'palettes' => [
        'default' => 'Artikel,Kategorie,Subkategorie,Beschreibung,PreisStueck2_5,PreisPaar2_5,PreisStueck2_3,PreisPaar2_3,PreisStueckEK,PreisPaarEK;'
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
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['sorting'],
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
			//'label'                   => @$GLOBALS['TL_LANG']['tl_heike_preisliste']['import_source'],
			'label'                   => 'PBD hallo',
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
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['Artikel'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => true,
            'eval'    => ['mandatory'=>true],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        
        'Kategorie' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['Kategorie'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => true,
            'eval'    => ['mandatory'=>true],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'Subkategorie' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['Subkategorie'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => true,
            'eval'    => ['mandatory'=>true],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'PreisStueck2_5' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['PreisStueck25'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => false,
            'eval'    => ['rgxp'=>'digit'],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'PreisPaar2_5' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['PreisPaar25'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => false,
            'eval'    => ['rgxp'=>'digit'],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'PreisStueck2_3' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['PreisStueck23'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => false,
            'eval'    => ['rgxp'=>'digit'],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'PreisPaar2_3' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['PreisPaar23'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => false,
            'eval'    => ['rgxp'=>'digit'],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'PreisStueckEK' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['PreisStueckEK'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => false,
            'eval'    => ['rgxp'=>'digit'],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'PreisPaarEK' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['PreisPaarEK'],
            'inputType' => 'text',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => false,
            'eval'    => ['rgxp'=>'digit'],
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
        'Beschreibung' => [
            'label'   => &$GLOBALS['TL_LANG']['tl_heike_preisliste']['Beschreibung'],
            'inputType' => 'textarea',
            'exclude' => false,
            'search'  => true,
            'sorting' => true,
            'filter'  => true,
            'eval'    => ['mandatory'=>false, 'default'=>'Initialer Wert'], // Set default value
            'sql'     => "varchar(255) NOT NULL default ''"
        ],
    ],
];



// src/Resources/contao/dca/tl_heike_preisliste.php

// diese Klasse wird benötigt um den Button Callback auf eine route umzulenktn
class tl_heike_preisliste extends Backend
{
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
        $strRet='<a href="'.$url.'?table=tl_heike_preisliste"  class="' . $class .'" title="' . $title . '"' . $attributes . '>' . $label . '</a> ';
        return $strRet;
    }
    
    /*
     * nachdem der Datensatz gespeichert ist,werden die defaultwerte noch gesetzt.
     */
    public function stattic setDefaultValues(DataContainer $dc)
    {
//die ('setDefaultValues gerufen');
        // Setze tstamp auf den aktuellen Zeitstempel, falls es nicht gesetzt ist
        if (!$dc->activeRecord->tstamp) {
//die ("set defualt tstamp");
        }
/*
        // Automatisches Setzen von sorting basierend auf der höchsten Sortierreihenfolge
        if (!$dc->activeRecord->sorting) {
            $maxSorting = $this->Database->prepare("SELECT MAX(sorting) AS sorting FROM tl_heike_preisliste WHERE pid=?")
                ->execute($dc->activeRecord->pid)
                ->sorting;

            $this->Database->prepare("UPDATE tl_heike_preisliste SET sorting=? WHERE id=?")
                ->execute($maxSorting + 1, $dc->id);
        }

        // Setze pid, falls es nicht gesetzt ist
        if (!$dc->activeRecord->pid) {
            $defaultPid = 1; // Standard-PID-Wert
            $this->Database->prepare("UPDATE tl_heike_preisliste SET pid=? WHERE id=?")
                ->execute($defaultPid, $dc->id);
        }
*/
    }

}



