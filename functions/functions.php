<?php

global $archiveProjectType, $archivePropertyType, $queryStrVenta, $queryStrAlquiler;

$archiveProjectType = 'proyectos';
$archivePropertyType = 'inmuebles';
$queryStrVenta = 'venta';
$queryStrAlquiler = 'alquiler';


if( !function_exists('plazam_ajax_filter_properties') ) {
    function plazam_ajax_filter_properties() {
        $taxo_arr = array();
        $tax_query = array();
        $tags = json_decode( stripslashes( $_POST['tags'] ), true );
        $project_str = stripslashes( $_POST['project_str']);
        $archive_type = stripslashes( $_POST['archiveType']);
        $is_project = strpos($archive_type,$archiveProjectType) >= 0 ? true : false;
        $taxo_arr = createTaxonomyArr($tags);        
        $tax_query = loadTaxQueryArr($taxo_arr,$is_project);
    
        $args = array(
            'post_type' => array('property'),  
            'post_status' => array('publish'),
            'posts_per_page' => 10,
            'page' => 1,
            's' => $project_str,
            'tax_query' => $tax_query        
        );    

        wp_send_json([ 
            'args' => $tags
        ]);        
        die();
        $paginacion = plazam_pagination($query->max_num_pages);
        $query = new WP_Query( $args );
        $count_posts = $query->found_posts;
    
        ob_start();
    
        if( ! $query->have_posts() ) { 
            get_template_part('template-parts/listing/item-none');
            wp_send_json([ 
                'query' => ob_get_clean(),
                'count_posts'  => 0
            ]);
        }
        else {
            while ( $query->have_posts() ) { 
                $query->the_post();
                get_template_part('template-parts/listing/item-v5');
            }
            wp_reset_postdata();
            wp_send_json([ 
                'query' => ob_get_clean(),
                'count_posts'  => $count_posts,
                'paginacion' => $paginacion,
                'args' => $args
            ]);
        }
        wp_die();
    }   
}
add_action( 'wp_ajax_ajax_filter', 'plazam_ajax_filter_properties' ); 
add_action( 'wp_ajax_nopriv_ajax_filter', 'plazam_ajax_filter_properties' );

if( !function_exists('urlGets') ) {
    function urlGets($querystr, $isParent){
        global $post;
        if($isParent)
            return strpos(get_post_field( 'post_name', $post->post_parent ),$querystr);        
        else
            return strpos(get_post_field( 'post_name', $post->slug ),$querystr);
    }
}

if( !function_exists('getArchiveType') ) {
    function getArchiveType(){
        global $archiveProjectType, $archivePropertyType;
        $retorno = $archivePropertyType;
        if(strpos(get_post_field( 'post_name', $post->slug ),$archiveProjectType))
            $retorno = $archiveProjectType;
        return $retorno;
    }
}

