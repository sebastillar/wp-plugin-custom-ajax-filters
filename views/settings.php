<div class="wrap">
 
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
 
    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
 
        <div id="universal-message-container">
            <h2>Configuración</h2>
 
            <div class="options">
                <p>
                    <label>Cantidad de propiedades a mostrar en páginas de listado:</label>
                    <br />
                    <input type="text" name="plazam-count-properties" value="<?php echo esc_attr( $this->deserializer->get_value( 'plazam-count-properties' ) ); ?>" />
                </p>
        </div>
        <?php
            wp_nonce_field( 'plazam-settings-save', 'plazam-nonce' );
            submit_button();
        ?>        
    </form>
 
</div><!-- .wrap -->