<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) die();
require_once 'printerITree.php';

class nspages_printerTree extends nspages_printerITree {
    private $rootNS;

    function __construct($plugin, $mode, $renderer, $data){
        parent::__construct($plugin, $mode, $renderer, $data);
    }

    function _print($tab, $type) {
        $tree = $this->_groupByNs($tab);
        $trimmedTree = $this->_getTrimmedTree($tree);
        $orderedTree = $this->_orderTree($trimmedTree);
        $this->_printTree($orderedTree);
    }

    private function _printTree($tree) {
        $this->renderer->listu_open();

        foreach($tree->children as $subTree){
            $this->_printSubTree($subTree, 1);
        }

         foreach($tree->pages as $page){
             $this->_printElement($page, 1);
         }

         $this->renderer->listu_close();
    }

    private function _printSubTree($tree, $level) {
        $this->_printElementOpen($level);
        if ( !is_null($tree->self) ){
            $this->_printElementContent($tree->self, $level);
        } else {
          $this->renderer->doc .= '<div>' . $tree->id  . '</div>';
        }

        $hasInnerData = !empty($tree->children) || !empty($tree->pages);
        if($hasInnerData){
            $this->renderer->listu_open();
        }
        foreach($tree->children as $subTree){
            $this->_printSubTree($subTree, $level+1);
        }
        foreach($tree->pages as $page){
            $this->_printElement($page, $level+1);
        }
        if($hasInnerData){
            $this->renderer->listu_close();
        }
        $this->_printElementClose();
    }
}
