<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
if(!defined('DOKU_INC')) die();
require_once 'namespacePreparer.php';
require_once 'pagePreparer.php';

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
        $preparer = new pagePreparer($this->data['excludedPages'], $this->data['pregPagesOn'], $this->data['pregPagesOff'], $this->data['title'], $this->data['sortid'], $this->data['idAndTitle']);
        return $this->getFiles($preparer);
    }

    function getSubnamespaces(){
        $preparer = new namespacePreparer($this->data['excludedNS'], $this->data['pregNSOn'], $this->data['pregNSOff'], $this->data['title'], $this->data['sortid'], $this->data['idAndTitle']);
        return $this->getFiles($preparer);
    }

    private function getFiles($preparer){
        $files = array();
        foreach($this->files as $item) {
           if($preparer->isFileWanted($item)) {
               $preparer->prepareFile($item);
               $files[] = $item;
               if ($this->hasEnoughFiles($files)){
                 break;
               }
           }
        }
        return $files;
    }

    private function hasEnoughFiles($files){
      $nbItemsMax = $this->data['nbItemsMax'];
      return $nbItemsMax && count($files) >= $nbItemsMax;
    }
}
