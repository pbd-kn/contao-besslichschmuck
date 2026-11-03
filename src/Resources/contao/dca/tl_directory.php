<?php
use Contao\DataContainer;
// Keep migration state with its directory; no installation-specific IDs in source code.
$GLOBALS['TL_DCA']['tl_directory']['fields']['besslichPriceActive'] = [
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_directory']['fields']['besslichPricePreferred'] = [
    'sql' => 'blob NULL',
];
