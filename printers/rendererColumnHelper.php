<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) die();

class rendererColumnHelper {
    private $nbCols;

    function __construct($nbCols){
        $this->nbCols = $this->_computeActualNbCols($nbCols);
    }
    
    private function _computeActualNbCols($nbCols){
        dbg("computeActualNbCols");
        dbg($nbCols);
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
    public function _computeNbItemPerColumns($nbItems) {
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
