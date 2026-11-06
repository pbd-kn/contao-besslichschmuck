<?php

namespace Pbdkn\ContaoBesslichschmuck\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

#[AsHook('generatePage')]
final class FrontendStylesListener
{
    public function __invoke(): void
    {
        $GLOBALS['TL_CSS']['besslichschmuck-gallery'] = 'bundles/contaobesslichschmuck/css/festyle.css';
    }
}
