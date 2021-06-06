<?php
class Submenu_Page {
 
        /**
     * This function renders the contents of the page associated with the Submenu
     * that invokes the render method. In the context of this plugin, this is the
     * Submenu class.
     */


 
    public function __construct( $deserializer ) {
        $this->deserializer = $deserializer;
    }

    public function render() {
        include_once( PLAZAM_PLUGIN_PATH . '/views/settings.php' );
    }
}