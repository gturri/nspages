<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) die();
require_once 'printerITree.php';
require_once 'rendererXhtmlHelper.php';
require_once 'rendererColumnHelper.php';

class nspages_printerTreeXHTML extends nspages_printerITree {
    private $rendererColumnHelper;
    private $nbItemsPrinted;
    private $nbItems;

    function __construct($plugin, $mode, $renderer, $data){
        parent::__construct($plugin, $mode, $renderer, $data);
        $nbCols = 1;
        if($data['nbCol']){
            $nbCols = $data['nbCol'];
        }
        $this->rendererColumnHelper = new rendererColumnHelper($nbCols);
    }
    
    function _print($tab, $type) {
        $this->nbItems=sizeof($tab);
        $tree = $this->_groupByNs($tab);
        $trimmedTree = $this->_getTrimmedTree($tree);
        $orderedTree = $this->_orderTree($trimmedTree);
        $this->_printTree($orderedTree);
    }
    
    private function _printTree($tree) {
        $nbItemPerColumns = $this->rendererColumnHelper->_computeNbItemPerColumns($this->nbItems);
        $actualNbCols = count($nbItemPerColumns);
        $helper = new rendererXhtmlHelper($this->renderer, $actualNbCols, $this->plugin, $this->anchorName);
        $helper->openColumn();
        $helper->openListOfItems();
        $idxCol = 0;
        $this->nbItemsPrinted = 0;

        foreach($tree->children as $subTree){
            //change to the next column if necessary
            if($this->nbItemsPrinted >= $nbItemPerColumns[$idxCol]) {
                $idxCol++;
                $helper->closeListOfItems();
                $helper->closeColumn();
                $helper->openColumn();
                $helper->openListOfItems();
            }
            $this->_printSubTree($subTree, 1, $helper);
        }

         foreach($tree->pages as $page){
             $this->_printElement($page, 1);
         }

        $helper->closeListOfItems();
        $helper->closeColumn();
    }

    private function _printSubTree($tree, $level, $helper) {
        $helper->openListOfItems();
        if ( !is_null($tree->self) ){
            $this->_printElementContent($tree->self, $level);
        } else {
          $this->renderer->doc .= '<div>' . $tree->id  . '</div>';
        }

        $hasInnerData = !empty($tree->children) || !empty($tree->pages);
        if($hasInnerData){
            $helper->openListOfItems();
        }
        foreach($tree->children as $subTree){
            $this->_printSubTree($subTree, $level+1, $helper);
        }
        foreach($tree->pages as $page){
            $this->_printElement($page, $level+1);
            $this->nbItemsPrinted++;
        }
        if($hasInnerData){
            $helper->closeListOfItems();
        }
        $helper->closeListOfItems();
    }
}
