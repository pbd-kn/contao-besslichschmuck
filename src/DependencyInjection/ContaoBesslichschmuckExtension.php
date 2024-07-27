<?php
// src/DependencyInjection/ContaoBesslichschmuckExtension.php

namespace Pbdkn\ContaoBesslichschmuck\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContaoBesslichschmuckExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
//echo "PbdknContaoBesslichschmuckExtension load\n";
        $configDir = __DIR__ . '/../Resources/config';
//echo "Loading services.yaml from: $configDir\n";
        
        if (!is_dir($configDir)) {
            echo "Directory does not exist: $configDir";
        }

        $loader = new YamlFileLoader($container, new FileLocator($configDir));
//echo "PbdknContaoBesslichschmuckExtension loader da\n";
        $loader->load('services.yaml');
//echo "PbdknContaoBesslichschmuckExtension services load\n";
        // Wenn du die routes.yaml nicht explizit laden musst, kommentiere diese Zeile aus.
        //$loader->load('routes.yaml');
//echo "PbdknContaoBesslichschmuckExtension ende load\n";
    }
}
