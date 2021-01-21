<?php

add_shortcode( 'ag-purchase-books-listing', 'display_ag_all_publications' );
function display_ag_all_publications($atts){
    ob_start();
    set_query_var('atts', $atts);
    get_template_part_ag("publications");
    return ob_get_clean(); 
}

add_shortcode( 'ag-courses-filter-form', 'display_courses_filter_form' );
function display_courses_filter_form($atts){
    ob_start();
    set_query_var('atts', $atts);
    get_template_part_ag("courses-filter-form");
    return ob_get_clean();
}

add_shortcode( 'ag-user-login-form', 'display_ag_user_login_form' );
function display_ag_user_login_form($atts){
    ob_start();
    set_query_var('atts', $atts);
    get_template_part_ag("user-login");
    return ob_get_clean();
}

//add_shortcode( 'ag-user-register-form', 'display_ag_user_register_form' );
//function display_ag_user_register_form($atts){
//    ob_start();
//    set_query_var('atts', $atts);
//    get_template_part_ag("user-register");
//    return ob_get_clean();
//}

add_shortcode( 'ag-users-dashboard', 'display_ag_user_dashboard' );
function display_ag_user_dashboard($atts){
    ob_start();
    set_query_var('atts', $atts);
    get_template_part_ag("user-dashboard");
    return ob_get_clean();
}

add_shortcode( 'ag-register-form', 'display_ag_registration_form' );
function display_ag_registration_form($atts){
    ob_start();
    set_query_var('atts', $atts);
    get_template_part_ag("user-register");
    return ob_get_clean();
}

?>