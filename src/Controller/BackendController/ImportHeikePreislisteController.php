<?php
// src/Controller/BackendController/ImportHeikePreislisteController.php
namespace Pbdkn\ContaoBesslichschmuck\Controller\BackendController;

use Contao\System;  // Import the System class from Contao
use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface; // Korrekte Verwendung des Namespaces
use Contao\CoreBundle\Exception\RedirectResponseException;


use Contao\BackendTemplate;

use Contao\FilesModel;
use Contao\File;
use Contao\Folder;
use Contao\Message;
use Contao\FileTree;
use Contao\Checkbox;
use Contao\RadioButton;
use Contao\Input;
use Contao\Widget\Widget;
use Contao\Database;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Contao\CoreBundle\Picker\FilePickerProvider;
use Symfony\Component\Routing\RouterInterface;

class ImportHeikePreislisteController extends AbstractBackendController
{
    private $framework;
    private $router;
    private $filePickerProvider;
    private $strTemplate;
    private $Template;
    private $strTable;


    public function __construct(ContaoFrameworkInterface $framework,RouterInterface $router)
    {
        $this->framework = $framework;
        $this->router = $router;
        //$this->filePickerProvider = $filePickerProvider;

        $this->framework->initialize();
        \Controller::loadDataContainer('tl_files');
        System::loadLanguageFile('default');
        System::loadLanguageFile('modules');


    }
    /**
     * @Route("/besslich/importCSV", 
     * name="ImportHeikePreislisteController::importAction",
     * methods={"GET", "POST"})
     * @throws \Exception
     */

    public function importAction(Request $request): Response
    {
        // Verwenden eines Contao-Backend-Templates
        //$this->Template = new BackendTemplate('be_main'); // Standard-Backend-Template

       // Initialisieren Sie das Contao Framework
        ini_set('xdebug.var_display_max_depth', '10');    // dient zur vergroesserung der ausgabe von var_dump
        ini_set('xdebug.var_display_max_children', '256');
        ini_set('xdebug.var_display_max_data', '8192');
//var_dump('<pre>'.$request.'</pre>');                
        $this->strTemplate='be_import_csv';
        $this->Template = new \BackendTemplate($this->strTemplate);

        $objWidget = $this->generateFileTreeWidget();
        if ($request->query->get('key') === 'importCSV') {
        //if ($request->getMethod() === 'POST') {
            $objWidget->validate();
            if (!$objWidget->hasErrors()) {            
              //$strFile = $request->request->get('fileTree');
              // $strFile='files/heike-files/downloads/CSV/2024_Preisliste_small.csv';
              // Logik zum Verarbeiten der eingereichten Daten
              // z.B.: Dateipfad aus $objWidget->value auslesen
              $strFile=$objWidget->value;            
              if ($strFile) {
                $objFile = new File($strFile, true);
                if ($objFile->extension === 'csv') {
                    //ob_start();
                    $arrImportIgnoreFields = ['id', 'pid', 'tstamp', 'sorting', 'import_source'];
                    $arrImportantFields=[];
                    // Nehmen wir an, dass wir die DCA für ein bestimmtes Modul oder Tabelle laden wollen, z.B. tl_member
                    //\Contao\Controller::loadDataContainer('tl_member');
                    \Controller::loadDataContainer($this->strTable);
                    // Die DCA-Definition für die Tabelle erhalten
                    if (isset($GLOBALS['TL_DCA'][$this->strTable])) {
                      $arrDcaFields = $GLOBALS['TL_DCA'][$this->strTable]['fields'];
                      $dca = $GLOBALS['TL_DCA'][$this->strTable];
                    } else {
                      Message::addError("DCA für '".$this->strTable ."' ist nicht geladen oder existiert nicht.");
                      return new Response($this->Template->parse());
                    }
                   // Durchlaufe alle Felder in der DCA
                   foreach ($dca['fields'] as $fieldName => $fieldConfig) {
                     // Überprüfen, ob das Feld als mandatory definiert ist
                     if (isset($fieldConfig['eval']['mandatory']) && $fieldConfig['eval']['mandatory']) {
                       $arrImportantFields[] = $fieldName;
                     }
                   }
                   $delEntries=1;    // einräge löschen
//                   var_dump($arrImportantFields);
                    $resArr=$this->importFromCSV($objFile,$dca,$delEntries,$arrImportIgnoreFields,$arrImportantFields);
                    Message::addConfirmation('Die CSV-Datei wurde erfolgreich importiert.');
                    //ob_end_flush();
                    return new Response($resArr);

                    //throw new RedirectResponseException($this->get('router')->generate('import_heike_preisliste'));
                }
            }
          }
        }


        $this->Template->action = $this->generateUrl('ImportHeikePreislisteController::importAction'); // aktion fuer die <form
        $this->Template->headline = 'Preisliste von CSV-Datei importieren';
        $this->Template->submit = 'Importieren';
        $this->Template->fileTreeParse=$objWidget->generate();   // das erzeugt fileTree widget html-code
        $strT=$this->Template->parse().$this->getFilepickerJavascript('reloadEfgFiletree');
//var_dump('<pre>'.$strT.'</pre>');
        return new Response($strT);
    }
    /**
     * @Route("/besslich/importFromCheckbox", 
     * name="ImportHeikePreislisteController::importFromCheckbox",
     * methods={"GET", "POST"})
     * @throws \Exception
     */

