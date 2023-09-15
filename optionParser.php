<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Guillaume Turri <guillaume.turri@gmail.com>
 */

use dokuwiki\Utf8\PhpString;

if(!defined('DOKU_INC')) die();

class optionParser {

    static function checkRegEx(&$match, $pattern, &$arrayAffected) {
        optionParser::preg_match_all_wrapper($pattern, $match, $found);
        foreach($found as $regex) {
            $arrayAffected[] = $regex[1];
            $match           = optionParser::_removeFromMatch($regex[0], $match);
        }
    }

    /**
     * Check if a given option has been given, and remove it from the initial string
     *
     * @param string $match The string match by the plugin
     * @param string $pattern The pattern which activate the option
     * @param mixed  $varAffected The variable which will memorise the option
     * @param mixed  $valIfFound the value affected to the previous variable if the option is found
     */
    static function checkOption(&$match, $pattern, &$varAffected, $valIfFound) {
        if(optionParser::preg_match_wrapper($pattern, $match, $found)) {
            $varAffected = $valIfFound;
            $match       = optionParser::_removeFromMatch($found[0], $match);
        }
    }

    static function checkRecurse(&$match, &$varAffected){
        if(optionParser::preg_match_wrapper('r *=? *\"?([[:digit:]]*)\"?', $match, $found)) {
            if($found[1] != '') {
                $varAffected = (int) $found[1];
            } else {
                $varAffected = 0; //no limit
            }
            $match = optionParser::_removeFromMatch($found[0], $match);
        }
    }

    static function checkNbColumns(&$match, &$varAffected){
        if(optionParser::preg_match_wrapper("nb?Cols? *=? *\"?([[:digit:]]*)\"?", $match, $found)) {
            if($found[1] != '') {
                $varAffected = max((int) $found[1], 1);
            }
            $match = optionParser::_removeFromMatch($found[0], $match);
        }
    }

    static function checkNbItemsMax(&$match, &$varAffected){
        if(optionParser::preg_match_wrapper("nb?Items?Max *=? *\"?([[:digit:]]*)\"?", $match, $found)) {
            if($found[1] != '') {
                $varAffected = max((int) $found[1], 1);
            }
            $match = optionParser::_removeFromMatch($found[0], $match);
        }
    }

    static function checkAnchorName(&$match, &$varAffected){
        if(optionParser::preg_match_wrapper("anchorName *=? *\"?([[:alnum:]]+)\"?", $match, $found)) {
            $varAffected = $found[1];
            $match = optionParser::_removeFromMatch($found[0], $match);
        }
    }

    static function checkSimpleStringArgument(&$match, &$varAffected, $plugin, $argumentName){
        if(optionParser::preg_match_wrapper($argumentName . " *= *\"([^\"]*)\"", $match, $found)) {
            $varAffected = $found[1];
            $match       = optionParser::_removeFromMatch($found[0], $match);
        } else {
            $varAffected = null;
        }
    }

    static function checkDictOrder(&$match, &$varAffected, $plugin){
        if(optionParser::preg_match_wrapper("dict(?:ionary)?Order *= *\"([^\"]*)\"", $match, $found)) {
            $varAffected = $found[1];
            $match       = optionParser::_removeFromMatch($found[0], $match);
        } else {
            $varAffected = null;
        }
    }

    static function checkExclude(&$match, &$excludedPages, &$excludedNs, &$excludeSelfPage){
        //--Looking if the syntax -exclude[item1 item2] has been used
        if(optionParser::preg_match_wrapper("exclude:\[(.*)\]", $match, $found)) {
            $match = optionParser::_removeFromMatch($found[0], $match);
            self::_addListOfItemsToExclude(explode(' ', $found[1]), $excludedPages, $excludedNs);
        }

        //--Checking if specified subnamespaces have to be excluded
        optionParser::preg_match_all_wrapper("exclude:([^[ <>]*):", $match, $found);
        foreach($found as $subns) {
            $excludedNs[] = $subns[1];
            $match        = optionParser::_removeFromMatch($subns[0], $match);
        }

        //--Checking if specified pages have to be excluded
        optionParser::preg_match_all_wrapper("exclude:([^[ <>]*)", $match, $found);
        foreach($found as $page) {
            $excludedPages[] = $page[1];
            $match           = optionParser::_removeFromMatch($page[0], $match);
        }

        //--Looking if the current page has to be excluded
        if(optionParser::preg_match_wrapper("exclude", $match, $found)) {
            $excludeSelfPage = true;
            $match           = optionParser::_removeFromMatch($found[0], $match);
        }
    }

    static function checkGlobalExclude($globalExclude, &$excludedPages, &$excludedNs) {
        if(!empty($globalExclude)) {
          self::_addListOfItemsToExclude(explode(',', $globalExclude), $excludedPages, $excludedNs);
        }
    }

    private static function _addListOfItemsToExclude($excludeList, &$excludedPages, &$excludedNs) {
        foreach($excludeList as $exclude) {
            $exclude = trim($exclude);
            if ($exclude === "") {
                return;
            }
            if($exclude[-1] === ':') {
                $excludedNs[] = PhpString::substr($exclude, 0, -1);
            } else {
                $excludedPages[] = $exclude;
            }
       }
    }

    static function checkActualTitle(&$match, &$varAffected){
        $foundOption = false;
        if ( optionParser::preg_match_wrapper("actualTitle *= *([[:digit:]])", $match, $found) ){
            $varAffected = $found[1];
            $foundOption = true;
        } else if ( optionParser::preg_match_wrapper("actualTitle", $match, $found) ){
            $varAffected = 2;
            $foundOption = true;
        }

        if ($foundOption) {
            $match = optionParser::_removeFromMatch($found[0], $match);
        }
    }

    static private function preg_match_wrapper($pattern, $subject, &$matches){
        return preg_match('/\s-' . $pattern . '/i', $subject, $matches);
    }

    static private function preg_match_all_wrapper($pattern, $subject, &$matches){
        return preg_match_all('/\s-' . $pattern . '/i', $subject, $matches, PREG_SET_ORDER);
    }

    static private function _removeFromMatch($matched, $match){
        $matched = trim($matched); // to handle the case of the option "-r" which already matches an extra whitespace
        // Matched option including any leading and at least one trailing whitespace
        $regex = '/\s*' . preg_quote($matched, '/') . '\s+/';
        return preg_replace($regex, ' ', $match);
    }
}
