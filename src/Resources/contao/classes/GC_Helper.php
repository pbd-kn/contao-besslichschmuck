<?php

/*
 * Hilfsfunktionen zum Gallery Creator
 * 
 */
 
namespace PBDKN\ContaoBesslichschmuck\Resources\contao\classes;

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
\System::log("PBD Besslich getPicture name $name", __METHOD__, TL_GENERAL);
    if(empty($name)||strlen($name)==0) return null;
    $this->import('Database');
/*
    $objAlbums = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE pid=? AND published=? ')->execute(0, 1);
\System::log('PBD Besslich getPicture query '.'SELECT * FROM tl_gallery_creator_albums WHERE pid=0 AND published=1 ', __METHOD__, TL_GENERAL);
    $arrSelectedAlbums =$objAlbums->fetchEach('id');
    
    foreach ($arrSelectedAlbums as $key => $albumId)
    {
\System::log("PBD Besslich getPicture objAlbums[$key]:$albumId", __METHOD__, TL_GENERAL);
        $objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE (SELECT COUNT(id) FROM tl_gallery_creator_pictures WHERE pid = ? AND published=?) > 0 AND id=? AND published=?')->execute($albumId, 1, $albumId, 1);
        // if the album doesn't exist
        if (!$objAlbum->numRows && !GalleryCreatorAlbumsModel::hasChildAlbums($objAlbum->id) && !$this->gc_hierarchicalOutput)
        {
            unset($arrSelectedAlbums[$key]);
            continue;
        }
    }
    // build up the new array
    $arrSelectedAlbums = array_values($arrSelectedAlbums);
    // abort if no album is selected
    if (empty($arrSelectedAlbums))
    {
\System::log("PBD Besslich getPicture arrSelectedAlbums 1 leer", __METHOD__, TL_GENERAL);
        return null;
    }

    foreach ($arrSelectedAlbums as $k => $albumId)
    {
      $objAlbum = \GalleryCreatorAlbumsModel::findByPk($albumId);
      if ($objAlbum->pid > 0)
      {
        unset($arrSelectedAlbums[$k]);
      }
    }
    $arrSelectedAlbums = array_values($arrSelectedAlbums);
    if (empty($arrSelectedAlbums))
    {
\System::log("PBD Besslich getPicture arrSelectedAlbums 2 leer", __METHOD__, TL_GENERAL);
        return null;
    }
*/    
    $pictures=\GalleryCreatorPicturesModel::findAll();
\System::log("PBD Besslich pictures array len: ".count($pictures), __METHOD__, TL_GENERAL);
    foreach ($pictures as $k=>$value)
    {
      $file = \FilesModel::findByUuid($value->uuid);
      $teile = explode(" ", $pizza);
      $arrbasename=explode(".",basename($file->path));
      if (count($arrbasename) == 0) continue;
      if (trim(strtolower($name)) == $arrbasename[0]) {
\System::log('PBD Besslich pictures gefunden '.$name.' basename ' . $arrbasename[0].' path ' . $file->path, __METHOD__, TL_GENERAL);
         $res[path]=$file->path;
         $res[uuid]=$value->uuid;
         $res[pid]=$value->pid;
         return $res;
      }
\System::log("PBD Besslich pictures array[$k] pid ".$value->pid.' basename ' . $basename, __METHOD__, TL_GENERAL);
    }


    return null;
  } 
} 
?>