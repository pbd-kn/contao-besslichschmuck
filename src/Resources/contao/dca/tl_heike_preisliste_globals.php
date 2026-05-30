<?php

use Contao\DataContainer;

\Contao\System::loadLanguageFile('tl_heike_preisliste_globals');

$GLOBALS['TL_DCA']['tl_heike_preisliste_globals'] = array(
    'config' => array(
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'sql' => array(
            'keys' => array(
                'id' => 'primary',
                'param_key' => 'unique',
            ),
        ),
    ),
    'list' => array(
        'sorting' => array(
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => array('param_key'),
            'panelLayout' => 'filter;search,sort,limit',
        ),
        'label' => array(
            'fields' => array('param_key', 'label', 'param_value', 'unit'),
            'format' => '%s - %s: %s %s',
        ),
        'operations' => array(
            'edit' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_globals']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ),
            'copy' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_globals']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ),
            'delete' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_globals']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'Wirklich loeschen?\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_globals']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ),
        ),
    ),
    'palettes' => array(
        'default' => '{variable_legend},param_key,label,param_value,unit',
    ),
    'fields' => array(
        'id' => array(
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ),
        'pid' => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'sorting' => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'tstamp' => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'param_key' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_globals']['param_key'],
            'exclude' => false,
            'inputType' => 'text',
            'search' => true,
            'sorting' => true,
            'eval' => array('mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'),
            'sql' => "varchar(64) NOT NULL default ''",
        ),
        'label' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_globals']['label'],
            'exclude' => false,
            'inputType' => 'text',
            'search' => true,
            'eval' => array('mandatory' => true, 'maxlength' => 120, 'tl_class' => 'w50'),
            'sql' => "varchar(120) NOT NULL default ''",
        ),
        'param_value' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_globals']['param_value'],
            'exclude' => false,
            'inputType' => 'text',
            'sorting' => true,
            'eval' => array('mandatory' => true, 'tl_class' => 'w50'),
            'sql' => 'decimal(12,4) NOT NULL default 0',
        ),
        'unit' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_globals']['unit'],
            'exclude' => false,
            'inputType' => 'text',
            'eval' => array('maxlength' => 20, 'tl_class' => 'w50'),
            'sql' => "varchar(20) NULL",
        ),
    ),
);
