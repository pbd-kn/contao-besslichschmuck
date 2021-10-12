<?php
// config/autoload.php
/*
 * In Zeile 26 registrieren wir unsere Klasse und in Zeile 34 das Template. 
 */
/**
 * Variables
 */
/* !!! Achtung ganz wichtig !! der Namespace darf keine Sonderzeichen auch kein - enthalten
 * sonst wird die Klasse nicht gefunden
 * A valid variable name starts with a letter or underscore, followed by any number of letters, numbers, or underscores
 * Kein - Zeichen
 */

$strFolder      = 'besslichschmuck';                // name im Moduleverzeichnis
$strNamespace   = 'besslich\\' . $strFolder;         // unter diesem Namespace liegt der Artikelcode des ce
 
/**
 * Register the namespaces
 */
/*
ClassLoader::addNamespaces(array
(
    $strNamespace
));
*/ 
/**
 * Register the classes
 * $strFolder      = 'esitcontent';
$strNamespace   = 'esit\\' . $strFolder;
 */
/*
ClassLoader::addClasses(array
(
    // Elements
    $strNamespace . '\\classes\\elements\\ContentSchmuckartikel' => "system/modules/$strFolder/classes/elements/ContentSchmuckartikel.php"
));    
*/ 
class_alias(PBDKN\ContaoBesslichschmuck\Resources\contao\classes\elements\ContentSchmuckartikel::class, 'ContentSchmuckartikel');


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'ce_besslichschmuck' => 'PBDKN/ContaoBesslichschmuck/Resources/contao/templates'
));
?>