<?php

/**
 * Template Name: PlazaMayor Template
* Description: Custom template for PlazaMayor Real States.
 */
get_header();

global $post, $listings_tabs, $total_listing_found;

/*
Plazam Custom Vars
*/
/*-----*/
$filters = Filter::getTaxonomies();  
$archiveType = plazamGetArchiveType();
$titulo = $archiveType == 'proyectos' ? 'Filtrar proyectos' : 'Filtrar inmuebles';
/*-----*/

$is_sticky = '';
$sticky_sidebar = houzez_option('sticky_sidebar');

if( $sticky_sidebar['property_listings'] != 0 ) { 
    $is_sticky = 'houzez_sticky'; 
}


$page_content_position = houzez_get_listing_data('listing_page_content_area');

/*
$listing_args = array(
    'post_type' => 'property',
    'post_status' => 'publish'
);
$listing_args = apply_filters( 'houzez20_property_filter', $listing_args );
$listing_args = houzez_prop_sort ( $listing_args );
*/



$listing_args = array();

/*
echo "<pre>";
print_r($args_pesos);
echo "</pre>";

echo "<pre>";
print_r($args_dolares);
echo "</pre>";

$args_pesos = plazam_template_args_query($default_tags, $archiveType,$pesos);
$args_dolares = plazam_template_args_query($default_tags, $archiveType,$dolares);
$my_query = new WP_Query( $args_pesos );
$related_ids = array_map( function( $v ) {return $v->ID;}, $my_query->posts );
$args_dolares['posts_per_page'] = 10 - $my_query->post_count;
$args_dolares['post__not_in']   = array_merge( array( $post->ID ), $related_ids ));
$more_query = new WP_Query( $args_dolares );
$my_query->posts = array_merge( $my_query->posts, $more_query->posts );
$total_listing_found = count( $my_query->posts );

*/

$plazam_query ='';
if(function_exists('plazamDefaultQuery')){
    $plazam_query = plazamDefaultQuery();    
}


/*

$listings_query = new WP_Query( $listing_args );
$total_listing_found = $listings_query->found_posts;

$listings_tabs = get_post_meta( $post->ID, 'fave_listings_tabs', true );
$mb = '';
if( $listings_tabs != 'enable' ) {
    $mb = 'mb-2';
}
*/
$mb = '';
if(get_post_meta( $post->ID, 'fave_listings_tabs', true ) != 'enable'){
    $mb = 'mb-2';
}



?>
<section class="listing-wrap listing-v1">
    <div class="container">
        
        <div class="page-title-wrap">

            <?php get_template_part('template-parts/listing/listing-page-title'); ?>  

        </div><!-- page-title-wrap -->

        <div class="row">
            <div class="col-lg-2 col-md-2 bt-sidebar-wrap <?php echo esc_attr($is_sticky); ?>">
                <aside id="sidebar-plazam" class="sidebar-wrap">
                    <div class="advanced-search-module" data-type="<?php echo $archiveType ?>">
                        <div class="advanced-search-v1">
                            <div class="row">
                                <div class="col-md-12 col-6 d-none d-sm-block">                            
                                    <div class="row">
                                        <div class="col-sm-12">                     
                                            <?php if(!empty($titulo)) { ?>                                                
                                                <div class="advanced-search-module-title">
                                                    <?php echo esc_attr($titulo); ?>
                                                    <!--<i class="houzez-icon icon-search mr-2"></i> <?php echo esc_attr($titulo); ?>-->
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
                                        
                                        if(function_exists('plazamMarkupDefaultFilters')){
                                            echo plazamMarkupDefaultFilters($archiveType, $key, $value);
                                        }

                                        echo '</div>';
                                        echo '</div>';                                                                                          
                                    }

                                }
                                /*
                                if(function_exists('plazamMarkupPrecios')){
                                    echo plazamMarkupPrecios();
                                }
                                */
                                ?>

                                <div>
                                    <h5 class=" mt-3">Precio</h5>
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                                      <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#tab-pesos" type="button" role="tab" aria-controls="home" aria-selected="true">Pesos</button>
                                      </li>
                                      <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#tab-dolares" type="button" role="tab" aria-controls="profile" aria-selected="false">Dólares</button>
                                      </li>
                                    </ul>

                                    <!-- Tab panes -->
                                    <div class="tab-content">
                                        <div class="tab-pane active fade" id="tab-pesos" role="tabpanel" aria-labelledby="home-tab">
                                            <ul>
                                                <li><a href="#">Hasta 20.000</a></li>
                                                <li><a href="#">20.000 a 45.000</a></li>
                                                <li><a href="#">Más de 45.000</a></li>                                             
                                            </ul>                                          
                                        </div>
                                        <div class="tab-pane fade" id="tab-dolares" role="tabpanel" aria-labelledby="profile-tab">
                                            <ul>
                                                <li><a href="#">Hasta USD 500</a></li>
                                                <li><a href="#">USD500 a USD1000</a></li>
                                                <li><a href="#">Más de USD1000</a></li>                                             
                                            </ul>                                          
                                        </div>
                                    </div>

                                </div>
                                

                            </div>
                        </div>
                    </div>
                    
                </aside>

            </div><!-- bt-sidebar-wrap -->        
            <div class="col-lg-8 col-md-8"> 
                <?php
                if( $archiveType == $archiveProjectType){
                    if(function_exists('plazamPrintBuscadorStr'))
                    echo plazamPrintBuscadorStr();
                }

                if ( $page_content_position !== '1' ) {
                    if ( have_posts() ) {
                        while ( have_posts() ) {
                            the_post();
                            ?>
                            <article <?php post_class(); ?>>
                                <?php the_content(); ?>
                            </article>
                            <?php
                        }
                    } 
                }?>                      
                
                <div class="listing-tools-wrap">
                    <div class="d-flex align-items-center <?php echo esc_attr($mb); ?>">
                        <?php //get_template_part('template-parts/listing/listing-tabs'); ?>    
                        <?php get_template_part('template-parts/listing/listing-sort-by'); ?>   
                    </div><!-- d-flex -->
                </div><!-- listing-tools-wrap -->   

                <div class="listing-view grid-view card-deck grid-view-3-cols">
                    <?php                
                    //if ( $listings_query->have_posts() ) :
                    if ( $plazam_query->have_posts() ) :                    
                        while ( $plazam_query->have_posts() ) : $plazam_query->the_post();

                            get_template_part('template-parts/listing/item', 'v1');

                        endwhile;
                    else:
                        get_template_part('template-parts/listing/item', 'none');
                    endif;
                    wp_reset_postdata();
                    ?>   
                </div><!-- listing-view -->

                <?php houzez_pagination( $plazam_query->max_num_pages, $range = 2 ); ?>
                
            </div><!-- col-lg-12 col-md-12 -->
        </div><!-- row -->
    </div><!-- container -->
</section><!-- listing-wrap -->

<?php
if ('1' === $page_content_position ) {
    if ( have_posts() ) {
        while ( have_posts() ) {
            the_post();
            ?>
            <section class="content-wrap">
                <?php the_content(); ?>
            </section>
            <?php
        }
    }
}
?>

<?php get_footer(); ?>
