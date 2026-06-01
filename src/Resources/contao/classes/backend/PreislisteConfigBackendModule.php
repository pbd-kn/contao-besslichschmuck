<?php

namespace Pbdkn\ContaoBesslichschmuck\Resources\contao\classes\backend;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\System;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PreislisteConfigBackendModule extends Backend
{
    public function generate()
    {
        $router = System::getContainer()->get('router');
        $template = new BackendTemplate('be_preisliste_config');

        $template->headline = 'Preisliste Konfiguration';
        $template->tableUrl = $router->generate('contao_backend', array('do' => 'PreislisteOdt', 'table' => 'tl_heike_preisliste_odt'));
        $template->catalogUrl = $router->generate('besslich_preisliste_catalog');
        $template->calculationUrl = $router->generate('besslich_preisliste_calculation');
        $template->listUrl = $router->generate('besslich_preisliste_list');
        $template->printUrl = $router->generate('besslich_preisliste_print');
        $template->transferUrl = $router->generate('besslich_preisliste_transfer');
        $template->createUrl = $router->generate('contao_backend', array(
            'do' => 'PreislisteOdt',
            'table' => 'tl_heike_preisliste_odt',
            'act' => 'create',
            'rt' => \defined('REQUEST_TOKEN') ? REQUEST_TOKEN : '',
            'ref' => $this->createRefererFor($template->tableUrl),
        ));
        $template->editUrl = $template->tableUrl;
        $template->deleteUrl = $template->tableUrl;
        $template->importUrl = $router->generate('ImportHeikePreislisteController::importFromCheckbox', array('table' => 'tl_heike_preisliste_odt'), UrlGeneratorInterface::ABSOLUTE_PATH);
        $template->globalsUrl = $router->generate('besslich_preisliste_globals');
        $template->rulesUrl = $router->generate('besslich_preisliste_formula_rules');

        return $template->parse();
    }

    private function createRefererFor($url)
    {
        $session = System::getContainer()->get('session');
        $referers = $session->get('referer');

        if (!\is_array($referers)) {
            $referers = array();
        }

        $refererId = bin2hex(random_bytes(16));
        $refererUrl = ltrim($url, '/');

        $referers[$refererId] = array(
            'current' => $refererUrl,
            'last' => $refererUrl,
        );

        $session->set('referer', $referers);

        return $refererId;
    }
}
