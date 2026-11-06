<?php

// Price directory selection and preferred entries for duplicate articles.
$GLOBALS['TL_DCA']['tl_directory']['fields']['besslichPriceActive'] = [
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_directory']['fields']['besslichPricePreferred'] = [
    'sql' => 'blob NULL',
];
