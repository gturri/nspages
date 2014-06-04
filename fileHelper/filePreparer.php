<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
if(!defined('DOKU_INC')) die();

abstract class filePreparer {
    /**
     * Array  A list of files that shouldn't be displayed
     */
    private $excludedFiles;

    /**
     * Array  RegEx that a file should match to be displayed
     */
    private $pregOn;

    /**
     * Array  RegEx that a file shouldn't match to be displayed
     */
    private $pregOff;

    protected $useTitle;
    protected $useIdAndTitle;

    /**
     * bool
     */
    protected $sortPageById;

    function __construct($excludedFiles, $pregOn, $pregOff, $useTitle, $sortPageById, $useIdAndTitle){
        $this->excludedFiles = $excludedFiles;
        $this->pregOn = $pregOn;
        $this->pregOff = $pregOff;
        $this->useTitle = $useTitle;
        $this->sortPageById = $sortPageById;
        $this->useIdAndTitle = $useIdAndTitle;
    }

    /**
     * Check if the user wants a file to be displayed.
     * Filters consider the "id" and not the "title". Therefore, the treatment is the same for files and for subnamespace.
     * Moreover, filters remain valid even if the title of a page is changed.
     *
     * @param Array  $excludedFiles  A list of files that shouldn't be displayed
     * @param string $file
     * @return bool
     */
    function isFileWanted($file) {
        $wanted = true;
        $noNSId = noNS($file['id']);
        $wanted &= (!in_array($noNSId, $this->excludedFiles));
        foreach($this->pregOn as $preg) {
            $wanted &= preg_match($preg, $noNSId);
        }
        foreach($this->pregOff as $preg) {
            $wanted &= !preg_match($preg, $noNSId);
        }
        return $wanted;
    }

    abstract function prepareFile(&$file);
}
