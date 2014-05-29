<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
if(!defined('DOKU_INC')) die();
require_once 'filePreparer.php';

class pagePreparer extends filePreparer {
    function __construct($excludedFiles, $pregOn, $pregOff, $useTitle, $sortPageById, $useIdAndTitle){
        parent::__construct($excludedFiles, $pregOn, $pregOff, $useTitle, $sortPageById, $useIdAndTitle);
    }

    function isFileWanted($file){
        return ($file['type'] != 'd') && parent::isFileWanted($file);
    }

    function prepareFile(&$page){
        $page['title'] = $this->buildTitle($page['title'], $page['id']);
        $page['sort'] = $this->buildSortAttribute($page['title'], $page['id']);
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

    private function buildSortAttribute($pageTitle, $pageId){
        if($this->sortPageById) {
            return noNS($pageId);
        } else {
            return $pageTitle;
        }

    }
}
