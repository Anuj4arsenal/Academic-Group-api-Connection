<?php

class AG_AdminPages extends AG_utility{

    public function __construct(){
            //all setting/option fields
            add_action( 'admin_init', array($this,'register_ag_wp_settings' ));
            //setup admin pages
            add_action('admin_menu', array($this,'_academic_group_menu_pages'));
            //define new user roles according to Academic Group
            add_action('admin_init', array($this,'ag_new_role'));
            //create new taxonomy for product(ag product type : courses,publications)
            add_action('init', array($this,'add_categories_to_product_cat_taxonomy') );

            //create new taxonomy for course type
            add_action( 'init', array($this,'custom_taxonomy_AG_Course_type') );

            //login hooks
            //set cookies of admin('academic_admin','hackadtf123') in usermeta data if the logged in user is admin
            add_action( 'wp_authenticate', array($this,'ag_login_extend'),30,2);

            add_action( 'init', array($this,'set_users_active_status'));
            add_action( 'admin_init', array($this,'set_users_active_status'));

            //create the directory to upload publication(ag api images)
            add_action('admin_init', array($this,'create_new_directory'));

            /*login redirect*/
            add_filter('woocommerce_login_redirect', array($this,'wc_login_redirect'));
            /*logout redirect*/
            add_action('wp_logout',array($this,'auto_redirect_after_logout'));

           add_filter( 'woocommerce_min_password_strength', array($this, 'reduce_min_strength_password_requirement' ));

           add_filter( 'password_hint', array($this, 'smarter_password_hint' ));




    }

    public function set_users_active_status(){
        //for init woocommerce cart session
        if(!is_admin()) {
            WC()->session->set_customer_session_cookie(true);
        }

        $user = wp_get_current_user(); //Get the current user's data
        $user_id = $user->ID;
         if ($user_id != 0){
            set_transient('users_status_'.$user_id, true, 900); //Set this transient to expire 15 (900)minutes after it is created.
        }
         $this->delete_not_active_user();

         //for logout handle
        if(isset($_GET['ag_logout'])){
            ?>
            <script type="text/javascript">
            sessionStorage.removeItem('ag_selected_student');
            sessionStorage.removeItem('ag_local_students');
            </script>
            <?php
        }
    }
    public function delete_not_active_user(){
      $users = get_users();
      foreach ($users as $user){
          $user_id = $user->ID;
          $is_logged_in = get_transient('users_status_'.$user_id);
          if(!$is_logged_in){
              wp_delete_user($user_id,1);
          }
      }
    }

    public function ag_login_extend($username,$password){
        $user_data = wp_authenticate($username,$password);
        if(!empty($username) && !empty($password)){
            $con_ag = new AG_API_Connector();
            $user_id = 0;
            $userObj = new Users_module();
            if(!isset($user_data->ID)) {
                $cookie = $con_ag->login($username, $password);
                if (!empty($cookie)) {
                    $is_ag_admin = $userObj->is_ag_admin($cookie);
                    $user_details = $userObj->get_user_details_from_ag($username);
                    $organization_details = $userObj->get_organization_details_from_ag($username);

                    if($is_ag_admin){
                        $user_id = $userObj->create_ag_admin($username,$password,$cookie);
                    }
                    else if (isset($organization_details->results[0])) {
                        $user_id = $userObj->handle_ag_login($username, $password, $cookie, $organization_details, 'org');
                    } else if (isset($user_details->results[0])) {
                        $user_id = $userObj->handle_ag_login($username, $password, $cookie, $user_details, 'user');
                    }
                }
            }
            else{
                $cookie = $con_ag->login($username, $password);
                update_user_meta($user_data->ID,'ag_cookie',$cookie);
            }
            if($user_id != 0) {
                set_transient('users_status_'.$user_id, true, 900); //Set this transient to expire 15 (900)minutes after it is created.
                $this->reassign_my_order($user_id);
            }
        }
    }

    public function reassign_my_order($user_id){
        $user = get_user_by('ID',$user_id);
        $email = $user->data->user_email;
        $args = array(
            'post_type' => 'shop_order',
            'meta_key' => '_billing_email',
            'meta_value' => $email,
            'post_status' => 'any',
            'numberposts' => -1
        );
        $orders = get_posts($args);
        foreach ($orders as $order) {
            update_post_meta($order->ID,'_customer_user',$user_id);
        }

        //for downloadable products
        global $wpdb;
        $query = "UPDATE wpri_woocommerce_downloadable_product_permissions SET user_id = ".$user_id." WHERE user_email = '".$email."'";
        $wpdb->get_results($query);

    }

    public function register_ag_wp_settings(){
            register_setting( 'ag-api-settings-group', 'api_url' );
            register_setting( 'ag-api-settings-group', 'api_environment' );
            register_setting( 'ag-api-settings-group', 'admin_username' );
            register_setting( 'ag-api-settings-group', 'admin_password' );
            register_setting( 'ag-api-settings-group', 'ag_email_address' );
            register_setting( 'ag-api-settings-group', 'ag_clearout_duration' );

            register_setting( 'ag-api-settings-group-csv_uploads', 'dimensions_weight_csv_file' );

            register_setting( 'ag-api-settings-group_publication_thumbnail', 'ag_defualt_publication_thumbnail' );
            register_setting( 'ag-api-settings-group_course_thumbnail', 'ag_defualt_course_thumbnail' );
            register_setting( 'ag-api-settings-group_exampaper_thumbnail', 'ag_defualt_exampaper_thumbnail' );
    }

