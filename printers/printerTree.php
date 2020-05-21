<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) die();
require_once 'printer.php';

class nspages_printerTree extends nspages_printer {
    private $rootNS;

    function __construct($plugin, $mode, $renderer, $data){
        parent::__construct($plugin, $mode, $renderer, $data);
        $this->rootNS = $data['wantedNS'] . ':';
    }

    function _print($tab, $type) {
        $tree = $this->_groupByNs($tab);
        $trimmedTree = $this->_getTrimmedTree($tree);
        $this->_printTree($trimmedTree);
    }

    private function _groupByNs($tab) {
        $tree = new NspagesTreeNsNode();
        foreach($tab as $item){
            $this->_fillTree($tree, $this->_getNS($item), $item, '');
        }
        return $tree;
    }

    /**
     * Get rid of the "trunk" of the tree. ie: remove the first "empty" nodes. It prevents printing
     * something like
     * - A
     *  - B
     *    - C
     *      - page1
     *      - page2
     *      - page3
     * when the ns the user asked for is actully ns C
     */
    private function _getTrimmedTree($tree){
        if ($tree->id === $this->rootNS){
            return $tree;
        } else {
            if (is_null($tree->children)) {
                // This case should never happen. But I handle it neverthelss because if I'm wrong
                // then the recursion will never end
                return $tree;
            }
            $firstAndOnlyChild = reset($tree->children);
            return $this->_getTrimmedTree($firstAndOnlyChild);
        }
    }

    private function _getNS($item) {
        if($item['type'] === 'd'){
            // If $item is itself a namespace then:
            // - its 'id' will look like 'a:b:c:'
            // - its 'ns' will look like 'a:b''
            // What we want is array ['a', 'b', 'c']

            $IdSplit = explode(':', $item['id']);
            array_pop($IdSplit); // Remove the last element (which is "empty string" because of the final colon
            return $IdSplit;
        } else {
            // It $item is a page then:
            // - its 'id' will look like 'a:b:page'
            // - its 'ns' will look like 'a:b'
            // What we want is array ['a', 'b']
            return explode(':', $item['ns']);
        }
    }

    private function _fillTree($tree, $keys, $item, $parentId) {
        if (empty($keys)){ // We've reach the end of the journey. We register the data of $item
            if($item['type'] === 'd') {
                $tree->self = $item;
            } else {
                $tree->pages []= $item;
            }
        } else { // We're not at the place of $item in the tree yet, we continue to go down
            $key = $keys[0];
            $currentId = $parentId . $key . ':';
            if (!array_key_exists($key, $tree->children)){
                $node = new NspagesTreeNsNode();
                $node->id = $currentId;
                $tree->children[$key] = $node;
            }
            array_shift($keys);
            $this->_fillTree($tree->children[$key], $keys, $item, $currentId);
        }
    }

    private function _printTree($tree) {
        $this->renderer->listu_open();

        foreach($tree->children as $subTree){
            $this->_printSubTree($subTree, 1);
        }

         foreach($tree->pages as $page){
             $this->_printElement($page, 1);
         }

         $this->renderer->listu_close();
    }

    private function _printSubTree($tree, $level) {
        $this->_printElementOpen($level);
        if ( !is_null($tree->self) ){
            $this->_printElementContent($tree->self);
        } else {
          $this->renderer->doc .= '<div>' . $tree->id  . '</div>';
        }

        $hasInnerData = !empty($tree->children) || !empty($tree->pages);
        if($hasInnerData){
            $this->renderer->listu_open();
        }
        foreach($tree->children as $subTree){
            $this->_printSubTree($subTree, $level+1);
        }
        foreach($tree->pages as $page){
            $this->_printElement($page, $level+1);
        }
        if($hasInnerData){
            $this->renderer->listu_close();
        }
        $this->_printElementClose();
    }
}

/**
 * Represent a namespace and its inner content
 */
class NspagesTreeNsNode {
    /**
     * The list of pages directly in the namespace (does not include pages in subnamespaces)
     */
    public $pages = array();

    /**
     * The list of subnamespaces at level n+1 (does not include their own subnamespaces)
     */
    public $children = array();

    /**
     * The data about the current namespace iteslf. It may be empty in two cases:
     * - when nspages is displaying only pages (because in that case we did not search for ns)
     * - when this instance represent the root of the tree (because nspages doesn't display it)
     */
    public $self = null;

    /**
     * Used to represent the current namespace when we're in a case where we want to display it
     * but when $self is empty.
     * In practice it is used to represent namespace nodes when we're asked to display pages only
     */
    public $id = null;
}
