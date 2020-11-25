<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) die();
require_once 'sorters.php';

abstract class nspages_printer {
    protected $plugin;
    protected $renderer;
    protected $mode;
    private $pos;
    private $actualTitleLevel;
    private $natOrder;
    private $nbItemsMax;
    private $dictOrder;
    protected $_displayModificationDate;
    protected $_sorter;

    function __construct($plugin, $mode, $renderer, $data){
      $this->plugin = $plugin;
      $this->renderer =& $renderer;
      $this->mode = $mode;
      $this->pos = $data['pos'];
      $this->natOrder = $data['natOrder'];
      $this->actualTitleLevel = $data['actualTitleLevel'];
      $this->nbItemsMax = $data['nbItemsMax'];
      $this->dictOrder = $data['dictOrder'];
      $this->_displayModificationDate = $data['displayModificationDate']
        || $data['modificationDateOnPictures']; // This is a deprecated option. We should kill it after checking no users are still using it
      $this->_sorter = $this->_getSorter($data['reverse']);
    }

    function printTOC($tab, $type, $text, $hideno){
        $this->_printHeader($tab, $type, $text, $hideno);

        if(empty($tab)) {
            return;
        }

        $this->_print($tab, $type);
    }

    abstract function _print($tab, $type);

    function printUnusableNamespace($wantedNS){
         $this->renderer->section_open(1);
         $this->renderer->cdata($this->plugin->getLang('doesntexist').$wantedNS);
         $this->renderer->section_close();
    }

    private function _printHeader(&$tab, $type, $text, $hideno) {
        if(empty($tab) && $hideno) return;

        $this->_sorter->sort($tab);
        $this->_keepOnlyNMaxItems($tab);

        if($text != '') {
            if($this->actualTitleLevel){
                $this->renderer->header($text, $this->actualTitleLevel, $this->pos);
            } else if($this->mode == 'xhtml') {
                $this->renderer->doc .= '<p class="catpageheadline">';
                $this->renderer->cdata($text);
                $this->renderer->doc .= '</p>';
            } else {
                $this->renderer->linebreak();
                $this->renderer->p_open();
                $this->renderer->cdata($text);
                $this->renderer->p_close();
            }
        }

        if(empty($tab)) {
            $this->renderer->p_open();
            $this->renderer->cdata($this->plugin->getLang(($type == 'page') ? 'nopages' : 'nosubns'));
            $this->renderer->p_close();
        }
    }

    private function _getSorter($reverse) {
        if ( $this->natOrder ){
            return new nspages_naturalOrder_sorter($reverse);
        } else if ($this->dictOrder) {
            return new nspages_dictOrder_sorter($reverse, $this->dictOrder);
        } else {
            return new nspages_default_sorter($reverse);
        }
    }

    private function _keepOnlyNMaxItems(&$tab){
        if ($this->nbItemsMax){
            $tab = array_slice($tab, 0, $this->nbItemsMax);
        }
    }

    /**
     * @param Array        $item      Represents the file
     */
    protected function _printElement($item, $level=1, $node=false) {
        $this->_printElementOpen($level, $node);
        $this->_printElementContent($item);
        $this->_printElementClose();
    }

    protected function _printElementOpen($level=1, $node=false) {
        if($item['type'] !== 'd') {
            $this->renderer->listitem_open($level, $node);
        } else { //Case of a subnamespace
            if($this->mode == 'xhtml') {
                $this->renderer->doc .= '<li class="closed">';
            } else {
                $this->renderer->listitem_open($level, $node);
            }
        }
    }

    protected function _printElementContent($item) {
        $this->renderer->listcontent_open();
        $this->_printElementLink($item);
        $this->renderer->listcontent_close();
    }

    protected function _printElementLink($item) {
        $linkText = "";
        if ($this->_displayModificationDate) {
          $linkText = '[' . date('Y-m-d', $item["mtime"]) . '] - ';
        }
        $linkText .= $item['nameToDisplay'];
        $this->renderer->internallink(':'.$item['id'], $linkText);
    }

    protected function _printElementClose() {
        $this->renderer->listitem_close();
    }

    function printBeginning(){
        if($this->mode == 'xhtml') {
            $this->renderer->doc .= '<div class="plugin_nspages">';
        }
    }

    function printEnd(){
        //this is needed to make sure everything after the plugin is written below the output
        if($this->mode == 'xhtml') {
            $this->renderer->doc .= '<div class="catpageeofidx"></div>';
            $this->renderer->doc .= '</div>';
        } else {
            $this->renderer->linebreak();
        }
    }

    function printTransition(){ }
}
