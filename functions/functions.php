<?php

global $archiveProjectType, $archivePropertyType, $queryStrVenta, $queryStrAlquiler, $montevideo, $punta_del_este,$pesos,$dolares;

$archiveProjectType = 'proyectos';
$archivePropertyType = 'inmuebles';
$queryStrVenta = 'venta';
$queryStrAlquiler = 'alquiler';
$montevideo = 'montevideo';
$punta_del_este = 'punta-del-este';
$pesos = '$U';
$dolares = 'USD';

if( !function_exists('plazam_ajax_filter_properties') ) {
    function plazam_ajax_filter_properties() {
        global $archiveProjectType;
        $taxo_arr = array();
        $tax_query = array();
        $tags = json_decode( stripslashes( $_POST['tags'] ), true );
        $project_str = stripslashes( $_POST['project_str']);
        $archive_type = stripslashes( $_POST['archiveType']);
        $is_project = (strpos($archive_type,$archiveProjectType) !== false AND strpos($archive_type,$archiveProjectType)) >= 0 ? true : false;
        $taxo_arr = plazamCreateTaxonomyArr($tags, false);        
        $tax_query = plazamLoadTaxQueryArr($taxo_arr,$is_project);
    
        $args = array(
            'post_type' => array('property'),  
            'post_status' => array('publish'),
            'posts_per_page' => 10,
            'page' => 1,
            's' => $project_str,
            'tax_query' => $tax_query        
        );    
        $paginacion = plazam_pagination($query->max_num_pages);
        $query = new WP_Query( $args );
        $count_posts = $query->found_posts;
        wp_send_json([ 
            'query' => $tags,
            'count_posts'  => $count_posts,
            'paginacion' => $paginacion,
            'args' => $args
        ]);
        die();
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


if( !function_exists('plazam_template_args_query') ) {
    function plazam_template_args_query($tags,$archive_type, $is_featured) {
        global $archiveProjectType;        
        $is_project = (strpos($archive_type,$archiveProjectType) !== false AND strpos($archive_type,$archiveProjectType) >= 0) ? true : false;

        $taxo_arr = plazamCreateTaxonomyArr($tags,true);
        $tax_query = plazamLoadTaxQueryArr($taxo_arr,$is_project);
        $count = count($tax_query);
        $args = array(
            'post_type'     => array('property'),  
            'post_status'   => array('publish'),
            'posts_per_page'=> 10,
            'paged'         => 1
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

        if($is_featured){
            $featured = array(
                'key'       =>  '',
                'value'     =>  '1',
                'types'     =>  'NUMERIC',
                'compare'   =>  'LIKE'
            );             
            array_push($meta_query,$featured);
        }
        return $args;
    }   
}

if( !function_exists('plazamGetArchiveType') ) {
    function plazamGetArchiveType(){
        global $archiveProjectType, $archivePropertyType;
        $retorno = $archivePropertyType;
        if(strpos(get_post_field( 'post_name', $post->slug ),$archiveProjectType) !== false AND strpos(get_post_field( 'post_name', $post->slug ),$archiveProjectType) >= 0 )
            $retorno = $archiveProjectType;
        return $retorno;
    }
}

if( !function_exists('plazamMarkupDefaultFilters') ) {
    function plazamMarkupDefaultFilters($archiveType, $key, $value){
        global $archiveProjectType, $archivePropertyType;
        $output = "";
        if( $archiveType != $archiveProjectType){
            $output .= '<h5 class="'.$key.' mt-3">'. get_taxonomy( $key )->labels->name .'</h5>';
            $output .= '<ul class="list-group '.$key.'">';            
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
                            $output .= '<li class="filter-options alquiler-venta" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                        }
                    }
                    elseif($key == 'property_status'){
                        if(strpos($term['slug'],'avance') === false){
                            if(get_query_var('operacion') == 'venta'){
                                if(strpos($term['slug'],'venta') !== false){
                                    $output .= '<li class="filter-options filter-default alquiler-venta" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';                               
                                }
                                else{
                                    $output .= '<li class="filter-options alquiler-venta" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                                }                                                    
                            }
                            else{
                                if(strpos($term['slug'],'alquiler') !== false){
                                    $output .= '<li class="filter-options filter-default alquiler-venta" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';                                            
                                }
                                else{
                                    $output .= '<li class="filter-options alquiler-venta" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                                }
                            }
                        }                                                                                  
                    }
                    elseif($key == 'property_area'){
                        $output .= '<li class="filter-options generico"><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                    }                    
                    else{
                        $output .= '<li class="filter-options generico"><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                    }
                }
                ?>
                <?php  
                $output .= '</ul>';
            }            
        }
        else{
            $output .= '<h5 class="'.$key.' mt-3">'. get_taxonomy( $key )->labels->name .'</h5>';       
            $output .= '<ul class="list-group '.$key.'">';            
            foreach ($value as $taxonomy => $term) {                 
                if($term['count']){
                    if($key == 'property_type'){
                        if(strpos($term['slug'],'proyecto') !== false){
                            $output .= '<li class="filter-options filter-default proyecto" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                        }
                        else{
                            if( strpos($term['slug'],'apartamento') === false AND 
                                strpos($term['slug'],'casa') === false AND
                                strpos($term['slug'],'local') === false AND
                                strpos($term['slug'],'oficina') === false AND
                                strpos($term['slug'],'propiedad') === false
                                ){
                                $output .= '<li class="filter-options proyecto" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                            }
                        }
                    }
                    elseif($key == 'property_status'){
                        if(strpos($term['slug'],'venta') === false AND strpos($term['slug'],'alquiler') === false){
                            $output .= '<li class="filter-options proyecto" ><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                        }
                    }
                    elseif($key == 'property_area'){
                        $option_name = '_houzez_property_area_'.$term['id'];
                        $ciudad = get_option($option_name)['parent_city']; 

                        if(strpos(get_query_var('ciudad'), $ciudad) !== false AND strpos(get_query_var('ciudad'), $ciudad) >= 0 )
                            $output .= '<li class="filter-options generico"><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';                        
                    }                                                              
                    else{
                        $output .= '<li class="filter-options generico"><a href="'.$key.'|'.$term['slug'].'">'.$term['name'].' ('.$term['count'].')</a></li>';
                    }
                }
                ?>
                <?php  
            }
            $output .= '</ul>';
        }
        return $output;
    }
}

if(!function_exists('plazamMarkupPrecios')){
    function plazamMarkupPrecios(){
        $output = '<div>';
        $output .= '<h5 class=" mt-3">Precio</h5>';
        $output .= '<ul class="nav nav-tabs mb-3" id="ex1" role="tablist">';
        $output .= '<li class="nav-item" role="presentation">';
        $output .= '<a class="nav-link active" id="tab-1" data-mdb-toggle="tab" href="#tabs-1" role="tab" aria-controls="tabs-1" aria-selected="true">Pesos</a>';
        $output .= '</li>';
        $output .= '<li class="nav-item" role="presentation">';
        $output .= '<a class="nav-link" id="tab-2" data-mdb-toggle="tab" href="#tabs-2" role="tab" aria-controls="tabs-2" aria-selected="false">Dólares</a>';
        $output .= '</li>';
        $output .= '</ul>';
        $output .= '<div class="tab-content" id="ex1-content">';
        $output .= '<div class="tab-pane fade show active" id="tabs-1" role="tabpanel" aria-labelledby="tab-1">';
        $output .= '<ul>';
        $output .= '<li><a href="#">Hasta 20.000</a></li>';
        $output .= '<li><a href="#">20.000 a 45.000</a></li>';
        $output .= '<li><a href="#">Más de 45.000</a></li>';
        $output .= '</ul>';
        $output .= '</div>';
        $output .= '<div class="tab-pane fade" id="tabs-2" role="tabpanel" aria-labelledby="tab-2">';
        $output .= '<ul>';
        $output .= '<li><a href="#">Hasta USD 500</a></li>';
        $output .= '<li><a href="#">USD500 a USD1000</a></li>';
        $output .= '<li><a href="#">Más de USD1000</a></li>';
        $output .= '</ul>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div>';
        return $output;       
    }
}



function plazamMarkupListItemPrecio(){
    $output = "";
    $i = 1;
    foreach($labels as $key => $value){
        $output .= '<li class="nav-item" role="presentation">';
        
            $output .= '<a class="nav-link';
            $i=1 ? $output .= 'active' : '';
            $output .= '" id="tab-'.$i.'" data-mdb-toggle="tab" href="#tabs-'.$i.'" role="tab" aria-controls="tabs-'.$i.'" aria-selected="true">'.$key.'</a>';            
        

        $output .= '</li>';
        $i++;        
    }
    $output .= '<li class="nav-item" role="presentation">';
$output .= '<a class="nav-link" id="tab-2" data-mdb-toggle="tab" href="#tabs-2" role="tab" aria-controls="tabs-2" aria-selected="false">Dólares</a>';
 $output .= '</li>';
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

if(!function_exists('plazamPrintBuscadorStr')){
    function plazamPrintBuscadorStr(){
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
    
            if ( strpos($item->post_title,'ropiedades en Venta') !== false AND strpos($item->post_title,'ropiedades en Venta') >= 0)
                $item->url = add_query_arg( 'operacion', 'venta', $item->url );
            elseif(strpos($item->post_title,'ropiedades en Alquiler') !== false AND strpos($item->post_title,'ropiedades en Alquiler') >= 0)
                $item->url = add_query_arg( 'operacion', 'alquiler', $item->url );
            elseif(strpos($item->post_title,'royectos en Montevideo') !== false AND strpos($item->post_title,'royectos en Montevideo') >= 0)
                $item->url = add_query_arg( 'ciudad', 'proyectos-montevideo', $item->url );
            elseif(strpos($item->post_title,'royectos en Punta') !== false AND strpos($item->post_title,'royectos en Punta') >= 0)
                $item->url = add_query_arg( 'ciudad', 'proyectos-punta-del-este', $item->url );
            elseif(strpos($item->post_title,'Vivienda Social') !== false AND strpos($item->post_title,'Vivienda Social') >= 0)
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
}
add_filter( 'query_vars', 'plazam_register_query_vars' );


if(!function_exists('plazamCreateTaxonomyArr')){
    function plazamCreateTaxonomyArr($tags,$isDefault){
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

if(!function_exists('plazamLoadTaxQueryArr')){
    function plazamLoadTaxQueryArr($taxo_arr, $is_project){
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


if(!function_exists('plazamLoadMetaQueryArr')){
    function plazamLoadMetaQueryArr(){
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


if(!function_exists('plazamGetCityTag')){
    function plazamGetCityTag(){
        global $archiveProjectType, $montevideo, $punta_del_este;
        $retorno = "";
        $archive_type = plazamGetArchiveType();
        if(strpos($archive_type,$archiveProjectType) !== false AND strpos($archiveProjectType,$archive_type) >= 0){
            if(strpos(get_query_var('ciudad'),$montevideo) !== false AND strpos(get_query_var('ciudad'),$montevideo) >= 0)
                $retorno = 'ciudad-de-'.$montevideo;
            else
                $retorno = 'ciudad-de-'.$punta_del_este;
        }
        return $retorno;
    }    
}

if(!function_exists('plazamSetDefaultTags')){
    function plazamSetDefaultTags(){
        global $queryStrVenta;
        $status = "";

        if(strpos(plazamGetArchiveType(),'proyecto') === false){
            if(plazamIsVenta())
                $status = 'listado-de-propiedades-en-uruguay-en-venta';
            else
                $status = 'listado-de-propiedades-en-uruguay-en-alquiler';
        }
        return array(
            'property_type' => (strpos(plazamGetArchiveType(),'proyecto') !== false AND strpos(plazamGetArchiveType(),'proyecto') >= 0) ? 'proyecto' : 'propiedad',
            'property_city' => plazamGetCityTag(),
            'property_status' => (strpos(plazamGetArchiveType(),'proyecto') !== false AND strpos(plazamGetArchiveType(),'proyecto') >= 0) ? '' : $status
        ); 
    }
}

if(!function_exists('plazamIsVenta')){
    function plazamIsVenta(){
        global $queryStrVenta;        
        return strpos(get_query_var('operacion'),$queryStrVenta) !== false AND strpos(get_query_var('operacion'),$queryStrVenta) >= 0;
    }

}

if(!function_exists('plazamDefaultQuery')){
    function plazamDefaultQuery($is_featured){
        $archiveType = plazamGetArchiveType();
        $default_tags = plazamSetDefaultTags();
        $args = plazam_template_args_query($default_tags, $archiveType, $is_featured);
        $query = new WP_Query( $args );
        return $query;
    }    
}


if(!function_exists('plazamSortFeatured')){
    function plazamSortFeatured(){
        $args = array(
            'post_type'     => array('property'),  
            'post_status'   => array('publish'),
            'posts_per_page' => -1,
            'meta_key'      => 'fave_featured',
            'meta_value'=>'1',
            'meta_compare' => 'LIKE',
            'meta_query'    => array(
                'relation'      => 'AND',
                'query_one'     => array(
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





