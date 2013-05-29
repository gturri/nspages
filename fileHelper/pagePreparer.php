<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
if(!defined('DOKU_INC')) die();
require_once 'filePreparer.php';

class pagePreparer extends filePreparer {
    private $sortPageById;

    function __construct($excludedFiles, $pregOn, $pregOff, $useTitle, $sortPageById){
        parent::__construct($excludedFiles, $pregOn, $pregOff, $useTitle);
        $this->sortPageById = $sortPageById;
    }

    function isFileWanted($file){
        return ($file['type'] != 'd') && parent::isFileWanted($file);
    }

    function prepareFile(&$page){
        if(!$this->useTitle || $page['title'] === null) {
            $page['title'] = noNS($page['id']);
        }

        if($this->sortPageById) {
            $page['sort'] = noNS($page['id']);
        } else {
            $page['sort'] = $page['title'];
        }

    }
}
