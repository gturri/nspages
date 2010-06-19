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
require_once(DOKU_INC . 'inc/pageutils.php');//to use the noNS() function


/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_nspages extends DokuWiki_Syntax_Plugin {

  function getInfo() {
    return confToHash(dirname(__FILE__).'/plugin.info.txt');
  } // getInfo()

  function connectTo($aMode) {
    $this->Lexer->addSpecialPattern('<nspages[^>]*>', $aMode, 'plugin_nspages');
  } // connectTo()

  function getSort() {
    return 999;
  } // getSort()

  function getType() {
    return 'substition';
  } // getType()

  function handle($match, $state, $pos, &$handler) {
    $return = array('subns' => false, 'nopages' => false, 'simpleList' =>
		    false, 'excludedPages' => array(), 'excludedNS' => array(),
		    'title' => false, 'wantedNS' => '', 'wantedDir' => '', 'safe' => true,
		    'textNS' => '', 'textPages' => '',
                    'pregPagesOn' => array(), 'pregPagesOff' => array(),
                    'pregNSOn' => array(), 'pregNSOff' => array());

     $match = mb_substr($match, 9, -1); //9 = strlen("<nspages ")
     $match .= ' ';
  
    //Looking the first options
    $this->_checkOption($match, "/-subns/i", $return['subns'], true);
    $this->_checkOption($match, "/-nopages/i", $return['nopages'], true);
    $this->_checkOption($match, "/-simpleListe?/i", $return['simpleList'], true);
    $this->_checkOption($match, "/-title/i", $return['title'], true);
    $this->_checkOption($match, "/-h1/i", $return['title'], true);

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

      //--Checking if specified page have to be excluded
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
        if ( $item[strlen($item)-1] == ':' ){ //not mb_strlen() on purpose
          $return['excludedNS'][] = utf8_substr($item, 0, -1); //idem for mb_substr
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
      global $NS;
      $return['wantedNS'] = $NS;
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
    if($mode == 'xhtml'){
      // Make sure the namespace exists
      if ( @opendir($conf['savedir'].'/pages/'.$data['wantedDir']) === false || !$data['safe'] ){
        $renderer->doc .= '<div>'.$this->getLang('doesntexist').$data['wantedNS'].'</div>';
        return TRUE;
      }

      // Getting the files
      $opt = array( 'depth'=>1, 'keeptxt'=>false, 'listfiles'=>!$data['nopages'],
        'listdirs'=>$data['subns'], 'pageonly'=>true, 'skipacl'=>false,
        'sneakyacl'=>false, 'hash'=>false, 'meta'=>false, 'showmsg'=>false,
        'showhidden'=>false, 'firsthead'=>true);
      $files = array();
      search($files, $conf['savedir'].'/pages/', 'search_universal', $opt, $data['wantedDir']);

      $pages = array();
      $subnamespaces = array();
      foreach ( $files as $item ){
          if ( $item['type'] == 'd' ){
            if ( $this->_wantedFile($data['excludedNS'], $data['pregNSOn'], $data['pregNSOff'], $item) ){
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
      $printFunc = $data['simpleList'] ? '_print' : '_printNicely';
      //--listing the subnamespaces (if needed)
      if( $data['subns'] ){
	call_user_func(array($this, $printFunc), $renderer, $subnamespaces, 'subns', $data['textNS']);
      }

      //--listing the pages
      if( !$data['nopages'] ){
	call_user_func(array($this, $printFunc), $renderer, $pages, 'page', $data['textPages']);
      }

      //this is needed to make sure everything after the plugin is written below the output
      $renderer->doc .= '<br class="catpageeofidx">';
      
      return TRUE;
    }  else {
      return FALSE;
    }

  } // render()

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

  function _printNicely(&$renderer, $tab, $type, $text){
    $this->_sort($tab);

    //calculate how many elements should be in the first and second column
    $collength = ceil( sizeof($tab) / 3 );
    $dblcollength = ceil( sizeof($tab) / 3 ) * 2;
    //if there are less than three elements keep them in one column (just for beauty-issues)
    if ( sizeof($tab) < 3 ){
      $collength = 10;
      $dblcollength = 10;
    }

    //use actpage to count how many pages we have already processed
    $actpage=0;

    //write this (localized) text as headline for the section
    if ( $text != '' ){
      $renderer->doc .= '<p class="catpageheadline">'.$text."</p>";
    }
    if( sizeof($tab) == 0){
      $renderer->doc .= '<p>'.$this->getLang( ($type=='page') ? 'nopages' : 'nosubns').'</p>';
    }
    else{
      $renderer->doc .= "\n".'<div class="catpagecol"><ul>';
      //firstchar stores the first character of the last added page
      $firstchar = $this->_firstChar($tab[0]);
      
      //write the first index-letter
      $renderer->doc .= '<div class="catpagechars">'.$firstchar."</div>\n";

      foreach( $tab as $item ){
        //change to the next column if necessary
        if ($actpage == $collength || $actpage == $dblcollength) {
          $renderer->doc .= "</ul></div>\n".'<div class="catpagecol"><ul>'."\n";

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

        $this->_printElement($renderer, $item);
        $actpage++;
      }
      $renderer->doc .= "</ul></div>\n";
    }
  } // _printNicely()

  /**
   * Sort the $tab according to the ['title'] key of its elements if they are pages,
   * or the ['id'] key if they are directory
   */
  function _sort(&$tab){
    usort($tab, array("syntax_plugin_nspages", "_order"));
  } // _sort

  static function _order($p1, $p2){
    if ( $p1['type'] == 'd' ){
      return strcasecmp(mb_strtoupper($p1['id']), mb_strtoupper($p2['id']));
    } else {
      return strcasecmp(mb_strtoupper($p1['title']), mb_strtoupper($p2['title']));
    }
  } //_order

  function _firstChar($item){
    if ( $item['type'] == 'd' ){
      return mb_strtoupper(utf8_substr(noNS($item['id']),0,1));
    } else {
      return mb_strtoupper(utf8_substr($item['title'],0,1));
    }
  } // _firstChar

  function _print(&$renderer, $tab, $type, $text){
    $this->_sort($tab);

    if ( $text != '' ){
      $renderer->doc .= '<p class="catpageheadline">'.$text."</p>";
    }
    if( sizeof($tab) == 0){
      $renderer->doc .= '<p>'.$this->getLang( ($type=='page') ? 'nopages' : 'nosubns').'</p>';
    } else {
      $renderer->doc .= "<ul>\n";
      foreach ( $tab as $item ){
        $this->_printElement($renderer, $item);
      }
      $renderer->doc .= "</ul>\n";
    }
  } // _print()

  /**
   * @param Array  $item      Represents the file
   * @param string $type      Either 'page' of 'subns'
   */
  function _printElement(&$renderer, $item){
    if( $item['type'] !== 'd' ){
      $renderer->doc .= '<li class="level1"><div class="li">'
        .$renderer->internallink(':'.$item['id'], $item['title'], NULL,true)
        .'</div></li>';
    } else{  //Case of a subnamespace
      $renderer->doc .= '<li class="closed"><div class="li">'
        .$renderer->internallink(':'.$item['id'].':', noNS($item['id']), NULL,true)
        .'</div></li>';
    }
  } // _printElement()
  
} // class syntax_plugin_nspages