    public function importFromCheckbox(Request $request): Response
    {
        $this->strTable='tl_heike_preisliste';
        if ($request->query->get('table')) {
           $this->strTable=$request->query->get('table'); 
        }

        $this->strTemplate='be_import_csv_checkbox';
        $this->Template = new \BackendTemplate($this->strTemplate);

        ini_set('xdebug.var_display_max_depth', '10');    // dient zur vergroesserung der ausgabe von var_dump
        ini_set('xdebug.var_display_max_children', '256');
        ini_set('xdebug.var_display_max_data', '8192');
    
        // Definiere die Attribute
        $attributes = [
            'id' => 'checkboxFileCSV',
            'name' => 'checkboxFileCSV',
            'label' => 'Wähle das CSV-File aus',
            'options' => [],
            'eval' => ['mandatory' => true,'tl_class'=>'w50'],
            'multiple' => false,
        ];
        $arrCSVFiles=$this->getCsvFilesFromContaoDirectory('files/heike-files/downloads/CSV');
        foreach($arrCSVFiles as $k=>$v) {
          $attributes['options'][]=['value' => $v, 'label' => basename($v)];
        }
        $redirekturl = $this->router->generate('contao_backend', [
            'do' => 'Preisliste', // Verwende hier den Namen, den du im BE_MOD-Array definiert hast
            'table' => "$this->strTable", // Optional: wenn du eine bestimmte Tabelle direkt ansprechen möchtest
        ]);

        
        // erzeuge das Template, damit es im Fehlerfall auch verwendet werden kann 
        $csvWidget = new \Contao\RadioButton($attributes);
        $strCSVListe = $csvWidget->generate();
        $this->Template->action = $this->generateUrl('ImportHeikePreislisteController::importAction'); // aktion fuer die <form
        $this->Template->headline = 'Preisliste von CSV-Datei importieren';
        $this->Template->submit = 'Importieren';
        $this->Template->csvCheckbox=$strCSVListe;   // string erzeugt das checkbox widget
        $this->Template->redirekturl=$redirekturl;
        $strT=$this->Template->parse();
        

        // Formularvalidierung und -verarbeitung
        if (Input::post('FORM_SUBMIT') == 'csv_form') {  // request kommt von form
            $csvWidget->validate();
            if (!$csvWidget->hasErrors()) {
                $fname = $csvWidget->value; // Dies gibt den ausgewaehlten Wert zurück
                 // Validierung: Ist das Feld mandatory und leer?
                if (!$fname) {
                  Message::addError('Das CSV-File muss ausgewählt werden.');
                  return new Response($this->Template->parse());
                }               
                $delEntries = \Input::post('delEntries') ? '1' : '0';
                // Verarbeite den ausgewählten Wert
                //echo "delEntries $delEntries   ";
                \Controller::loadDataContainer($this->strTable);
                // Die DCA-Definition für die Tabelle erhalten
                if (isset($GLOBALS['TL_DCA'][$this->strTable])) {
                  $arrDcaFields = $GLOBALS['TL_DCA'][$this->strTable]['fields'];
                  $dca = $GLOBALS['TL_DCA'][$this->strTable];
                } else {
                  Message::addError("DCA für '".$this->strTable ."' ist nicht geladen oder existiert nicht.");
                  return new Response($this->Template->parse());
                }
                
                $arrImportantFields=[];
                // Durchlaufe alle Felder in der DCA
                foreach ($dca['fields'] as $fieldName => $fieldConfig) {
                  // Überprüfen, ob das Feld als mandatory definiert ist
                  if (isset($fieldConfig['eval']['mandatory']) && $fieldConfig['eval']['mandatory']) {
                    $arrImportantFields[] = $fieldName;
                  }
                }
                $arrImportIgnoreFields = ['id', 'pid', 'tstamp', 'sorting', 'import_source'];

//var_dump($arrImportantFields);
                $objFile = new File($fname, true);

                $resArr=$this->importFromCSV($objFile,$dca,$delEntries,$arrImportIgnoreFields,$arrImportantFields);
//var_dump($resArr);
                $strResp="";
                foreach ($resArr['res'] as $k=>$s) $strResp.="$s<br>";
//                $strResp.="debug<br>"; foreach ($resArr['debug'] as $k=>$s) $strResp.="$s<br>";
                if (count($resArr['warning'])>0)$strResp.="<br>Warnungen<br>";foreach ($resArr['warning'] as $k=>$s) $strResp.="$s<br>";
                if (count($resArr['error'])>0)$strResp.="<br>Fehler<br>";foreach ($resArr['error'] as $k=>$s) $strResp.="$s<br>";
//                $strResp.="res<br>";
                $url="importFromCheckbox?table=tl_heike_preisliste";
                $strResp.="<a href = $url > Zurück </a>";

                return new Response($strResp);
            }
        }
        return new Response($this->Template->parse());

    }
    


