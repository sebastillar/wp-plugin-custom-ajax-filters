<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class Filter{
    private static $propertyTaxonomies;

    public static function init() {
        //$this->propertyTaxonomies = get_taxonomies();        
        self::$propertyTaxonomies = [
            'property_type' => array(),
            'property_status' => array(),
            'property_feature' => array(),
            'property_label' => array(),
            'property_city' => array(),
            'property_area' => array()            
        ];
        foreach(self::$propertyTaxonomies as $key => $value){
            self::setTerms($key,get_terms($key, array('hide_empty' => false)));
        }
    }

    public static function getTaxonomies() {
        return self::$propertyTaxonomies;
    }

    private static function setTerms($keyTaxonomy, $terms){
        foreach($terms as $term){
            $termArr = [
                'name' => $term->name,
                'slug' => $term->slug,
                'count'=> $term->count               
            ];
            array_push(self::$propertyTaxonomies[$keyTaxonomy],$termArr);
        }
    }
    
    
}

