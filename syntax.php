<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Guillaume Turri <guillaume.turri@gmail.com>
 * @author  Daniel Schranz <xla@gmx.at>
 * @author  Ignacio Bergmann
 * @author  Andreas Gohr <gohr@cosmocode.de>
 */
if(!defined('DOKU_INC')) die();
require_once 'printers/printerOneLine.php';
require_once 'printers/printerSimpleList.php';
require_once 'printers/printerNice.php';
require_once 'fileHelper/fileHelper.php';
require_once 'optionParser.php';
require_once 'namespaceFinder.php';

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_nspages extends DokuWiki_Syntax_Plugin {
    function connectTo($aMode) {
        $this->Lexer->addSpecialPattern('<nspages[^>]*>', $aMode, 'plugin_nspages');
    }

    function getSort() {
        //Execute before html mode
        return 189;
    }

    function getType() {
        return 'substition';
    }

    function handle($match, $state, $pos, &$handler) {
        $return = $this->_getDefaultOptions();
        $return['pos'] = $pos;

        $match = utf8_substr($match, 9, -1); //9 = strlen("<nspages ")
        $match .= ' ';

        optionParser::checkOption($match, "/-subns/i", $return['subns'], true);
        optionParser::checkOption($match, "/-nopages/i", $return['nopages'], true);
        optionParser::checkOption($match, "/-simpleListe?/i", $return['simpleList'], true);
        optionParser::checkOption($match, "/-title/i", $return['title'], true);
        optionParser::checkOption($match, "/-h1/i", $return['title'], true);
        optionParser::checkOption($match, "/-simpleLine/i", $return['simpleLine'], true);
        optionParser::checkOption($match, "/-sort(By)?Id/i", $return['sortid'], true);
        optionParser::checkOption($match, "/-reverse/i", $return['reverse'], true);
        optionParser::checkOption($match, "/-pagesinns/i", $return['pagesinns'], true);
        optionParser::checkRecurse($match, $return['maxDepth']);
        optionParser::checkNbColumns($match, $return['nbCol']);
        optionParser::checkTextPages($match, $return['textPages'], $this);
        optionParser::checkTextNs($match, $return['textNS'], $this);
        optionParser::checkRegEx($match, "/-pregPages?On=\"([^\"]*)\"/i", $return['pregPagesOn']);
        optionParser::checkRegEx($match, "/-pregPages?Off=\"([^\"]*)\"/i", $return['pregPagesOff']);
        optionParser::checkRegEx($match, "/-pregNSOn=\"([^\"]*)\"/i", $return['pregNSOn']);
        optionParser::checkRegEx($match, "/-pregNSOff=\"([^\"]*)\"/i", $return['pregNSOff']);
        optionParser::checkExclude($match, $return['excludedPages'], $return['excludedNS']);
        optionParser::checkAnchorName($match, $return['anchorName']);
        optionParser::checkActualTitle($match, $return['actualTitleLevel']);

        //Now, only the wanted namespace remains in $match
        $nsFinder = new namespaceFinder($match);
        $return['wantedNS'] = $nsFinder->getWantedNs();
        $return['safe'] = $nsFinder->isNsSafe();
        $return['wantedDir'] = $nsFinder->getWantedDirectory();

        return $return;
    }

    private function _getDefaultOptions(){
        return array(
            'subns'                => false, 'nopages' => false, 'simpleList' =>
            false, 'excludedPages' => array(), 'excludedNS' => array(),
            'title'                => false, 'wantedNS' => '', 'wantedDir' => '', 'safe' => true,
            'textNS'               => '', 'textPages' => '', 'pregPagesOn' => array(),
            'pregPagesOff'         => array(), 'pregNSOn' => array(), 'pregNSOff' => array(),
            'maxDepth'             => (int) 1, 'nbCol' => 3, 'simpleLine' => false,
            'sortid'               => false, 'reverse' => false,
            'pagesinns'            => false, 'anchorName' => null, 'actualTitleLevel' => false
        );
    }

    function render($mode, &$renderer, $data) {
        global $conf;
        $printer = $this->_selectPrinter($mode, $renderer, $data);

        if( ! $this->_isNamespaceUsable($data)){
            $printer->printUnusableNamespace($data['wantedNS']);
            return TRUE;
        }

        $fileHelper = new fileHelper($data);
        $pages = $fileHelper->getPages();
        $subnamespaces = $fileHelper->getSubnamespaces();
        if ( $this->_shouldPrintPagesAmongNamespaces($data) ){
            $subnamespaces = array_merge($subnamespaces, $pages);
        }

        $this->_print($printer, $data, $subnamespaces, $pages);
        $printer->printEnd();

        return TRUE;
    }

    private function _shouldPrintPagesAmongNamespaces($data){
        return $data['pagesinns'];
    }

    private function _print($printer, $data, $subnamespaces, $pages){
        if($data['subns']) {
            $printer->printTOC($subnamespaces, 'subns', $data['textNS'], $data['reverse']);
        }

        if(!$this->_shouldPrintPagesAmongNamespaces($data)) {

            if ( $this->_shouldPrintTransition($data) ){
              $printer->printTransition();
            }

            if(!$data['nopages']) {
                $printer->printTOC($pages, 'page', $data['textPages'], $data['reverse']);
            }
        }
    }

    private function _shouldPrintTransition($data){
        return $data['textPages'] === '' && !$data['nopages'] && $data['subns'];
    }

    private function _isNamespaceUsable($data){
        global $conf;
        return @opendir($conf['datadir'] . '/' . $data['wantedDir']) !== false && $data['safe'];
    }

    private function _selectPrinter($mode, &$renderer, $data){
        if($data['simpleList']) {
            return new nspages_printerSimpleList($this, $mode, $renderer, $data);
        } else if($data['simpleLine']) {
            return new nspages_printerOneLine($this, $mode, $renderer, $data);
        } else if($mode == 'xhtml') {
            return new nspages_printerNice($this, $mode, $renderer, $data['nbCol'], $data['anchorName'], $data);
        }
        return new nspages_printerSimpleList($this, $mode, $renderer, $data);
    }
}
