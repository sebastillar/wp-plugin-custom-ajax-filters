<?php
 
class Deserializer {
 
    /**
     * Retrieves the value for the option identified by the specified key. If
     * the option value doesn't exist, then an empty string will be returned.
     *
     * @param  string $option_key The key used to identify the option.
     * @return string             The value of the option or an empty string.
     */
    public function get_value( $option_key ) {
        return get_option( $option_key, '10' );
    }
}