    /**
     * @Route("/besslich/showSupportedContexts", 
     * name="ImportHeikePreislisteController::showSupportedContexts",
     * methods={"GET"})
     * @throws \Exception
     */
     
    public function showSupportedContexts(): Response
    {
        $contexts = [];

        // Überprüfen, ob der `file` Kontext unterstützt wird
        if ($this->filePickerProvider->supportsContext('file')) {
            $contexts['file'] = 'Supported';
        } else {
            $contexts['file'] = 'Not Supported';
        }

        // Ausgabe der unterstützten Kontexte
        return new Response('<pre>' . print_r($contexts, true) . '</pre>');
    }

    public function generateFileTreeWidget()
    {
      /* aus DC_Formdata efg 5388 */
      // liest die daten aus der dca
      $attributes = \Widget::getAttributesFromDca(
        $GLOBALS['TL_DCA'][$this->strTable]['fields']['import_source'], 'import_source', 
        null, 'import_source', 'tl_heike_preisliste'
      );
      $attributes['eval']['path'] = 'files/heike-files';  // Beispielpfad
      $objWidget = new \FileTree($attributes);
      return $objWidget;
    }
    /* 
     * liest das csv File in die db
     * File Fileobject der datei 
     * dca array der Dca
     * arrImportIgnoreFields dies Felder könen nicht importiert werden
     * arrImportantFields Felder die unbedingt vorhanden sein muessen
     * return array
     * $res['debug'][] array das alle debuginf0 enhält
     * $res['warning'][] array das alle Warnungen enhält
     * $res['error'][] array das alle Fehler enhält
     * $res['res'][] array das alle Standardrückmeldungen enhält
     */
     
