<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) die();

class rendererXhtmlHelper {
    private $renderer;
    private $percentWidth;
    private $plugin;

    function __construct($renderer, $nbCols, $plugin){
        $this->renderer =& $renderer;
        $this->percentWidth = $this->buildWidth($nbCols);
        $this->plugin = $plugin;
    }

    private function buildWidth($nbCols){
        return (100 / $nbCols) . '%';
    }

    function printHeaderChar($char, $continued = false){
        $text = $char;
        if ( $continued ){
            $text .= $this->plugin->getLang('continued');
        }

        $this->renderer->doc .= '<div class="catpagechars">' . $text . "</div>\n";
    }

    function openColumn(){
        $this->renderer->doc .= "\n".'<div class="catpagecol" style="width: '.$this->percentWidth.'" >';
    }

    function closeColumn(){
        $this->renderer->doc .= "</div>\n";
    }

    function openListOfItems(){
        $this->renderer->doc .= "<ul class=\"nspagesul\">\n";
    }

    function closeListOfItems(){
        $this->renderer->doc .= '</ul>';
    }
}
