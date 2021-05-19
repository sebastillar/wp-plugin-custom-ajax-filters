<?php
class Plazam {
    /**
     * Plugin instance.
     *
     * @var Plazam
     */
    protected static $instance;   
    
    /**
     * Plugin version.
     *
     * @var string
     */
    protected static $version = '1.0.0';
    

    /**
     * Constructor.
     */
    protected function __construct()
    {   
        add_action( 'init', array( __CLASS__, 'init' ) );
        $this->actions();
        $this->plazam_inc_files();
        $this->filters();
        self::enqueue_scripts();

        //add_action( 'current_screen', array( $this, 'conditional_includes' ) );
    }    

    /**
     * Return plugin version.
     *
     * @return string
     */
    public static function getVersion() {
        return static::$version;
    }

    /**
     * Return plugin instance.
     *
     * @return Plazam
     */
    protected static function getInstance() {
        return is_null( static::$instance ) ? new Plazam() : static::$instance;
    }
    
    /**
     * Initialize plugin.
     *
     * @return void
     */
    public static function run() {
        self::plazam_class_loader();
        self::plazam_function_loader();        
        static::$instance = static::getInstance();
    }    

    /**
     * include files
     *
     * @since 1.0
     *
    */
    function plazam_inc_files() {
        require_once(PLAZAM_PLUGIN_PATH . '/shortcodes/section-filters.php');
    }

    /**
     * Plugin actions.
     *
     * @return void
     */
    public function actions() {

    }

    /**
     * Add filters to the WordPress functionality.
     *
     * @return void
     */
    public function filters() {
        
    }    

    /**
     * Initialize classes
     *
     * @return void
     */
    public function init() {
        $filtro = Filter::init();
    }    

    public static function enqueue_scripts() {
        $js_path = 'assets/frontend/js/';
        $css_path = 'assets/frontend/css/';      

        wp_enqueue_style('plazam-frontend-style', PLAZAM_PLUGIN_URL . $css_path . 'style.css', array(), '1.0.0', 'all');
        wp_enqueue_script( 'plazam-frontend-js',  PLAZAM_PLUGIN_URL . $js_path . 'plazam.js', array( 'jquery' ), '1.0', true );

        wp_register_script('ajax-filter',PLAZAM_PLUGIN_URL . $js_path . 'ajax-filter.js',array('jquery'));
        wp_enqueue_script( 'ajax-filter');        

        wp_localize_script( 'ajax-filter', 'ajaxfilter', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'query_vars' => json_encode( $wp_query->query ),            
            'loadingmessage' => __('Sending user info, please wait...')
        ));
    }    


    /**
     * Load plugin files.
     *
     * @return void
     */
    public static function plazam_class_loader()
    {
        $files = apply_filters( 'plazam_class_loader', array(
            PLAZAM_PLUGIN_PATH . '/classes/class-filter.php',
        ) );

        foreach ( $files as $file ) {
            if ( file_exists( $file ) ) {
                include $file;
            }
        }
    }   
    

    public static function plazam_function_loader() {
        $files = apply_filters( 'plazam_function_loader', array(
            PLAZAM_PLUGIN_PATH . '/functions/functions.php',
            
        ) );

        foreach ( $files as $file ) {
            if ( file_exists( $file ) ) {
                require_once $file;
            }
        }
    }    
}
