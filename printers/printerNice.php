<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) die();
require_once 'printer.php';

class nspages_printerNice extends nspages_printer {
    private $nbCols;

    function __construct($plugin, $mode, $renderer, $nbCols){
        parent::__construct($plugin, $mode, $renderer);
        $this->nbCols = $this->_computeActualNbCols($nbCols);
    }

    function _print($tab, $type, $text, $reverse) {
        //use actpage to count how many pages we have already processed
        $actpage = 0;

        $nbItemPerColumns = $this->_computeNbItemPerColumns(sizeof($tab));

        $percentWidth = 100 / sizeof($nbItemPerColumns);
        $percentWidth .= '%';

        $this->renderer->doc .= "\n".'<div class="catpagecol" style="width: '.$percentWidth.'" >';
        //firstchar stores the first character of the last added page
        $firstchar = $this->_firstChar($tab[0]);

        //write the first index-letter
        $this->renderer->doc .= '<div class="catpagechars">'.$firstchar."</div>\n<ul class=\"nspagesul\">\n";

        $idxCol = 0;
        foreach($tab as $item) {
            //change to the next column if necessary
            if($actpage == $nbItemPerColumns[$idxCol]) {
                $idxCol++;
                $this->renderer->doc .= "</ul></div>\n".'<div class="catpagecol" style="width: '.$percentWidth.'">'."\n";

                $newLetter = $this->_firstChar($item);
                if($newLetter != $firstchar) {
                    $firstchar = $newLetter;
                    $this->renderer->doc .= '<div class="catpagechars">'.$firstchar."</div>\n<ul class=\"nspagesul\">\n";
                } else {
                    $this->renderer->doc .= '<div class="catpagechars">'.$firstchar.$this->plugin->getLang('continued')."</div>\n<ul class=\"nspagesul\">\n";
                }
            }
            //write the index-letters
            $newLetter = $this->_firstChar($item);
            if($newLetter != $firstchar) {
                $firstchar = $newLetter;
                $this->renderer->doc .= '</ul><div class="catpagechars">'.$firstchar."</div>\n<ul class=\"nspagesul\">\n";
            }

            $this->_printElement($item);
            $actpage++;
        }
        $this->renderer->doc .= "</ul></div>\n";
    }

    private function _firstChar($item) {
        return utf8_strtoupper(utf8_substr($item['sort'], 0, 1));
    }

    private function _computeActualNbCols($nbCols){
        $nbCols = (int) $nbCols;
        if(!isset($nbCols) || is_null($nbCols) || $nbCols < 1) {
            $nbCols = 3;
        }
        return $nbCols;
    }

    /**
     * Compute the number of element to display per column
     * When $nbItems / $nbCols isn't an int, we make sure, for aesthetic reasons,
     * that the first are the ones which have the more items
     * Moreover, if we don't have enought items to display, we may choose to display less than the number of columns wanted
     *
     * @param int $nbItems The total number of items to display
     * @return an array which contains $nbCols int.
     */
    private function _computeNbItemPerColumns($nbItems) {
        $result = array();

        if($nbItems < $this->nbCols) {
            for($idx = 0; $idx < $nbItems; $idx++) {
                $result[] = $idx + 1;
            }
            return $result;
        }

        $collength    = $nbItems / $this->nbCols;
        $nbItemPerCol = array();
        for($idx = 0; $idx < $this->nbCols; $idx++) {
            $nbItemPerCol[] = ceil(($idx + 1) * $collength) - ceil($idx * $collength);
        }
        rsort($nbItemPerCol);

        $result[] = $nbItemPerCol[0];
        for($idx = 1; $idx < $this->nbCols; $idx++) {
            $result[] = end($result) + $nbItemPerCol[$idx];
        }

        return $result;
    }
}
