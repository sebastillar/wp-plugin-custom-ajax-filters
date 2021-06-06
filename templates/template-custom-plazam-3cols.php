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
$archiveType = plazam_get_archive_type();
$titulo = $archiveType == 'proyectos' ? 'Filtrar proyectos' : 'Filtrar inmuebles';
/*-----*/


$is_sticky = '';
$sticky_sidebar = houzez_option('sticky_sidebar');

if( $sticky_sidebar['property_listings'] != 0 ) { 
    $is_sticky = 'houzez_sticky'; 
}


$page_content_position = houzez_get_listing_data('listing_page_content_area');

$listing_args = array();

$plazam_query ='';
if(function_exists('plazam_default_query')){
    $plazam_query = plazam_default_query();    
}


/*
$listing_args = array(
    'post_type' => 'property',
    'post_status' => 'publish'
);
$listing_args = apply_filters( 'houzez20_property_filter', $listing_args );
$listing_args = houzez_prop_sort ( $listing_args ); 

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
                                        
                                        if(function_exists('plazam_markup_default_filters')){
                                            echo plazam_markup_default_filters($archiveType, $key, $value);
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

                                <div id="precio">
                                    <h5 class=" mt-3"><?php echo __('Precio','houzez') ?></h5>
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                                      <li class="nav-item alquiler" role="presentation">
                                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#tab-pesos" type="button" role="tab" aria-controls="home" aria-selected="true"><?php echo __('Pesos','houzez') ?></button>
                                      </li>
                                      <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#tab-dolares" type="button" role="tab" aria-controls="profile" aria-selected="false"><?php echo __('Dólares','houzez') ?></button>
                                      </li>
                                    </ul>

                                    <!-- Tab panes -->
                                    <div class="tab-content">
                                        <div class="tab-pane active fade" id="tab-pesos" role="tabpanel" aria-labelledby="home-tab">
                                            <ul class="alquiler">
                                                <li class="filter-options"><a class="pesos" href="#" data-price="0-20000" data-currency="$U"> <?php echo __('Hasta $20.000','houzez') ?></a></li>
                                                <li class="filter-options"><a class="pesos" href="#" data-price="20001-45000" data-currency="$U"> <?php echo __('$20.001 a $45.000','houzez') ?></a></li>
                                                <li class="filter-options"><a class="pesos" href="#" data-price="45001-999999" data-currency="$U"><?php echo __('Más de $45.000','houzez') ?></a></li>                                             
                                            </ul>                                          
                                        </div>
                                        <div class="tab-pane fade" id="tab-dolares" role="tabpanel" aria-labelledby="profile-tab">
                                            <ul class="alquiler">
                                                <li class="filter-options"><a class="dolares" href="#" data-price="0-500" data-currency="USD"><?php echo __('Hasta USD500','houzez') ?></a></li>
                                                <li class="filter-options"><a class="dolares" href="#" data-price="501-1000" data-currency="USD"><?php echo __('USD501 a USD1.000','houzez') ?></a></li>
                                                <li class="filter-options"><a class="dolares" href="#" data-price="1001-999999" data-currency="USD"><?php echo __('Más de USD1.000','houzez') ?></a></li>                                             
                                            </ul>
                                            <ul class="venta">
                                                <li class="filter-options"><a class="dolares" href="#" data-price="0-100000" data-currency="USD"><?php echo __('Hasta USD100.000','houzez') ?></a></li>
                                                <li class="filter-options"><a class="dolares" href="#" data-price="100000-250000" data-currency="USD"><?php echo __('USD100.001  a USD250.000','houzez') ?></a></li>
                                                <li class="filter-options"><a class="dolares" href="#" data-price="250000-9999999" data-currency="USD"><?php echo __('Más de USD250.000','houzez') ?></a></li>                                             
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
                
                echo plazam_print_buscador_str($archiveType);                

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
                        <?php //get_template_part('template-parts/listing/listing-sort-by'); ?>   
                    </div><!-- d-flex -->
                </div><!-- listing-tools-wrap -->   

                <div class="listing-view grid-view card-deck grid-view-3-cols">
                    <div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>
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

                <?php 
                    //houzez_pagination( $plazam_query->max_num_pages, $range = 2 );
                    echo plazam_pagination($plazam_query->max_num_pages);
                ?>
                
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
