<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) die();
require_once 'printer.php';

class nspages_printerOneLine extends nspages_printer {
    function __construct($plugin, $mode, $renderer){
        parent::__construct($plugin, $mode, $renderer);
    }

    function _print($tab, $type, $text, $reverse) {
        $sep = '';
        foreach($tab as $item) {
            $this->renderer->cdata($sep);
            $this->renderer->internallink(':'.$item['id'], $item['title']);
            $sep = ', ';
        }
    }

    function printTransition(){
      $this->renderer->cdata(', ');
    }
}
