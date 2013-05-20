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

        // Make sure the namespace exists
        if(@opendir($conf['datadir'] . '/' . $data['wantedDir']) === false || !$data['safe']) {
            $printer->printUnusableNamespace($data['wantedNS']);
            return TRUE;
        }

        // Getting the files
        $opt   = array(
            'depth'     => $data['maxDepth'], 'keeptxt'=> false, 'listfiles'=> !$data['nopages'],
            'listdirs'  => $data['subns'], 'pageonly'=> true, 'skipacl'=> false,
            'sneakyacl' => true, 'hash'=> false, 'meta'=> false, 'showmsg'=> false,
            'showhidden'=> false, 'firsthead'=> true
        );
        $files = array();
        search($files, $conf['datadir'], 'search_universal', $opt, $data['wantedDir']);

        $pages         = array();
        $subnamespaces = array();
        foreach($files as $item) {
            if($item['type'] == 'd') {
                if($this->_wantedFile($data['excludedNS'], $data['pregNSOn'], $data['pregNSOff'], $item)) {
                    $this->_prepareNS($item, $data['title']);
                    $subnamespaces[] = $item;
                }
            } else {
                if($this->_wantedFile($data['excludedPages'], $data['pregPagesOn'], $data['pregPagesOff'], $item)) {
                    $this->_preparePage($item, $data);
                    if($data['pagesinns']) {
                        $subnamespaces[] = $item;
                    } else {
                        $pages[] = $item;
                    }
                }
            }
        }

        //--listing the subnamespaces (if needed)
        if($data['subns']) {
            $printer->printTOC($subnamespaces, 'subns', $data['textNS'], $data['reverse']);
        }

        if(!$data['pagesinns']) {

            if($data['textPages'] === '' && !$data['nopages']) {
              $printer->printTransition();
            }

            //--listing the pages
            if(!$data['nopages']) {
                $printer->printTOC($pages, 'page', $data['textPages'], $data['reverse']);
            }

        }

        $printer->printEnd();

        return TRUE;
    } // render()

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

    /**
     * Check if the user wants a file to be displayed.
     * Filters consider the "id" and not the "title". Therefore, the treatment is the same for files and for subnamespace.
     * Moreover, filters remain valid even if the title of a page is changed.
     *
     * @param Array  $excludedFiles  A list of files that shouldn't be displayed
     * @param Array  $pregOn       RegEx that a file should match to be displayed
     * @param Array  $pregOff      RegEx that a file shouldn't match to be displayed
     * @param string $file
     * @return bool
     */
    function _wantedFile($excludedFiles, $pregOn, $pregOff, $file) {
        $wanted = true;
        $noNSId = noNS($file['id']);
        $wanted &= (!in_array($noNSId, $excludedFiles));
        foreach($pregOn as $preg) {
            $wanted &= preg_match($preg, $noNSId);
        }
        foreach($pregOff as $preg) {
            $wanted &= !preg_match($preg, $noNSId);
        }
        return $wanted;
    }

    /**
     * Fix or build attributes a page should have
     */
    function _preparePage(&$page, $data) {
        if(!$data['title'] || $page['title'] === null) {
            $page['title'] = noNS($page['id']);
        }

        if($data['sortid']) {
            $page['sort'] = noNS($page['id']);
        } else {
            $page['sort'] = $page['title'];
        }
    }

    /**
     * When we display a namespace, we want to:
     * - link to it's main page (if such a page exists)
     * - get the id of this main page (if the option is active)
     *
     * @param         $ns  A structure which represents a namespace
     * @param boolean $useTitle Do we have to check the title of the ns?
     */
    function _prepareNS(&$ns, $useTitle) {
        $idMainPage = $ns['id'].':';
        resolve_pageid('', $idMainPage, $exist); //get the id of the main page of the ns
        $ns['title'] = noNS($ns['id']);

        if(!$exist) { //if there is currently no main page for this namespace, then...
            $ns['id'] .= ':'; //...we'll link directly to the namespace
        } else { //if there is a main page, then...
            $title = p_get_first_heading($idMainPage, true); //...we adapt the title to use
            if(!is_null($title) && $useTitle) {
                $ns['title'] = $title;
            }
            $ns['id'] = $idMainPage; //... and we'll link directly to this page
        }
        $ns['sort'] = $ns['title'];
    }


} // class syntax_plugin_nspages