    public function _academic_group_menu_pages() {
            add_menu_page( 'AG API Connection', 'AG API Connection', 'edit_posts', 'ag-api-connection');
            add_submenu_page( "ag-api-connection", "Dashboard", "Dashoard", "manage_options", "ag-api-connection", array($this,"ag_api_connection_dashboard_template"));
            add_submenu_page( "ag-api-connection", "Settings", "Settings", "manage_options", "ag-settings", array($this,"ag_api_connection_settings_template"));
            add_submenu_page( "ag-api-connection", "Logs", "Logs", "manage_options", "ag-logs", array($this,"ag_api_connection_logs_template"));
            add_submenu_page( "ag-api-connection", "Csv uploads", "Csv uploads", "manage_options", "csv-uploads-for-weight-dimension", array($this,"ag_csv_upload_for_weight_dimension_sync"));
    }

    public function ag_api_connection_dashboard_template(){
            if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/views/admin/dashboard.php')){
                require_once AG_PLUGIN_BASE_PATH . '/inc/views/admin/dashboard.php';
            }
    }

    public function ag_api_connection_logs_template(){
        $action = isset($_GET['action'])?trim($_GET['action']):"";
            if($action == 'log-details'){
                $log_id = isset($_GET['log_id'])?intval($_GET['log_id']):"";
                require_once AG_PLUGIN_BASE_PATH . '/inc/views/admin/log-details.php';
            }  else {
                if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/views/admin/logs.php')){
                    require_once AG_PLUGIN_BASE_PATH . '/inc/views/admin/logs.php';
                }
        }
    }

    public function ag_csv_upload_for_weight_dimension_sync(){
        if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/views/admin/csv-uploads.php')){
                require_once AG_PLUGIN_BASE_PATH . '/inc/views/admin/csv-uploads.php';
            }
    }

    public function ag_api_connection_settings_template(){
            if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/views/admin/settings.php')){
                require_once AG_PLUGIN_BASE_PATH . '/inc/views/admin/settings.php';
            }
    }

    public function ag_new_role() {
            add_role(
                'AG_PARENT',
                'AG Parent',
                array(
                    'read'         => true,
                    'delete_posts' => false
                )
            );
            add_role(
                'AG_TEACHER',
                'AG Teacher',
                array(
                    'read'         => true,
                    'delete_posts' => false
                )
            );
            
            add_role(
                'AG_STUDENT',
                'AG Student',
                array(
                    'read'         => true,
                    'delete_posts' => false
                )
            );
            add_role(
                'AG_BOOKSHOP',
                'AG Book Shop Keeper',
                array(
                    'read'         => true,
                    'delete_posts' => false
                )
            );
            add_role(
                'AG_PUBLIC_LIBRARY',
                'AG Librarian',
                array(
                    'read'         => true,
                    'delete_posts' => false
                )
            );

    }

        public function add_categories_to_product_cat_taxonomy(){
            wp_insert_term(
                'Course',
                'product_cat',
                array(
                  'description' => 'Course Type',
                  'slug'        => 'ag-course-type'
                )
            );
            wp_insert_term(
                'Publication',
                'product_cat',
                array(
                  'description' => 'Publication Type',
                  'slug'        => 'ag-publication-type'
                )
            );
        }

        public function create_new_directory(){
            if (!file_exists(wp_upload_dir()["basedir"].'/ag_uploads/img')) {
                mkdir(wp_upload_dir()["basedir"].'/ag_uploads/img/', 0777, true);
            }
        }

        public function wc_login_redirect( $redirect_to ) {
            $redirect_page_id = url_to_postid( $redirect_to );
            $checkout_page_id = wc_get_page_id( 'checkout' );

            //redirect to checkout page if login from checkout page
            if( $redirect_page_id == $checkout_page_id ) {
                $redirect_to = wc_get_checkout_url();
            }
            //redirect to home page if login from any other pages
            else{
                $redirect_to = get_home_url();
            }

            return $redirect_to;
        }

        // First, change the required password strength
        function reduce_min_strength_password_requirement( $strength ) {
            // 8 => Strong (default) | 6 => Medium | 3 => Weak | 0 => Very Weak (anything).
            return 8;
        }

        // Second, change the wording of the password hint.

        function smarter_password_hint ( $hint ) {
            $hint = 'Hint: The password should be at least eight characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ & )..';
            return $hint;
        }

         
       


        public function custom_taxonomy_AG_Course_type(){
             $labels = array(
                'name'                       => 'Course Type',
                'singular_name'              => 'Course Type',
                'menu_name'                  => 'Course Type',
                // 'all_items'                  => 'All Items',
                // 'parent_item'                => 'Parent Item',
                // 'parent_item_colon'          => 'Parent Item:',
                // 'new_item_name'              => 'New Item Name',
                // 'add_new_item'               => 'Add New Item',
                // 'edit_item'                  => 'Edit Item',
                // 'update_item'                => 'Update Item',
                // 'separate_items_with_commas' => 'Separate Item with commas',
                // 'search_items'               => 'Search Items',
                // 'add_or_remove_items'        => 'Add or remove Items',
                // 'choose_from_most_used'      => 'Choose from the most used Items',
            );
            $args = array(
                'labels'                     => $labels,
                'hierarchical'               => true,
                'public'                     => true,
                'show_ui'                    => true,
                'show_admin_column'          => true,
                // 'show_in_nav_menus'          => true,
                'show_tagcloud'              => true,
            );
            register_taxonomy( 'course_type', 'product', $args );
            register_taxonomy_for_object_type( 'course_type', 'product' );
            // wp_insert_term(
            //     'Master',
            //     'course_type',
            //     array(
            //       'description' => 'master',
            //       'slug'        => 'master'
            //     )
            // );
        }


        public function auto_redirect_after_logout(){
        ?>

            <?php
            wp_redirect( home_url().'?ag_logout=true');
            exit();
        }

}

$AG_AdminPages =  new AG_AdminPages();
