<?php
/*-----------------------------------------------------------------------------------*/
/*  Advance Search
/*-----------------------------------------------------------------------------------*/
if( !function_exists('plazam_filters') ) {
    function plazam_filters($atts, $content = null)
    {
        extract(shortcode_atts(array(
            'filters_title' => ''
        ), $atts));

        ob_start();

        $filters = Filter::getTaxonomies();  
        ?>
        <div class="advanced-search-module">
            <?php if(!empty($filters_title)) { ?>
            <div class="advanced-search-module-title">
                <i class="houzez-icon icon-search mr-2"></i> <?php echo esc_attr($filters_title); ?>
            </div>
            <?php } ?>
            <div class="advanced-search-v1">
                <div class="row">
                    <div class="col-md-12 col-6 d-none d-sm-block">;                            
                        <div class="row">
                            <div class="col-sm-12">                                                                
                                <h5 data-filtro="<?php echo getDefaultFilterName();   ?>">Filtros</h5>                                                                    
                                <ul id="filtros-seleccionados"></ul>
                            </div>
                        </div>
                    <?php
                    if ($filters) {
                        foreach ($filters as $key=>$value) { 
                            echo '<div class="row">';                                
                            echo '<div class="col-sm-12">';                                                            
                            if(getDefaultFilterName() != 'project'){
                                echo '<h5 class="'.$key.'">'.get_taxonomy( $key )->labels->name.'</h5>';                                
                                echo '<ul class="list-group '.$key.'">';
                                foreach ($value as $taxonomy => $term) {  
                                    if($term['count']){
                                        if(strpos($term['slug'],'avance') === false)
                                            echo '<li class="filter-options"><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                                    }
                                    ?>
                                    <?php  
                                }
                            }
                            else{
                                if(!strpos($key,'type')){
                                    echo '<h5 class="'.$key.'">'.get_taxonomy( $key )->labels->name.'</h5>';                                    
                                    echo '<ul class="list-group '.$key.'">';
                                    foreach ($value as $taxonomy => $term) {  
                                        if($term['count']){
                                            if(strpos($term['slug'],'venta') === false AND strpos($term['slug'],'alquiler') === false)
                                                echo '<li class="filter-options"><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                                        }
                                        ?>
                                        <?php  
                                    }
                                }
                            }
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

