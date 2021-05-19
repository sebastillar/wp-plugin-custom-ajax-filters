<?php

if( !function_exists('plazam_ajax_filter_properties') ) {
    function plazam_ajax_filter_properties() {
        $tags = json_decode( stripslashes( $_POST['tags'] ), true );
        $taxo_arr = array();
        $tax_query = array(
            'relation' => 'AND', 
        );
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
    
        foreach($taxo_arr as $tax => $val){
            array_push($tax_query,[
                'taxonomy' => $tax,
                'field' => 'slug',
                'terms' => $val,
                'operator' => 'IN' 
            ]);
        }
    
        $args = array(
            'post_type' => array('property'),  
            'post_status' => array('publish'),
            'posts_per_page' => -1,
            'tax_query' => $tax_query        
        );    
    
        $query = new WP_Query( $args );
    
        ob_start();
    
        if( ! $query->have_posts() ) { 
            get_template_part('template-parts/listing/item-none');
        }
        else {
            while ( $query->have_posts() ) { 
                $query->the_post();
                get_template_part('template-parts/listing/item-v5');
            }
            wp_reset_postdata();
            //wp_send_json_success(ob_get_clean());
            wp_send_json([ 'query' => ob_get_clean() ]);    
    
        }
    
        //wp_send_json([ 'query' => $query->posts ]);    
        wp_die();
    }   
}
add_action( 'wp_ajax_ajax_filter', 'plazam_ajax_filter_properties' ); 
add_action( 'wp_ajax_nopriv_ajax_filter', 'plazam_ajax_filter_properties' );

if( !function_exists('isProject') ) {
    function isProject(){
        global $post;
        return strpos(get_post_field( 'post_name', $post->post_parent ),'proyectos');
    }
}

if( !function_exists('isVenta') ) {
    function isVenta(){
        global $post;
        return strpos(get_post_field( 'post_name', $post->post_parent ),'venta');
    }
}

if( !function_exists('getDefaultFilterName') ) {
    function getDefaultFilterName(){
        $retorno = "alquiler";
        if(isProject())
            $retorno = "project";
        else{
            if(isVenta())
                $retorno = "venta";        
        }
        return $retorno;
    }
}