    protected function importFromCSV(File $objFile,array $dca,$delEntries,array $arrImportIgnoreFields,array $arrImportantFields) :array
    {
        $res=[
          'warning'=> [],
          'error'=>[],
          'res'=>[],
        ];
        // dca Felder
        $arrDcaFields = $dca['fields'];
        //foreach ($arrDcaFields as $k=>$v) $res.="arrDcaFields[$k]<br>";
        $db = Database::getInstance();
        if ($delEntries == 1) {
          $res['debug'][]="Einträge werden gelöscht";
          $sql = "DELETE FROM ".$this->strTable;
          $db->execute($sql);
          // Alternativ: Wenn du den Autoincrement-Zähler zurücksetzen möchtest (optional)
          $db->execute("ALTER TABLE ".$this->strTable." AUTO_INCREMENT = 1");
          $res['res'][]="Einträge in Tabelle ".$this->strTable." wurden gelöscht";
        }
        $content=$objFile->getContent();
        $encoding = mb_detect_encoding($content, "auto", true);
        $res['debug'][]="Die vermutete Kodierung ist: $encoding";        
        if ($encoding != 'UTF-8') {
          $res['debug'][]="falsche codierung der Preisliste $encoding sollte UTF-8 sein. Es wird versucht zu wandeln";
          // Inhalt nach UTF-8 konvertieren, falls nötig
          $content = mb_convert_encoding($content, "UTF-8", $encoding);
        }
        // Ersetze mögliche Windows-Zeilenumbrüche durch Unix-Zeilenumbrüche
        $content = str_replace("\r\n", "\n", $content);

        // Teile den String an jedem Unix-Zeilenumbruch
        $csvLines = explode("\n", $content);
        $strDelimiter = ';';
        //$csvLines = $objFile->getContentAsArray();
        $firstLine=true;                 // erste Zeile enthält namen
        $arrspalten=[];
        // csv einlesen
        $cnt=0;
        $linesOK=0;
        foreach ($csvLines as $line) {
          $cnt++;
          $res['debug'][]="zeile $cnt |$line|";
          if ($line == "") {
            $res['warning'][]="zeile $cnt übersprungen leer";
            continue;
          }
          $arr=str_getcsv($line,$strDelimiter);   // werte einer csv-Zeile
          if ($firstLine) {
            $res['debug'][]="Feldnamen<br>";
            foreach ($arr as $spalte=>$feldname) {            
              $res['debug'][]="$feldname, ";
              //var_dump($arrDcaFields);
              if (!isset($arrDcaFields[$feldname])) {
                 $s=" Felder in dca ";
                 foreach ($arrDcaFields as $k=>$v) $s.="$k, ";
                 $s="";
                 $res['error'][]="$feldname in Zeile $cnt <br>nicht in der tabelledefinition von ".$this->strTable." $s"; 
                 return $res;
              }
              if (in_array($feldname, $arrspalten)) { $res['error'][]="$feldname mehrfach in Zeile 1 enthalten"; return $res;}
              if (in_array($feldname, $arrImportIgnoreFields)) {
                $res['warning'][]="$feldname wird ignoriert"; continue;
              }
              $arrspalten[$spalte] = $feldname;   // index ist die reihenfolge inhalt der name des feldes
            }
            $firstLine=false;
          } else {
            $insertData=[];
            $fehlerListe="";
            foreach ($arrspalten as $spalte=>$feldname) {
               // ueberpruefen ob importantes Feld da ist
               if (in_array("$feldname", $arrImportantFields)) {
                  if (!isset($arr[$spalte]) || $arr[$spalte]=="") {
                    $fehlerListe.="$feldname, ";
                   }
               }
               $insertData[$feldname] = $arr[$spalte];  // aufbau des InsertFeldes
            }
            if ($fehlerListe!="") {
                    $res['warning'][]="zeile $cnt übersprungen $fehlerListe  ist/sind leer.";
                    continue;
            }
            
            $insertData['tstamp']=time();
            $res['debug'][]='<pre>' . htmlspecialchars(print_r($insertData,true)) . '</pre>';
            try {
                    $result=$db->prepare("INSERT INTO ".$this->strTable." %s")
                    ->set($insertData)
                    ->execute();
                    $insertId = $result->insertId; // Die ID des neu eingefügten Datensatzes abrufen
            } catch (\Exception $e) {
               // Fehlerbehandlung
              $res['warning'][]="Es ist ein Fehler aufgetreten: Zeile $cnt " . $e->getMessage();
              continue;
            }   
            
            $linesOK++;
          }
        }
      $res['res'][]="eingelesene Zeilen $cnt gespeicherte Einträge $linesOK";
      return $res;
    }
    
