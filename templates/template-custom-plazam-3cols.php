<?php

/**
 * Template Name: PlazaMayor Template
* Description: Custom template for PlazaMayor Real States.
 */
get_header();

global $post, $listings_tabs, $total_listing_found;

$is_sticky = '';
$sticky_sidebar = houzez_option('sticky_sidebar');

if( $sticky_sidebar['property_listings'] != 0 ) { 
    $is_sticky = 'houzez_sticky'; 
}


$page_content_position = houzez_get_listing_data('listing_page_content_area');

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
?>
<section class="listing-wrap listing-v1">
    <div class="container">
        
        <div class="page-title-wrap">

            <?php get_template_part('template-parts/listing/listing-page-title'); ?>  

        </div><!-- page-title-wrap -->

        <div class="row">
            <div class="col-lg-2 col-md-2 bt-sidebar-wrap <?php echo esc_attr($is_sticky); ?>">
                <aside id="sidebar-plazam" class="sidebar-wrap">
                    <?php
                    $filters = Filter::getTaxonomies();  
                    $archiveType = getArchiveType();
                    $titulo = $archiveType == 'proyectos' ? 'Filtrar proyectos' : 'Filtrar inmuebles';
                    ?>
                    <div class="advanced-search-module" data-type="<?php echo $archiveType ?>">
                        <div class="advanced-search-v1">
                            <div class="row">
                                <div class="col-md-12 col-6 d-none d-sm-block">;                            
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
                    
                </aside>

            </div><!-- bt-sidebar-wrap -->        
            <div class="col-lg-8 col-md-8"> 
                <?php
                if( $archiveType == $archiveProjectType){
                    echo printBuscadorStr();
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
                        <?php get_template_part('template-parts/listing/listing-tabs'); ?>    
                        <?php get_template_part('template-parts/listing/listing-sort-by'); ?>   
                    </div><!-- d-flex -->
                </div><!-- listing-tools-wrap -->   

                <div class="listing-view grid-view card-deck grid-view-3-cols">
                    <?php
                    if ( $listings_query->have_posts() ) :
                        while ( $listings_query->have_posts() ) : $listings_query->the_post();

                            get_template_part('template-parts/listing/item', 'v1');

                        endwhile;
                    else:
                        get_template_part('template-parts/listing/item', 'none');
                    endif;
                    wp_reset_postdata();
                    ?>   
                </div><!-- listing-view -->

                <?php houzez_pagination( $listings_query->max_num_pages, $range = 2 ); ?>
                
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
