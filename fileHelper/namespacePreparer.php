<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
if(!defined('DOKU_INC')) die();
require_once 'filePreparer.php';

class namespacePreparer extends filePreparer {
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
        $idMainPage = $ns['id'].':';
        resolve_pageid('', $idMainPage, $exist); //get the id of the main page of the ns
        $ns['title'] = noNS($ns['id']);

        if(!$exist) { //if there is currently no main page for this namespace, then...
            $ns['id'] .= ':'; //...we'll link directly to the namespace
        } else { //if there is a main page, then...
            $title = p_get_first_heading($idMainPage, true); //...we adapt the title to use
            if(!is_null($title) && $this->useTitle) {
                $ns['title'] = $title;
            }
            $ns['id'] = $idMainPage; //... and we'll link directly to this page
        }
        $ns['sort'] = $ns['title'];
    }
}
