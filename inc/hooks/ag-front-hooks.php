<?php

class front_hooks extends AG_utility{

    public function __construct(){
        parent::__construct();
        $this->define_all_frontend_hooks();
        $this->define_all_backend_hooks();
    }

    public function define_all_backend_hooks(){
        add_action('admin_enqueue_scripts', array(&$this, 'ag_admin_enqueue_script'));
    }

    public function define_all_frontend_hooks(){
        add_action( 'wp_enqueue_scripts', array($this,'ag_front_css_js'),99);
//        add_filter( 'wp_nav_menu_items', array($this,'add_extra_item_to_nav_menu'), 10, 2 );

        //append login/logout url to the primary main menu
        add_filter( 'wp_nav_menu_items', array($this,'add_loginout_link_ag'), 10, 2 );


    }

    function add_loginout_link_ag( $items, $args ) {
        $my_account_page_url = get_permalink( get_option('woocommerce_myaccount_page_id'));
        if (is_user_logged_in() && $args->menu == 'main-menu') {
            $items .= '<li><a class="menu-link elementor-item" href="'. $my_account_page_url .'">My Account</a></li>';
            $items .= '<li><a class="menu-link elementor-item" href="'. wp_logout_url() .'">Logout</a></li>';
        }
        elseif (!is_user_logged_in() && $args->menu == 'main-menu') {
            $register_page_url = get_permalink( get_page_by_title( 'register' ) );

            $items .= '<li><a class="menu-link elementor-item" href="'. $register_page_url .'">Register</a></li>';
            $items .= '<li><a class="menu-link elementor-item" href="'. $my_account_page_url .'">Login</a></li>';
        }
        return $items;
    }


    public function ag_front_css_js(){
        wp_register_style( 'ag-style', AG_PLUGIN_BASE_URL.'inc/assets/css/ag-master.css', array(), '20130608');
        wp_enqueue_style( 'ag-style' );

        wp_register_style( 'montserrat-font', 'https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;1,100;1,200;1,300;1,400;1,500&display=swap', array(), '20130608');
        wp_enqueue_style( 'montserrat-font' );

        wp_register_style( 'bootstrap', AG_PLUGIN_BASE_URL.'inc/assets/css/bootstrap.css', array(), '20130608');
        wp_enqueue_style( 'bootstrap' );

        wp_enqueue_script('ag-javascript',AG_PLUGIN_BASE_URL.'inc/assets/js/Request.js');
        if(is_page( 'checkout' )) {
            //wp_enqueue_script('ag-jquery','https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
            wp_enqueue_script('ag-checkout-javascript', AG_PLUGIN_BASE_URL . 'inc/assets/js/checkout-request.js');
        }
        wp_enqueue_script('ag-ajx','https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js');
        wp_enqueue_script('ag-bootstrap',AG_PLUGIN_BASE_URL.'inc/assets/js/bootstrap.js');
    }

    public function ag_admin_enqueue_script(){
        wp_enqueue_script( 'jquery');
        wp_enqueue_script('ag-datatable',AG_PLUGIN_BASE_URL.'inc/assets/js/jquery.dataTables.min.js');
        wp_register_style( 'datatable-min-css',  AG_PLUGIN_BASE_URL . "assets/css/jquery.dataTables.min.css");


    }

}

?>