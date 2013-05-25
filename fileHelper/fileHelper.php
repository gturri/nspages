<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
if(!defined('DOKU_INC')) die();

class fileHelper {
    private $files;
    private $data;

    function __construct($data){
        $this->data = $data;
        $this->files = $this->searchFiles($data);
    }

    private function searchFiles(){
        global $conf;
        $opt   = array(
            'depth'     => $this->data['maxDepth'], 'keeptxt'=> false, 'listfiles'=> !$this->data['nopages'],
            'listdirs'  => $this->data['subns'], 'pageonly'=> true, 'skipacl'=> false,
            'sneakyacl' => true, 'hash'=> false, 'meta'=> false, 'showmsg'=> false,
            'showhidden'=> false, 'firsthead'=> true
        );
        $files = array();
        search($files, $conf['datadir'], 'search_universal', $opt, $this->data['wantedDir']);
        return $files;
    }

    function getPages(){
        $pages = array();
        foreach($this->files as $item) {
            if($item['type'] != 'd') {
                if($this->wantedFile($this->data['excludedPages'], $this->data['pregPagesOn'], $this->data['pregPagesOff'], $item)) {
                    $this->preparePage($item, $this->data);
                    $pages[] = $item;
                }
            }
        }
        return $pages;
    }

    /**
     * Fix or build attributes a page should have
     */
    private function preparePage(&$page) {
        if(!$this->data['title'] || $page['title'] === null) {
            $page['title'] = noNS($page['id']);
        }

        if($this->data['sortid']) {
            $page['sort'] = noNS($page['id']);
        } else {
            $page['sort'] = $page['title'];
        }
    }

    function getSubnamespaces(){
        $subnamespaces = array();
        foreach($this->files as $item) {
            if($item['type'] == 'd') {
                if($this->wantedFile($this->data['excludedNS'], $this->data['pregNSOn'], $this->data['pregNSOff'], $item)) {
                    $this->prepareNS($item, $this->data['title']);
                    $subnamespaces[] = $item;
                }
            }
        }
        return $subnamespaces;
    }

    /**
     * When we display a namespace, we want to:
     * - link to it's main page (if such a page exists)
     * - get the id of this main page (if the option is active)
     *
     * @param         $ns  A structure which represents a namespace
     * @param boolean $useTitle Do we have to check the title of the ns?
     */
    private function prepareNS(&$ns, $useTitle) {
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
    private function wantedFile($excludedFiles, $pregOn, $pregOff, $file) {
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
}
