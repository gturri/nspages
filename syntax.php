<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Guillaume Turri <guillaume.turri@gmail.com>
 *
 * modified under the terms of the GPL by Daniel Schranz <xla@gmx.at>
 * date of change: February 4th 2009
 * changes made only to function render(...), but added the file style.css
 *  implemented a three-column output, similar to the way Wikipedia has styled it's category pages
 *
 * modified under the terms of the GPL by Ignacio Bergmann
 * date of change: 2009-06-02
 * introduction of Doku_Renderer_xhtml in the _printElement function
 */


if (! defined('DOKU_INC')) die();

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
require_once(DOKU_INC . 'inc/search.php');//to use the search() functions
require_once(DOKU_INC . 'inc/pageutils.php');//to use noNS, getNS and resolve_pageid
require_once(DOKU_INC . 'inc/parserutils.php');//to use the p_get_first_heading function


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
    $return = array('subns' => false, 'nopages' => false, 'simpleList' =>
            false, 'excludedPages' => array(), 'excludedNS' => array(),
            'title' => false, 'wantedNS' => '', 'wantedDir' => '', 'safe' => true,
            'textNS' => '', 'textPages' => '', 'pregPagesOn' => array(),
            'pregPagesOff' => array(), 'pregNSOn' => array(), 'pregNSOff' => array(),
            'maxDepth' => (int) 1, 'nbCol' => 3, 'simpleLine' => false);

     $match = utf8_substr($match, 9, -1); //9 = strlen("<nspages ")
     $match .= ' ';
  
    //Looking the first options
    $this->_checkOption($match, "/-subns/i", $return['subns'], true);
    $this->_checkOption($match, "/-nopages/i", $return['nopages'], true);
    $this->_checkOption($match, "/-simpleListe?/i", $return['simpleList'], true);
    $this->_checkOption($match, "/-title/i", $return['title'], true);
    $this->_checkOption($match, "/-h1/i", $return['title'], true);
    $this->_checkOption($match, "/-simpleLine/i", $return['simpleLine'], true);

    //Looking for the -r option
    if ( preg_match("/-r *=? *\"?([[:digit:]]*)\"?/i", $match, $found) ){
      if ( $found[1] != '' ){
        $return['maxDepth'] = (int) $found[1];
      } else {
        $return['maxDepth'] = 0; //no limit
      }
      $match = str_replace($found[0], '', $match);
    }

    //Looking for the number of columns
    if ( preg_match("/-nb?Cols? *=? *\"?([[:digit:]]*)\"?/i", $match, $found) ){
      if ( $found[1] != '' ){
        $return['nbCol'] = max((int) $found[1], 1);
      }
      $match = str_replace($found[0], '', $match);
    }

    //Looking for the -textPages option
    if ( preg_match("/-textPages? *= *\"([^\"]*)\"/i", $match, $found) ){
      $return['textPages'] = $found[1];
      $match = str_replace($found[0], '', $match);
    } else {
      $return['textPages'] = $this->getLang('pagesinthiscat');
    }
    $return['textPages'] = htmlspecialchars($return['textPages']);

    //Looking for the -textNS option
    if ( preg_match("/-textNS *= *\"([^\"]*)\"/i", $match, $found) ){
      $return['textNS'] = $found[1];
      $match = str_replace($found[0], '', $match);
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
    foreach ( $found as $subns ){
      $return['excludedNS'][] = $subns[1];
      $match = str_replace($subns[0], '', $match);
    }

      //--Checking if specified pages have to be excluded
    preg_match_all("/-exclude:([^[ <>]*) /", $match, $found, PREG_SET_ORDER);
    foreach ( $found as $page ){
      $return['excludedPages'][] = $page[1];
      $match = str_replace($page[0], '', $match);
    }

      //--Looking if the current page has to be excluded
    global $ID;
    if ( preg_match("/-exclude /", $match, $found) ){
      $return['excludedPages'][] = noNS($ID);
      $match = str_replace($found[0], '', $match);
    }

    //--Looking if the syntax -exclude[item1 item2] has been used
    if(preg_match("/-exclude:\[(.*)\]/", $match, $found)){
      $match = str_replace($found[0], '', $match);
      $found = str_replace('@', '', $found[1]); //for retrocompatibility
      $found = explode(' ', $found);
      foreach ( $found as $item ){
        if ( $item[strlen($item)-1] == ':' ){ //not utf8_strlen() on purpose
          $return['excludedNS'][] = utf8_substr($item, 0, -1);
        } else {
          $return['excludedPages'][] = $item;
        }
      }
    }

    //Looking for the wanted namespace
    //Now, only the wanted namespace remains in $match
    $wantedNS = trim($match);
    if ( $wantedNS == '' ){
      //If there is nothing, we take the current namespace
      $wantedNS = '.';
    }
    if ( $wantedNS[0] == '.' ){
      //if it start with a '.', it is a relative path
      $return['wantedNS'] = getNS($ID);
    }
    $return['wantedNS'] .= ':'.$wantedNS.':';

    //For security reasons, and to pass the cleanid() function, get rid of '..'
    $return['wantedNS'] = explode(':', $return['wantedNS']);

    for ( $i=0 ; $i < count($return['wantedNS']) ; $i++ ){
      if ( $return['wantedNS'][$i] === '' || $return['wantedNS'][$i] === '.' ){
        array_splice($return['wantedNS'], $i, 1);
        $i--;
      } else if ( $return['wantedNS'][$i] == '..' ){
        if ( $i == 0 ){
          //The first can't be '..', to stay inside 'data/pages'
          break;
        } else {
          //simplify the path, getting rid of 'ns:..'
          array_splice($return['wantedNS'], $i-1, 2);
          $i -= 2;
        }
      }
    }

    if ( $return['wantedNS'][0] == '..' ){
      //path would be outside the 'pages' directory
      $return['safe'] = false;
    }

    $return['wantedNS'] = implode(':', $return['wantedNS']);

    //Deduce the wanted directory
    $return['wantedDir'] = utf8_encodeFN(str_replace(':','/',$return['wantedNS']));

    return $return;
  } // handle()

  /**
   * Check if a given option has been given, and remove it from the initial string
   * @param string $match The string match by the plugin
   * @param string $pattern The pattern which activate the option
   * @param $varAffected The variable which will memorise the option
   * @param $valIfFound the value affected to the previous variable if the option is found
   */
  function _checkOption(&$match, $pattern, &$varAffected, $valIfFound){
    if ( preg_match($pattern, $match, $found) ){
      $varAffected = $valIfFound;
      $match = str_replace($found[0], '', $match);
    }
  } // _checkOption

  function _checkRegEx(&$match, $pattern, &$arrayAffected){
    preg_match_all($pattern, $match, $found, PREG_SET_ORDER);
    foreach ( $found as $regex ){
      $arrayAffected[] = $regex[1];
      $match = str_replace($regex[0], '', $match);
    }
  }

  function render($mode, &$renderer, $data) {
    global $conf; //to get $conf['savedir']
    // Make sure the namespace exists
    if ( @opendir($conf['savedir'].'/pages/'.$data['wantedDir']) === false || !$data['safe'] ){
      $renderer->section_open(1);
      $renderer->cdata($this->getLang('doesntexist').$data['wantedNS']);
      $renderer->section_close();
      return TRUE;
    }

    // Getting the files
    $opt = array( 'depth'=>$data['maxDepth'], 'keeptxt'=>false, 'listfiles'=>!$data['nopages'],
        'listdirs'=>$data['subns'], 'pageonly'=>true, 'skipacl'=>false,
        'sneakyacl'=>true, 'hash'=>false, 'meta'=>false, 'showmsg'=>false,
        'showhidden'=>false, 'firsthead'=>true);
    $files = array();
    search($files, $conf['savedir'].'/pages/', 'search_universal', $opt, $data['wantedDir']);

    $pages = array();
    $subnamespaces = array();
    foreach ( $files as $item ){
      if ( $item['type'] == 'd' ){
        if ( $this->_wantedFile($data['excludedNS'], $data['pregNSOn'], $data['pregNSOff'], $item) ){
          $this->_prepareNS($item, $data['title']);
          $subnamespaces[] = $item;
        }
      } else {
        if ( $this->_wantedFile($data['excludedPages'], $data['pregPagesOn'], $data['pregPagesOff'], $item) ){
          if ( !$data['title'] || $item['title'] === null ){
            $item['title'] = noNS($item['id']);
          }
          $pages[] = $item;
        }
      }
    }

    //writting the output
    $printFunc = $this->selectPrintStrategy($data, $mode);

    //--listing the subnamespaces (if needed)
    if( $data['subns'] ){
      call_user_func(array($this, $printFunc), $renderer, $subnamespaces, 'subns', $data['textNS'], $mode, $data['nbCol']);
    }

    if ( $printFunc=='_printOneLine' && $data['textPages'] === '' && !$data['nopages'] ){
        $renderer->cdata(', ');
    }

    //--listing the pages
    if( !$data['nopages'] ){
      call_user_func(array($this, $printFunc), $renderer, $pages, 'page', $data['textPages'], $mode, $data['nbCol']);
    }

    //this is needed to make sure everything after the plugin is written below the output
    if ( $mode == 'xhtml' ){
        $renderer->doc .= '<br class="catpageeofidx">';
    } else {
        $renderer->linebreak();
    }

    return TRUE;

  } // render()

  function selectPrintStrategy($data, $mode){
    $result_l = '_print'; //default

    if ( $data['simpleList'] ){
      $result_l = '_print';
    } else if ( $data['simpleLine'] ){
      $result_l = '_printOneLine';
    } else if ( $mode == 'xhtml' ){
      $result_l = '_printNicely';
    }

    return $result_l;
  }

  /**
   * Check if the user wants a file to be displayed.
   * Filters consider the "id" and not the "title". Therefore, the treatment is the same for files and for subnamespace.
   * Moreover, filters remain valid even if the title of a page is changed.
   *
   * @param Array $excludedFiles  A list of files that shouldn't be displayed
   * @param Array $pregOn       RegEx that a file should match to be displayed
   * @param Array $pregOff      RegEx that a file shouldn't match to be displayed
   */
  function _wantedFile($excludedFiles, $pregOn, $pregOff, $file){
    $wanted = true;
    $noNSId = noNS($file['id']);
    $wanted &= (! in_array($noNSId, $excludedFiles) );
    foreach ( $pregOn as $preg ){
      $wanted &= preg_match($preg, $noNSId);
    }
    foreach ( $pregOff as $preg ){
      $wanted &= !preg_match($preg, $noNSId);
    }
    return $wanted;
  }

  /**
   * When we display a namespace, we want to:
   * - link to it's main page (if such a page exists)
   * - get the id of this main page (if the option is active)
   * @param $ns  A structure which represents a namespace
   * @param boolean $useTitle Do we have to check the title of the ns?
   */
  function _prepareNS(&$ns, $useTitle){
    $idMainPage = $ns['id'].':';
    resolve_pageid('', $idMainPage, $exist);  //get the id of the main page of the ns
    $ns['title'] = noNS($ns['id']);

    if ( ! $exist ){ //if there is currently no main page for this namespace, then...
      $ns['id'] .= ':'; //...we'll link directly to the namespace
    } else { //if there is a main page, then...
      $title = p_get_first_heading($idMainPage, true); //...we adapt the title to use
      if ( !is_null($title) && $useTitle ){
        $ns['title'] = $title;
      }
      $ns['id'] = $idMainPage; //... and we'll link directly to this page
    }
  }

  function _printNicely(&$renderer, $tab, $type, $text, $mode, $nbCol){
    $this->_beginPrint($renderer, $tab, $type, $text, $mode);

    //use actpage to count how many pages we have already processed
    $actpage=0;

    if( empty($tab)){
      return;
    }

    $nbItemPerColumns = $this->computeNbItemPerColumns(sizeof($tab), $nbCol);

    $percentWidth = 100/sizeof($nbItemPerColumns);
    $percentWidth .= '%';

    $renderer->doc .= "\n".'<div class="catpagecol" style="width: ' . $percentWidth . '" ><ul>';
    //firstchar stores the first character of the last added page
    $firstchar = $this->_firstChar($tab[0]);

    //write the first index-letter
    $renderer->doc .= '<div class="catpagechars">'.$firstchar."</div>\n";

    $idxCol = 0;
    foreach( $tab as $item ){
      //change to the next column if necessary
      if ($actpage == $nbItemPerColumns[$idxCol]) {
        $idxCol++;
        $renderer->doc .= "</ul></div>\n".'<div class="catpagecol" style="width: ' . $percentWidth . '"><ul>'."\n";

        $newLetter = $this->_firstChar($item);
        if ( $newLetter != $firstchar ) {
          $firstchar = $newLetter;
          $renderer->doc .= '<div class="catpagechars">'.$firstchar."</div>\n";
        }
        else {
          $renderer->doc .= '<div class="catpagechars">'.$firstchar.$this->getLang('continued')."</div>\n";
        }
      }
      //write the index-letters
      $newLetter = $this->_firstChar($item);
      if ( $newLetter != $firstchar ) {
        $firstchar = $newLetter;
        $renderer->doc .= '<div class="catpagechars">'.$firstchar."</div>\n";
      }

      $this->_printElement($renderer, $item, $mode);
      $actpage++;
    }
    $renderer->doc .= "</ul></div>\n";
  } // _printNicely()

  /**
   * Sort the $tab according to the ['title'] key of its elements if they are pages,
   * or the ['id'] key if they are directory
   */
  function _sort(&$tab){
    usort($tab, array("syntax_plugin_nspages", "_order"));
  } // _sort

  static function _order($p1, $p2){
    return strcasecmp(utf8_strtoupper($p1['title']), utf8_strtoupper($p2['title']));
  } //_order

  function _firstChar($item){
    return utf8_strtoupper(utf8_substr($item['title'], 0, 1));
  } // _firstChar

  function _print(&$renderer, $tab, $type, $text, $mode, $nbCol){
    $this->_beginPrint($renderer, $tab, $type, $text, $mode);

    if( empty($tab)){
      return;
    }

    $renderer->listu_open();
    foreach ( $tab as $item ){
      $this->_printElement($renderer, $item, $mode);
    }
    $renderer->listu_close();
  } // _print()

  function _printOneLine(&$renderer, $tab, $type, $text, $mode, $nbCol){
    $this->_beginPrint($renderer, $tab, $type, $text, $mode);

    if( empty($tab) ){
      return;
    }

    $sep = '';
    foreach ( $tab as $item ){
      $renderer->cdata($sep);
      $renderer->internallink(':'.$item['id'], $item['title']);
      $sep = ', ';
    }
  } // _printOneLine

  function _beginPrint(&$renderer, &$tab, $type, $text, $mode){
    $this->_sort($tab);

    if ( $text != '' ){
      if ( $mode == 'xhtml' ){
        $renderer->doc .= '<p class="catpageheadline">'.$text.'</p>';
      } else {
        $renderer->linebreak();
        $renderer->p_open();
        $renderer->cdata($text);
        $renderer->p_close();
      }
    }

    if ( empty($tab) ){
      $renderer->p_open();
      $renderer->cdata($this->getLang( ($type=='page') ? 'nopages' : 'nosubns'));
      $renderer->p_close();
    }
  }



  /**
   * @param Array  $item      Represents the file
   * @param string $type      Either 'page' of 'subns'
   */
  function _printElement(&$renderer, $item, $mode){
    if( $item['type'] !== 'd' ){
      $renderer->listitem_open(1);
      $renderer->listcontent_open();
      $renderer->internallink(':'.$item['id'], $item['title']);
      $renderer->listcontent_close();
      $renderer->listitem_close();
    } else{  //Case of a subnamespace
      if ( $mode == 'xhtml' ){
        $renderer->doc .= '<li class="closed">';
      } else {
        $renderer->listitem_open(1);
      }
      $renderer->listcontent_open();
      $renderer->internallink(':'.$item['id'], $item['title']);
      $renderer->listcontent_close();
      $renderer->listitem_close();
    }
  } // _printElement()

  /**
   * Compute the number of element to display per column
   * When $nbItems / $nbCols isn't an int, we make sure that, for aesthetic reasons,
   * that the first are the ones which have the more items
   * Moreover, if we don't have enought items to display, we may choose to display less than the number of columns wanted
   * @param int $nbItems The total number of items to display
   * @param int $nbCols the number of columns to consider
   * @return an array which contains $nbCols int.
   */
  function computeNbItemPerColumns($nbItems, $nbCols){
    $result = array();

    //We make sure this value is correct (it may be 'null' if we upgrade the plugin, and don't run "handle" because of the cache
    if ( !isset($nbCols) || is_null($nbCols) || $nbCols < 1 ){
        $nbCols = 3;
    }

    if ( $nbItems < $nbCols ){
        for ( $idx=0 ; $idx < $nbItems ; $idx++ ){
            $result[] = $idx + 1;
        }
        return $result;
    }

    $collength = $nbItems / $nbCols;
    $nbItemPerCol = array();
    for ( $idx=0 ; $idx < $nbCols ; $idx++ ){
        $nbItemPerCol[] = ceil(($idx+1) * $collength) - ceil($idx * $collength);
    }
    rsort($nbItemPerCol);

    $result[] = $nbItemPerCol[0];
    for ( $idx=1 ; $idx < $nbCols ; $idx++ ){
        $result[] = end($result) + $nbItemPerCol[$idx];
    }

    return $result;
  }
  
} // class syntax_plugin_nspages
