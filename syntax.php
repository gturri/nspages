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
        $return = array(
            'subns'                => false, 'nopages' => false, 'simpleList' =>
            false, 'excludedPages' => array(), 'excludedNS' => array(),
            'title'                => false, 'wantedNS' => '', 'wantedDir' => '', 'safe' => true,
            'textNS'               => '', 'textPages' => '', 'pregPagesOn' => array(),
            'pregPagesOff'         => array(), 'pregNSOn' => array(), 'pregNSOff' => array(),
            'maxDepth'             => (int) 1, 'nbCol' => 3, 'simpleLine' => false,
            'sortid'               => false, 'reverse' => false,
            'pagesinns'              => false,
        );

        $match = utf8_substr($match, 9, -1); //9 = strlen("<nspages ")
        $match .= ' ';

        //Looking the first options
        $this->_checkOption($match, "/-subns/i", $return['subns'], true);
        $this->_checkOption($match, "/-nopages/i", $return['nopages'], true);
        $this->_checkOption($match, "/-simpleListe?/i", $return['simpleList'], true);
        $this->_checkOption($match, "/-title/i", $return['title'], true);
        $this->_checkOption($match, "/-h1/i", $return['title'], true);
        $this->_checkOption($match, "/-simpleLine/i", $return['simpleLine'], true);
        $this->_checkOption($match, "/-sort(By)?Id/i", $return['sortid'], true);
        $this->_checkOption($match, "/-reverse/i", $return['reverse'], true);
        $this->_checkOption($match, "/-pagesinns/i", $return['pagesinns'], true);

        //Looking for the -r option
        if(preg_match("/-r *=? *\"?([[:digit:]]*)\"?/i", $match, $found)) {
            if($found[1] != '') {
                $return['maxDepth'] = (int) $found[1];
            } else {
                $return['maxDepth'] = 0; //no limit
            }
            $match = str_replace($found[0], '', $match);
        }

        //Looking for the number of columns
        if(preg_match("/-nb?Cols? *=? *\"?([[:digit:]]*)\"?/i", $match, $found)) {
            if($found[1] != '') {
                $return['nbCol'] = max((int) $found[1], 1);
            }
            $match = str_replace($found[0], '', $match);
        }

        //Looking for the -textPages option
        if(preg_match("/-textPages? *= *\"([^\"]*)\"/i", $match, $found)) {
            $return['textPages'] = $found[1];
            $match               = str_replace($found[0], '', $match);
        } else {
            $return['textPages'] = $this->getLang('pagesinthiscat');
        }
        $return['textPages'] = htmlspecialchars($return['textPages']);

        //Looking for the -textNS option
        if(preg_match("/-textNS *= *\"([^\"]*)\"/i", $match, $found)) {
            $return['textNS'] = $found[1];
            $match            = str_replace($found[0], '', $match);
        } else {
            $return['textNS'] = $this->getLang('subcats');
        }
        $return['textNS'] = htmlspecialchars($return['textNS']);

        //Looking for preg options
        $this->_checkRegEx($match, "/-pregPages?On=\"([^\"]*)\"/i", $return['pregPagesOn']);
        $this->_checkRegEx($match, "/-pregPages?Off=\"([^\"]*)\"/i", $return['pregPagesOff']);
        $this->_checkRegEx($match, "/-pregNSOn=\"([^\"]*)\"/i", $return['pregNSOn']);
        $this->_checkRegEx($match, "/-pregNSOff=\"([^\"]*)\"/i", $return['pregNSOff']);

        //Looking for excluded pages and subnamespaces
        //--Checking if specified subnamespaces have to be excluded
        preg_match_all("/-exclude:([^[ <>]*):/", $match, $found, PREG_SET_ORDER);
        foreach($found as $subns) {
            $return['excludedNS'][] = $subns[1];
            $match                  = str_replace($subns[0], '', $match);
        }

        //--Checking if specified pages have to be excluded
        preg_match_all("/-exclude:([^[ <>]*) /", $match, $found, PREG_SET_ORDER);
        foreach($found as $page) {
            $return['excludedPages'][] = $page[1];
            $match                     = str_replace($page[0], '', $match);
        }

        //--Looking if the current page has to be excluded
        global $ID;
        if(preg_match("/-exclude /", $match, $found)) {
            $return['excludedPages'][] = noNS($ID);
            $match                     = str_replace($found[0], '', $match);
        }

        //--Looking if the syntax -exclude[item1 item2] has been used
        if(preg_match("/-exclude:\[(.*)\]/", $match, $found)) {
            $match = str_replace($found[0], '', $match);
            $found = str_replace('@', '', $found[1]); //for retrocompatibility
            $found = explode(' ', $found);
            foreach($found as $item) {
                if($item[strlen($item) - 1] == ':') { //not utf8_strlen() on purpose
                    $return['excludedNS'][] = utf8_substr($item, 0, -1);
                } else {
                    $return['excludedPages'][] = $item;
                }
            }
        }

        //Looking for the wanted namespace
        //Now, only the wanted namespace remains in $match
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

    /**
     * Check if a given option has been given, and remove it from the initial string
     *
     * @param string $match The string match by the plugin
     * @param string $pattern The pattern which activate the option
     * @param        $varAffected The variable which will memorise the option
     * @param        $valIfFound the value affected to the previous variable if the option is found
     */
    function _checkOption(&$match, $pattern, &$varAffected, $valIfFound) {
        if(preg_match($pattern, $match, $found)) {
            $varAffected = $valIfFound;
            $match       = str_replace($found[0], '', $match);
        }
    } // _checkOption

    function _checkRegEx(&$match, $pattern, &$arrayAffected) {
        preg_match_all($pattern, $match, $found, PREG_SET_ORDER);
        foreach($found as $regex) {
            $arrayAffected[] = $regex[1];
            $match           = str_replace($regex[0], '', $match);
        }
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
