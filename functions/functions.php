<?php

global $archiveProjectType, $archivePropertyType, $queryStrVenta, $queryStrAlquiler, $montevideo, $punta_del_este, $pesos, $dolares, $projectStatus;

$archiveProjectType = 'proyectos';
$archivePropertyType = 'inmuebles';
$queryStrVenta = 'venta';
$queryStrAlquiler = 'alquiler';
$montevideo = 'montevideo';
$punta_del_este = 'punta-del-este';
$pesos = '$U';
$dolares = 'USD';
$projectStatus = array(
    'proyecto-en-lanzamiento',
    'proyecto-estrena-ya',
    'proyecto-en-construccion'    
);

if( !function_exists('plazam_ajax_filter_properties') ) {
    function plazam_ajax_filter_properties() {
        global $archiveProjectType;
        $tags = json_decode( stripslashes( $_POST['tags'] ), true );
        $precios = json_decode( stripslashes( $_POST['prices'] ), true );
        $project_str_or_prop_id = stripslashes( $_POST['project_str']);
        $archive_type = stripslashes( $_POST['archiveType']);
        $moneda = $_POST['currency'];
        $is_project = $_POST['is_project'];
        $taxo_arr = plazam_create_taxonomy_arr($tags, false);        
        $tax_query = plazam_load_tax_query_arr($taxo_arr,$is_project);
        $deserializer = new Deserializer();

        $args = array(
            'post_type' => array('property'),  
            'post_status' => array('publish'),
            'posts_per_page' => $deserializer->get_value( 'plazam-count-properties' ),
            'page' => 1,
            'tax_query' => $tax_query        
        );

        if(filter_var($is_project, FILTER_VALIDATE_BOOLEAN)){
            if(isset($project_str_or_prop_id) && $project_str_or_prop_id != ''){
                $args['s'] = $project_str_or_prop_id;
                $args['compare'] = 'LIKE';
            }
            $args['orderby']    = array('date');
            $args['order']      = 'DESC';
        }else{
            $meta_query = array(
                'relation'      =>   'AND',

            );
            if($precios && count($precios)>1){
                $meta_query['moneda'] = array(
                    'key'       => 'fave_currency',
                    'type'      => 'CHAR',
                    'value'     => $moneda,
                    'compare'   => 'LIKE'
                );                
                $meta_query['precio'] = array(
                    'key'       => 'fave_property_price',
                    'type'      => 'NUMERIC',
                    'value'     => array(intval($precios[0]),intval($precios[1])),
                    'compare'   => 'BETWEEN'
                );
            }
            else{
                $meta_query['moneda'] = array(
                    'key'       => 'fave_currency'
                );
                $meta_query['precio'] = array(
                    'key'       => 'fave_property_price',
                    'type'      => 'NUMERIC',
                );                
            }
            if(isset($project_str_or_prop_id) && $project_str_or_prop_id != ''){
                array_push($meta_query, array(
                    'key'       => 'fave_property_id',
                    'type'      => 'NUMERIC',
                    'value'     => $project_str_or_prop_id,
                    'compare'   => '='
                )); 
            }            

            $args['meta_query'] = $meta_query;
            $args['orderby']    = array(
                'moneda' => 'ASC',
                'precio' => 'ASC',
            );             
        }   



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
                get_template_part('template-parts/listing/item-v1');
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


if( !function_exists('plazam_template_args_query') ) {
    function plazam_template_args_query($tags,$archive_type) {
        global $archiveProjectType;        
        $is_project = (strpos($archive_type,$archiveProjectType) !== false && strpos($archive_type,$archiveProjectType) >= 0) ? true : false;

        $deserializer = new Deserializer();     
        $taxo_arr = plazam_create_taxonomy_arr($tags,true);
        $tax_query = plazam_load_tax_query_arr($taxo_arr,$is_project);
        $count = count($tax_query);
        $args = array(
            'post_type'     => array('property'),  
            'post_status'   => array('publish'),
            'posts_per_page'=> $deserializer->get_value( 'plazam-count-properties' ),
            'paged'         => get_query_var('paged')
        );

        if($count > 0 ) {
            $args['tax_query'] = $tax_query;
        }        

        if($is_project){
            $args['orderby']    = array('date');
            $args['order']      = 'DESC';
        }
        else{
            $meta_query = array(
                'relation'      =>   'AND',
                'query_one'     => array(
                    'key'       => 'fave_currency'
                ),
                'query_two' => array(
                    'key'       => 'fave_property_price',
                    'type'      => 'NUMERIC',
                ),
            );
            $args['meta_query'] = $meta_query;
            $args['orderby']    = array(
                'query_one' => 'ASC',
                'query_two' => 'ASC',
            );            
        }

        return $args;
    }   
}

if( !function_exists('plazam_get_archive_type') ) {
    function plazam_get_archive_type(){
        global $archiveProjectType;
        $retorno = $archiveProjectType;

        if(strpos(get_post_field( 'post_name', $post->slug ),$archiveProjectType) === false)
            $retorno = 'inmuebles';

        return $retorno;
    }
}

if(!function_exists('plazam_is_project')){
    function plazam_is_project(){
        global $archiveProjectType;

        $is_project = plazam_get_archive_type() != "" && strpos(plazam_get_archive_type(),$archiveProjectType) !== false && strpos(plazam_get_archive_type(),$archiveProjectType) >= 0;
        return $is_project;
    }    
}


if( !function_exists('plazam_markup_default_filters') ) {
    function plazam_markup_default_filters($archiveType, $key, $value){
        global $archiveProjectType, $archivePropertyType;
        $output = "";
        if( !plazam_is_project()){
            $output .= '<h5 class="'.$key.' mt-3">'. __( get_taxonomy( $key )->labels->name, 'houzez' ) .'</h5>';
            $output .= '<ul class="list-group '.$key.'">';            
            foreach ($value as $taxonomy => $term) {  
                if($term['count']){
                    if($key == 'property_type'){
                        $output .= '<li class="filter-options alquiler-venta" ><a href="#" data-term="'.$key.'|'.$term['slug'].'">'.__( $term['name'],'houzez' ).' ('.$term['count'].')</a></li>';
                    }
                    elseif($key == 'property_status'){
                        if(!plazam_status_in_project($term['slug'])){
                            if(get_query_var('operacion') == 'venta'){
                                if(strpos($term['slug'],'venta') !== false){
                                    $output .= '<li class="filter-options filter-default alquiler-venta" ><a href="#" data-term="'.$key.'|'.$term['slug'].'">'. __( $term['name'], 'houzez').' ('.$term['count'].')</a></li>';                               
                                }
                                else{
                                    $output .= '<li class="filter-options alquiler-venta" ><a href="#" data-term="'.$key.'|'.$term['slug'].'">'. __( $term['name'], 'houzez') .' ('.$term['count'].')</a></li>';
                                }                                                    
                            }
                            else{
                                if(strpos($term['slug'],'alquiler') !== false){
                                    $output .= '<li class="filter-options filter-default alquiler-venta" ><a href="#" data-term="'.$key.'|'.$term['slug'].'">'. __( $term['name'], 'houzez' ).' ('.$term['count'].')</a></li>';                                            
                                }
                                else{
                                    $output .= '<li class="filter-options alquiler-venta" ><a href="#" data-term="'.$key.'|'.$term['slug'].'">'. __( $term['name'], 'houzez') .' ('.$term['count'].')</a></li>';
                                }
                            }
                        }                                                                                  
                    }
                    elseif($key == 'property_area'){
                        $output .= '<li class="filter-options generico"><a href="#" data-term="'.$key.'|'.$term['slug'].'">'. __($term['name'],'houzez' ).' ('.$term['count'].')</a></li>';
                    }                    
                    else{
                        $output .= '<li class="filter-options generico"><a href="#" data-term="'.$key.'|'.$term['slug'].'">'.__( $term['name'], 'houzez' ).' ('.$term['count'].')</a></li>';
                    }
                }
            }          
            $output .= '</ul>';
        }
        else{
            if($key != 'property_type' && $key != 'property_city')
                $output .= '<h5 class="'.$key.' mt-3">'. __( get_taxonomy( $key )->labels->name, 'houzez') .'</h5>';       
            $output .= '<ul class="list-group '.$key.'">';            
            foreach ($value as $taxonomy => $term) {                 
                if($term['count']){
                    if($key == 'property_type'){
                        continue;
                    }
                    elseif($key == 'property_status'){
                        if(plazam_status_in_project($term['slug'])){
                            $output .= '<li class="filter-options proyecto" ><a href="#" data-term="'.$key.'|'.$term['slug'].'">'. __( $term['name'], 'houzez' ).' ('.$term['count'].')</a></li>';
                        }
                    }
                    elseif($key == 'property_city'){
                        /*
                        if(plazam_get_city_tag()!="" && strpos($term['slug'],plazam_get_city_tag()) !== false ){
                            $output .= '<li class="filter-options filter-default proyecto" ><a href="#" data-term="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                        }
                        else{
                            $output .= '<li class="filter-options proyecto" ><a href="#" data-term="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                        } 
                        */                       

                    }
                    elseif($key == 'property_area'){
                        $option_name = '_houzez_property_area_'.$term['id'];
                        $ciudad = get_option($option_name)['parent_city']; 

                        if(plazam_belongs_to_city($ciudad) )
                            $output .= '<li class="filter-options generico"><a href="#" data-term="'.$key.'|'.$term['slug'].'">'. __( $term['name'], 'houzez' ) .' ('.$term['count'].')</a></li>';                        
                    }                                                              
                    else{
                        $output .= '<li class="filter-options generico"><a href="#" data-term="'.$key.'|'.$term['slug'].'">'. __($term['name'],'houzez') .' ('.$term['count'].')</a></li>';
                    }
                }             
            }
            $output .= '</ul>';            
        }
        return $output;
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
        $query_str = "";

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

        if(get_query_var('operacion'))
            $query_str = '?operacion='.get_query_var('operacion');
        if(get_query_var('ciudad'))
            $query_str = '?ciudad='.get_query_var('ciudad');        

        if( 1 != $pages ){

            $output = "";
            $inner = "";
            $output .= '<div class="pagination-wrap">';
                $output .= '<nav>';
                    $output .= '<ul class="pagination justify-content-center">';
                        
                        if( $paged > 2 && $paged > $range+1 && $showitems < $pages ) { 
                            $output .= '<li class="page-item">';
                                $output .= '<a class="page-link" href="'.$base.'page/1/" aria-label="Previous">';
                                    $output .= '<i class="houzez-icon arrow-button-left-1"></i>';
                                $output .= '</a>';
                            $output .= '</li>';
                        }

                        if( $paged > 1 ) { 
                            $output .= '<li class="page-item">';
                                $output .= '<a class="page-link" href="'.$base.'page/$prev" aria-label="Previous">';
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
                                    $inner .= '<li class="page-item active"><a class="page-link" href="'.$base.'page/'.$i.'/'.$query_str.'">'.$i.' <span class="sr-only"></span></a></li>';
                                } else {
                                    $inner .= '<li class="page-item"><a class="page-link" href="'.$base.'page/'.$i.'/'.$query_str.'">'.$i.'</a></li>';
                                }
                            }
                        }
                        $output .= $inner;
                        

                        if($paged < $pages) {
                            $output .= '<li class="page-item">';
                                $output .= '<a class="page-link" href="'.$base.'page/'.$next.'" aria-label="Next">';
                                    $output .= '<i class="houzez-icon icon-arrow-right-1"></i>';
                                $output .= '</a>';
                            $output .= '</li>';
                        }

                        if( $paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages ) {
                            $output .= '<li class="page-item">';
                                $output .= '<a class="page-link" href="'.$base.'page/'.$pages.'" aria-label="Next">';
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

if(!function_exists('plazam_print_buscador_str')){
    function plazam_print_buscador_str(){
        $output = '<div class="flex-search flex-grow-1"><div class="form-group"><div class="search-icon mb-4">';
        if(plazam_get_archive_type() == $archiveProjectType){
            $output .= '<input id="buscador-proyectos" name="keyword" type="text" class="houzez-keyword-autocomplete form-control" value="" placeholder="Ingrese nombre del proyecto">';
        }
        else{
            $output .= '<input id="buscador-proyectos" name="keyword" type="text" class="houzez-keyword-autocomplete form-control" value="" placeholder="Ingrese número de referencia">';
        }
        $output .= '</div></div>';
        $output .= '<div class="flex-search btn-no-right-padding">';
        $output .= '<button id="btn-buscador" type="submit" class="btn btn-search btn-secondary btn-full-width ">';$output .= 'Búsqueda';
        $output .= '</button></div></div>';
        return $output;
    }
}

if(! function_exists('plazam_item_menu_query_str')){
    function plazam_item_menu_query_str( $items, $menu, $args ) {
        if( is_admin() )
            return $items;
        foreach( $items as $item ) {
            if ( strpos($item->post_title,'ropiedades en Venta') !== false && strpos($item->post_title,'ropiedades en Venta') >= 0){
                $item->url = add_query_arg( 'operacion', 'venta', $item->url );
            }
            elseif(strpos($item->post_title,'ropiedades en Alquiler') !== false && strpos($item->post_title,'ropiedades en Alquiler') >= 0)
                $item->url = add_query_arg( 'operacion', 'alquiler', $item->url );
            elseif(strpos($item->post_title,'royectos en Montevideo') !== false && strpos($item->post_title,'royectos en Montevideo') >= 0)
                $item->url = add_query_arg( 'ciudad', 'proyectos-montevideo', $item->url );
            elseif(strpos($item->post_title,'royectos en Punta') !== false && strpos($item->post_title,'royectos en Punta') >= 0)
                $item->url = add_query_arg( 'ciudad', 'proyectos-punta-del-este', $item->url );
            elseif(strpos($item->post_title,'Vivienda Social') !== false && strpos($item->post_title,'Vivienda Social') >= 0)
                $item->url = add_query_arg( 'tipo', 'proyectos-vivienda-social', $item->url );                
        }
        return $items;
    }
}
add_filter( 'wp_get_nav_menu_items','plazam_item_menu_query_str', 11, 3 );

if(! function_exists('plazam_register_query_vars')){
    function plazam_register_query_vars( $vars ) {
        $vars[] = 'operacion';
        $vars[] = 'tipo';        
        $vars[] = 'ciudad';
        return $vars;
    }    
}
add_filter( 'query_vars', 'plazam_register_query_vars' );


if(!function_exists('plazam_create_taxonomy_arr')){
    function plazam_create_taxonomy_arr($tags,$isDefault){
        $taxo_arr = array();
        if($isDefault){
            if($tags['property_city'] == '') unset($tags['property_city']);
            if($tags['property_status'] == '') unset($tags['property_status']);            
            $taxo_arr = $tags;
        }
        else{
            foreach($tags as $tag => $value){
                $tag_arr = array(); 
                $tag_arr = explode('|', $value['slug']);
                if($taxo_arr[$tag_arr[0]]){
                    if(!in_array($tag_arr[1],$taxo_arr[$tag_arr[0]]))
                        array_push($taxo_arr[$tag_arr[0]],$tag_arr[1]);
                }else{
                    $taxo_arr[$tag_arr[0]]=array();
                    array_push($taxo_arr[$tag_arr[0]],$tag_arr[1]);
                }
            }    
        }
        return $taxo_arr;
    }
}

if(!function_exists('plazam_load_tax_query_arr')){
    function plazam_load_tax_query_arr($taxo_arr, $is_project){
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
        /*
        if($is_project){
            array_push($tax_query,[
                'taxonomy'  => 'property_type',
                'field'     => 'slug',
                'terms'     => 'propiedad',
                'operator'  => 'NOT IN' 
            ]);
        }
        else{
            array_push($tax_query,[
                'taxonomy'  => 'property_type',
                'field'     => 'slug',
                'terms'     => 'proyecto',
                'operator'  => 'NOT IN' 
            ]);
        }
        */
        return $tax_query;            
    }
}


if(!function_exists('plazam_load_meta_query_arr')){
    function plazam_load_meta_query_arr(){
        $meta_query = array(
            'meta_query' => array(
                'relation' => 'AND',
                'moneda_clause' => array(
                    'key' => 'fave_currency',
                    'compare' => 'EXISTS',
                    'type'      => 'NUMERIC'                    
                ),
                'precio_clause' => array(
                    'key' => 'fave_property_price',
                    'compare' => 'EXISTS',
                    'type'      => 'NUMERIC'
                )
                 
            ),
            'orderby' => array('precio_clause'),
            'order' => 'ASC'                    
        );
        return $meta_query;
    }
}


if(!function_exists('plazam_get_city_tag')){
    function plazam_get_city_tag(){
        global $archiveProjectType, $montevideo, $punta_del_este;
        $retorno = "";
        $archive_type = plazam_get_archive_type();
        if(strpos($archive_type,$archiveProjectType) !== false && strpos($archiveProjectType,$archive_type) >= 0){
            if(strpos(get_query_var('ciudad'),$montevideo) !== false && strpos(get_query_var('ciudad'),$montevideo) >= 0)
                $retorno = 'ciudad-de-'.$montevideo;
            else
                $retorno = 'ciudad-de-'.$punta_del_este;
        }

        return $retorno;
    }    
}

if(!function_exists('plazam_set_default_tags')){
    function plazam_set_default_tags(){
        global $queryStrVenta;
        $status = array();

        if(strpos(plazam_get_archive_type(),'proyecto') === false){
            if(plazamIsVenta())
                array_push($status,'propiedad-en-venta');
            else
                array_push($status,'propiedades-en-alquiler');
        }
        else{
            array_push($status,'proyecto-en-lanzamiento');
            array_push($status,'proyecto-estrena-ya');
            array_push($status,'proyecto-en-construccion');
        }
        return array(
            'property_city' => plazam_get_city_tag(),
            'property_status' => $status
        ); 
    }
}

if(!function_exists('plazamIsVenta')){
    function plazamIsVenta(){
        global $queryStrVenta;        
        return strpos(get_query_var('operacion'),$queryStrVenta) !== false && strpos(get_query_var('operacion'),$queryStrVenta) >= 0;
    }

}

if(!function_exists('plazam_default_query')){
    function plazam_default_query(){
        $archiveType = plazam_get_archive_type();
        $default_tags = plazam_set_default_tags();
        $args = plazam_template_args_query($default_tags, $archiveType);
        $query = new WP_Query( $args );
        return $query;
    }    
}


if(!function_exists('plazam_sort_featured')){
    function plazam_sort_featured(){
        $args = array(
            'post_type'     => array('property'),  
            'post_status'   => array('publish'),
            'posts_per_page'=> -1,
            'meta_key'      => 'fave_featured',
            'meta_value'    =>  '1',
            'meta_compare'  => 'LIKE',
            'meta_query'    => array(
                'relation'  => '&&',
                'query_one' => array(
                    'key'       => 'fave_currency'
                ),
                'query_two' => array(
                    'key'       => 'fave_property_price',
                    'type'      => 'NUMERIC',
                ),
            ),
            'orderby'    => array(
                'query_one' => 'ASC',
                'query_two' => 'ASC',
            )  
        );
        $query = new WP_Query($args);
        return $query;
    }
}

if(!function_exists('plazam_status_in_project')){
    function plazam_status_in_project($status){
        global $projectStatus;
        return in_array($status, $projectStatus);
    }
}

if(!function_exists('plazam_belongs_to_city')){
    function plazam_belongs_to_city($city){
        $city = str_replace('ciudad-de-', '',$city);

        return (strpos(get_query_var('ciudad'), $city) !== false && strpos(get_query_var('ciudad'), $city) >= 0);
    }
}
