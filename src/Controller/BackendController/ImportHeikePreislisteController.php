<?php
namespace Pbdkn\ContaoBesslichschmuck\Controller\BackendController;
use Contao\CoreBundle\Controller\AbstractBackendController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/** Legacy import links now lead to the active directory price-list importer. */
class ImportHeikePreislisteController extends AbstractBackendController
{
    /** @Route("/besslich/importCSV", name="ImportHeikePreislisteController::importAction", methods={"GET", "POST"}) */
    public function importAction(Request $request): Response
    {
        return new RedirectResponse($this->generateUrl('contao_backend', ['do'=>'heike_price_csv']));
    }
    /** @Route("/besslich/importFromCheckbox", name="ImportHeikePreislisteController::importFromCheckbox", methods={"GET", "POST"}) */
    public function importFromCheckbox(Request $request): Response
    {
        return new RedirectResponse($this->generateUrl('contao_backend', ['do'=>'heike_price_csv']));
    }
}
