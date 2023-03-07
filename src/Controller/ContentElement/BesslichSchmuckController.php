<?php

/**
 * @package   Besslich-Schmuck
 * @author    Peter Broghammer
 * @license   LGPL
 * @copyright Peter Broghammer
 */
 
/**
 * Da die Klasse von \Contao\ContentElement erbt muss sie eine Methode mit dem Namen compile implementieren. 
 * Diese ist für die Ausgabe zuständig. 
 * Ich splitte an dieser Stelle immer in zwei Methoden auf, eine für die Backend- und eine für die Frontend-Ausgabe. 
 * Die Methode genBeOutput ist für die Ausgabe im Backend zuständig. 
 * Es interessiert aber mehr die Methode genFeOutput. 
 * Diese erstellt die Ausgabe für das Frontend. 
 * Die Eigenschaften werden von Contao als serialisiertes Array gespeichert. 
 * In Zeile 60 werden sie deshalb deserialisiert, damit wir sie nutzen können. 
 * Wir speichern das Array in $this->Template->arrProperties und haben dann im Template über $this->arrProperties zugriff darauf.
 */
 
namespace PBDKN\ContaoBesslichschmuck\Resources\contao\classes\elements;
use PBDKN\ContaoBesslichschmuck\Resources\contao\classes\GC_Helper;
namespace PBDKN\Efgco4\Resources\ContaoBesslichschmuck\classes\BLog;
declare(strict_types=1);


use Contao\Config;
use Contao\ContentModel;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\CoreBundle\String\HtmlDecoder;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\PageModel;
use Contao\Pagination;
use Contao\StringUtil;
use Contao\Template;
use Doctrine\DBAL\Driver\Exception as DoctrineDBALDriverException;
use Doctrine\DBAL\Exception as DoctrineDBALException;
use FOS\HttpCacheBundle\Http\SymfonyResponseTagger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * @ContentElement(BesslichSchmuckController::TYPE, category="Besslich Schmuck")
 */

class BesslichSchmuckController extends AbstractGalleryCreatorController
{
    public const TYPE = 'besslichschmuckNew';

    protected ContaoFramework $framework;
    protected TwigEnvironment $twig;
    protected HtmlDecoder $htmlDecoder;
    protected ?SymfonyResponseTagger $responseTagger;   // responseTagger entweder der Typ oder null
    protected ?string $viewMode = null;
    protected array $arrAlbumListing = [];
    protected ?ContentModel $model;
    protected ?PageModel $pageModel;
    
        // Adapters
    protected Adapter $config;
    protected Adapter $environment;
    protected Adapter $input;
    protected Adapter $stringUtil;

    public function __construct(DependencyAggregate $dependencyAggregate, ContaoFramework $framework, TwigEnvironment $twig, HtmlDecoder $htmlDecoder, ?SymfonyResponseTagger $responseTagger)
    {
        Blog::setDebugmode(255);
        parent::__construct($dependencyAggregate);
        $this->framework = $framework;
        $this->twig = $twig;
        $this->htmlDecoder = $htmlDecoder;
        $this->responseTagger = $responseTagger;

        // Adapters
        $this->config = $this->framework->getAdapter(Config::class);
        $this->environment = $this->framework->getAdapter(Environment::class);
        $this->input = $this->framework->getAdapter(Input::class);
        $this->stringUtil = $this->framework->getAdapter(StringUtil::class);
    }
        /**
     * @throws DoctrineDBALException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(Request $request, ContentModel $model, string $section, array $classes = null, PageModel $pageModel = null): Response
    {
        WriteLog($level, $method, $line, $value)
        BLog::EfgwriteLog(1, __METHOD__, __LINE__, '-> _invoke');
        // Do not parse the content element in the backend
        if ($this->scopeMatcher->isBackendRequest($request)) {
            return new Response(
                $this->twig->render('@MarkocupicGalleryCreator/Backend/backend_element_view.html.twig', [])
            );
        }
        
        $this->model = $model;
        $this->pageModel = $pageModel;

    }

 
    /**
     * Erzeugt die Ausgebe für das Frontend.
     * @return string
     */
    private function genFeOutput()
    {
        if ($this->preisliste != '') {
            // prüfen ob inhalt da
            $arr =  deserialize($this->preisliste, true);
            foreach ($arr as $str) {
              if ($str != "") {
                $this->Template->arrpreisliste = deserialize($this->preisliste, true);
              }
            }
        }
    }
}

?>