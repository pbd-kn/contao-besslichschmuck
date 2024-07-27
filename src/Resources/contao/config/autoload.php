<?php
// config/autoload.php
class_alias(Pbdkn\ContaoBesslichschmuck\Resources\contao\classes\elements\ContentSchmuckartikel::class, 'ContentSchmuckartikel');


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'ce_besslichschmuck' => 'Pbdkn/ContaoBesslichschmuck/Resources/contao/templates'
));
?>