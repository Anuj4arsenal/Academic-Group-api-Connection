<?php

class ag_main_integration{
        public function __construct(){
            $this->_load_dependencies();
            $this->_load_all_modules();
            $this->ag_all_shortcodes();
            $this->ag_define_all_hooks();
            // add_action('wp_enqueue_scripts', 'change_current_selected_form_script_ag');
            // 
        }

        private function _load_dependencies(){
            if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/classes/lib/ag-utility-functions.php')){
                require_once AG_PLUGIN_BASE_PATH . '/inc/classes/lib/ag-utility-functions.php';
            }

            if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/classes/lib/ag-api-connector.php')){
                require_once AG_PLUGIN_BASE_PATH . '/inc/classes/lib/ag-api-connector.php';
            }
            require_once(ABSPATH.'wp-admin/includes/user.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        private function _load_all_modules(){
                //check if publication module file exists
                $publications_module_exists = file_exists(AG_PLUGIN_BASE_PATH . '/inc/classes/class-publications-module.php');
                //check if course moduel file exists
                $programs_module_exists = file_exists(AG_PLUGIN_BASE_PATH . '/inc/classes/class-programs-module.php');
                if($publications_module_exists){
                    require_once AG_PLUGIN_BASE_PATH . '/inc/classes/class-publications-module.php';
                }
                if($programs_module_exists){
                    require_once AG_PLUGIN_BASE_PATH . '/inc/classes/class-programs-module.php';
                }
                //load cart module only if publication and course module exists
                if($publications_module_exists && $programs_module_exists){
                    //Synchorization module to sync publications,courses,users from the AG portal
                    if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/classes/class-ag-sync-module.php')){
                        require_once AG_PLUGIN_BASE_PATH . '/inc/classes/class-ag-sync-module.php';
                    }
                }

                if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/classes/class-users-module.php')){
                    require_once AG_PLUGIN_BASE_PATH . '/inc/classes/class-users-module.php';
                }
            if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/classes/addons/custom-extra-fields.php')){
                require_once AG_PLUGIN_BASE_PATH . '/inc/classes/addons/custom-extra-fields.php';
            }
        }

        private function ag_all_shortcodes(){
                //all templates for frontend
                if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/shortcodes/ag-template-shortcodes.php')){
                    require_once AG_PLUGIN_BASE_PATH . '/inc/shortcodes/ag-template-shortcodes.php';
                }
        }

        private function ag_define_all_hooks(){

            
                //hooks front view/templates
                //add css/js
                if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/hooks/ag-front-hooks.php')){
                    require_once AG_PLUGIN_BASE_PATH . '/inc/hooks/ag-front-hooks.php';
                    $front_hooks = new front_hooks();
                }
                //hooks to handle all ajax requests
                if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/hooks/ag-ajax-response.php')){
                    require_once AG_PLUGIN_BASE_PATH . '/inc/hooks/ag-ajax-response.php';
                    $AG_Ajax_responses = new AG_Ajax_responses();
                }

                //hooks to handle/modify admin functionalities
                //setup admin pages for the plugin
                //define new user roles for ag
                if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/hooks/ag-admin-hooks.php')){
                    require_once AG_PLUGIN_BASE_PATH . '/inc/hooks/ag-admin-hooks.php';
                }

                //hooks before the wc order is placed
                //hooks after the wc order is placed
                //hooks before cart calculation
                if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/hooks/ag-wocommerce-hook.php')){
                    require_once AG_PLUGIN_BASE_PATH . '/inc/hooks/ag-wocommerce-hook.php';
                }

                if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/hooks/academia-core.php')){
                    require_once AG_PLUGIN_BASE_PATH . '/inc/hooks/academia-core.php';
                }
                
                // if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/hooks/ag-checkout-snippets.php')){
                //     require_once AG_PLUGIN_BASE_PATH . '/inc/hooks/ag-checkout-snippets.php';
                // }

                // if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/hooks/checkout-custom-template/helper-function.php')){
                //     require_once AG_PLUGIN_BASE_PATH . '/inc/hooks/checkout-custom-template/helper-function.php';
                // }

        }
}
