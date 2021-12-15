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
        $orderedTree = $this->_orderTree($trimmedTree);
        $this->_printTree($orderedTree);
    }

    /**
     * We received the nodes all ordered together, but building the tree has probably
     * lost the order for namespaces, we hence need to sort again each node
     */
    function _orderTree($tree) {
        // We only need to sort "children". We don't need to sort "pages" because with the current
        // workflow of the plugin nodes are provided already sorted to _print, and the way we
        // build the tree preserves the order of the pages.
        // An optimization could be to disable the preliminary sort and to instead sort pages here.
        // That could save some CPU cycles because instead of sorting a big list we would sort
        // several smaller ones. However it would require
        // - a regression test which assert on the order of the pages when a flag is passed to
        //   have a custom sort (eg: "-h1") to ensure we don't have the correct order just because
        //   the DW search API returned sorted results based on the id of the pages
        // - benchmarking (because it could be detrimental if usort has a constant overhead which
        //   would make several small sort more costly than a single one bigger)
        $this->_sorter->sort($tree->children);

        foreach($tree->children as $subTree){
            $this->_orderTree($subTree);
        }
        return $tree;
    }

    private function _groupByNs($tab) {
        $tree = new NspagesTreeNsNode(':');
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
            // - its 'id' will look like either:
            //   1. 'a:b:c:' if the ns has no main page
            //   2. 'a:b:c:start' or 'a:b:c:c' (if this page exists)
            //   3. 'a:b:c' (case where there is a page a:b:c and no page a:b:c:start, see bug #120)
            // - its 'ns' will look like 'a:b'
            // What we want is array ['a', 'b', 'c']

            // For a page at the root of the repo:
            // - the 'id' will look like either
            //   4. 'a:start' in most cases
            //   5. 'a' (case where the is a page 'a' and no page 'a:start', see bug #120)
            // - the 'ns' will be FALSE

            $lastChar = substr($item['id'], -1);
            $IdSplit = explode(':', $item['id']);

            if ($item['ns'] !== false){
                if ($lastChar === ':' // case 1
                  || count(explode(':', $item['ns'])) === count($IdSplit) -2){ // case 2
                    array_pop($IdSplit);
                } else { // case 3 (nothing to do here)
                }
            } else {
                if ($this->str_contains($item['id'], ':')){ // case 4
                    array_pop($IdSplit);
                } else { // case 5 (nothing to do here)
                }
            }

            return $IdSplit;
        } else {
            // It $item is a page then:
            // - its 'id' will look like 'a:b:page'
            // - its 'ns' will look like 'a:b'
            // What we want is array ['a', 'b']
            if ($item['ns'] === false) {
              // Special case of the pages at the root of the wiki: for them "ns" is set to boolean FALSE
              return array();
            } else {
              return explode(':', $item['ns']);
            }
        }
    }

    /**
     * This is similar to https://www.php.net/manual/en/function.str-contains.php, but the PHP str_contains
     * method is available only from PHP 8 so for now we re-implement this feature
     */
    private function str_contains(string $haystack, string $needle){
        return strpos($haystack, $needle) !== false;
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
                $node = new NspagesTreeNsNode($currentId);
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
            $this->_printElementContent($tree->self, $level);
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
class NspagesTreeNsNode implements ArrayAccess {
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

    function __construct($id){
        $this->id = $id;
    }

    /**
     * Implement ArrayAccess because instances of this class should be sortable with nspages_sorter
     * implementations and that those implementation are performing sorts based on $item["sort"].
     */
    public function offsetSet($offset, $value) {
        throw new BadMethodCallException("Not implemented by design");
    }
    public function offsetExists($offset) {
        return $offset == "sort";
    }
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }
    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->self["sort"] : null;
    }
}
