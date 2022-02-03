<?php
echo "import<br>\n";
$staticXml =   <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<clipboard>
	<metatags>
		<create_unix>1472659767</create_unix>
		<create_date>2016-08-31</create_date>
		<create_time>16:09</create_time>
		<title><![CDATA[###MegaTag###]]></title>
		<attribute><![CDATA[]]></attribute>
		<group_count><![CDATA[0]]></group_count>
		<childs>0</childs>
		<table>tl_article</table>
	</metatags>
	<datacontainer>
		<article table="tl_article">
			<row>
				<field name="sorting" type="int">0</field>
				<field name="tstamp" type="int">1470758223</field>
				<field name="title" type="text"><![CDATA['###name###']]></field>
				<field name="alias" type="text"><![CDATA['###name###']]></field>
				<field name="author" type="int">1</field>
				<field name="inColumn" type="text"><![CDATA['main']]></field>
				<field name="teaserCssID" type="text"><![CDATA['a:2:{i:0;s:0:"";i:1;s:0:"";}']]></field>
				<field name="groups" type="null">NULL</field>
				<field name="cssID" type="text"><![CDATA['a:2:{i:0;s:0:"";i:1;s:0:"";}']]></field>
				<field name="space" type="text"><![CDATA['a:2:{i:0;s:0:"";i:1;s:0:"";}']]></field>
				<field name="published" type="text"><![CDATA['1']]></field>
			</row>
			<content table="tl_content">
				<row>
					<field name="ptable" type="text"><![CDATA['tl_article']]></field>
					<field name="sorting" type="int">768</field>
					<field name="type" type="text"><![CDATA['text']]></field>
					<field name="text" type="text"><![CDATA['<img src="###imgsrc###" alt="">###imgtxt###']]></field>
					<field name="cssID" type="text"><![CDATA['a:2:{i:0;s:0:"";i:1;s:14:"schmuck-detail";}']]></field>
				</row>
			</content>
		</article>
	</datacontainer>
</clipboard>
EOT;
function getHeikeHtml ($filename,$staticXml,$albumprefix,$commentfile) {
  $myxml= $staticXml;
  $doc = new DOMDocument();
                                                                                                          
  $res =  $doc->loadHTMLFile($filename);
  //echo "File: $filename <br> $res<br>";
  //echo $doc->saveHTML();

  //$tags = $doc->getElementsByTagName('img');
  $htmltxt="";
  $commenttxt="";
  $elementname = "";

  $rows = $doc->getElementsByTagName('tr');
  foreach ($rows as $tr) {       
    /*
      echo "<br><br>------------------<br>tr: " .$tr->nodeName . " = |" . $tr->nodeValue . "|<br>";
      foreach ($tr->getElementsByTagName('td') as $v) {
        echo "! childs: " .$v->nodeName . " = " . $v->nodeValue . "<br>";
        foreach ($v->childNodes as $v1) {
          echo "!   childs: " .$v1->nodeName . " = " . $v1->nodeValue . "<br>";
        }
      }
    */
    $p = $tr->getElementsByTagName('img');     // gib alle img elemente

    if (count($p) > 0) {     // Bild da
      $src1="";
      foreach($p as $elem) {                                                                                         
        $src = explode ( '/' , $elem->getAttribute('src') );
        $f = array_pop($src);    // f ist der  Bildname
        $e = explode('.',$f);       // Image anhang jpg entfernen
        $elementname = strtolower($e[0]);
      }
      if ($elementname != "" && count($src) > 0) { 
        $myxml= str_replace ( "###name###" , $elementname , $myxml );
        $src1 = "files/gallery_creator_albums/" . $albumprefix . "/" . $f;
        $myxml= str_replace ( "###imgsrc###" , $src1 , $myxml );      
        //echo "Element: $elementname<br> src: $src<br> src1: $src1<br>";
        //$commenttxt .= "$elementname\n\n";
        $myxml= str_replace ( "###MegaTag###" , $elementname , $myxml );        
      }
    }
    $p = $tr->getElementsByTagName('p');       // gib alle p elemente
    if (count($p) > 0) {
      foreach($p as $elem) {
        $nodes = $elem->childNodes;
        if (count($nodes) > 0) {
          foreach($nodes as $node) {
            //echo "!    ppp: " .$node->nodeName . " = " . $node->nodeValue . "<br>";
            switch ($node->nodeName) {
              case "#text": 
                   $htmltxt .= trim($node->nodeValue);
                   $commenttxt .= trim($node->nodeValue);
                   break;
              case "br":
                   $htmltxt .= "<br>\n";
                   $commenttxt .= " ";
                   break;
              case "span":
                   $htmltxt .= "<b>" . trim($node->nodeValue) . "</b>\n";
                   $commenttxt .= trim($node->nodeValue) . " ";
                   break;
              default:
                   break;
            }
          }
        }
        //fwrite($commentfile, $commenttxt); 
      }
    }
  } 
  $htmltxt .= "<br><a class=\"anfrage-detail\" title=\"Anfrage zu Artikel $elementname\" href=\"{{link_url::anfrage-detail}}?name=$elementname\">Anfrage</a><br>";
  $htmltxt .= "<p class=\"detail-linkback\">{{insert_article::linkback}}</p>";
  $myxml= str_replace ( "###imgtxt###" , $htmltxt , $myxml );
  //echo "hallo<br>";
  $new_path="files/clipboard/admin/$albumprefix";
  if(is_dir($new_path)) {
    echo "The Directory {$new_path} exists<br>";
  } else {
    mkdir($new_path , 0777);
    echo "The Directory {$new_path} was created<br>";
  }
        

  $fn = "$new_path/article,$elementname.xml";
  $xmlfile= fopen($fn,"w");
  fwrite($xmlfile,$myxml);
  fclose($xmlfile);
  if ($commenttxt != "") {
    $sql = "UPDATE tl_gallery_creator_pictures SET comment='$commenttxt' WHERE name='$elementname.jpg';\n";
    fwrite($commentfile, $sql);
  } else {
    echo "No Info filename $filename albumprefix $albumprefix<br>";
  } 
  //echo "HTML:<br>$htmltxt<br>SQL:<br><br>$sql<br>";
  //echo "hallo $fn<br>";
  //echo "$sql<br>";
} 
/* Beispiel XML fuer article
<?xml version="1.0" encoding="UTF-8"?>
<clipboard>
	<metatags>
		<create_unix>1472659767</create_unix>
		<create_date>2016-08-31</create_date>
		<create_time>16:09</create_time>
		<title><![CDATA[ab1soMetatagTitle]]></title>
		<attribute><![CDATA[]]></attribute>
		<group_count><![CDATA[0]]></group_count>
		<childs>0</childs>
		<table>tl_article</table>
	</metatags>
	<datacontainer>
		<article table="tl_article">
			<row>
				<field name="sorting" type="int">0</field>
				<field name="tstamp" type="int">1470758223</field>
				<field name="title" type="text"><![CDATA['ab1soxx']]></field>
				<field name="alias" type="text"><![CDATA['ab1soxx']]></field>
				<field name="author" type="int">1</field>
				<field name="inColumn" type="text"><![CDATA['main']]></field>
				<field name="teaserCssID" type="text"><![CDATA['a:2:{i:0;s:0:"";i:1;s:0:"";}']]></field>
				<field name="groups" type="null">NULL</field>
				<field name="cssID" type="text"><![CDATA['a:2:{i:0;s:0:"";i:1;s:0:"";}']]></field>
				<field name="space" type="text"><![CDATA['a:2:{i:0;s:0:"";i:1;s:0:"";}']]></field>
				<field name="published" type="text"><![CDATA['1']]></field>
			</row>
			<content table="tl_content">
				<row>
					<field name="ptable" type="text"><![CDATA['tl_article']]></field>
					<field name="sorting" type="int">768</field>
					<field name="type" type="text"><![CDATA['text']]></field>
					<field name="text" type="text"><![CDATA['<p><img src="files/gallery_creator_albums/anhaenger_bluetengeheimnis/ab1so.jpg" alt=""></p>
<p><span style="font-weight: bold;">anhänger blütengeheimnis xxxxxxxxxxxxxxx</span><br> best.nr. AB1SO<br> 925/000 Ag oxidiert<br> zweischotig mit weißer süßwasserperle<br> länge anhänger: 37,5 mm<br> <span style="font-weight: bold;">ankerkettchen </span><br> best.-nr. KA1SO<br> 925/000 Ag oxydiert, länge kette: ca. 48 cm</p>
<p>{{insert_article::linkback}}</p>']]></field>
					<field name="cssID" type="text"><![CDATA['a:2:{i:0;s:0:"";i:1;s:14:"schmuck-detail";}']]></field>
				</row>
			</content>
		</article>
	</datacontainer>
</clipboard>

 */
$importfile = "C:/wampneu/www/heike/importfile.txt";
$zeilen = file($importfile);
$c=1;
echo "count Zeilen: " . count($zeilen) . "<br>";
$albumprefix="";
$filedir = "";
$commentfile ="";
$commentfile = fopen("C:/wampneu/www/heike/comment.sql", "w") or die("Unable to open commentfile!");
foreach ($zeilen as $zeile) { 
  $sp = explode (" " , trim($zeile));
  echo "$c: " . count($sp) . " $zeile<br>"; $c++;   
  if (count($sp) != 2) continue;
  switch ($sp[0]) {
    case "filegal" : 
      $albumprefix=$sp[1];
      break;
    case "filedir" :
      $filedir=$sp[1] . "/";
      break;
    case "filename" :
      $filename=$filedir . $sp[1];
      //echo "XXXX:$filename<br>";
      getHeikeHtml ($filename,$staticXml,$albumprefix,$commentfile); 
      break;
  } 
}
?>                                             