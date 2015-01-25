<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Guillaume Turri <guillaume.turri@gmail.com>
 */
if(!defined('DOKU_INC')) die();

class optionParser {

    function checkRegEx(&$match, $pattern, &$arrayAffected) {
        optionParser::preg_match_all_wrapper($pattern, $match, $found);
        foreach($found as $regex) {
            $arrayAffected[] = $regex[1];
            $match           = str_replace($regex[0], '', $match);
        }
    }

    /**
     * Check if a given option has been given, and remove it from the initial string
     *
     * @param string $match The string match by the plugin
     * @param string $pattern The pattern which activate the option
     * @param        $varAffected The variable which will memorise the option
     * @param        $valIfFound the value affected to the previous variable if the option is found
     */
    static function checkOption(&$match, $pattern, &$varAffected, $valIfFound) {
        if(optionParser::preg_match_wrapper($pattern, $match, $found)) {
            $varAffected = $valIfFound;
            $match       = str_replace($found[0], '', $match);
        }
    }

    static function checkRecurse(&$match, &$varAffected){
        if(optionParser::preg_match_wrapper('r *=? *\"?([[:digit:]]*)\"?', $match, $found)) {
            if($found[1] != '') {
                $varAffected = (int) $found[1];
            } else {
                $varAffected = 0; //no limit
            }
            $match = str_replace($found[0], '', $match);
        }
    }

    static function checkNbColumns(&$match, &$varAffected){
        if(optionParser::preg_match_wrapper("nb?Cols? *=? *\"?([[:digit:]]*)\"?", $match, $found)) {
            if($found[1] != '') {
                $varAffected = max((int) $found[1], 1);
            }
            $match = str_replace($found[0], '', $match);
        }
    }

    static function checkNbItemsMax(&$match, &$varAffected){
        if(optionParser::preg_match_wrapper("nb?Items?Max *=? *\"?([[:digit:]]*)\"?", $match, $found)) {
            if($found[1] != '') {
                $varAffected = max((int) $found[1], 1);
            }
            $match = str_replace($found[0], '', $match);
        }
    }

    static function checkAnchorName(&$match, &$varAffected){
        if(optionParser::preg_match_wrapper("anchorName *=? *\"?([[:alnum:]]+)\"?", $match, $found)) {
            $varAffected = $found[1];
            $match = str_replace($found[0], '', $match);
        }
    }

    static function checkTextPages(&$match, &$varAffected, $plugin){
        if(optionParser::preg_match_wrapper("textPages? *= *\"([^\"]*)\"", $match, $found)) {
            $varAffected = $found[1];
            $match       = str_replace($found[0], '', $match);
        } else {
            $varAffected = null;
        }
    }

    static function checkTextNs(&$match, &$varAffected, $plugin){
        if(optionParser::preg_match_wrapper("textNS *= *\"([^\"]*)\"", $match, $found)) {
            $varAffected = $found[1];
            $match       = str_replace($found[0], '', $match);
        } else {
            $varAffected = null;
        }
    }

    static function checkExclude(&$match, &$excludedPages, &$excludedNs){
        //--Looking if the syntax -exclude[item1 item2] has been used
        if(optionParser::preg_match_wrapper("exclude:\[(.*)\]", $match, $found)) {
            $match = str_replace($found[0], '', $match);
            $found = str_replace('@', '', $found[1]); //for retrocompatibility
            $found = explode(' ', $found);
            foreach($found as $item) {
                if($item[strlen($item) - 1] == ':') { //not utf8_strlen() on purpose
                    $excludedNS[] = utf8_substr($item, 0, -1);
                } else {
                    $excludedPages[] = $item;
                }
            }
        }

        //--Checking if specified subnamespaces have to be excluded
        optionParser::preg_match_all_wrapper("exclude:([^[ <>]*):", $match, $found);
        foreach($found as $subns) {
            $excludedNs[] = $subns[1];
            $match        = str_replace($subns[0], '', $match);
        }

        //--Checking if specified pages have to be excluded
        optionParser::preg_match_all_wrapper("exclude:([^[ <>]*)", $match, $found);
        foreach($found as $page) {
            $excludedPages[] = $page[1];
            $match           = str_replace($page[0], '', $match);
        }

        //--Looking if the current page has to be excluded
        global $ID;
        if(optionParser::preg_match_wrapper("exclude", $match, $found)) {
            $excludedPages[] = noNS($ID);
            $match                     = str_replace($found[0], '', $match);
        }
    }

    static function checkActualTitle(&$match, &$varAffected){
        if ( optionParser::preg_match_wrapper("actualTitle *= *([[:digit:]])", $match, $found) ){
            $varAffected = $found[1];
        } else if ( optionParser::preg_match_wrapper("actualTitle", $match, $found) ){
            $varAffected = 2;
        }
        $match = str_replace($found[0], '', $match);
    }

    static private function preg_match_wrapper($pattern, $subject, &$matches){
        return preg_match('/\s-' . $pattern . '/i', $subject, $matches);
    }

    static private function preg_match_all_wrapper($pattern, $subject, &$matches){
        return preg_match_all('/\s-' . $pattern . '/i', $subject, $matches, PREG_SET_ORDER);
    }
}
