<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
if(!defined('DOKU_INC')) die();
require_once 'filePreparer.php';

class pagePreparer extends filePreparer {
    function __construct($excludedNs, $excludedFiles, $pregOn, $pregOff, $useTitle, $sortPageById, $useIdAndTitle, $sortPageByDate, $sortByCreationDate){
        parent::__construct($excludedFiles, $pregOn, $pregOff, $useTitle, $sortPageById, $useIdAndTitle, $sortPageByDate, $sortByCreationDate);
        $this->excludedNs = $excludedNs;
    }

    function isFileWanted($file){
        return ($file['type'] != 'd') && parent::isFileWanted($file) && $this->passSubNsfilterInRecursiveMode($file);
    }

    private function passSubNsfilterInRecursiveMode($file){
        $subNss = explode(':', $file['id']);
        if ( count($subNss) <= 2 ){ //It means we're not in recursive mode
            return true;
        }
        $firstChildSubns = $subNss[1];
        return !in_array($firstChildSubns, $this->excludedNs);
    }

    function prepareFile(&$page){
        $page['title'] = $this->buildTitle($page['title'], $page['id']);
        $page['sort'] = $this->buildSortAttribute($page['title'], $page['id'], $page['mtime']);
    }

    private function buildTitle($currentTitle, $pageId){
        if($this->useIdAndTitle && $currentTitle !== null ){
          return noNS($pageId) . " - " . $currentTitle;
        }

        if(!$this->useTitle || $currentTitle === null) {
            return noNS($pageId);
        }
        return $currentTitle;
    }

    private function buildSortAttribute($pageTitle, $pageId, $mtime){
        if($this->sortPageById) {
            return noNS($pageId);
        } else if ( $this->sortPageByDate ){
            return $mtime;
        } else if ($this->sortByCreationDate ){
            $meta = p_get_metadata($pageId);
            return $meta['date']['created'];
        } else {
            return $pageTitle;
        }

    }
}
