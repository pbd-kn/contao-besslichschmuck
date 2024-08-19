<?php
// languages/de/
/*
  Bevor wir uns nun mit der Verarbeitung und Ausgabe der Daten beschäftigen, wollen wir noch die deutschen Begriffe einfügen. 
  Wir legen die Datei languages/de/tl_content.php an. 
*/
/**
 * Table: tl_content
 */
$strName = 'tl_content';
 
/**
 * Fields
 */
$GLOBALS['TL_LANG'][$strName]['artikelzusatz'] = array('Zusatzbeschreibung zum Schmuckartikels', 'Bitte geben Sie die Zusatzbeschreibung des Artikels ein. z.B. Beschreibung des Anh&auml;ngers.');
$GLOBALS['TL_LANG'][$strName]['preisliste']  = array('Preisliste', 'Zus&auml;tzliche Schmuckartikel deren Preis hinzugefügt werden soll.');
$GLOBALS['TL_LANG'][$strName]['schmuckartikelname'] = array('Schmuckartikel Name', 'Bitte geben Sie den Namen des Schmuckartikels ein'); 
$GLOBALS['TL_LANG'][$strName]['myaddImage'] = array('Schmuckartikel Bild', 'Bitte w&auml;hlen Sie ein Bild zum Schmuckartikel aus'); 
$GLOBALS['TL_LANG'][$strName]['customTpl']  = array('Individuelles für Besslich-Schmuckartikel','Hier können Sie das Standard-Template überschreiben.');
$GLOBALS['TL_LANG'][$strName]['zusatzinfo']      = array('Zusatzinformation','Bitte Aliasname des Artikels eingeben der im Popup dargestellt wird');
/*
 * Legends
 */      
$GLOBALS['TL_LANG'][$strName]['mytype_legend'] = '!! Bitte Type Schmuckartikel auswählen  !!';
$GLOBALS['TL_LANG'][$strName]['beschreibung_legend'] = 'Schmuck Artikel Beschreibung';
$GLOBALS['TL_LANG'][$strName]['schmuckartikel_legend'] = 'Schmuckartikel Name';
$GLOBALS['TL_LANG'][$strName]['myimage_legend'] = "Bild zum Schmuckartikel auswählen";

/*
 * Fehlertexte 
 */
$GLOBALS['TL_LANG'][$strName]['no_artikellist'] = 'Fehler: %s kein Zugriff auf Preisliste ';
$GLOBALS['TL_LANG'][$strName]['no_artikelexist'] = 'Artikel |%s| in Preisliste nicht vorhanden';
$GLOBALS['TL_LANG'][$strName]['no_image']       = 'Kein Bild zu %s im Gallery Creator vorhanden';
$GLOBALS['TL_LANG'][$strName]['invalid_uuid']   = 'es existiert kein File(Bild) im Gallery Creator zu %s';

