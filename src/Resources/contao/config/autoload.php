<?php
// config/autoload.php
class_alias(PBDKN\ContaoBesslichschmuck\Resources\contao\classes\elements\ContentSchmuckartikel::class, 'ContentSchmuckartikel');


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'ce_besslichschmuck' => 'PBDKN/ContaoBesslichschmuck/Resources/contao/templates'
));
?>