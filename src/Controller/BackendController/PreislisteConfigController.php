<?php

namespace Pbdkn\ContaoBesslichschmuck\Controller\BackendController;

use Contao\Ajax;
use Contao\BackendMain;
use Contao\BackendTemplate;
use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Environment;
use Contao\Input;
use Contao\Message;
use Contao\System;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PreislisteConfigController extends AbstractBackendController
{
    private const GLOBAL_PARAMS_TABLE = 'tl_heike_preisliste_globals';
    private const FORMULA_RULES_TABLE = 'tl_heike_preisliste_formula_rules';

    private $framework;
    private $connection;

    public function __construct(ContaoFrameworkInterface $framework, Connection $connection)
    {
        $this->framework = $framework;
        $this->connection = $connection;
        $this->framework->initialize();
        System::loadLanguageFile('default');
        System::loadLanguageFile('modules');
    }

    private function renderBackendTemplate(BackendTemplate $template)
    {
        $backendContext = (new class() extends BackendMain {
            public function __invoke()
            {
                $this->Template = new BackendTemplate('be_main');
                $this->Template->version = $GLOBALS['TL_LANG']['MSC']['version'].' '.ContaoCoreBundle::getVersion();

                if ($_POST && Environment::get('isAjaxRequest')) {
                    $this->objAjax = new Ajax(Input::post('action'));
                    $this->objAjax->executePreActions();
                }

                $this->Template->setData($this->compileTemplateData($this->Template->getData()));
                $this->Template->getResponse();

                return $this->Template->getData();
            }
        })();

        $template->setData(array_merge($backendContext, $template->getData()));

        return $template->getResponse();
    }

    /**
     * @Route("/contao/besslich/preisliste/catalog", name="besslich_preisliste_catalog", methods={"GET", "POST"}, defaults={"_scope"="backend", "_token_check"=true})
     */
    public function catalogAction(Request $request)
    {
        $this->ensurePreislisteSchema();

        if (Input::get('view') === 'edit' || Input::post('action') === 'save') {
            return $this->editAction($request);
        }

        $fields = $this->preislisteFields();
        $q = trim((string) Input::get('q'));
        $sql = 'SELECT id,'.implode(',', $fields).' FROM tl_heike_preisliste_odt';
        $params = array();

        if ($q !== '') {
            $parts = array();

            foreach ($fields as $field) {
                $parts[] = $field.' LIKE ?';
                $params[] = '%'.$q.'%';
            }

            $sql .= ' WHERE '.implode(' OR ', $parts);
        }

        $sql .= ' ORDER BY id ASC LIMIT 5000';
        $rows = $this->connection->fetchAllAssociative($sql, $params);

        $template = new BackendTemplate('be_preisliste_catalog');
        $template->headline = 'Katalogansicht';
        $template->rows = $rows;
        $template->fields = $fields;
        $template->fieldGroups = $this->preislisteFieldGroups();
        $template->fieldLabels = $this->preislisteFieldLabels();
        $template->q = $q;
        $template->backUrl = $this->backendUrl('PreislisteKonfiguration');
        $template->editUrl = $this->backendUrl('Preisliste', array('table' => 'tl_heike_preisliste_odt'));

        return $this->renderBackendTemplate($template);
    }

    /**
     * @Route("/contao/besslich/preisliste/list", name="besslich_preisliste_list", methods={"GET"}, defaults={"_scope"="backend", "_token_check"=true})
     */
    public function listAction(Request $request)
    {
        $this->ensurePreislisteSchema();

        $fields = array('Artikel', 'Kategorie', 'Subkategorie', 'PreisStueck2_5', 'PreisPaar2_5', 'PreisStueck2_3', 'PreisPaar2_3', 'PreisStueckEK', 'PreisPaarEK');
        $rows = $this->connection->fetchAllAssociative('SELECT id,'.implode(',', $fields).' FROM tl_heike_preisliste_odt ORDER BY id DESC LIMIT 500');

        $template = new BackendTemplate('be_preisliste_catalog');
        $template->headline = 'Preisliste - neueste 500';
        $template->rows = $rows;
        $template->fields = $fields;
        $template->q = '';
        $template->backUrl = $this->backendUrl('PreislisteKonfiguration');
        $template->editUrl = $this->backendUrl('Preisliste', array('table' => 'tl_heike_preisliste_odt'));

        return $this->renderBackendTemplate($template);
    }

    /**
     * @Route("/contao/besslich/preisliste/calculation", name="besslich_preisliste_calculation", methods={"GET"}, defaults={"_scope"="backend", "_token_check"=true})
     */
    public function calculationAction(Request $request)
    {
        $this->ensurePreislisteSchema();

        $q = trim((string) Input::get('q'));
        $row = null;
        $rows = array();
        $explain = null;
        $message = '';

        if ($q !== '') {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT id,Artikel,Kategorie,Subkategorie,Beschreibung,PreisStueckEK,PreisPaarEK,PreisStueck2_5,PreisPaar2_5,PreisStueck2_3,PreisPaar2_3 FROM tl_heike_preisliste_odt WHERE Artikel LIKE ? OR Kategorie LIKE ? OR Subkategorie LIKE ? ORDER BY Artikel LIMIT 50',
                array('%'.$q.'%', '%'.$q.'%', '%'.$q.'%')
            );
        }

        if (Input::get('id')) {
            $row = $this->connection->fetchAssociative('SELECT * FROM tl_heike_preisliste_odt WHERE id=?', array((int) Input::get('id'))) ?: null;

            if ($row) {
                $this->ensureGlobalParamsTable();
                $this->ensureFormulaRulesTable();
                try {
                    $explain = $this->buildCalculationExplanation($row);
                } catch (\Throwable $exception) {
                    $message = $exception->getMessage();
                }
            } else {
                $message = 'Datensatz nicht gefunden.';
            }
        }

        $template = new BackendTemplate('be_preisliste_calculation');
        $template->headline = 'Berechnung anzeigen';
        $template->q = $q;
        $template->rows = $rows;
        $template->fieldGroups = $this->preislisteFieldGroups();
        $template->fieldLabels = $this->preislisteFieldLabels();
        $template->row = $row;
        $template->message = $message;
        $template->explain = $explain;
        $template->backUrl = $this->backendUrl('PreislisteKonfiguration');
        $template->globalsUrl = $this->generateUrl('besslich_preisliste_globals');
        $template->rulesUrl = $this->generateUrl('besslich_preisliste_formula_rules');

        return $this->renderBackendTemplate($template);
    }

    public function editAction(Request $request)
    {
        $this->ensurePreislisteSchema();

        $fields = $this->preislisteFields();
        $message = '';
        $isError = false;
        $row = null;
        $rows = array();
        $q = trim((string) Input::get('q'));
        $id = (int) (Input::get('id') ?: Input::post('id'));

        if ($request->isMethod('POST') && Input::post('action') === 'save') {
            try {
                if ($id <= 0) {
                    throw new \RuntimeException('Ungültige ID.');
                }

                $data = array();
                foreach ($fields as $field) {
                    $data[$field] = trim((string) Input::post($field));
                }
                $data = $this->withCalculatedPriceFields($data);

                $set = array();
                $values = array();
                foreach ($data as $field => $value) {
                    $set[] = $field.'=?';
                    $values[] = $value;
                }
                $values[] = $id;

                $this->connection->executeStatement(
                    'UPDATE tl_heike_preisliste_odt SET '.implode(',', $set).' WHERE id=?',
                    $values
                );

                $message = 'Datensatz gespeichert.';
            } catch (\Throwable $exception) {
                $isError = true;
                $message = $exception->getMessage();
            }
        }

        if ($id > 0) {
            $row = $this->connection->fetchAssociative(
                'SELECT id,'.implode(',', $fields).' FROM tl_heike_preisliste_odt WHERE id=?',
                array($id)
            ) ?: null;
            if ($row) {
                $row = $this->withCalculatedPriceFields($row);
            }
        } elseif ($q !== '') {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT id,Artikel,Kategorie,Subkategorie,Beschreibung FROM tl_heike_preisliste_odt WHERE Artikel LIKE ? OR Kategorie LIKE ? OR Subkategorie LIKE ? ORDER BY Artikel LIMIT 50',
                array('%'.$q.'%', '%'.$q.'%', '%'.$q.'%')
            );
        }

        $template = new BackendTemplate('be_preisliste_edit');
        $template->fieldGroups = $this->preislisteFieldGroups();
        $template->fieldLabels = $this->preislisteFieldLabels();
        $template->headline = 'Datensatz ändern';
        $template->fields = $fields;
        $template->row = $row;
        $template->rows = $rows;
        $template->q = $q;
        $template->message = $message;
        $template->isError = $isError;
        $template->backUrl = $this->backendUrl('PreislisteKonfiguration');
        $template->editUrl = $this->generateUrl('besslich_preisliste_catalog', array('view' => 'edit'));

        return $this->renderBackendTemplate($template);
    }

    /**
     * @Route("/contao/besslich/preisliste/print", name="besslich_preisliste_print", methods={"GET"}, defaults={"_scope"="backend", "_token_check"=true})
     */
    public function printAction(Request $request)
    {
        $response = $this->catalogAction($request);
        $response->setContent(str_replace('<body>', '<body onload="window.print()">', $response->getContent()));

        return $response;
    }

    /**
     * @Route("/contao/besslich/preisliste/transfer", name="besslich_preisliste_transfer", methods={"GET", "POST"}, defaults={"_scope"="backend", "_token_check"=true})
     */
    public function transferAction(Request $request)
    {
        $this->ensurePreislisteSchema();

        $result = null;
        $fields = $this->transferablePreislisteFields();

        if ($request->isMethod('POST') && Input::post('action') === 'transfer') {
            try {
                $result = $this->transferOdtToPreisliste($fields);
                Message::addConfirmation(
                    'Übertragung abgeschlossen: '.$result['inserted'].' neu angelegt, '.$result['updated'].' aktualisiert, '.$result['skipped'].' übersprungen.'
                );
            } catch (\Throwable $exception) {
                Message::addError($exception->getMessage());
            }
        }

        $template = new BackendTemplate('be_preisliste_transfer');
        $template->headline = 'In Preisliste übertragen';
        $template->fields = $fields;
        $template->result = $result;
        $template->sourceCount = $this->tableExists('tl_heike_preisliste_odt') ? (int) $this->connection->fetchOne('SELECT COUNT(*) FROM tl_heike_preisliste_odt') : 0;
        $template->targetCount = $this->tableExists('tl_heike_preisliste') ? (int) $this->connection->fetchOne('SELECT COUNT(*) FROM tl_heike_preisliste') : 0;
        $template->backUrl = $this->backendUrl('PreislisteKonfiguration');

        return $this->renderBackendTemplate($template);
    }

    /**
     * @Route("/contao/besslich/preisliste/globals", name="besslich_preisliste_globals", methods={"GET", "POST"}, defaults={"_scope"="backend", "_token_check"=true})
     */
    public function globalsAction(Request $request)
    {
        $this->ensureGlobalParamsTable();
        $editKey = $this->normalizeParamKey((string) Input::get('edit'));

        if ($request->isMethod('POST')) {
            try {
                $action = (string) Input::post('action');
                $paramKey = $this->normalizeParamKey((string) Input::post('param_key'));

                if (!preg_match('/^[A-Z_][A-Z0-9_]*$/', $paramKey)) {
                    throw new \RuntimeException('Variablenname ist ungültig.');
                }

                if ($action === 'delete') {
                    $this->connection->executeStatement('DELETE FROM '.self::GLOBAL_PARAMS_TABLE.' WHERE param_key=?', array($paramKey));
                    Message::addConfirmation('Variable gelöscht: '.$paramKey);
                } else {
                    $label = trim((string) Input::post('label'));
                    $value = $this->parseDecimal((string) Input::post('param_value'));
                    $unit = trim((string) Input::post('unit'));

                    $this->connection->executeStatement(
                        'INSERT INTO '.self::GLOBAL_PARAMS_TABLE.' (tstamp, param_key, label, param_value, unit) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE tstamp=VALUES(tstamp), label=VALUES(label), param_value=VALUES(param_value), unit=VALUES(unit)',
                        array(time(), $paramKey, $label !== '' ? $label : $paramKey, $value, $unit)
                    );

                    Message::addConfirmation('Variable gespeichert: '.$paramKey);
                }
            } catch (\Throwable $exception) {
                Message::addError($exception->getMessage());
            }
        }

        $rows = $this->connection->fetchAllAssociative('SELECT param_key, label, param_value, unit, FROM_UNIXTIME(tstamp) AS updated_at FROM '.self::GLOBAL_PARAMS_TABLE.' ORDER BY param_key');
        $editRow = null;

        if ($editKey !== '') {
            $editRow = $this->connection->fetchAssociative(
                'SELECT param_key, label, param_value, unit FROM '.self::GLOBAL_PARAMS_TABLE.' WHERE param_key=?',
                array($editKey)
            ) ?: null;
        }

        $template = new BackendTemplate('be_preisliste_globals');
        $template->headline = 'Globale Variablen';
        $template->rows = $rows;
        $template->editRow = $editRow;
        $template->defaultRows = $this->defaultGlobalParams();
        $template->globalsUrl = $this->generateUrl('besslich_preisliste_globals');
        $template->backUrl = $this->backendUrl('PreislisteKonfiguration');

        return $this->renderBackendTemplate($template);
    }

    /**
     * @Route("/contao/besslich/preisliste/formula-rules", name="besslich_preisliste_formula_rules", methods={"GET", "POST"}, defaults={"_scope"="backend", "_token_check"=true})
     */
    public function formulaRulesAction(Request $request)
    {
        $this->ensureGlobalParamsTable();
        $this->ensureFormulaRulesTable();
        $editArticleCode = trim((string) Input::get('artikel_code'));
        $editTargetField = trim((string) Input::get('target_field'));

        if ($request->isMethod('POST')) {
            try {
                $action = (string) Input::post('action');
                $articleCode = trim((string) Input::post('artikel_code'));
                $targetField = trim((string) Input::post('target_field'));

                if ($action === 'delete') {
                    $this->connection->executeStatement('DELETE FROM '.self::FORMULA_RULES_TABLE.' WHERE artikel_code=? AND target_field=?', array($articleCode, $targetField));
                    Message::addConfirmation('Formelregel gelöscht.');
                } else {
                    $formula = trim((string) Input::post('formula'));
                    $label = trim((string) Input::post('label'));

                    if ($targetField === '' || $formula === '') {
                        throw new \RuntimeException('Zielfeld und Formel sind Pflichtfelder.');
                    }

                    $this->connection->executeStatement(
                        'INSERT INTO '.self::FORMULA_RULES_TABLE.' (tstamp, artikel_code, target_field, formula, label, active) VALUES (?, ?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE tstamp=VALUES(tstamp), formula=VALUES(formula), label=VALUES(label), active=1',
                        array(time(), $articleCode, $targetField, $formula, $label)
                    );

                    Message::addConfirmation('Formelregel gespeichert.');
                }
            } catch (\Throwable $exception) {
                Message::addError($exception->getMessage());
            }
        }

        $rows = $this->connection->fetchAllAssociative('SELECT artikel_code, target_field, formula, label, active, FROM_UNIXTIME(tstamp) AS updated_at FROM '.self::FORMULA_RULES_TABLE.' ORDER BY artikel_code, target_field');
        $globalRows = $this->connection->fetchAllAssociative('SELECT param_key, label, param_value, unit FROM '.self::GLOBAL_PARAMS_TABLE.' ORDER BY param_key');
        $editRule = null;

        if ($editTargetField !== '') {
            $editRule = $this->connection->fetchAssociative(
                'SELECT artikel_code, target_field, formula, label FROM '.self::FORMULA_RULES_TABLE.' WHERE artikel_code=? AND target_field=?',
                array($editArticleCode, $editTargetField)
            ) ?: null;
        }

        $template = new BackendTemplate('be_preisliste_formula_rules');
        $template->headline = 'Formelregeln';
        $template->rows = $rows;
        $template->editRule = $editRule;
        $template->targetFields = $this->defaultRuleFields();
        $template->targetLabels = $this->formulaRuleLabels();
        $template->formulaHints = $this->formulaHints();
        $template->availableVariables = $this->availableFormulaVariables();
        $template->globalRows = $globalRows;
        $template->globalsUrl = $this->generateUrl('besslich_preisliste_globals');
        $template->rulesUrl = $this->generateUrl('besslich_preisliste_formula_rules');
        $template->backUrl = $this->backendUrl('PreislisteKonfiguration');

        return $this->renderBackendTemplate($template);
    }

    private function ensureGlobalParamsTable()
    {
        $this->connection->executeStatement('
            CREATE TABLE IF NOT EXISTS '.self::GLOBAL_PARAMS_TABLE.' (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                pid INT UNSIGNED NOT NULL DEFAULT 0,
                sorting INT UNSIGNED NOT NULL DEFAULT 0,
                tstamp INT UNSIGNED NOT NULL DEFAULT 0,
                param_key VARCHAR(64) NOT NULL,
                label VARCHAR(120) NOT NULL,
                param_value DECIMAL(12,4) NOT NULL,
                unit VARCHAR(20) NULL,
                UNIQUE KEY uq_param_key (param_key)
            )
        ');

        if ($this->tableExists('global_params') && !$this->hasRows(self::GLOBAL_PARAMS_TABLE)) {
            $this->connection->executeStatement(
                'INSERT IGNORE INTO '.self::GLOBAL_PARAMS_TABLE.' (tstamp, param_key, label, param_value, unit) SELECT UNIX_TIMESTAMP(COALESCE(updated_at, NOW())), param_key, label, param_value, unit FROM global_params'
            );
        }

        foreach ($this->defaultGlobalParams() as $param) {
            $this->connection->executeStatement(
                'INSERT INTO '.self::GLOBAL_PARAMS_TABLE.' (tstamp, param_key, label, param_value, unit) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE param_key=param_key',
                array(time(), $param[0], $param[1], $param[2], $param[3])
            );
        }
    }

    private function backendUrl($module, array $parameters = array())
    {
        return $this->generateUrl('contao_backend', array_merge(array('do' => $module), $parameters));
    }

    private function ensureFormulaRulesTable()
    {
        $this->connection->executeStatement('
            CREATE TABLE IF NOT EXISTS '.self::FORMULA_RULES_TABLE.' (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                pid INT UNSIGNED NOT NULL DEFAULT 0,
                sorting INT UNSIGNED NOT NULL DEFAULT 0,
                tstamp INT UNSIGNED NOT NULL DEFAULT 0,
                artikel_code VARCHAR(150) NOT NULL DEFAULT \'\',
                target_field VARCHAR(64) NOT NULL,
                formula TEXT NOT NULL,
                label VARCHAR(120) NULL,
                active TINYINT(1) NOT NULL DEFAULT 1,
                UNIQUE KEY uq_formula_rule (artikel_code, target_field)
            )
        ');

        if ($this->tableExists('catalog_formula_rules') && !$this->hasRows(self::FORMULA_RULES_TABLE)) {
            $this->connection->executeStatement(
                'INSERT IGNORE INTO '.self::FORMULA_RULES_TABLE.' (tstamp, artikel_code, target_field, formula, label, active) SELECT UNIX_TIMESTAMP(COALESCE(updated_at, NOW())), artikel_code, target_field, formula, label, active FROM catalog_formula_rules'
            );
        }

        foreach ($this->defaultCatalogFormulaRules() as $field => $formula) {
            $this->connection->executeStatement(
                'INSERT INTO '.self::FORMULA_RULES_TABLE.' (tstamp, artikel_code, target_field, formula, label, active) VALUES (?, \'\', ?, ?, ?, 1) ON DUPLICATE KEY UPDATE target_field=target_field',
                array(time(), $field, $formula, 'Default '.$field)
            );
        }
    }

    private function hasRows($tableName)
    {
        return (int) $this->connection->fetchOne('SELECT COUNT(*) FROM '.$tableName) > 0;
    }

    private function preislisteFields()
    {
        return array_merge(array(
            'Artikel',
            'Kategorie',
            'Subkategorie',
            'Beschreibung',
        ), $this->technicalPreislisteFields(), array(
            'PreisStueck2_5',
            'PreisPaar2_5',
            'PreisStueck2_3',
            'PreisPaar2_3',
            'PreisStueckEK',
            'PreisPaarEK',
        ));
    }

    private function technicalPreislisteFields()
    {
        return array(
            'bild',
            'ag_in_g',
            'ag_incl_verlust',
            'au_in_g',
            'au_incl_verlust',
            'zeit_in_h',
            'artikel_zusatz',
            'stueck_1',
            'steine_perlen_ek',
            'steine_messe',
            'artikel_2',
            'stueck_2',
            'furnituren_steine_ek',
            'steine_messe_2',
            'plattierung_oxidation',
            'schnur_2',
            'leer_1',
            'leer_2',
            'artikelnr',
            'ek',
            'vkstk_ek_2_5_ungerundet',
            'paarpreis_vk_2_5_ungerundet',
            'nochmals_artikel',
            'vkstk_ek_2_3_ungerundet',
            'vkpaar_ek_2_3_ungerundet',
            'reserve_1',
            'reserve_2',
            'reserve_3',
            'reserve_4',
        );
    }

    private function preislisteFieldGroups()
    {
        return array(
            'Stammdaten' => array('Artikel', 'Kategorie', 'Subkategorie', 'Beschreibung'),
            'Kalkulation Basis' => array('bild', 'ag_in_g', 'ag_incl_verlust', 'au_in_g', 'au_incl_verlust', 'zeit_in_h', 'artikel_zusatz'),
            'Steine, Furnituren, Zuschlaege' => array('stueck_1', 'steine_perlen_ek', 'steine_messe', 'artikel_2', 'stueck_2', 'furnituren_steine_ek', 'steine_messe_2', 'plattierung_oxidation', 'schnur_2'),
            'Preise und Zwischenergebnisse' => array('ek', 'PreisStueckEK', 'PreisPaarEK', 'vkstk_ek_2_5_ungerundet', 'PreisStueck2_5', 'paarpreis_vk_2_5_ungerundet', 'PreisPaar2_5', 'vkstk_ek_2_3_ungerundet', 'PreisStueck2_3', 'vkpaar_ek_2_3_ungerundet', 'PreisPaar2_3'),
            'Import / Reserve' => array('artikelnr', 'nochmals_artikel', 'leer_1', 'leer_2', 'reserve_1', 'reserve_2', 'reserve_3', 'reserve_4'),
        );
    }

    private function preislistePairFields()
    {
        return array(
            'PreisPaarEK',
            'paarpreis_vk_2_5_ungerundet',
            'PreisPaar2_5',
            'vkpaar_ek_2_3_ungerundet',
            'PreisPaar2_3',
        );
    }

    private function preislisteFieldLabels()
    {
        return array(
            'Artikel' => 'Artikel',
            'Kategorie' => 'Kategorie',
            'Subkategorie' => 'Subkategorie',
            'Beschreibung' => 'Beschreibung',
            'bild' => 'Bild',
            'ag_in_g' => 'AG in g',
            'ag_incl_verlust' => 'AG inkl. Verlust',
            'au_in_g' => 'AU in g',
            'au_incl_verlust' => 'AU inkl. Verlust',
            'zeit_in_h' => 'Zeit in h',
            'artikel_zusatz' => 'Artikel Zusatz',
            'stueck_1' => 'Stueck 1',
            'steine_perlen_ek' => 'Steine/Perlen EK',
            'steine_messe' => 'Steine Messe',
            'artikel_2' => 'Artikel 2',
            'stueck_2' => 'Stueck 2',
            'furnituren_steine_ek' => 'Furnituren/Steine EK',
            'steine_messe_2' => 'Steine Messe 2',
            'plattierung_oxidation' => 'Plattierung/Oxidation',
            'schnur_2' => 'Schnur 2',
            'leer_1' => 'Leer 1',
            'leer_2' => 'Leer 2',
            'artikelnr' => 'Artikelnummer',
            'ek' => 'EK',
            'PreisStueckEK' => 'Preis Stueck EK',
            'PreisPaarEK' => 'Preis Paar EK',
            'vkstk_ek_2_5_ungerundet' => 'VK Stueck 2,5 ungerundet',
            'PreisStueck2_5' => 'Preis Stueck 2,5',
            'paarpreis_vk_2_5_ungerundet' => 'Paarpreis 2,5 ungerundet',
            'PreisPaar2_5' => 'Preis Paar 2,5',
            'vkstk_ek_2_3_ungerundet' => 'VK Stueck 2,3 ungerundet',
            'PreisStueck2_3' => 'Preis Stueck 2,3',
            'vkpaar_ek_2_3_ungerundet' => 'Paarpreis 2,3 ungerundet',
            'PreisPaar2_3' => 'Preis Paar 2,3',
            'nochmals_artikel' => 'Nochmals Artikel',
            'reserve_1' => 'Reserve 1',
            'reserve_2' => 'Reserve 2',
            'reserve_3' => 'Reserve 3',
            'reserve_4' => 'Reserve 4',
        );
    }

    private function ensurePreislisteSchema()
    {
        $this->ensureGlobalParamsTable();
        $this->ensureFormulaRulesTable();
        $this->ensurePreislisteOdtTable();

        $existingRows = $this->connection->fetchAllAssociative('SHOW COLUMNS FROM tl_heike_preisliste_odt');
        $existing = array();

        foreach ($existingRows as $row) {
            $existing[(string) $row['Field']] = true;
        }

        foreach ($this->preislisteColumnSql() as $field => $sql) {
            if (!isset($existing[$field])) {
                $this->connection->executeStatement('ALTER TABLE tl_heike_preisliste_odt ADD '.$field.' '.$sql);
            }
        }

        $this->backfillPreislisteFromCatalogItems();
    }

    private function ensurePreislisteOdtTable()
    {
        if ($this->tableExists('tl_heike_preisliste_odt')) {
            return;
        }

        $columns = array(
            'id int(10) unsigned NOT NULL auto_increment',
            'tstamp int(10) unsigned NOT NULL default 0',
        );

        foreach ($this->preislisteFields() as $field) {
            $columns[] = $field.' '.$this->preislisteFieldSql($field);
        }

        $this->connection->executeStatement(
            'CREATE TABLE IF NOT EXISTS tl_heike_preisliste_odt ('.
            implode(',', $columns).
            ', PRIMARY KEY (id), KEY Artikel (Artikel(64))) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    private function backfillPreislisteFromCatalogItems()
    {
        if ($this->tableExists('catalog_items')) {
            $this->backfillPreislisteFromSourceTable('catalog_items');
        }

        $sourceDatabase = getenv('PREISLISTE_SOURCE_DB') ?: 'preisliste_db';
        if ($this->databaseTableExists($sourceDatabase, 'catalog_items')) {
            $this->backfillPreislisteFromSourceTable('`'.$sourceDatabase.'`.`catalog_items`');
        }
    }

    private function backfillPreislisteFromSourceTable($sourceTable)
    {
        $this->insertMissingPreislisteRowsFromSourceTable($sourceTable);

        $assignments = array();
        foreach ($this->catalogToPreislisteFieldMap() as $catalogField => $preislisteField) {
            if (!$this->columnExists($sourceTable, $catalogField) || !$this->columnExists('tl_heike_preisliste_odt', $preislisteField)) {
                continue;
            }

            if (in_array($preislisteField, $this->preislistePairFields(), true)) {
                $assignments[] = 't.'.$preislisteField.' = c.'.$catalogField;
            } else {
                $assignments[] = 't.'.$preislisteField.' = IF(t.'.$preislisteField." IS NULL OR t.".$preislisteField." = '', c.".$catalogField.', t.'.$preislisteField.')';
            }
        }

        if (!$assignments) {
            return;
        }

        $joinConditions = array();
        foreach (array('artikel_code', 'artikel') as $articleField) {
            if ($this->columnExists($sourceTable, $articleField)) {
                $joinConditions[] = 'LOWER(t.Artikel) = LOWER(c.'.$articleField.')';
            }
        }

        if (!$joinConditions) {
            return;
        }

        $this->connection->executeStatement(
            'UPDATE tl_heike_preisliste_odt t INNER JOIN '.$sourceTable.' c ON '.implode(' OR ', $joinConditions).' SET '.implode(', ', $assignments)
        );
    }

    private function insertMissingPreislisteRowsFromSourceTable($sourceTable)
    {
        $articleSourceField = $this->columnExists($sourceTable, 'artikel_code') ? 'artikel_code' : null;
        if ($articleSourceField === null && $this->columnExists($sourceTable, 'artikel')) {
            $articleSourceField = 'artikel';
        }

        if ($articleSourceField === null) {
            return;
        }

        $fieldMap = array_merge(
            array(
                $articleSourceField => 'Artikel',
                'kategorie' => 'Kategorie',
                'subkategorie' => 'Subkategorie',
                'beschreibung' => 'Beschreibung',
            ),
            $this->catalogToPreislisteFieldMap()
        );

        $targetFields = array('tstamp');
        $sourceExpressions = array((string) time());

        foreach ($fieldMap as $sourceField => $targetField) {
            if (!$this->columnExists($sourceTable, $sourceField) || !$this->columnExists('tl_heike_preisliste_odt', $targetField)) {
                continue;
            }

            $targetFields[] = $targetField;
            $sourceExpressions[] = 'c.'.$sourceField;
        }

        if (!in_array('Artikel', $targetFields, true)) {
            return;
        }

        $this->connection->executeStatement(
            'INSERT INTO tl_heike_preisliste_odt ('.implode(',', $targetFields).') '.
            'SELECT '.implode(',', $sourceExpressions).' FROM '.$sourceTable.' c '.
            'WHERE c.'.$articleSourceField." IS NOT NULL AND c.".$articleSourceField." <> '' ".
            'AND NOT EXISTS (SELECT 1 FROM tl_heike_preisliste_odt t WHERE LOWER(t.Artikel) = LOWER(c.'.$articleSourceField.'))'
        );
    }

    private function transferablePreislisteFields()
    {
        if (!$this->tableExists('tl_heike_preisliste')) {
            return array();
        }

        System::loadLanguageFile('tl_heike_preisliste');
        \Contao\Controller::loadDataContainer('tl_heike_preisliste');

        $dcaFields = array_keys($GLOBALS['TL_DCA']['tl_heike_preisliste']['fields'] ?? array());
        $sourceColumns = $this->tableColumns('tl_heike_preisliste_odt');
        $targetColumns = $this->tableColumns('tl_heike_preisliste');
        $fields = array();

        foreach ($dcaFields as $field) {
            if ($field === 'id' || !isset($targetColumns[$field])) {
                continue;
            }

            if ($field === 'tstamp') {
                $fields[] = $field;
                continue;
            }

            if (isset($sourceColumns[$field])) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    private function transferOdtToPreisliste(array $fields)
    {
        if (!$this->tableExists('tl_heike_preisliste')) {
            throw new \RuntimeException('Die Zieltabelle tl_heike_preisliste existiert nicht.');
        }

        if (!in_array('Artikel', $fields, true)) {
            throw new \RuntimeException('Das Feld Artikel ist nicht in beiden Tabellen vorhanden.');
        }

        $sourceFields = array_values(array_filter($fields, function ($field) {
            return $field !== 'tstamp';
        }));

        $rows = $this->connection->fetchAllAssociative(
            'SELECT '.implode(',', array_map(array($this, 'quoteIdentifier'), $sourceFields)).' FROM tl_heike_preisliste_odt ORDER BY id'
        );

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $article = trim((string) ($row['Artikel'] ?? ''));
            if ($article === '') {
                ++$skipped;
                continue;
            }

            $data = array();
            foreach ($fields as $field) {
                $data[$field] = $field === 'tstamp' ? time() : ($row[$field] ?? '');
            }
            if (!$this->isPairArticle($data)) {
                foreach ($this->preislistePairFields() as $pairField) {
                    if (array_key_exists($pairField, $data)) {
                        $data[$pairField] = '';
                    }
                }
            }

            $existingId = $this->connection->fetchOne(
                'SELECT id FROM tl_heike_preisliste WHERE Artikel=? ORDER BY id LIMIT 1',
                array($article)
            );

            if ($existingId) {
                $set = array();
                $values = array();
                foreach ($data as $field => $value) {
                    $set[] = $this->quoteIdentifier($field).'=?';
                    $values[] = $value;
                }
                $values[] = (int) $existingId;

                $this->connection->executeStatement(
                    'UPDATE tl_heike_preisliste SET '.implode(',', $set).' WHERE id=?',
                    $values
                );
                ++$updated;
            } else {
                $fieldNames = array_keys($data);
                $this->connection->executeStatement(
                    'INSERT INTO tl_heike_preisliste ('.implode(',', array_map(array($this, 'quoteIdentifier'), $fieldNames)).') VALUES ('.implode(',', array_fill(0, count($fieldNames), '?')).')',
                    array_values($data)
                );
                ++$inserted;
            }
        }

        $this->clearTargetPairFieldsWithoutOdtPairEk();

        return array('inserted' => $inserted, 'updated' => $updated, 'skipped' => $skipped);
    }

    private function clearTargetPairFieldsWithoutOdtPairEk()
    {
        if (!$this->tableExists('tl_heike_preisliste') || !$this->tableExists('tl_heike_preisliste_odt')) {
            return;
        }

        if (!$this->columnExists('tl_heike_preisliste', 'Artikel') || !$this->columnExists('tl_heike_preisliste_odt', 'Artikel') || !$this->columnExists('tl_heike_preisliste_odt', 'PreisPaarEK')) {
            return;
        }

        $set = array();
        foreach ($this->preislistePairFields() as $field) {
            if ($this->columnExists('tl_heike_preisliste', $field)) {
                $set[] = 't.'.$this->quoteIdentifier($field)."=''";
            }
        }

        if (!$set) {
            return;
        }

        $this->connection->executeStatement(
            'UPDATE tl_heike_preisliste t INNER JOIN tl_heike_preisliste_odt o ON LOWER(t.Artikel) = LOWER(o.Artikel) '.
            'SET '.implode(',', $set).' '.
            "WHERE TRIM(COALESCE(o.PreisPaarEK, '')) = ''"
        );
    }

    private function catalogToPreislisteFieldMap()
    {
        return array(
            'bild' => 'bild',
            'ag_in_g' => 'ag_in_g',
            'ag_incl_verlust' => 'ag_incl_verlust',
            'au_in_g' => 'au_in_g',
            'au_incl_verlust' => 'au_incl_verlust',
            'zeit_in_h' => 'zeit_in_h',
            'artikel_zusatz' => 'artikel_zusatz',
            'stueck_1' => 'stueck_1',
            'steine_perlen_ek' => 'steine_perlen_ek',
            'steine_messe' => 'steine_messe',
            'artikel_2' => 'artikel_2',
            'stueck_2' => 'stueck_2',
            'furnituren_steine_ek' => 'furnituren_steine_ek',
            'steine_messe_2' => 'steine_messe_2',
            'plattierung_oxidation' => 'plattierung_oxidation',
            'schnur_2' => 'schnur_2',
            'leer_1' => 'leer_1',
            'leer_2' => 'leer_2',
            'artikelnr' => 'artikelnr',
            'ek' => 'ek',
            'preis_stueck_ek' => 'PreisStueckEK',
            'preis_paar_ek' => 'PreisPaarEK',
            'vkstk_ek_2_5_ungerundet' => 'vkstk_ek_2_5_ungerundet',
            'preis_stueck_2_5' => 'PreisStueck2_5',
            'paarpreis_vk_2_5_ungerundet' => 'paarpreis_vk_2_5_ungerundet',
            'preis_paar_2_5' => 'PreisPaar2_5',
            'nochmals_artikel' => 'nochmals_artikel',
            'vkstk_ek_2_3_ungerundet' => 'vkstk_ek_2_3_ungerundet',
            'preis_stueck_2_3' => 'PreisStueck2_3',
            'vkpaar_ek_2_3_ungerundet' => 'vkpaar_ek_2_3_ungerundet',
            'preis_paar_2_3' => 'PreisPaar2_3',
            'reserve_1' => 'reserve_1',
            'reserve_2' => 'reserve_2',
            'reserve_3' => 'reserve_3',
            'reserve_4' => 'reserve_4',
        );
    }

    private function columnExists($table, $column)
    {
        try {
            return (bool) $this->connection->fetchOne('SHOW COLUMNS FROM '.$table.' LIKE ?', array($column));
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function databaseTableExists($database, $table)
    {
        try {
            return (bool) $this->connection->fetchOne(
                'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=? AND table_name=?',
                array($database, $table)
            );
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function preislisteColumnSql()
    {
        $columns = array(
            'tstamp' => 'int(10) unsigned NOT NULL default 0',
        );

        foreach ($this->preislisteFields() as $field) {
            $columns[$field] = $this->preislisteFieldSql($field);
        }

        return $columns;
    }

    private function preislisteFieldSql($field)
    {
        if ($field === 'Beschreibung') {
            return 'text NULL';
        }

        return "varchar(255) NOT NULL default ''";
    }

    private function defaultGlobalParams()
    {
        return array(
            array('A3', 'Materialpreis AG925 EUR/g', 7.3, 'EUR/g'),
            array('A5', 'Materialpreis AU750 EUR/g', 141.0, 'EUR/g'),
            array('A7', 'Materialpreis WG750 EUR/g', 142.2, 'EUR/g'),
            array('B3', 'Arbeitszeitpreis EUR/h', 35.0, 'EUR/h'),
            array('C3', 'Verlust AG', 0.1, '%'),
            array('C5', 'Verlust AU/WG', 0.03, '%'),
            array('D13', 'Plattierung/Oxidation Basiswert', 1.0, 'EUR'),
            array('E3', 'Furnitur GG 750/333', 19.9, 'EUR'),
            array('F7', 'Furnitur AG Wert', 4.2, 'EUR'),
            array('H5', 'Schnur Basiswert', 2.0, 'EUR'),
            array('MULT_VK', 'Verkaufsfaktor 2.5', 2.5, 'factor'),
            array('R3', 'Fixkosten R3', 0.85, 'EUR'),
            array('R5', 'Fixkosten R5', 1.0, 'EUR'),
            array('S3', 'Fixkosten S3', 1.0, 'EUR'),
            array('S7', 'Metallzuschlag AG', 0.6, 'factor'),
            array('S9', 'Fixkosten S9', 3.0, 'EUR'),
            array('S11', 'Metallzuschlag AU/WG', 0.8, 'factor'),
        );
    }

    private function formulaRuleLabels()
    {
        return array(
            'ag_incl_verlust' => 'AG inkl. Verlust',
            'au_incl_verlust' => 'AU inkl. Verlust',
            'material' => 'Material',
            'arbeitszeit' => 'Arbeitszeit',
            'stueck_1_messe' => 'Stueck 1 / Messe',
            'stueck_2_messe' => 'Stueck 2 / Messe',
            'plattierung_oxidation' => 'Plattierung / Oxidation',
            'schnur_2' => 'Schnur 2',
            'fixkosten_r5' => 'Fixkosten R5',
            'fixkosten_s' => 'Fixkosten S',
            'metall_zuschlag_s' => 'Metall-Zuschlag S',
            'ek' => 'EK',
            'factor_25' => 'Faktor VK 2,5',
            'factor_23' => 'Faktor VK 2,3',
            'preis_stueck_ek' => 'Preis Stueck EK',
            'preis_paar_ek' => 'Preis Paar EK',
            'vkstk_ek_2_5_ungerundet' => 'VK Stueck 2,5 ungerundet',
            'preis_stueck_2_5' => 'Preis Stueck 2,5',
            'paarpreis_vk_2_5_ungerundet' => 'Paarpreis 2,5 ungerundet',
            'preis_paar_2_5' => 'Preis Paar 2,5',
            'vkstk_ek_2_3_ungerundet' => 'VK Stueck 2,3 ungerundet',
            'preis_stueck_2_3' => 'Preis Stueck 2,3',
            'vkpaar_ek_2_3_ungerundet' => 'VK Paar 2,3 ungerundet',
            'preis_paar_2_3' => 'Preis Paar 2,3',
        );
    }

    private function formulaHints()
    {
        return array(
            'ag_incl_verlust' => 'Normal: ag_in_g * (1 + C3), wenn AG inkl. Verlust nicht direkt im Artikel steht.',
            'au_incl_verlust' => 'Normal: au_in_g * (1 + C5), wenn AU/WG inkl. Verlust nicht direkt im Artikel steht.',
            'material' => 'Verwendet je nach Artikel A3 (AG), A5 (AU) oder A7 (WG).',
            'arbeitszeit' => 'Verwendet zeit_in_h und B3.',
            'stueck_1_messe' => 'Verwendet stueck_1 und steine_messe; falls leer, steine_perlen_ek * 1.5.',
            'stueck_2_messe' => 'Verwendet stueck_2 und steine_messe_2; falls leer, furnituren_steine_ek * 1.5.',
            'fixkosten_r5' => 'Verwendet R5 bei Silber, nicht bei Gold/Ohrsteckern.',
            'fixkosten_s' => 'Verwendet R3 + S3 bei Ohrsteckern oder S9 bei Gold.',
            'metall_zuschlag_s' => 'Verwendet S7 bei Silber oder S11 bei bestimmten Gold-Artikeln.',
            'ek' => 'Summiert Material, Arbeitszeit, Steine/Furnituren, Plattierung, Schnur und Zuschlaege.',
            'preis_stueck_ek' => 'Standard ist default_preis_stueck_ek, also aufgerundeter EK.',
            'preis_paar_ek' => 'Standard ist der importierte Wert aus PreisPaarEK. Wenn PreisPaarEK leer ist, ist es ein Einzelartikel.',
            'preis_stueck_2_5' => 'Standard ist default_preis_stueck_2_5, also aufgerundeter VK 2,5.',
            'preis_paar_2_5' => 'Standard ist default_preis_paar_2_5, berechnet aus PreisPaarEK * Faktor 2,5. Wenn PreisPaarEK leer ist, bleibt es leer.',
            'preis_stueck_2_3' => 'Standard ist default_preis_stueck_2_3, also aufgerundeter VK 2,3.',
            'preis_paar_2_3' => 'Standard ist default_preis_paar_2_3, berechnet aus PreisPaarEK * Faktor 2,3. Wenn PreisPaarEK leer ist, bleibt es leer.',
        );
    }

    private function availableFormulaVariables()
    {
        return array_merge(
            array(
                'ag_in_g', 'ag_incl_verlust', 'au_in_g', 'au_incl_verlust', 'zeit_in_h',
                'stueck_1', 'steine_perlen_ek', 'steine_messe', 'stueck_2',
                'furnituren_steine_ek', 'steine_messe_2', 'plattierung_oxidation', 'schnur_2',
                'material', 'arbeitszeit', 'stueck_1_messe', 'stueck_2_messe',
                'fixkosten_r5', 'fixkosten_s', 'metall_zuschlag_s',
                'ek', 'factor_25', 'factor_23', 'preis_stueck_ek', 'preis_stueck_2_5', 'preis_stueck_2_3',
                'A3', 'A5', 'A7', 'B3', 'C3', 'C5', 'R3', 'R5', 'S3', 'S7', 'S9', 'S11', 'MULT_VK',
            ),
            array_map(function ($field) {
                return 'default_'.$field;
            }, $this->defaultRuleFields())
        );
    }

    private function normalizeParamKey($key)
    {
        return strtoupper(trim($key));
    }

    private function parseDecimal($value)
    {
        $value = trim($value);
        $value = str_replace(array('EUR', '€', ' '), '', $value);
        if ($value === '') {
            return 0.0;
        }

        $commaPos = strrpos($value, ',');
        $dotPos = strrpos($value, '.');

        if ($commaPos !== false && $dotPos !== false) {
            if ($commaPos > $dotPos) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }

            return (float) $value;
        }

        if ($commaPos !== false) {
            return (float) str_replace(',', '.', $value);
        }

        if ($dotPos !== false) {
            $parts = explode('.', $value);
            $lastPart = end($parts);

            if (count($parts) > 2) {
                $value = strlen($lastPart) <= 2
                    ? str_replace('.', '', implode('.', array_slice($parts, 0, -1))).'.'.$lastPart
                    : str_replace('.', '', $value);
            } elseif (strlen($lastPart) === 3 && preg_match('/^-?\d{1,3}$/', $parts[0])) {
                $value = str_replace('.', '', $value);
            }
        }

        return (float) $value;
    }

    private function buildCalculationExplanation(array $rawRow)
    {
        $row = $this->normalizeCalculationRow($rawRow);
        $row = $this->enrichCalculationRowFromCatalogItems($row);
        $globals = $this->loadGlobalParams();
        $globalMeta = $this->loadGlobalMeta();
        $rules = $this->loadFormulaRules((string) $row['artikel_code']);
        $baseParts = $this->catalogCalculationParts($row, $globals);
        $parts = $this->configuredCatalogCalculationParts($row, $globals, $rules, $baseParts);
        $calc = $this->calculateCatalogPrices($row, $globals, $rules, $parts, $baseParts);
        $variables = array_merge($this->catalogFormulaVars($row, $globals), $parts, $calc['variables']);

        return array(
            'articleCode' => $row['artikel_code'],
            'articleTitle' => $row['artikel'],
            'summary' => $calc['summary'],
            'priceSteps' => $calc['priceSteps'],
            'partSteps' => $this->buildPartSteps($row, $globals, $globalMeta, $parts),
            'rules' => $rules,
            'globals' => $globalMeta,
            'variables' => $this->usedVariablesByRule($rules, $variables, $globalMeta),
            'missingSourceFields' => $this->missingSourceFields($row),
        );
    }

    private function enrichCalculationRowFromCatalogItems(array $row)
    {
        if (!$this->tableExists('catalog_items')) {
            return $row;
        }

        $articleCode = trim((string) ($row['artikel_code'] ?? ''));
        if ($articleCode === '') {
            return $row;
        }

        $catalogRow = $this->connection->fetchAssociative(
            'SELECT * FROM catalog_items WHERE artikel_code=? OR artikel=? ORDER BY id LIMIT 1',
            array($articleCode, $articleCode)
        );

        if (!$catalogRow) {
            return $row;
        }

        foreach ($catalogRow as $field => $value) {
            if (!array_key_exists($field, $row) || trim((string) $row[$field]) === '') {
                $row[$field] = $value;
            }
        }

        return $row;
    }

    private function tableExists($table)
    {
        try {
            return (bool) $this->connection->fetchOne('SHOW TABLES LIKE ?', array($table));
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function tableColumns($table)
    {
        $columns = array();

        try {
            foreach ($this->connection->fetchAllAssociative('SHOW COLUMNS FROM '.$table) as $row) {
                $columns[(string) $row['Field']] = true;
            }
        } catch (\Throwable $exception) {
            return array();
        }

        return $columns;
    }

    private function quoteIdentifier($identifier)
    {
        return '`'.str_replace('`', '``', (string) $identifier).'`';
    }

    private function normalizeCalculationRow(array $row)
    {
        $map = array(
            'Artikel' => 'artikel_code',
            'Kategorie' => 'kategorie',
            'Subkategorie' => 'subkategorie',
            'Beschreibung' => 'beschreibung',
            'PreisStueckEK' => 'preis_stueck_ek',
            'PreisPaarEK' => 'preis_paar_ek',
            'PreisStueck2_5' => 'preis_stueck_2_5',
            'PreisPaar2_5' => 'preis_paar_2_5',
            'PreisStueck2_3' => 'preis_stueck_2_3',
            'PreisPaar2_3' => 'preis_paar_2_3',
        );

        foreach ($map as $source => $target) {
            if (!array_key_exists($target, $row) && array_key_exists($source, $row)) {
                $row[$target] = $row[$source];
            }
        }

        $row['artikel_code'] = (string) ($row['artikel_code'] ?? $row['artikel'] ?? '');
        $row['artikel'] = (string) ($row['artikel'] ?? $row['artikel_code'] ?? '');

        return $row;
    }

    private function loadGlobalParams()
    {
        $this->ensureGlobalParamsTable();
        $rows = $this->connection->fetchAllAssociative('SELECT param_key, param_value FROM '.self::GLOBAL_PARAMS_TABLE);
        $params = array();

        foreach ($rows as $row) {
            $params[(string) $row['param_key']] = (float) $row['param_value'];
        }

        return $params;
    }

    private function loadGlobalMeta()
    {
        $this->ensureGlobalParamsTable();
        $rows = $this->connection->fetchAllAssociative('SELECT param_key, label, param_value, unit FROM '.self::GLOBAL_PARAMS_TABLE.' ORDER BY param_key');
        $meta = array();

        foreach ($rows as $row) {
            $meta[(string) $row['param_key']] = $row;
        }

        return $meta;
    }

    private function loadFormulaRules($articleCode)
    {
        $this->ensureFormulaRulesTable();
        $rules = $this->defaultCatalogFormulaRules();
        $rows = $this->connection->fetchAllAssociative('SELECT target_field, formula FROM '.self::FORMULA_RULES_TABLE." WHERE active=1 AND artikel_code=''");

        foreach ($rows as $row) {
            $rules[(string) $row['target_field']] = (string) $row['formula'];
        }

        $articleCode = trim((string) $articleCode);
        if ($articleCode !== '') {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT target_field, formula FROM '.self::FORMULA_RULES_TABLE.' WHERE active=1 AND artikel_code=?',
                array($articleCode)
            );
            foreach ($rows as $row) {
                $rules[(string) $row['target_field']] = (string) $row['formula'];
            }
        }

        return $rules;
    }

    private function defaultCatalogFormulaRules()
    {
        return array(
            'ag_incl_verlust' => 'default_ag_incl_verlust',
            'au_incl_verlust' => 'default_au_incl_verlust',
            'material' => 'default_material',
            'arbeitszeit' => 'default_arbeitszeit',
            'stueck_1_messe' => 'default_stueck_1_messe',
            'stueck_2_messe' => 'default_stueck_2_messe',
            'plattierung_oxidation' => 'default_plattierung_oxidation',
            'schnur_2' => 'default_schnur_2',
            'fixkosten_r5' => 'default_fixkosten_r5',
            'fixkosten_s' => 'default_fixkosten_s',
            'metall_zuschlag_s' => 'default_metall_zuschlag_s',
            'ek' => 'material + arbeitszeit + stueck_1_messe + stueck_2_messe + plattierung_oxidation + schnur_2 + fixkosten_r5 + fixkosten_s + metall_zuschlag_s',
            'factor_25' => '2.5',
            'factor_23' => '2.3',
            'preis_stueck_ek' => 'default_preis_stueck_ek',
            'preis_paar_ek' => 'default_preis_paar_ek',
            'vkstk_ek_2_5_ungerundet' => 'default_vkstk_ek_2_5_ungerundet',
            'preis_stueck_2_5' => 'default_preis_stueck_2_5',
            'paarpreis_vk_2_5_ungerundet' => 'default_paarpreis_vk_2_5_ungerundet',
            'preis_paar_2_5' => 'default_preis_paar_2_5',
            'vkstk_ek_2_3_ungerundet' => 'default_vkstk_ek_2_3_ungerundet',
            'preis_stueck_2_3' => 'default_preis_stueck_2_3',
            'vkpaar_ek_2_3_ungerundet' => 'default_vkpaar_ek_2_3_ungerundet',
            'preis_paar_2_3' => 'default_preis_paar_2_3',
        );
    }

    private function catalogFormulaVars(array $row, array $globals)
    {
        $vars = $globals;

        foreach ($row as $key => $value) {
            if (is_string($key) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
                $vars[$key] = $this->parseDecimal((string) $value);
            }
        }

        return $vars;
    }

    private function evalCatalogFormula($formula, array $vars)
    {
        $formula = trim((string) $formula);
        if ($formula === '') {
            return 0.0;
        }

        if ($formula[0] === '=') {
            $formula = trim(substr($formula, 1));
        }

        $tokens = preg_split('/([()+\-*\/])/', $formula, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $phpExpression = '';

        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token === '') {
                continue;
            }

            if (preg_match('/^[()+\-*\/]$/', $token)) {
                $phpExpression .= $token;
                continue;
            }

            if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $token)) {
                if (!array_key_exists($token, $vars)) {
                    throw new \RuntimeException('Unbekannte Variable in Formel: '.$token);
                }
                $phpExpression .= '('.(float) $vars[$token].')';
                continue;
            }

            if (preg_match('/^[0-9]+([,.][0-9]+)?$/', $token)) {
                $phpExpression .= str_replace(',', '.', $token);
                continue;
            }

            throw new \RuntimeException('Ungueltiger Ausdruck in Formel: '.$token);
        }

        set_error_handler(function () {
            throw new \RuntimeException('Formel konnte nicht berechnet werden.');
        });

        try {
            $result = eval('return '.$phpExpression.';');
        } finally {
            restore_error_handler();
        }

        return (float) $result;
    }

    private function parseFormulaOrDecimal($value, array $vars)
    {
        $raw = trim((string) $value);
        if ($raw !== '' && $raw[0] === '=') {
            return $this->evalCatalogFormula($raw, $vars);
        }

        return $this->parseDecimal($raw);
    }

    private function globalParam(array $globals, $key, $default = 0.0)
    {
        return array_key_exists($key, $globals) ? (float) $globals[$key] : (float) $default;
    }

    private function parseWorkHours($value)
    {
        $hours = $this->parseDecimal((string) $value);
        $normalized = str_replace(',', '.', trim((string) $value));

        if (preg_match('/^[0-9]+\.[0-9]{2}$/', $normalized)) {
            $fraction = $hours - floor($hours);
            if (abs($fraction - 0.67) < 0.0001) {
                return $hours - 0.004;
            }
            if (abs($fraction - 0.33) < 0.0001) {
                return $hours + 0.00333;
            }
            if (abs($fraction - 0.83) < 0.0001) {
                return $hours + 0.003;
            }
        }

        return $hours;
    }

    private function catalogMetalRate(array $row, array $globals)
    {
        $articleCode = strtoupper((string) ($row['artikel_code'] ?? ''));
        if ($this->parseDecimal((string) ($row['au_incl_verlust'] ?? '')) > 0 || $this->parseDecimal((string) ($row['au_in_g'] ?? '')) > 0) {
            return strpos($articleCode, 'WG') !== false ? $this->globalParam($globals, 'A7') : $this->globalParam($globals, 'A5');
        }

        return $this->globalParam($globals, 'A3');
    }

    private function catalogCalculationParts(array $row, array $globals)
    {
        $vars = $this->catalogFormulaVars($row, $globals);
        $agIn = $this->parseFormulaOrDecimal($row['ag_in_g'] ?? '0', $vars);
        $auIn = $this->parseFormulaOrDecimal($row['au_in_g'] ?? '0', $vars);
        $agIncl = trim((string) ($row['ag_incl_verlust'] ?? '')) !== '' ? $this->parseFormulaOrDecimal($row['ag_incl_verlust'], $vars) : ($agIn > 0 ? $agIn * (1 + $this->globalParam($globals, 'C3')) : 0.0);
        $auIncl = trim((string) ($row['au_incl_verlust'] ?? '')) !== '' ? $this->parseFormulaOrDecimal($row['au_incl_verlust'], $vars) : ($auIn > 0 ? $auIn * (1 + $this->globalParam($globals, 'C5')) : 0.0);
        $zeitRaw = trim((string) ($row['zeit_in_h'] ?? ''));
        $arbeitszeitHours = ($zeitRaw !== '' && $zeitRaw[0] === '=') ? $this->evalCatalogFormula($zeitRaw, $vars) : $this->parseWorkHours($zeitRaw);
        $stueck1 = $this->parseFormulaOrDecimal($row['stueck_1'] ?? '0', $vars);
        $steineEk = $this->parseFormulaOrDecimal($row['steine_perlen_ek'] ?? '0', $vars);
        $steineMesseRaw = trim((string) ($row['steine_messe'] ?? ''));
        $steineMesse = $steineMesseRaw !== '' ? $this->parseFormulaOrDecimal($steineMesseRaw, $vars) : ($steineEk * 1.5);
        $stueck2 = $this->parseFormulaOrDecimal($row['stueck_2'] ?? '0', $vars);
        $furniturenEk = $this->parseFormulaOrDecimal($row['furnituren_steine_ek'] ?? '0', $vars);
        $steineMesse2Raw = trim((string) ($row['steine_messe_2'] ?? ''));
        $steineMesse2 = $steineMesse2Raw !== '' ? $this->parseFormulaOrDecimal($steineMesse2Raw, $vars) : ($furniturenEk * 1.5);
        $isGold = $auIncl > 0;
        $isOhrstecker = strtolower((string) ($row['kategorie'] ?? '')) === 'ohrstecker';
        $articleCode = strtoupper((string) ($row['artikel_code'] ?? ''));
        $metalSurcharge = (!$isGold && !$isOhrstecker) ? $agIncl * $this->globalParam($globals, 'S7') : 0.0;
        $fixedSurcharge = 0.0;

        if ($isGold) {
            if (strpos($articleCode, 'AH8') !== false) {
                $metalSurcharge = $auIncl * $this->globalParam($globals, 'S11');
            } else {
                $fixedSurcharge = $this->globalParam($globals, 'S9');
            }
        } elseif ($isOhrstecker) {
            $fixedSurcharge = $this->globalParam($globals, 'R3') + $this->globalParam($globals, 'S3');
        }

        return array(
            'ag_incl_verlust' => $agIncl,
            'au_incl_verlust' => $auIncl,
            'material' => ($auIncl > 0 ? $auIncl : $agIncl) * $this->catalogMetalRate($row, $globals),
            'arbeitszeit' => $arbeitszeitHours * $this->globalParam($globals, 'B3'),
            'stueck_1_messe' => $stueck1 * $steineMesse,
            'stueck_2_messe' => $stueck2 * $steineMesse2,
            'plattierung_oxidation' => $this->parseFormulaOrDecimal($row['plattierung_oxidation'] ?? '0', $vars),
            'schnur_2' => $this->parseFormulaOrDecimal($row['schnur_2'] ?? '0', $vars),
            'fixkosten_r5' => $isGold || $isOhrstecker ? 0.0 : $this->globalParam($globals, 'R5'),
            'fixkosten_s' => $fixedSurcharge,
            'metall_zuschlag_s' => $metalSurcharge,
        );
    }

    private function configuredCatalogCalculationParts(array $row, array $globals, array $rules, array $baseParts)
    {
        $parts = $baseParts;
        $formulaVars = array_merge($this->catalogFormulaVars($row, $globals), $parts);
        foreach ($baseParts as $field => $value) {
            $formulaVars['default_'.$field] = $value;
        }
        foreach (array_keys($baseParts) as $field) {
            $parts[$field] = $this->evalCatalogFormula($rules[$field], $formulaVars);
            $formulaVars[$field] = $parts[$field];
        }

        return $parts;
    }

    private function withCalculatedPriceFields(array $data)
    {
        $row = $this->normalizeCalculationRow($data);
        $globals = $this->loadGlobalParams();
        $rules = $this->loadFormulaRules($row['artikel_code'] ?? '');
        $baseParts = $this->catalogCalculationParts($row, $globals);
        $parts = $this->configuredCatalogCalculationParts($row, $globals, $rules, $baseParts);
        $calc = $this->calculateCatalogPrices($row, $globals, $rules, $parts, $baseParts);
        $variables = $calc['variables'];
        $isPairArticle = $this->isPairArticle($row);

        $priceFields = array(
            'ek' => 'ek',
            'PreisStueckEK' => 'preis_stueck_ek',
            'PreisPaarEK' => 'preis_paar_ek',
            'vkstk_ek_2_5_ungerundet' => 'vkstk_ek_2_5_ungerundet',
            'PreisStueck2_5' => 'preis_stueck_2_5',
            'paarpreis_vk_2_5_ungerundet' => 'paarpreis_vk_2_5_ungerundet',
            'PreisPaar2_5' => 'preis_paar_2_5',
            'vkstk_ek_2_3_ungerundet' => 'vkstk_ek_2_3_ungerundet',
            'PreisStueck2_3' => 'preis_stueck_2_3',
            'vkpaar_ek_2_3_ungerundet' => 'vkpaar_ek_2_3_ungerundet',
            'PreisPaar2_3' => 'preis_paar_2_3',
        );
        $pairFields = $this->preislistePairFields();

        foreach ($priceFields as $preislisteField => $variableName) {
            if (!$isPairArticle && in_array($preislisteField, $pairFields, true)) {
                $data[$preislisteField] = '';
                continue;
            }
            $data[$preislisteField] = number_format((float) ($variables[$variableName] ?? 0), 2, '.', '');
        }

        return $data;
    }

    private function isPairArticle(array $row)
    {
        return $this->parseDecimal((string) ($row['preis_paar_ek'] ?? $row['PreisPaarEK'] ?? '')) > 0;
    }

    private function repairScaledPriceValue($value, $referenceValue)
    {
        $value = (float) $value;
        $referenceValue = (float) $referenceValue;

        if ($value < 1000 || $referenceValue <= 0) {
            return $value;
        }

        $scaledValue = $value / 100;

        if ($scaledValue >= $referenceValue * 0.5 && $scaledValue <= $referenceValue * 5) {
            return $scaledValue;
        }

        return $value;
    }

    private function calculateCatalogPrices(array $row, array $globals, array $rules, array $parts, array $baseParts)
    {
        $isPairArticle = $this->isPairArticle($row);
        $formulaVars = array_merge($this->catalogFormulaVars($row, $globals), $parts);
        foreach ($baseParts as $field => $value) {
            $formulaVars['default_'.$field] = $value;
        }
        $ek = $this->evalCatalogFormula($rules['ek'], $formulaVars);
        $formulaVars['ek'] = $ek;
        $formulaVars['default_ek'] = $ek;
        $sourcePreisStueckEk = trim((string) ($row['preis_stueck_ek'] ?? $row['PreisStueckEK'] ?? ''));
        $sourcePreisPaarEk = trim((string) ($row['preis_paar_ek'] ?? $row['PreisPaarEK'] ?? ''));
        $defaultPreisStueckEk = $sourcePreisStueckEk !== '' ? $this->parseFormulaOrDecimal($sourcePreisStueckEk, $formulaVars) : ceil($ek);
        $formulaVars['default_preis_stueck_ek'] = $this->repairScaledPriceValue($defaultPreisStueckEk, $ek);
        $preisStueckEk = $this->evalCatalogFormula($rules['preis_stueck_ek'], $formulaVars);
        $formulaVars['preis_stueck_ek'] = $preisStueckEk;
        $defaultPreisPaarEk = $isPairArticle ? $this->parseFormulaOrDecimal($sourcePreisPaarEk, $formulaVars) : 0.0;
        $formulaVars['default_preis_paar_ek'] = $isPairArticle ? $this->repairScaledPriceValue($defaultPreisPaarEk, max($preisStueckEk * 2, $ek)) : 0.0;
        $preisPaarEk = $this->evalCatalogFormula($rules['preis_paar_ek'], $formulaVars);
        if (!$isPairArticle) {
            $preisPaarEk = 0.0;
        }
        $formulaVars['preis_paar_ek'] = $preisPaarEk;
        $factor25 = $this->evalCatalogFormula($rules['factor_25'], $formulaVars);
        $formulaVars['factor_25'] = $factor25;
        $formulaVars['default_vkstk_ek_2_5_ungerundet'] = $preisStueckEk * $factor25;
        $vk25 = $this->evalCatalogFormula($rules['vkstk_ek_2_5_ungerundet'], $formulaVars);
        $formulaVars['vkstk_ek_2_5_ungerundet'] = $vk25;
        $formulaVars['default_preis_stueck_2_5'] = ceil($vk25);
        $preis25 = $this->evalCatalogFormula($rules['preis_stueck_2_5'], $formulaVars);
        $formulaVars['preis_stueck_2_5'] = $preis25;
        $formulaVars['default_paarpreis_vk_2_5_ungerundet'] = $isPairArticle ? $preisPaarEk * $factor25 : 0.0;
        $paarVk25 = $this->evalCatalogFormula($rules['paarpreis_vk_2_5_ungerundet'], $formulaVars);
        if (!$isPairArticle) {
            $paarVk25 = 0.0;
        }
        $formulaVars['paarpreis_vk_2_5_ungerundet'] = $paarVk25;
        $formulaVars['default_preis_paar_2_5'] = $isPairArticle ? ceil($paarVk25) : 0.0;
        $preisPaar25 = $this->evalCatalogFormula($rules['preis_paar_2_5'], $formulaVars);
        if (!$isPairArticle) {
            $preisPaar25 = 0.0;
        }
        $formulaVars['preis_paar_2_5'] = $preisPaar25;
        $factor23 = $this->evalCatalogFormula($rules['factor_23'], $formulaVars);
        $formulaVars['factor_23'] = $factor23;
        $formulaVars['default_vkstk_ek_2_3_ungerundet'] = $preisStueckEk * $factor23;
        $vk23 = $this->evalCatalogFormula($rules['vkstk_ek_2_3_ungerundet'], $formulaVars);
        $formulaVars['vkstk_ek_2_3_ungerundet'] = $vk23;
        $formulaVars['default_preis_stueck_2_3'] = ceil($vk23);
        $preis23 = $this->evalCatalogFormula($rules['preis_stueck_2_3'], $formulaVars);
        $formulaVars['preis_stueck_2_3'] = $preis23;
        $formulaVars['default_vkpaar_ek_2_3_ungerundet'] = $isPairArticle ? $preisPaarEk * $factor23 : 0.0;
        $paarVk23 = $this->evalCatalogFormula($rules['vkpaar_ek_2_3_ungerundet'], $formulaVars);
        if (!$isPairArticle) {
            $paarVk23 = 0.0;
        }
        $formulaVars['vkpaar_ek_2_3_ungerundet'] = $paarVk23;
        $formulaVars['default_preis_paar_2_3'] = $isPairArticle ? ceil($paarVk23) : 0.0;
        $preisPaar23 = $this->evalCatalogFormula($rules['preis_paar_2_3'], $formulaVars);
        if (!$isPairArticle) {
            $preisPaar23 = 0.0;
        }
        $formulaVars['preis_paar_2_3'] = $preisPaar23;

        return array(
            'summary' => array('ek' => $ek, 'preis_stueck_ek' => $preisStueckEk, 'preis_paar_ek' => $preisPaarEk, 'preis_stueck_2_5' => $preis25, 'preis_paar_2_5' => $preisPaar25, 'preis_stueck_2_3' => $preis23, 'preis_paar_2_3' => $preisPaar23),
            'variables' => $formulaVars,
            'priceSteps' => array(
                array('ek', $rules['ek'], $ek),
                array('preis_stueck_ek', $rules['preis_stueck_ek'].'; default_preis_stueck_ek = ceil(ek)', $preisStueckEk),
                array('preis_paar_ek', $rules['preis_paar_ek'], $preisPaarEk),
                array('factor_25', $rules['factor_25'], $factor25),
                array('vkstk_ek_2_5_ungerundet', $rules['vkstk_ek_2_5_ungerundet'].'; default = preis_stueck_ek * factor_25', $vk25),
                array('preis_stueck_2_5', $rules['preis_stueck_2_5'].'; default = ceil(vkstk_ek_2_5_ungerundet)', $preis25),
                array('preis_paar_2_5', $rules['preis_paar_2_5'], $preisPaar25),
                array('factor_23', $rules['factor_23'], $factor23),
                array('vkstk_ek_2_3_ungerundet', $rules['vkstk_ek_2_3_ungerundet'].'; default = preis_stueck_ek * factor_23', $vk23),
                array('preis_stueck_2_3', $rules['preis_stueck_2_3'].'; default = ceil(vkstk_ek_2_3_ungerundet)', $preis23),
                array('preis_paar_2_3', $rules['preis_paar_2_3'], $preisPaar23),
            ),
        );
    }

    private function buildPartSteps(array $row, array $globals, array $globalMeta, array $parts)
    {
        $describeGlobal = function ($key) use ($globalMeta) {
            if (!isset($globalMeta[$key])) {
                return $key;
            }
            $unit = trim((string) ($globalMeta[$key]['unit'] ?? ''));
            return $key.' '.$globalMeta[$key]['label'].($unit !== '' ? ' ('.$unit.')' : '');
        };
        $isGold = $parts['au_incl_verlust'] > 0;
        $isOhrstecker = strtolower((string) ($row['kategorie'] ?? '')) === 'ohrstecker';
        $materialRateKey = $isGold ? (strpos(strtoupper((string) ($row['artikel_code'] ?? '')), 'WG') !== false ? 'A7' : 'A5') : 'A3';
        $materialWeight = $isGold ? $parts['au_incl_verlust'] : $parts['ag_incl_verlust'];

        return array(
            array('ag_incl_verlust', 'ag_in_g * (1 + '.$describeGlobal('C3').') = '.$this->formatNumber($this->parseDecimal((string) ($row['ag_in_g'] ?? '0'))).' * (1 + '.$this->formatNumber($this->globalParam($globals, 'C3')).')', $parts['ag_incl_verlust']),
            array('au_incl_verlust', 'au_in_g * (1 + '.$describeGlobal('C5').') = '.$this->formatNumber($this->parseDecimal((string) ($row['au_in_g'] ?? '0'))).' * (1 + '.$this->formatNumber($this->globalParam($globals, 'C5')).')', $parts['au_incl_verlust']),
            array('material', $materialWeight > 0 ? $describeGlobal($materialRateKey).': '.$this->formatNumber($materialWeight).' * '.$this->formatNumber($this->globalParam($globals, $materialRateKey)) : 'kein Materialgewicht oder Materialspalten fehlen', $parts['material']),
            array('arbeitszeit', 'zeit_in_h * '.$describeGlobal('B3').' = '.$this->formatNumber($this->parseWorkHours((string) ($row['zeit_in_h'] ?? '0'))).' * '.$this->formatNumber($this->globalParam($globals, 'B3')), $parts['arbeitszeit']),
            array('stueck_1_messe', 'stueck_1 * (steine_messe oder steine_perlen_ek * 1.5)', $parts['stueck_1_messe']),
            array('stueck_2_messe', 'stueck_2 * (steine_messe_2 oder furnituren_steine_ek * 1.5)', $parts['stueck_2_messe']),
            array('plattierung_oxidation', 'Artikelfeld plattierung_oxidation', $parts['plattierung_oxidation']),
            array('schnur_2', 'Artikelfeld schnur_2', $parts['schnur_2']),
            array('fixkosten_r5', (!$isGold && !$isOhrstecker) ? $describeGlobal('R5') : '0, weil Gold oder Ohrstecker', $parts['fixkosten_r5']),
            array('fixkosten_s', $isGold ? $describeGlobal('S9') : ($isOhrstecker ? $describeGlobal('R3').' + '.$describeGlobal('S3') : '0'), $parts['fixkosten_s']),
            array('metall_zuschlag_s', (!$isGold && !$isOhrstecker) ? 'ag_incl_verlust * '.$describeGlobal('S7') : '0 oder Gold-Sonderzuschlag S11', $parts['metall_zuschlag_s']),
        );
    }

    private function usedVariablesByRule(array $rules, array $variables, array $globalMeta)
    {
        $rows = array();
        foreach ($rules as $target => $formula) {
            preg_match_all('/\b[A-Za-z_][A-Za-z0-9_]*\b/', (string) $formula, $matches);
            foreach (array_unique($matches[0]) as $name) {
                if (!array_key_exists($name, $variables)) {
                    continue;
                }
                $rows[] = array('target' => $target, 'name' => $name, 'value' => $variables[$name], 'label' => isset($globalMeta[$name]) ? (string) $globalMeta[$name]['label'] : '', 'unit' => isset($globalMeta[$name]) ? (string) ($globalMeta[$name]['unit'] ?? '') : '', 'isGlobal' => isset($globalMeta[$name]));
            }
        }

        return $rows;
    }

    private function missingSourceFields(array $row)
    {
        $fields = array('ag_in_g', 'ag_incl_verlust', 'au_in_g', 'au_incl_verlust', 'zeit_in_h', 'stueck_1', 'steine_perlen_ek', 'steine_messe', 'stueck_2', 'furnituren_steine_ek', 'steine_messe_2', 'plattierung_oxidation', 'schnur_2');
        $missing = array();
        foreach ($fields as $field) {
            if (!array_key_exists($field, $row)) {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    private function formatNumber($value)
    {
        return number_format((float) $value, 4, ',', '.');
    }

    private function defaultRuleFields()
    {
        return array(
            'ag_incl_verlust',
            'au_incl_verlust',
            'material',
            'arbeitszeit',
            'stueck_1_messe',
            'stueck_2_messe',
            'plattierung_oxidation',
            'schnur_2',
            'fixkosten_r5',
            'fixkosten_s',
            'metall_zuschlag_s',
            'ek',
            'factor_25',
            'factor_23',
            'preis_stueck_ek',
            'preis_paar_ek',
            'vkstk_ek_2_5_ungerundet',
            'preis_stueck_2_5',
            'paarpreis_vk_2_5_ungerundet',
            'preis_paar_2_5',
            'vkstk_ek_2_3_ungerundet',
            'preis_stueck_2_3',
            'vkpaar_ek_2_3_ungerundet',
            'preis_paar_2_3',
        );
    }
}
