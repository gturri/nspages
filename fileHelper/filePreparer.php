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
    private $pregTitleOn;

    /**
     * Array  RegEx that a file shouldn't match to be displayed
     */
    private $pregOff;
    private $pregTitleOff;

    protected $useTitle;
    protected $useIdAndTitle;
    protected $sortPageByDate;
    protected $sortByCreationDate;

    /**
     * bool
     */
    protected $sortPageById;

    function __construct($excludedFiles, $pregOn, $pregOff, $pregTitleOn, $pregTitleOff, $useTitle, $sortPageById,
                         $useIdAndTitle, $sortPageByDate, $sortByCreationDate){
        $this->excludedFiles = $excludedFiles;
        $this->pregOn = $pregOn;
        $this->pregOff = $pregOff;
        $this->pregTitleOn = $pregTitleOn;
        $this->pregTitleOff = $pregTitleOff;
        $this->useTitle = $useTitle;
        $this->sortPageById = $sortPageById;
        $this->useIdAndTitle = $useIdAndTitle;
        $this->sortPageByDate = $sortPageByDate;
        $this->sortByCreationDate = $sortByCreationDate;
    }

    function isFileWanted($file, $useTitle) {
        $nameToFilterOn = $useTitle ? $file['title'] : noNS($file['id']);
        $pregOn = $useTitle ? $this->pregTitleOn : $this->pregOn;
        $pregOff = $useTitle ? $this->pregTitleOff : $this->pregOff;

        if (in_array($nameToFilterOn, $this->excludedFiles)) {
            return false;
        }
        foreach($pregOn as $preg) {
            if (!preg_match($preg, $nameToFilterOn)) {
                return false;
            }
        }
        foreach($pregOff as $preg) {
            if (preg_match($preg, $nameToFilterOn)) {
                return false;
            }
        }
        return true;
    }

    abstract function prepareFile(&$file);
    abstract function prepareFileTitle(&$file);
}
