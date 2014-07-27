<?php
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_nspages extends Dokuwiki_Action_Plugin {

    /**
     * Register its handlers with the dokuwiki's event controller
     */
    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('TOOLBAR_DEFINE', 'AFTER',  $this, 'insert_button', array());
    }

    function insert_button(& $event, $param) {
      $event->data[] = array (
          'type' => 'insert',
          'title' => 'nspages',
          'icon' => '../../plugins/nspages/images/tb_nspages.png',
          'insert' => $this->getConf('toolbar_inserted_markup')
          );
    }
}
