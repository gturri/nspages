<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
if(!defined('DOKU_INC')) die();
require_once 'filePreparer.php';

class namespacePreparer extends filePreparer {
    function __construct($excludedFiles, $pregOn, $pregOff, $useTitle, $sortPageById, $useIdAndTitle){
        parent::__construct($excludedFiles, $pregOn, $pregOff, $useTitle, $sortPageById, $useIdAndTitle);
    }

    function isFileWanted($file){
        return ($file['type'] == 'd') && parent::isFileWanted($file);
    }

    /**
     * When we display a namespace, we want to:
     * - link to it's main page (if such a page exists)
     * - get the id of this main page (if the option is active)
     *
     * @param         $ns  A structure which represents a namespace
     */
    function prepareFile(&$ns){
        $idMainPage = $this->getMainPageId($ns);

        $ns['title'] = $this->buildTitle($idMainPage, noNS($ns['id']));
        $ns['id'] = $this->buildIdToLinkTo($idMainPage, $ns['id']);
        $ns['sort'] = $this->buildSortAttribute($ns['title'], $ns['id']);
    }

    private function getMainPageId($ns){
        $idMainPage = $ns['id'].':';
        resolve_pageid('', $idMainPage, $exist); //get the id of the main page of the ns
        return $exist ? $idMainPage : null;
    }

    private function buildTitle($idMainPage, $defaultTitle){
        if ( ! is_null($idMainPage) ){
            $title = p_get_first_heading($idMainPage, true);
            if(!is_null($title)){
              if($this->useIdAndTitle){
                return $defaultTitle . " - " . $title;
              }

              if($this->useTitle) {
                return $title;
              }
            }
        }

        return $defaultTitle;
    }

    private function buildIdToLinkTo($idMainPage, $currentNsId){
        if(is_null($idMainPage)) {
            return $currentNsId . ':';
        } else {
            return $idMainPage;
        }
    }

    private function buildSortAttribute($nsTitle, $nsId){
        if ( $this->sortPageById ){
            return curNS($nsId);
        } else {
            return $nsTitle;
        }
    }
}
