<?php

declare(strict_types=1);
namespace Pbdkn\ContaoBesslichschmuck\Backend;
use Contao\BackendModule;
use Contao\BackendUser;
use Contao\System;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Pbdkn\ContaoBesslichschmuck\Util\PriceCsvImport;
use Pbdkn\ContaoBesslichschmuck\Util\WebsiteCsvFiles;
use Symfony\Component\Security\Csrf\CsrfToken;
final class PriceCsvBackend extends BackendModule
{
    protected $strTemplate = 'be_heike_price_csv';
    protected function compile(): void
    {
        if (!BackendUser::getInstance()->isAdmin) { throw new AccessDeniedException('Der Preislistenimport ist nur für Administratoren verfügbar.'); }
        $container = System::getContainer();
        $request = $container->get('request_stack')->getCurrentRequest();
        $session = $request->getSession();
        $csrf = $container->get('contao.csrf.token_manager');
        $import = new PriceCsvImport($container->get('database_connection'));
        $projectDir = $container->getParameter('kernel.project_dir');
        $csvFiles = WebsiteCsvFiles::listing($projectDir);
        $selectedFile = (string)$request->request->get('csv_path', '');
        $preview = null; $message = $error = '';
        if ($request->isMethod('POST') && $request->request->get('FORM_SUBMIT') === 'heike_price_csv') {
            $token = $request->request->get('REQUEST_TOKEN');
            if (!is_string($token) || !$csrf->isTokenValid(new CsrfToken($container->getParameter('contao.csrf_token_name'), $token))) { throw new AccessDeniedException('Ungültiges Anfragetoken.'); }
            try {
                if ($request->request->get('action') === 'execute') {
                    $saved = $session->get('heike_price_csv_preview');
                    if (!is_array($saved) || $request->request->get('confirmed') !== '1' || !hash_equals($saved['nonce'], (string)$request->request->get('nonce'))) { throw new \RuntimeException('Bitte zuerst die Vorschau prüfen und bestätigen.'); }
                    $count = $import->execute($saved['plan']);
                    $session->remove('heike_price_csv_preview');
                    $message = $count.' Datensätze importiert und veröffentlicht. Die neue Preisliste ist sofort für die Website verfügbar.';
                } else {
                    $session->remove('heike_price_csv_preview');
                    $contents = WebsiteCsvFiles::read($projectDir, $selectedFile);
                    $preview = $import->preview($contents, (string)$request->request->get('mode'), (string)$request->request->get('delimiter', 'auto'));
                    $preview['sourceFile'] = $selectedFile;
                    $nonce = bin2hex(random_bytes(16));
                    $session->set('heike_price_csv_preview', ['nonce'=>$nonce,'plan'=>$preview]);
                }
            } catch (\Throwable $e) { $error=$e->getMessage(); $session->remove('heike_price_csv_preview'); }
        }
        $this->Template->csvFiles = $csvFiles;
        $this->Template->selectedFile = $selectedFile;
        $this->Template->preview = $preview;
        $this->Template->nonce = $nonce ?? '';
        $this->Template->message = $message;
        $this->Template->error = $error;
        $this->Template->token = $csrf->getDefaultTokenValue();
    }
}
