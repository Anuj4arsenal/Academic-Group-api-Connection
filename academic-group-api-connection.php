<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Academic Group API connection
 * Plugin URI:        https://academicgroup.com.au/
 * Description:       Academic Group web Page Integration with API Connection
 * Version:           1.0.0
 * Author:            PWSa
 * Author URI:        https://professionalwebsolutions.com.au
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       academic-group
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
define( 'AG_PLUGIN', '2.0.0' );
define( 'AG_PLUGIN_BASE_PATH' , plugin_dir_path( __FILE__ ) );
define( 'AG_PLUGIN_BASE_URL' , plugin_dir_url( __FILE__ ) );

//for including plugin's front template views
function get_template_part_ag($slug, $name = null) {

  do_action("ccm_get_template_part_{$slug}", $slug, $name);

  $templates = array();
  if (isset($name))
      $templates[] = "{$slug}-{$name}.php";

  $templates[] = "{$slug}.php";
  get_template_path_ag($templates, true, false);
}

function get_template_path_ag($template_names, $load = false, $require_once = true ) {
  
    $located = ''; 
    foreach ( (array) $template_names as $template_name ) { 
      if ( !$template_name ) 
        continue; 

      /* search file within the AG_PLUGIN_BASE_PATH only */ 
      if ( file_exists(AG_PLUGIN_BASE_PATH. "inc/views/front/" . $template_name)) { 
        $located = AG_PLUGIN_BASE_PATH. "inc/views/front/" . $template_name; 
        break; 
      } 
    }

    if ( $load && '' != $located )
        load_template( $located, $require_once );

    return $located;
}


if(file_exists(AG_PLUGIN_BASE_PATH . '/inc/ag-main-integration.php')){
    require_once AG_PLUGIN_BASE_PATH . '/inc/ag-main-integration.php';
	$plugin = new ag_main_integration();
}