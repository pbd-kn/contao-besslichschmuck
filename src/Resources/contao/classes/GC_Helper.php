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
      //$this->import('Database');
  }

  /* ueberprüft ob zu dem Namen ein Bild im Gallerygenerator vorhanden ist
  */
  public function getPicture ($name) {
\System::log("PBD Besslich getPicture2Name name $name", __METHOD__, TL_GENERAL);
    if(empty($name)||strlen($name)==0) return null;
    $this->import('Database');

    $objAlbums = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE pid=? AND published=? ')->execute(0, 1);
    foreach ($objAlbums as $key => $albumId)
    {
\System::log("PBD Besslich getPicture2Name objAlbums[$key]:$albumId", __METHOD__, TL_GENERAL);

        $objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE (SELECT COUNT(id) FROM tl_gallery_creator_pictures WHERE pid = ? AND published=?) > 0 AND id=? AND published=?')->execute($albumId, 1, $albumId, 1);

        // if the album doesn't exist
        if (!$objAlbum->numRows && !GalleryCreatorAlbumsModel::hasChildAlbums($objAlbum->id) && !$this->gc_hierarchicalOutput)
        {
            unset($this->arrSelectedAlbums[$key]);
            continue;
        }
        // remove id from $this->arrSelectedAlbums if user is not allowed
        if (TL_MODE == 'FE' && $objAlbum->protected == true)
        {
            if (!$this->authenticate($objAlbum->alias))
            {
                unset($this->arrSelectedAlbums[$key]);
                continue;
            }
        }
    }

    return null;
  } 
}
?>