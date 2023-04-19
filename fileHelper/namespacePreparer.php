<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

use dokuwiki\File\PageResolver;

if(!defined('DOKU_INC')) die();
require_once 'filePreparer.php';

class namespacePreparer extends filePreparer {
    function __construct($excludedFiles, $pregOn, $pregOff, $pregTitleOn, $pregTitleOff, $useTitle, $sortPageById, $useIdAndTitle, $sortPageByDate, $sortByCreationDate){
        parent::__construct($excludedFiles, $pregOn, $pregOff, $pregTitleOn, $pregTitleOff, $useTitle, $sortPageById, $useIdAndTitle, $sortPageByDate, $sortByCreationDate);
    }

    function isFileWanted($file, $useTitle){
        return $file['type'] == 'd' && parent::isFileWanted($file, $useTitle);
    }

    function prepareFileTitle(&$ns){
        $idMainPage = $this->getMainPageId($ns);
        if ( !is_null($idMainPage) ){
            $ns['title'] = p_get_first_heading($idMainPage, true);
        } else {
            $ns['title'] = null;
        }
    }

    /**
     * When we display a namespace, we want to:
     * - link to it's main page (if such a page exists)
     * - get the id of this main page (if the option is active)
     *
     * @param         $ns  A structure which represents a namespace
     */
    function prepareFile(&$ns){
        $ns['nameToDisplay'] = $this->buildNameToDisplay($ns['title'], noNS($ns['id']));
        $ns['id'] = $this->buildIdToLinkTo($this->getMainPageId($ns), $ns['id']);
        $ns['sort'] = $this->buildSortAttribute($ns['nameToDisplay'], $ns['id'], $ns['mtime']);
    }

    private function getMainPageId(&$ns){
        if (!array_key_exists('idMainPage', $ns)){
            $idMainPage = $ns['id'].':';
            // Get the id of the namespace's main page
            $resolver = new PageResolver($idMainPage);
            $page = $resolver->resolveId($idMainPage);
            $ns['idMainPage'] = page_exists($page) ? $page : null;
        }
        return $ns['idMainPage'];
    }

    private function buildNameToDisplay($title, $defaultName){
        if ( ! is_null($title) ){
            if($this->useIdAndTitle){
                return $defaultName . " - " . $title;
            }

            if($this->useTitle) {
                return $title;
            }
        }

        return $defaultName;
    }

    private function buildIdToLinkTo($idMainPage, $currentNsId){
        if(is_null($idMainPage)) {
            return $currentNsId . ':';
        } else {
            return $idMainPage;
        }
    }

    private function buildSortAttribute($nameToDisplay, $nsId, $mtime){
        if ( $this->sortPageById ){
            return curNS($nsId);
        } else if ( $this->sortPageByDate ){
            return $mtime;
        } else {
            return $nameToDisplay;
        }
    }
}
