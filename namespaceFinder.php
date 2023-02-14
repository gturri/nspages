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

    private function computeWantedNs($path){
        global $ID;
        global $conf;
        
        // Convert all other separators to colons
        if ($conf['useslash']) $path = str_replace('/', ':', $path);
        $path = str_replace(';', ':', $path);

        $result = '';
        $wantedNS = trim($path);
        if($wantedNS == '') {
            $wantedNS = $this->getCurrentNamespace();
        }
        if( $this->isRelativePath($wantedNS) ) {
            $result = getNS($ID);
            // normalize initial dots ( ..:..abc -> ..:..:abc )
            $wantedNS = preg_replace('/^((\.+:)*)(\.+)(?=[^:\.])/', '\1\3:', $wantedNS);
        } elseif ( $this->isPageRelativePath($wantedNS) ) {
            $result = $ID;
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
        return utf8_encodeFN(str_replace(':', '/', $this->wantedNs));
    }
}