    // von efg DC_formdata übernommen wird beim aufbaut des filetrees mitgeliefert.
    // ob da funktioniert ???
        private function getFilepickerJavascript($strReload)
    {

        return "
<script>
// Callback-Funktion, die aufgerufen wird, wenn der POST-Request erfolgreich abgeschlossen ist
function onSuccessCallback() {
    // Hier können Sie Code ausführen, der nach Abschluss des POST-Requests ausgeführt werden soll
    console.log('PBD onSuccessCallback ');
    $('simple-modal').hide();
    $('simple-modal-overlay').hide();
    document.body.setStyle('overflow', 'auto');
}
function handleEfgFileselectorButton(){
	$$('a[href*=contao/picker]').addEvent('click', function(e){
    
    alert('PBD handleEfgFileselectorButton ');
    console.log('PBD handleEfgFileselectorButton ');
        debugger;
		var el = e.target;
		var elHidden = el.getParent('div.selector_container').getPrevious('input[type=hidden]');
		var opt = { 'id': elHidden.get('name'), 'url': e.target.get('href') };
        // hier wird der Tree aufgerufen !!!
		$$('div#simple-modal div.simple-modal-footer a.btn.primary').removeEvents('click').addEvent('click', function() {
 console.log('PBD removeEvents'); //uebernehmen filename klick
debugger;
			var val = [];
			var	frm = null;
			var frms = window.frames;
			for (var i=0; i<frms.length; i++) {
				if (frms[i].name == 'simple-modal-iframe') {
					frm = frms[i];
					break;
				}
			}
			if (frm === null) {
				alert('Could not find the SimpleModal frame');
				return;
			}
			if (frm.document.location.href.indexOf('contao\\/main.php') != -1)  {   // PBD Error / muss escaped sein
				//alert(Contao.lang.close);
				alert('invalid File selected');
				return; // see #5704     was das bedeutet Fehlermeldung sollt sein bitte andere Datei auswaehel  den Fehler such ich noch
			}

      /* PBD geändert die ID ist nicht mehr tl_listing sondern tl_select contao 3.5 */
      /* PBD geändert die ID ist in co4 wieder tl_listing */

			//var inp = frm.document.getElementById('tl_select').getElementsByTagName('input');
			var inp = frm.document.getElementById('tl_listing').getElementsByTagName('input');
			for (var i=0; i<inp.length; i++) {
				if (!inp[i].checked || inp[i].id.match(/^check_all_/)) continue;
				if (!inp[i].id.match(/^reset_/)) val.push(inp[i].get('value'));    // value aus filetree abspeichern in val
			}
			if (opt.tag) {
				$(opt.tag).value = val.join(',');
				if (opt.url.match(/page\\.php/)) {
					$(opt.tag).value = '{{link_url::' + $(opt.tag).value + '}}';
				}
				opt.self.set('href', opt.self.get('href').replace(/&value=[^&]*/, '&value='+val.join(',')));
			} else {
				$('ctrl_'+opt.id).value = val.join('\"\t\"');  // schreibe den Wert in das ctrl Hidden Element des Windows
//console.log('(pbd ctrl_'+opt.id)+ ' value: '+' $(\'ctrl_\'+opt.id).value);
				var act = (opt.url.indexOf('contao/page.php') != -1) ? 'reloadPagetree' : '".$strReload."';
                //alert('PBD Request '+act+' opt.id ' + opt.id + ' value: '+$('ctrl_'+opt.id).value);
console.log('PBD Request act: '+act+' opt.id(name) ' + opt.id + ' value: '+$('ctrl_'+opt.id).value+' REQUEST_TOKEN: '+Contao.request_token);
debugger;
                const url = '/efg/reloadFiletree';
                //const data = {'action':act, 'name':opt.id, 'value':$('ctrl_'+opt.id).value, 'REQUEST_TOKEN':Contao.request_token};
                const data = {'action':act, 'name':opt.id, 'value':$('ctrl_'+opt.id).value};
                strbody='data='+JSON.stringify(data)+'&REQUEST_TOKEN='+Contao.request_token;
debugger;
				new Request.Contao({
                    url: url,
					field: $('ctrl_'+opt.id),
					evalScripts: false,
					onRequest: AjaxRequest.displayBox(Contao.lang.loading + ' …'),
					onSuccess: function(txt, json) {
						$('ctrl_'+opt.id).getParent('div').set('html', json.content);
						json.javascript && Browser.exec(json.javascript);
						AjaxRequest.hideBox();
						window.fireEvent('ajax_change');
					}
				}).post({'action':act, 'name':opt.id, 'value':$('ctrl_'+opt.id).value, 'REQUEST_TOKEN':Contao.request_token});

/*
                fetch(url, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: strbody,
                })
                .then(response => {
                  if (!response.ok) {
                    //throw new Error('Network response was not ok');
                    alert('Abfrage Network response was not ok: ' + response.status + ' ' + response.statusText);                  
                    console.error('PBD Abfrage Network response was not ok: ' + response.status + ' ' + response.statusText);                  
                    throw new Error('Abfrage Network response was not ok: ' + response.status + ' ' + response.statusText);                  
                  }
                  console.log('PBD response ok');
                  return response.json();
                })
                .then(data => {
                  console.log('PBD Success:', data);
                })
                .catch(error => {
                  console.error('PBD Error catch:', error);
                  alert('error '+error);
                  debugger;
                  throw new Error('PBD Network response was not ok: ' + response.status + ' ' + response.statusText);                  
                });
*/
	          }    // ende else

      /* 
			$('simple-modal').hide();
			$('simple-modal-overlay').hide();
			document.body.setStyle('overflow', 'auto');
      */
		});
    console.log('ende click funktion');
	});
}
/* window.addEvent is not a function
window.addEvent('domready', function(){
	handleEfgFileselectorButton();
});
*/
</script>";
        /*
        'REQUEST_TOKEN':Contao.request_token
        'REQUEST_TOKEN':'4sWPMwuMLwLdLF7EYeaf8M6eQGjt0f6J30D739NI24c'
        <script>
              $("ft_import_source").addEvent("click", function(e) {
                e.preventDefault();
                Backend.openModalSelector({
                  "id": "tl_listing",
                  "title": "Quelldatei",
                  "url": this.href + document.getElementById("ctrl_import_source").value,
                  "callback": function(table, value) {
                    new Request.Contao({
                      evalScripts: false,
                      onSuccess: function(txt, json) {
                        $("ctrl_import_source").getParent("div").set("html", json.content);
                        json.javascript && Browser.exec(json.javascript);
                        $("ctrl_import_source").fireEvent("change");
                      }
                    }).post({"action":"reloadFiletree", "name":"import_source", "value":value.join("\t"), "REQUEST_TOKEN":"4sWPMwuMLwLdLF7EYeaf8M6eQGjt0f6J30D739NI24c"});
                  }
                });
              });
            </script></div></div>
        */
    }
function getCsvFilesFromContaoDirectory($directoryPath)
{
    // Überprüfen, ob das Verzeichnis existiert
    if (!is_dir(TL_ROOT . '/' . $directoryPath)) {
        return [];
    }

    // Liste der .csv-Dateien initialisieren
    $csvFiles = [];

    // Ordner durchgehen und Dateien abrufen
    $folder = new Folder($directoryPath);
    $files = scandir(TL_ROOT . '/' . $folder->path);

    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $filePath = $directoryPath . '/' . $file;
            $fileModel = FilesModel::findByPath($filePath);
            
            if ($fileModel !== null && $fileModel->extension === 'csv') {
                $csvFiles[] = $filePath;
            }
        }
    }

    return $csvFiles;
}
    

}
?>
