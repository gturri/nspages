<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
if(!defined('DOKU_INC')) die();
require_once 'filePreparer.php';

class pagePreparer extends filePreparer {

    private $customTitle;
    private $customTitleAllowListMetadata;
    private $sortByMetadata;

    function __construct($excludedNs, $excludedFiles, $pregOn, $pregOff, $pregTitleOn, $pregTitleOff, $useTitle,
                         $sortPageById, $useIdAndTitle, $sortPageByDate, $sortByCreationDate, $customTitle,
                         $customTitleAllowListMetadata, $sortByMetadata) {
        parent::__construct($excludedFiles, $pregOn, $pregOff, $pregTitleOn, $pregTitleOff, $useTitle, $sortPageById,
            $useIdAndTitle, $sortPageByDate, $sortByCreationDate);

        $this->excludedNs = $excludedNs;
        $this->customTitle = $customTitle;
        $this->customTitleAllowListMetadata = $customTitleAllowListMetadata;
        $this->sortByMetadata = $sortByMetadata;
    }

    function isFileWanted($file, $useTitle){
        return ($file['type'] != 'd') && parent::isFileWanted($file, $useTitle) && $this->passSubNsfilterInRecursiveMode($file);
    }

    function prepareFileTitle(&$file){
        // Nothing to do: for pages the title is already set
    }

    private function passSubNsfilterInRecursiveMode($file){
        $subNss = explode(':', $file['id']);
        if ( count($subNss) < 2 ){ //It means we're not in recursive mode
            return true;
        }
        for ($i = 0; $i < count($subNss) - 1; $i++) {
            if (in_array($subNss[$i], $this->excludedNs)) {
                return false;
            }
        }
        return true;
    }

    function prepareFile(&$page){
        $page['nameToDisplay'] = $this->buildNameToDisplay($page);
        $page['sort'] = $this->buildSortAttribute($page['nameToDisplay'], $page['id'], $page['mtime']);
    }

    /**
     * Get the a metadata value from a certain path.
     *
     * @param $metadata - The metadata object of a page. More details on https://www.dokuwiki.org/devel:metadata
     * @param $path - The path.
     *  Examples:
     *      date.created
     *      contributor.0
     *
     * @return mixed - The metadata value from a certain path.
     */

    private function getMetadataFromPath($metadata, $path) {
        return array_reduce(
            explode('.', $path),
            function ($object, $property) {
                return is_numeric($property) ? $object[$property] : $object[$property];
            },
            $metadata
        );
    }

    private function isPathInMetadataAllowList($path) {
        $metadataAllowList = explode(',', preg_replace('/\s+/', '', $this->customTitleAllowListMetadata));
        return in_array($path, $metadataAllowList);
    }

    /**
     * Get the page custom title from a template.
     *
     * @param $customTitle - The custom tile template.
     *  Examples:
     *      {title} ({data.created} by {user})
     * @param $metadata - The metadata object of a page. More details on https://www.dokuwiki.org/devel:metadata
     *
     * @return string - the custom title
     */

    private function getCustomTitleFromTemplate($customTitle, $metadata) {
        return preg_replace_callback(
            '/{(.*?)}/',
            function ($matches) use($metadata) {
                $path = $matches[1];
                if ($this->isPathInMetadataAllowList($path)) {
                    return $this->getMetadataFromPath($metadata, $path);
                } else {
                    return $path;
                }
            },
            $customTitle
        );
    }

    private function buildNameToDisplay($page){
        $title = $page['title'];
        $pageId = $page['id'];


        if ($this->customTitle !== null) {
            $meta = p_get_metadata($pageId, array(), true);
            return $this->getCustomTitleFromTemplate($this->customTitle, $meta);
        }

        if($this->useIdAndTitle && $title !== null ){
          return noNS($pageId) . " - " . $title;
        }

        if(!$this->useTitle || $title === null) {
            return noNS($pageId);
        }
        return $title;
    }

    private function buildSortAttribute($nameToDisplay, $pageId, $mtime){
        if ($this->sortByMetadata !== null) {
            $meta = p_get_metadata($pageId);
            return $this->getMetadataFromPath($meta, $this->sortByMetadata);
        } else if($this->sortPageById) {
            return noNS($pageId);
        } else if ( $this->sortPageByDate) {
            return $mtime;
        } else if ($this->sortByCreationDate) {
            $meta = p_get_metadata($pageId);
            return $meta['date']['created'];
        } else {
            return $nameToDisplay;
        }

    }
}
