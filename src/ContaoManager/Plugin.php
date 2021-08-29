<?php

declare(strict_types=1);


namespace PBDKN\ContaoBesslichschmuck\ContaoManager;


use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use PBDKN\ContaoBesslichschmuck\ContaoBesslichschmuck;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ContaoBesslichschmuck::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ,        ];
    }
}
