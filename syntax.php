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
require_once 'fileHelper.php';
require_once 'optionParser.php';

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_nspages extends DokuWiki_Syntax_Plugin {

    function connectTo($aMode) {
        $this->Lexer->addSpecialPattern('<nspages[^>]*>', $aMode, 'plugin_nspages');
    } // connectTo()

    function getSort() {
        //Execute before html mode
        return 189;
    } // getSort()

    function getType() {
        return 'substition';
    } // getType()

    function handle($match, $state, $pos, &$handler) {
        $return = $this->_getDefaultOptions();

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

        //Looking for the wanted namespace
        //Now, only the wanted namespace remains in $match
        global $ID;
        $wantedNS = trim($match);
        if($wantedNS == '') {
            //If there is nothing, we take the current namespace
            $wantedNS = '.';
        }
        if($wantedNS[0] == '.') {
            //if it start with a '.', it is a relative path
            $return['wantedNS'] = getNS($ID);
        }
        $return['wantedNS'] .= ':'.$wantedNS.':';

        //For security reasons, and to pass the cleanid() function, get rid of '..'
        $return['wantedNS'] = explode(':', $return['wantedNS']);

        for($i = 0; $i < count($return['wantedNS']); $i++) {
            if($return['wantedNS'][$i] === '' || $return['wantedNS'][$i] === '.') {
                array_splice($return['wantedNS'], $i, 1);
                $i--;
            } else if($return['wantedNS'][$i] == '..') {
                if($i == 0) {
                    //The first can't be '..', to stay inside 'data/pages'
                    break;
                } else {
                    //simplify the path, getting rid of 'ns:..'
                    array_splice($return['wantedNS'], $i - 1, 2);
                    $i -= 2;
                }
            }
        }

        if($return['wantedNS'][0] == '..') {
            //path would be outside the 'pages' directory
            $return['safe'] = false;
        }

        $return['wantedNS'] = implode(':', $return['wantedNS']);

        //Deduce the wanted directory
        $return['wantedDir'] = utf8_encodeFN(str_replace(':', '/', $return['wantedNS']));

        return $return;
    } // handle()

    private function _getDefaultOptions(){
        return array(
            'subns'                => false, 'nopages' => false, 'simpleList' =>
            false, 'excludedPages' => array(), 'excludedNS' => array(),
            'title'                => false, 'wantedNS' => '', 'wantedDir' => '', 'safe' => true,
            'textNS'               => '', 'textPages' => '', 'pregPagesOn' => array(),
            'pregPagesOff'         => array(), 'pregNSOn' => array(), 'pregNSOff' => array(),
            'maxDepth'             => (int) 1, 'nbCol' => 3, 'simpleLine' => false,
            'sortid'               => false, 'reverse' => false,
            'pagesinns'              => false,
        );
    }

    function render($mode, &$renderer, $data) {
        global $conf;
        $printer = $this->selectPrinter($mode, $renderer, $data);

        if( ! $this->_namespaceExists($data)){
            $printer->printUnusableNamespace($data['wantedNS']);
            return TRUE;
        }

        $fileHelper = new fileHelper($data);
        $pages = $fileHelper->getPages();
        $subnamespaces = $fileHelper->getSubnamespaces();
        if ( $data['pagesinns'] ){
            $subnamespaces = array_merge($subnamespaces, $pages);
        }

        $this->_print($printer, $data, $subnamespaces, $pages);
        $printer->printEnd();

        return TRUE;
    }

    private function _print($printer, $data, $subnamespaces, $pages){
        if($data['subns']) {
            $printer->printTOC($subnamespaces, 'subns', $data['textNS'], $data['reverse']);
        }

        if(!$data['pagesinns']) {

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

    private function _namespaceExists($data){
        global $conf;
        return @opendir($conf['datadir'] . '/' . $data['wantedDir']) !== false && $data['safe'];
    }

    function selectPrinter($mode, &$renderer, $data){
        if($data['simpleList']) {
            return new nspages_printerSimpleList($this, $mode, $renderer);
        } else if($data['simpleLine']) {
            return new nspages_printerOneLine($this, $mode, $renderer);
        } else if($mode == 'xhtml') {
            return new nspages_printerNice($this, $mode, $renderer, $data['nbCol']);
        }
        return new nspages_printerSimpleList($this, $mode, $renderer);
    }
} // class syntax_plugin_nspages
