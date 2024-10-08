<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Guillaume Turri <guillaume.turri@gmail.com>
 */
if(!defined('DOKU_INC')) die();

class namespaceFinder {
    private $wantedNs;
    private $isSafe;

    /**
     * Resolves the namespace on construction
     * 
     * @param string $path the namespace link
     */
    function __construct($path){
        $this->wantedNs = $this->computeWantedNs($path);
        $this->sanitizeNs();
    }

    private function computeWantedNs($wantedNS){
        global $ID;

        // Convert all other separators to colons
        //  Nb: slashes should be accepted as separator too as they can be a legit separator when the DW conf "useslash" is on. (and anyway slashes are not allowed in page name
        //  (see https://www.dokuwiki.org/pagename ) so it's the only correct way to deal with it.
        //  But we don't need to str_replace it because we don't go through "cleanID" (which would handle it when the conf is off) and because we never remove nor escape the slashes
        //  before they are converted to a FS path
        $wantedNS = str_replace(';', ':', $wantedNS); // accepted by DW as namespace separator according to https://www.dokuwiki.org/pagename

        $result = '';
        if($wantedNS == '') {
            $wantedNS = $this->getCurrentNamespace();
        }
        if( $this->isRelativePath($wantedNS) ) {
            $result = getNS($ID);
            // normalize initial dots ( ..:..abc -> ..:..:abc )
            $wantedNS = preg_replace('/^((\.+:)*)(\.+)(?=[^:\.])/', '\1\3:', $wantedNS);
        } elseif ( $this->isPageRelativePath($wantedNS) ) {
            $result = $ID;
            $wantedNS = substr($wantedNS, 1);
        }
        $result .= ':'.$wantedNS.':';
        return $result;
    }

    private function getCurrentNamespace(){
        return '.';
    }

    private function isRelativePath($path){
        return $path[0] == '.';
    }

    private function isPageRelativePath($path){
        return $path[0] == '~';
    }

    /**
     * Get rid of '..'.
     * Therefore, provides a ns which passes the cleanid() function,
     */
    private function sanitizeNs(){
        $ns = explode(':', $this->wantedNs);

        for($i = 0; $i < count($ns); $i++) {
            if($ns[$i] === '' || $ns[$i] === '.') {
                array_splice($ns, $i, 1);
                $i--;
            } else if($ns[$i] == '..') {
                if($i == 0) {
                    //the first can't be '..', to stay inside 'data/pages'
                    break;
                } else {
                    //simplify the path, getting rid of 'ns:..'
                    array_splice($ns, $i - 1, 2);
                    $i -= 2;
                }
            }
        }

        $this->isSafe = (count($ns) == 0 || $ns[0] != '..');
        $this->wantedNs = implode(':', $ns);
    }

    function getWantedNs(){
        return $this->wantedNs;
    }

    function isNsSafe(){
        return $this->isSafe;
    }

    function getWantedDirectory(){
        return $this->namespaceToDirectory($this->wantedNs);
    }

    static function namespaceToDirectory($ns){
        return utf8_encodeFN(str_replace(':', '/', $ns));
    }
}
