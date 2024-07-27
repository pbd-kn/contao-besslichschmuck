<?php

/*
 * Hilfsfunktionen zum Gallery Creator
 * 
 */
 
namespace Pbdkn\ContaoBesslichschmuck\Resources\contao\classes;
use Markocupic\GalleryCreatorBundle\Model\GalleryCreatorPicturesModel;

class GC_Helper extends \contao\System
{
  public function __construct()
  {
//\System::log("PBD Besslich constructor GC_Helper ", __METHOD__, TL_GENERAL);
      //$this->import('Database');
  }

  /* ueberprüft ob zu dem Namen ein Bild im Gallerygenerator vorhanden ist
  */
  public function getPicture ($name) {
//\System::log("PBD Besslich getPicture name $name", __METHOD__, TL_GENERAL);
    if(empty($name)||strlen($name)==0) return null;
    $this->import('Database');
    $pictures=GalleryCreatorPicturesModel::findAll();
//\System::log("PBD Besslich pictures array len: ".count($pictures), __METHOD__, TL_GENERAL);
    foreach ($pictures as $k=>$value)
    {
      $file = \FilesModel::findByUuid($value->uuid);
      $arrbasename=explode(".",basename($file->path));
      if (count($arrbasename) == 0) continue;
      if (trim(strtolower($name)) == $arrbasename[0]) {
//\System::log('PBD Besslich pictures gefunden '.$name.' basename ' . $arrbasename[0].' path ' . $file->path, __METHOD__, TL_GENERAL);
         $res['path']=$file->path;
         $res['uuid']=$value->uuid;
         $res['pid']=$value->pid;
         return $res;
      }
//\System::log("PBD Besslich pictures array[$k] pid ".$value->pid.' basename ' . $basename, __METHOD__, TL_GENERAL);
    }


    return null;
  } 
} 
?>