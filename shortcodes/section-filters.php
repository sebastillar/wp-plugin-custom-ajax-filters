<?php
/*-----------------------------------------------------------------------------------*/
/*  Advance Search
/*-----------------------------------------------------------------------------------*/
if( !function_exists('plazam_filters') ) {
    function plazam_filters($atts, $content = null)
    {
        extract(shortcode_atts(array(
            'filters_title' => 'Buscando inmuebles'
        ), $atts));

        ob_start();

        $filters = Filter::getTaxonomies();  
        $archiveType = getArchiveType();
        ?>
        <div class="advanced-search-module" data-type="<?php echo $archiveType ?>">
            <div class="advanced-search-v1">
                <div class="row">
                    <div class="col-md-12 col-6 d-none d-sm-block">;                            
                        <div class="row">
                            <div class="col-sm-12">                     
                                <?php if(!empty($filters_title)) { ?>
                                    <div class="advanced-search-module-title">
                                        <i class="houzez-icon icon-search mr-2"></i> <?php echo esc_attr($filters_title); ?>
                                    </div>
                                <?php } ?>
                                <ul id="filtros-seleccionados"></ul>
                            </div>
                        </div>
                    <?php
                    if ($filters) {
                        foreach ($filters as $key => $value) { 
                            echo '<div class="row">';                                
                            echo '<div class="col-sm-12">';   
                            
                            printDefaultFilters($archiveType, $key, $value);

                            echo '</ul>';
                            echo '</div>';
                            echo '</div>';                                                                                          
                        }
                    }
                    ?>
                    <div>;
                </div>
            </div>
        </div>
        <?php
        
        $result = ob_get_contents();
        ob_end_clean();
        return $result;

    }

    add_shortcode('plazam_filters', 'plazam_filters');
}
?>

