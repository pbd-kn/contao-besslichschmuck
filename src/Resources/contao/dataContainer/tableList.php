<?php

namespace Pbdkn\ContaoBesslichschmuck\Resources\contao\dataContainer;

use Contao\DC_Table;

class tableList extends DC_Table
{
    public function __construct($strTable, $arrModule = [])
    {
        parent::__construct($strTable, $arrModule);
    }

    protected function loadData()
    {
        if ($this->searchValue !== '')
        {
            // Wenn die Suchzeichenkette einen RegEx beinhaltet
            if (preg_match('/^\/.*\/$/', $this->searchValue))
            {
                $strRegEx = trim($this->searchValue, '/');
                $this->searchValue = '';
                
                // Angepasste Suchabfrage mit RegEx
                $this->procedure[] = "Artikel REGEXP ?";
                $this->values[] = $strRegEx;
            }
        }

        parent::loadData();
    }
}
