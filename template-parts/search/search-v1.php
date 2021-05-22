<?php
global $sticky_hidden, $sticky_data, $hidden_data;
$search_builder = houzez_search_builder();
$layout = $search_builder['enabled'];

if(empty($layout)) {
	$layout = array();
}

if(!taxonomy_exists('property_country')) {
    unset($layout['country']);
}

if(!taxonomy_exists('property_state')) {
    unset($layout['state']);
}

if(!taxonomy_exists('property_city')) {
    unset($layout['city']);
}

if(!taxonomy_exists('property_area')) {
    unset($layout['areas']);
}

unset($layout['placebo']);

if(houzez_is_radius_search() != 1) {
	unset($layout['geolocation']);
}
unset($layout['price']);
$is_geolocation = '';
if(array_key_exists('geolocation', $layout)) {
	$is_geolocation = 'advanced-search-v1-geolocation';
}
$both_keyword_location = $width_needed = false;
if(!array_key_exists('geolocation', $layout) && !array_key_exists('keyword', $layout)) {
	$both_keyword_location = true;
	$is_geolocation = 'advanced-search-v1-geolocation';

} else if(array_key_exists('geolocation', $layout) || array_key_exists('keyword', $layout)) {
	$width_needed = true;
}
?>
<section id="desktop-header-search" class="advanced-search advanced-search-nav <?php echo esc_attr($sticky_hidden); ?>" data-hidden="<?php echo esc_attr($hidden_data); ?>" data-sticky='<?php echo esc_attr( $sticky_data ); ?>'>
	<div class="<?php echo houzez_header_search_width(); ?>">
		<form class="houzez-search-form-js <?php houzez_search_filters_class(); ?>" method="get" autocomplete="off" action="<?php echo esc_url( houzez_get_search_template_link() ); ?>">

			<?php do_action('houzez_search_hidden_fields'); ?>
			
            <!-- Quito advanced-search-v1 -->
            <!-- Ver template-part en tema padre search/search-v1 -->            

		</form>
	</div><!-- container -->
</section><!-- advanced-search -->
