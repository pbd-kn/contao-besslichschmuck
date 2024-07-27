<?php

declare(strict_types=1);


namespace Pbdkn\ContaoBesslichschmuck\ContaoManager;


use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Pbdkn\ContaoBesslichschmuck\ContaoBesslichschmuckBundle;

class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
    //echo "PBD ContaoBesslichschmuck Plugin getBundles ";
        return [
            BundleConfig::create(ContaoBesslichschmuckBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),                
                ];
    }

    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
    //echo "PBD ContaoBesslichschmuck Plugin getRouteCollection ";
        return $resolver
            ->resolve(__DIR__.'/../Resources/config/routes.yaml')
            ->load   (__DIR__.'/../Resources/config/routes.yaml')        ;
    }        
    
}
