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
        preg_match_all($pattern, $match, $found, PREG_SET_ORDER);
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
        if(preg_match($pattern, $match, $found)) {
            $varAffected = $valIfFound;
            $match       = str_replace($found[0], '', $match);
        }
    }

    static function checkRecurse(&$match, &$varAffected){
        if(preg_match("/-r *=? *\"?([[:digit:]]*)\"?/i", $match, $found)) {
            if($found[1] != '') {
                $varAffected = (int) $found[1];
            } else {
                $varAffected = 0; //no limit
            }
            $match = str_replace($found[0], '', $match);
        }
    }

    static function checkNbColumns(&$match, &$varAffected){
        if(preg_match("/-nb?Cols? *=? *\"?([[:digit:]]*)\"?/i", $match, $found)) {
            if($found[1] != '') {
                $varAffected = max((int) $found[1], 1);
            }
            $match = str_replace($found[0], '', $match);
        }
    }

    static function checkAnchorName(&$match, &$varAffected){
        if(preg_match("/-anchorName *=? *\"?([[:alnum:]]+)\"?/i", $match, $found)) {
            $varAffected = $found[1];
            $match = str_replace($found[0], '', $match);
        }
    }

    static function checkTextPages(&$match, &$varAffected, $plugin){
        if(preg_match("/-textPages? *= *\"([^\"]*)\"/i", $match, $found)) {
            $varAffected = $found[1];
            $match       = str_replace($found[0], '', $match);
        } else {
            $varAffected = $plugin->getLang('pagesinthiscat');
        }
    }

    static function checkTextNs(&$match, &$varAffected, $plugin){
        if(preg_match("/-textNS *= *\"([^\"]*)\"/i", $match, $found)) {
            $varAffected = $found[1];
            $match       = str_replace($found[0], '', $match);
        } else {
            $varAffected = $plugin->getLang('subcats');
        }
    }

    static function checkExclude(&$match, &$excludedPages, &$excludedNs){
        //--Checking if specified subnamespaces have to be excluded
        preg_match_all("/-exclude:([^[ <>]*):/", $match, $found, PREG_SET_ORDER);
        foreach($found as $subns) {
            $excludedNs[] = $subns[1];
            $match        = str_replace($subns[0], '', $match);
        }

        //--Checking if specified pages have to be excluded
        preg_match_all("/-exclude:([^[ <>]*) /", $match, $found, PREG_SET_ORDER);
        foreach($found as $page) {
            $excludedPages[] = $page[1];
            $match           = str_replace($page[0], '', $match);
        }

        //--Looking if the current page has to be excluded
        global $ID;
        if(preg_match("/-exclude /", $match, $found)) {
            $excludedPages[] = noNS($ID);
            $match                     = str_replace($found[0], '', $match);
        }

        //--Looking if the syntax -exclude[item1 item2] has been used
        if(preg_match("/-exclude:\[(.*)\]/", $match, $found)) {
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
    }

    static function checkActualTitle(&$match, &$varAffected){
        if ( preg_match("/-actualTitle *= *([[:digit:]])/i", $match, $found) ){
            $varAffected = $found[1];
        } else if ( preg_match("/-actualTitle/", $match, $found) ){
            $varAffected = 2;
        }
        $match = str_replace($found[0], '', $match);
    }
}
