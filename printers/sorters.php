<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) die();

abstract class nspages_sorter {
    protected $reverse;

    function __construct($reverse){
        $this->reverse = $reverse;
    }

    function sort(&$array){
        $this->actualSort($array);
        if ($this->reverse) {
          $array = array_reverse($array);
        }
    }

    protected function actualSort(&$array){
        usort($array, array($this, 'comparator'));
    }

    abstract function comparator($item1, $item2);
}

class nspages_default_sorter extends nspages_sorter {
    function __construct($reverse){
        parent::__construct($reverse);
    }

    function comparator($item1, $item2){
        return strcasecmp($p1['sort'], $p2['sort']);
    }
}

class nspages_naturalOrder_sorter extends nspages_sorter {
    function __construct($reverse){
        parent::__construct($reverse);
    }

    function comparator($item1, $item2){
        return strnatcasecmp($p1['sort'], $p2['sort']);
    }
}

class nspages_dictOrder_sorter extends nspages_sorter {
    function __construct($reverse){
        parent::__construct($reverse);
    }

    function actualSort(&$array){
        $oldLocale=setlocale(LC_ALL, 0);
        setlocale(LC_COLLATE, $this->dictOrder);
        usort($tab, array($this, "comparator"));
        setlocale(LC_COLLATE, $oldLocale);
    }

    function comparator($item1, $item2){
        return strcoll($p1['sort'], $p2['sort']);
    }
}