if( !function_exists('printDefaultFilters') ) {
    function printDefaultFilters($archiveType, $key, $value){
        global $archiveProjectType, $archivePropertyType;
        echo '<h5 class="'.$key.' mt-3">'. get_taxonomy( $key )->labels->name .'</h5>';       
        echo '<ul class="list-group '.$key.'">';
        if( $archiveType != $archiveProjectType){
            foreach ($value as $taxonomy => $term) {  
                if($term['count']){
                    if($key == 'property_type'){
                        if(
                            strpos($term['slug'],'propiedad') === false
                            AND strpos($term['slug'],'proyecto') === false
                            AND strpos($term['slug'],'regimen') === false
                            AND strpos($term['slug'],'vivienda-social') === false                            
                        )                                                    
                        {
                            echo '<li class="filter-options alquiler-venta" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                        }
                    }
                    elseif($key == 'property_status'){
                        if(strpos($term['slug'],'avance') === false){
                            if(get_query_var('operacion') == 'venta'){
                                if(strpos($term['slug'],'venta') !== false){
                                    echo '<li class="filter-options filter-default alquiler-venta" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';                               
                                }
                                else{
                                    echo '<li class="filter-options alquiler-venta" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                                }                                                    
                            }
                            else{
                                if(strpos($term['slug'],'alquiler') !== false){
                                    echo '<li class="filter-options filter-default alquiler-venta" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';                                            
                                }
                                else{
                                    echo '<li class="filter-options alquiler-venta" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                                }
                            }
                        }                                                                                  
                    }
                    elseif($key == 'property_area'){
                        echo '<li class="filter-options generico"><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';                        
                        /*
                        $option_name = '_houzez_property_area_'.$term['id'];
                        if(urlGets(get_option($option_name)['parent_city'],false))
                            echo '<li class="filter-options generico"><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                        */
                    }                    
                    else{
                        echo '<li class="filter-options generico"><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                    }
                }
                ?>
                <?php  
            }
        }
        else{
            foreach ($value as $taxonomy => $term) {                 
                if($term['count']){
                    if($key == 'property_type'){
                        if(strpos($term['slug'],'proyecto') !== false){
                            echo '<li class="filter-options filter-default proyecto" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                        }
                        else{
                            if( strpos($term['slug'],'apartamento') === false AND 
                                strpos($term['slug'],'casa') === false AND
                                strpos($term['slug'],'local') === false AND
                                strpos($term['slug'],'oficina') === false AND
                                strpos($term['slug'],'propiedad') === false
                                ){
                                echo '<li class="filter-options proyecto" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                            }
                        }
                    }
                    elseif($key == 'property_status'){
                        if(strpos($term['slug'],'venta') === false AND strpos($term['slug'],'alquiler') === false){
                            echo '<li class="filter-options proyecto" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                        }
                    }
                    elseif($key == 'property_area'){
                        $option_name = '_houzez_property_area_'.$term['id'];
                        $ciudad = get_option($option_name)['parent_city']; 

                        if(strpos(get_query_var('ciudad'), $ciudad))
                            echo '<li class="filter-options generico"><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';                        
                    }                                                              
                    else{
                        echo '<li class="filter-options generico"><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                    }
                }
                ?>
                <?php  
            }                                
        }
        echo '</ul>';
    }
}

if( !function_exists('printDefaultCityFilter')){
    function printDefaultCityFilter($key, $term){
        if(urlGets('ontevideo',false)){
            if(strpos($term['slug'],'montevideo') !== false){
                echo '<li class="filter-options filter-default"><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';                                                    
            }else{
                echo '<li class="filter-options"><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
            }                   
        }else{
            if(strpos($term['slug'],'punta-del-este') !== false){
                echo '<li class="filter-options filter-default"><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';                                                    
            }else{
                echo '<li class="filter-options"><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
            }  
        }
    }
}


/**
 *   -------------------------------------------------------------
 *   Plazam Pagination
 *   -------------------------------------------------------------
 */
if( !function_exists( 'plazam_pagination' ) ){
    function plazam_pagination($pages = '', $range = 2 ) {
        global $paged;
        global $post;
        $base = get_permalink( $post );

        if(empty($paged))$paged = 1;

        $prev = $paged - 1;
        $next = $paged + 1;
        $showitems = ( $range * 2 )+1;
        $range = 2; // change it to show more links

        if( $pages == '' ){
            global $wp_query;
            $pages = $wp_query->max_num_pages;
            if( !$pages ){
                $pages = 1;
            }
        }

        if( 1 != $pages ){

            $output = "";
            $inner = "";
            $output .= '<div class="pagination-wrap">';
                $output .= '<nav>';
                    $output .= '<ul class="pagination justify-content-center">';
                        
                        if( $paged > 2 && $paged > $range+1 && $showitems < $pages ) { 
                            $output .= '<li class="page-item">';
                                $output .= '<a class="page-link" href="'.$base.'/page/1/" aria-label="Previous">';
                                    $output .= '<i class="houzez-icon arrow-button-left-1"></i>';
                                $output .= '</a>';
                            $output .= '</li>';
                        }

                        if( $paged > 1 ) { 
                            $output .= '<li class="page-item">';
                                $output .= '<a class="page-link" href="'.$base.'/page/$prev" aria-label="Previous">';
                                    $output .= '<i class="houzez-icon icon-arrow-left-1"></i>';
                                $output .= '</a>';
                            $output .= '</li>';
                        } else {
                            $output .= '<li class="page-item disabled">';
                                $output .= '<a class="page-link" aria-label="Previous">';
                                    $output .= '<i class="houzez-icon icon-arrow-left-1"></i>';
                                $output .= '</a>';
                            $output .= '</li>';
                        }

                        for ( $i = 1; $i <= $pages; $i++ ) {
                            if ( 1 != $pages &&( !( $i >= $paged+$range+1 || $i <= $paged-$range-1 ) || $pages <= $showitems ) )
                            {
                                if ( $paged == $i ){
                                    $inner .= '<li class="page-item active"><a class="page-link" href="'.$base.'/page/'.$i.'/">'.$i.' <span class="sr-only"></span></a></li>';
                                } else {
                                    $inner .= '<li class="page-item"><a class="page-link" href="'.$base.'/page/'.$i.'/">'.$i.'</a></li>';
                                }
                            }
                        }
                        $output .= $inner;
                        

                        if($paged < $pages) {
                            $output .= '<li class="page-item">';
                                $output .= '<a class="page-link" href="'.$base.'/page/'.$next.'" aria-label="Next">';
                                    $output .= '<i class="houzez-icon icon-arrow-right-1"></i>';
                                $output .= '</a>';
                            $output .= '</li>';
                        }

                        if( $paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages ) {
                            $output .= '<li class="page-item">';
                                $output .= '<a class="page-link" href="'.$base.'/page/'.$pages.'" aria-label="Next">';
                                    $output .= '<i class="houzez-icon arrow-button-right-1"></i>';
                                $output .= '</a>';
                            $output .= '</li>';
                        }


                    $output .= '</ul>';
                $output .= '</nav>';
            $output .= '</div>';
            
            return $output;

        }
    }
}

if(!function_exists('printBuscadorStr')){
    function printBuscadorStr(){
        $output = '<div class="flex-search flex-grow-1"><div class="form-group"><div class="search-icon mb-4">';
        $output .= '<input id="buscador-proyectos" name="keyword" type="text" class="houzez-keyword-autocomplete form-control" value="" placeholder="Ingrese nombre del proyecto">';
        $output .= '</div></div>';
        $output .= '<div class="flex-search btn-no-right-padding">';
        $output .= '<button id="btn-buscador" type="submit" class="btn btn-search btn-secondary btn-full-width ">';$output .= 'Búsqueda';
        $output .= '</button></div></div>';
        return $output;
    }
}

if(! function_exists('plazamItemMenuQueryStr')){
    function plazamItemMenuQueryStr( $items, $menu = 'your_menu_slug', $args ) {

        foreach( $items as $item ) {
    
            if ( strpos($item->post_title,'ropiedades en Venta'))
                $item->url = add_query_arg( 'operacion', 'venta', $item->url );
            elseif(strpos($item->post_title,'ropiedades en Alquiler'))
                $item->url = add_query_arg( 'operacion', 'alquiler', $item->url );
            elseif(strpos($item->post_title,'royectos en Montevideo'))
                $item->url = add_query_arg( 'ciudad', 'proyectos-montevideo', $item->url );
            elseif(strpos($item->post_title,'royectos en Punta'))
                $item->url = add_query_arg( 'ciudad', 'proyectos-punta-del-este', $item->url );
            elseif(strpos($item->post_title,'Vivienda Social'))
                $item->url = add_query_arg( 'tipo', 'proyectos-vivienda-social', $item->url );                
        }
        return $items;
    }
}
add_filter( 'wp_get_nav_menu_items','plazamItemMenuQueryStr', 11, 3 );

if(! function_exists('plazam_register_query_vars')){
    function plazam_register_query_vars( $vars ) {
        $vars[] = 'operacion';
        $vars[] = 'tipo';        
        $vars[] = 'ciudad';
        return $vars;
    }
    add_filter( 'query_vars', 'plazam_register_query_vars' );
    
}

if(!function_exists('createTaxonomyArr')){
    function createTaxonomyArr($tags){
        $taxo_arr = array();    
        foreach($tags as $tag => $value){
            $tagArr = explode('|', $value);
            if($taxo_arr[$tagArr[0]]){
                if(!in_array($tagArr[1],$taxo_arr[$tagArr[0]]))
                    array_push($taxo_arr[$tagArr[0]],$tagArr[1]);
            }else{
                $taxo_arr[$tagArr[0]]=array();
                array_push($taxo_arr[$tagArr[0]],$tagArr[1]);
            }
        }    
        return $taxo_arr;
    }
}

if(!function_exists('loadTaxQueryArr')){
    function loadTaxQueryArr($taxo_arr, $is_project){
        $tax_query = array(
            'relation' => 'AND', 
        );
        foreach($taxo_arr as $tax => $val){
            array_push($tax_query,[
                'taxonomy' => $tax,
                'field' => 'slug',
                'terms' => $val,
                'operator' => 'IN' 
            ]);
        }
        if(!$is_project){
            array_push($tax_query,[
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => 'proyecto',
                'operator' => 'NOT IN' 
            ]);
        }
        else{
            array_push($tax_query,[
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => 'propiedad',
                'operator' => 'NOT IN' 
            ]);
        }
        return $tax_query;            
    }
}
