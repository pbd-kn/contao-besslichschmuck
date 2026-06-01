<?php

use Contao\DataContainer;

\Contao\System::loadLanguageFile('tl_heike_preisliste_formula_rules');

$GLOBALS['TL_DCA']['tl_heike_preisliste_formula_rules'] = array(
    'config' => array(
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'sql' => array(
            'keys' => array(
                'id' => 'primary',
                'artikel_code,target_field' => 'unique',
            ),
        ),
    ),
    'list' => array(
        'sorting' => array(
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => array('artikel_code', 'target_field'),
            'panelLayout' => 'filter;search,sort,limit',
        ),
        'label' => array(
            'fields' => array('artikel_code', 'target_field', 'formula'),
            'format' => '%s - %s: %s',
        ),
        'operations' => array(
            'edit' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_formula_rules']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ),
            'copy' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_formula_rules']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ),
            'delete' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_formula_rules']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'Wirklich loeschen?\')) return false; Backend.getScrollOffset();"'
            ),
            'show' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_formula_rules']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ),
        ),
    ),
    'palettes' => array(
        'default' => '{rule_legend},artikel_code,target_field,formula,label,active',
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
        'artikel_code' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_formula_rules']['artikel_code'],
            'exclude' => false,
            'inputType' => 'text',
            'search' => true,
            'sorting' => true,
            'eval' => array('maxlength' => 150, 'tl_class' => 'w50'),
            'sql' => "varchar(150) NOT NULL default ''",
        ),
        'target_field' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_formula_rules']['target_field'],
            'exclude' => false,
            'inputType' => 'select',
            'options' => array(
                'ag_incl_verlust',
                'au_incl_verlust',
                'material',
                'arbeitszeit',
                'stueck_1_messe',
                'stueck_2_messe',
                'plattierung_oxidation',
                'schnur_2',
                'fixkosten_r5',
                'fixkosten_s',
                'metall_zuschlag_s',
                'ek',
                'factor_25',
                'factor_23',
                'preis_stueck_ek',
                'preis_paar_ek',
                'preis_stueck_2_5',
                'preis_paar_2_5',
                'preis_stueck_2_3',
                'preis_paar_2_3',
            ),
            'search' => true,
            'sorting' => true,
            'eval' => array('mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'),
            'sql' => "varchar(64) NOT NULL default ''",
        ),
        'formula' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_formula_rules']['formula'],
            'exclude' => false,
            'inputType' => 'textarea',
            'search' => true,
            'eval' => array('mandatory' => true, 'rte' => '', 'tl_class' => 'clr'),
            'sql' => 'text NOT NULL',
        ),
        'label' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_formula_rules']['label'],
            'exclude' => false,
            'inputType' => 'text',
            'search' => true,
            'eval' => array('maxlength' => 120, 'tl_class' => 'w50'),
            'sql' => "varchar(120) NULL",
        ),
        'active' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_heike_preisliste_formula_rules']['active'],
            'exclude' => false,
            'inputType' => 'checkbox',
            'filter' => true,
            'eval' => array('tl_class' => 'w50 m12'),
            'sql' => "tinyint(1) unsigned NOT NULL default '1'",
        ),
    ),
);
