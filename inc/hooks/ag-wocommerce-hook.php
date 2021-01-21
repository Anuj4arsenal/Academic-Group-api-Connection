<?php

if(!class_exists("AG_Wocommerce_Hooks")){
    require_once(AG_PLUGIN_BASE_PATH.'inc/classes/ag-checkout-snippets.php');
    class AG_Wocommerce_Hooks{
        public $checkout_snippet;
        public function __construct()
        {

            $this->checkout_snippet = new Ag_Custom_Chekout_Snippets();

            // Override woocommerce Templates only
            add_filter('woocommerce_locate_template', array($this, 'woo_adon_plugin_template'), 1, 3);
            // Override woocommerce Template parts also
            add_filter( 'wc_get_template_part',array($this,'woo_adon_override_woocommerce_template_part'), 10, 3 );


            //if user is logged in set poupulate the user details in checkout form
            // add_filter('woocommerce_checkout_fields', array($this, 'checkoutform_update_fields'));

            //disable shipping to different address option
            add_filter('woocommerce_cart_needs_shipping_address', '__return_false');

            //moving email field to top of the checkout form
            add_filter('woocommerce_checkout_fields', array($this, 'custom_override_default_locale_fields'));

            //course assignation table (if logged in and course exist in the cart)
            //adding teacher registration field if exampaper :
            add_action('woocommerce_before_order_notes', array($this, 'custom_checkout_field'));

            //validate the checkout form
            //save booking to ag api then only create an order in wc
            add_action('woocommerce_checkout_process', array($this, 'checkout_form_handler_and_savebooking_agi_api'));


            //js function for adding new student to the guardian
            add_action('wp_footer', array($this, 'javasrcipt_checkout_ag_api_interation'));

            //adding css class to the checkout form input field
            add_filter('woocommerce_checkout_fields', array($this, 'addBootstrapToCheckoutFields'));

            //for backend custom field of downloadable product
            add_action('woocommerce_product_options_downloads', array($this, 'add_custom_download_field'));
            add_action('woocommerce_process_product_meta', array($this, 'save_custom_download_field'));

            //for teacher checkout add registration number for exampeper order
            add_action('woocommerce_checkout_create_order', array($this,'ag_custom_checkout_field_update_order_meta'));

            //show the teacher reg number in admin page(order detail page)
            add_action( 'woocommerce_admin_order_data_after_billing_address', array($this,'my_custom_checkout_field_display_admin_order_meta'));

            //in case of courses of exceeded start time the user will only pay for remaing session
            //update cart according to the custom price after add to cart with custom price
            add_action( 'woocommerce_before_calculate_totals', array($this,'woocommerce_custom_price_to_cart_item') );


            //adding confirm password field
            add_action( 'woocommerce_checkout_fields', array($this,'wc_register_form_password_repeat') );
            // Add the code below to your theme's functions.php file to add a confirm password field on the register form under My Accounts.
            add_filter('woocommerce_after_checkout_validation', array($this,'registration_errors_validation'));

            $this->ag_log_manager_table();
            register_activation_hook( __FILE__, array($this, 'ag_log_manager_table'));
            
            //   change terms & conditions accordian to link
            add_action( 'wp', array($this,'agn_woocommerce_checkout_terms_and_conditions' ));
            
        }

        public function agn_woocommerce_checkout_terms_and_conditions() {
                remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30 );
        }


        public function ag_log_manager_table(){
            global $wpdb;
            $table_name = $wpdb->prefix . 'log_manager';
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name){
                $charset_collate = $wpdb->get_charset_collate();
                $sql = "CREATE TABLE IF NOT EXISTS ".$table_name."(
                    id int(11) NOT NULL AUTO_INCREMENT,
                    datetime DATETIME NULL,
                    url text(500) NULL,
                    request varchar (500) NULL,
                    response LONGTEXT NULL,
                    status varchar(250) NULL,
                    logged_in_user varchar(250) NULL,

                    updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY  (id)
            ) $charset_collate;";
            dbDelta($sql);
            }
        }

        public function custom_override_default_locale_fields( $checkout_fields ) {
            $checkout_fields['billing']['billing_email']['priority'] = 1;
            return $checkout_fields;
        }

        public function add_custom_download_field(){
            $args_start = array(
                'label' => __( 'Start Download Date', 'woocommerce' ),
                'placeholder' => __( 'Example : 2020-01-31 13:00', 'woocommerce' ),
                'id' => 'ag_start_dw_date',
                'desc_tip' => true,
                'description' => __( 'Please add the date from when it starts to download..', 'woocommerce' ),
            );
            $args_end = array(
                'label' => __( 'End Download Date', 'woocommerce' ),
                'placeholder' => __( 'Example : 2020-01-31 13:00', 'woocommerce' ),
                'id' => 'ag_end_dw_date',
                'desc_tip' => true,
                'description' => __( 'Please add the date from when download is not available', 'woocommerce' ),
            );
            woocommerce_wp_text_input( $args_start );
            woocommerce_wp_text_input( $args_end );
        }

        public function save_custom_download_field($post_id){
            $ag_start_dw_date = isset( $_POST[ 'ag_start_dw_date' ] ) ? $_POST[ 'ag_start_dw_date' ] : '';
            $ag_end_dw_date = isset( $_POST[ 'ag_end_dw_date' ] ) ? $_POST[ 'ag_end_dw_date' ] : '';
            $product = wc_get_product( $post_id );
            $product->update_meta_data( 'ag_start_dw_date', $ag_start_dw_date );
            $product->update_meta_data( 'ag_end_dw_date', $ag_end_dw_date );
            $product->save();
        }

        public function woo_adon_plugin_template( $template, $template_name, $template_path ) {
            global $woocommerce;
            $_template = $template;
            if ( ! $template_path ) $template_path = $woocommerce->template_url;

            $plugin_path  = untrailingslashit( AG_PLUGIN_BASE_PATH )  . '/inc/views/front/woocommerce/';

            // Look within passed path within the theme - this is priority
            $template = locate_template(
                array(
                    $template_path . $template_name,
                    $template_name
                )
            );

            if( ! $template && file_exists( $plugin_path . $template_name ) )
                $template = $plugin_path . $template_name;

            if ( ! $template )
                $template = $_template;

            return $template;
        }

        public function woo_adon_override_woocommerce_template_part($template, $template_name, $template_path){
            $plugin_path  = untrailingslashit( AG_PLUGIN_BASE_PATH )  . '/inc/views/front/woocommerce/';
            $path = $plugin_path . $template_name;
            return file_exists( $path ) ? $path : $template;
        }

        public function custom_checkout_field($checkout){
            
            if($this->check_if_cart_contains_exampapers()){
                $registeration_form = $this->checkout_snippet->ag_teacher_registration_number();
                echo $registeration_form;
            }

            if($this->check_if_cart_contains_courses()==true){
                global $woocommerce;
                $Users_module = new Users_module();
                $current_children_list = $Users_module->get_childrens_of_current_user_from_ag();
                $current_children_list = array_filter(
                    $current_children_list,
                    function ($std_obj) {
                        return  $std_obj->FirstName!="ag" && $std_obj->LastName!="children";
                    });
                $lists = [];

                foreach ($current_children_list as $key=>$list){
                    array_push($lists,array(
                            'id'=>str_replace('ID:','',$key),
                            'Email'=>$list->Email,
                            'FirstName'=>$list->FirstName,
                            'LastName'=>$list->LastName,
                            'Gender'=>$list->Gender->value,
                            'Mobile'=>$list->Mobile,
                            'SchoolTxt'=>$list->SchoolTxt,
                            'SchoolYear'=>$list->SchoolYear->value,
                    ));
                }

                $items = $woocommerce->cart->get_cart();
                ?>
                <script>
                    sessionStorage.setItem("ag_server_students", '<?php echo json_encode($lists); ?>');
                </script>

                <div class="class-list">
                    <h3>Courses/ Class</h3>
                    <ul class="cl-list">
                        <?php 
                        foreach($items as $item => $value){
                            $id = $value["product_id"];
                            if($this->isCourse($id)){
                            $_product =  wc_get_product( $id);
                            $product_quantity = $value['quantity']; ?>
                            <li class="cll-item">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p><?php echo get_the_title($id); ?>
                                        <p class="cll-item-quantity">Quantity : <?php echo $product_quantity; ?><b></b></p>
                                    </div>
                                    
                                   <div class="col-sm-6">
                                        <select onchange="selectStudentForCourse(<?php echo $id; ?>,this.value)" class="form-control guardian-childrens-list" id="ag_select_std_<?php echo $id;?>" >
                                            <option value="0" selected disabled>Select Student</option>
                                   </select>
                                        <a href="javascript:void(0)" data-course="<?php echo $id ?>" class="btn btn-primary ag-addStudent"><i class="fa fa-plus"> </i> Add Student</a>
                                    </div>
                                </div>
                                <p class="ag-qty-msg" style="color:red;margin-top: 5px" id="ag-qty-msg-<?php echo $id ?>"> * You need to add <?php echo $product_quantity ?> student<?php echo ($product_quantity == 1 ? '' : 's') ?> for this course.</p>
                                <input type="hidden" class="selected_std_lists_for_course" id="selected_std_lists_for_course_<?php echo $id;?>" value="" name="selected_std_lists_for_course_<?php echo $id;?>" />
                                <div style="display:block" id="std_table<?php echo $id;?>" class="cstm-tbl">
                                    <input type="hidden" id="total_qty_<?php echo $id ?>" value="<?php echo $product_quantity ?>"/>
                                    <div style="display: none" class="student-table-block" id="student-table-block-<?php echo $id;?>">
                                        <table>
                                            <colgroup>
                                                <col width="40px">
                                                <col>
                                                <col width="25%">
                                            </colgroup>
                                            <thead>
                                            <tr>
                                                <th class="title-row">&nbsp;</th>
                                                <th class="title-row">Students</th>
                                                <th class="title-row">Students No.</th>
                                            </tr>
                                            </thead>
                                            <tbody class="ag-course-selection-table" id="std_selected_table<?php echo $id;?>">
                                            <tr><td></td><td colspan="">Empty</td><td></td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </li>
                    <?php }} //endforeach ?>
                    </ul>
                </div>
                <?php }

            $this->display_all_book_publications_of_cart();

        }

        public function display_all_book_publications_of_cart(){
            if($this->check_if_cart_contains_books()==true){
                $publication_snippet = $this->checkout_snippet->display_all_book_publications_of_cart_snippet();
                echo $publication_snippet;
            }
        }

        public function check_if_cart_contains_courses(){
            global $woocommerce;
            $items = $woocommerce->cart->get_cart();

            $result = false;
            foreach($items as $item => $values) {
                $id = $values["product_id"];
                $post_terms = get_the_terms( $id, "product_cat" );
                foreach($post_terms as $term){
                    if($term->slug == "ag-course-type"){
                        $result = true;
                    }
                }
            }
            return $result;
        }
        public function isCourse($id){
            $post_terms = get_the_terms( $id, "product_cat" );
            foreach($post_terms as $term){
                if($term->slug == "ag-course-type"){
                    return true;
                }
            }
            return false;
        }

        public function check_if_cart_contains_books(){
            global $woocommerce;
            $items = $woocommerce->cart->get_cart();

            $result = false;
            foreach($items as $item => $values) {
                $id = $values["product_id"];
                $post_terms = get_the_terms( $id, "product_cat" );
                foreach($post_terms as $term){
                    if($term->slug == "ag-publication-type"){
                        $result = true;
                    }
                }
            }
            return $result;
        }

        public function check_if_cart_contains_exampapers(){
            global $woocommerce;
            $items = $woocommerce->cart->get_cart();

            $result = false;
            foreach($items as $item => $values) {
                $id = $values["product_id"];
                $post_terms = get_the_terms( $id, "product_cat" );
                foreach($post_terms as $term){
                    if($term->slug == "ag-exampaper-type"){
                        $result = true;
                    }
                }
            }
            return $result;
        }

        public function javasrcipt_checkout_ag_api_interation(){
             $modal_snippet = $this->checkout_snippet->show_student_add_form();
                echo $modal_snippet;
        }

        public function addBootstrapToCheckoutFields($fields) {
            foreach ($fields as &$fieldset) {
                foreach ($fieldset as &$field) {
                    // if you want to add the form-group class around the label and the input
                    // $field['class'][] = 'form-group';

                    // add form-control to the actual input
                    $field['input_class'][] = 'form-control';
                }
            }
            return $fields;
        }

        public function checkout_form_handler_and_savebooking_agi_api(){
            $email =  $_POST['billing_email'] ? $_POST['billing_email'] : "";
            $firstname = $_POST['billing_first_name'] ? $_POST['billing_first_name'] : "";
            $person_type = "PARENT"; //dummy data
            $PostalPostcodeSuburb = "ID:-2973901"; //dummy data
            $mobile_no = $_POST['billing_phone'] ? $_POST['billing_phone'] : ""; //dummy data
            $country = $_POST['billing_country'] ? $_POST['billing_country'] : "";
            $PostalAddress = "22"; //dummy data
            $StreetAddress = "21"; //dummy data
            $lastname = $_POST['billing_last_name'] ? $_POST['billing_last_name'] : "";
            $phone_no = $_POST['billing_phone'] ? $_POST['billing_phone'] : "";
            $VoucherCode = ""; //dummy data
            $StreetPostcodeSuburb = "ID:-2973901"; //dummy data

            $std_att = array();
            if(!is_user_logged_in()){
                $std_att["GuardianEmail"] = $email;
                $std_att["IsAlreadyUser"] = false;
                $std_att["ObjectClass"] = "adtf.orm.BookingNPO";
                $std_att["ObjectID"] = "New:1";
                $std_att["GuardianFirstName"] = $firstname;
                $std_att["PersonType"] = $person_type;
                $std_att["PostalPostcodeSuburb"] = $PostalPostcodeSuburb;
                $std_att["GuardianMobile"] = $mobile_no;
                $std_att["ConfirmPassword"] = "P@ssw0rd123@*977";
                $std_att["StreetCountry"] = $country;
                $std_att["PostalAddress"] = $PostalAddress;
                $std_att["StreetAddress"] = $StreetAddress;
                $std_att["PostalCountry"] = $country;
                $std_att["GuardianLastName"] = $lastname;
                $std_att["GuardianHomePhone"] = $phone_no;
                $std_att["VoucherCode"] = $VoucherCode;
                $std_att["StreetPostcodeSuburb"] = $StreetPostcodeSuburb;
                $std_att["Password"] = "P@ssw0rd123@*977";
                $create["New:1"] = $std_att;
                if($this->check_if_cart_contains_exampapers()){
                    wc_add_notice( __( 'You need to login as teacher to purchase the exampaper' ), 'error' );
                    return false;
                }

                //courses objects for save booking
                if ($this->check_if_cart_contains_courses()) {
                    global $woocommerce;
                    $items = $woocommerce->cart->get_cart();

                    foreach ($items as $item => $values) {
                        $id = $values["product_id"];
                        $post_terms = get_the_terms($id, "product_cat");
                        $product = new WC_Product($id);
                        $ag_course_id = "ID:".substr($product->get_sku(),4);
                        $stds_obj_names_array = array();
                        foreach ($post_terms as $term) {
                            if ($term->slug == "ag-course-type") {
                                $selected_stds_for_course = $_POST["selected_std_lists_for_course_" . $id];
                                $std_list_array = json_decode(stripslashes($selected_stds_for_course));

                                if($values['quantity']!=count($std_list_array)){

                                    wc_add_notice(__("Please select the student for each course unit", "woocommerce"), 'error'); 
                                    return false;
                                }

                                foreach($std_list_array as $std){
                                    $std->ObjectID = 'ST_NO'.$std->id;
                                    if(substr($std->ObjectID,0,2)=="ID"){
                                        $std_obj_name = substr($std->ObjectID,3);
                                        array_push($stds_obj_names_array,$std_obj_name);
                                    }
                                    else{
                                        //if new std object is not created, i.e same new std is used for only one course, if same new std is used for multiple courses then it we add new std obj only once and used obj name only for other courses
                                        if(!isset($create[$std->ObjectID])){
                                            $std_obj = array();
                                            $std_obj["ObjectClass"] = "adtf.orm.Student";
                                            $std_obj["Email"] = $std->Email;
                                            $std_obj["ObjectID"] = $std->ObjectID;
                                            $std_obj["FirstName"] = $std->FirstName;
                                            $std_obj["SchoolYear"] = $std->SchoolYear;
                                            $std_obj["Gender"] = $std->Gender;
                                            $std_obj["Mobile"] = $std->Mobile;
                                            $std_obj["IsOtherSchool"] = true;
                                            $std_obj["EnquirySource"] = "BOOK_SHOP";
                                            $std_obj["Note"] = "Test Note";
                                            $std_obj["SchoolTxt"] = "ABC School";
                                            $std_obj["IsCreatedFromFE"] = true;
                                            $std_obj["LastName"] = $std->LastName;

                                            $create[$std->ObjectID] = $std_obj;
                                        }

                                        array_push($stds_obj_names_array,$std->ObjectID);
                                    }
                                }
                                $book_obj = array();
                                $book_obj_name = "course_" . $id;
                                $book_obj["ObjectClass"] = "adtf.orm.BookingItemNPO";
                                $book_obj["Students"] = $stds_obj_names_array;
                                $book_obj["Qty"] = $values['quantity'];
                                $book_obj["SessionSubject"] = $ag_course_id;
                                $book_obj["Booking"] = "New:1";

                                $create[$book_obj_name] = $book_obj;
                            }

                        }

                    }
                }

                if($this->check_if_cart_contains_books()){

                    global $woocommerce;
                    $items = $woocommerce->cart->get_cart();

                    foreach($items as $item => $values) {
                        $id = $values["product_id"];
                        $post_terms = get_the_terms( $id, "product_cat" );
                        // year level is currently static since we are not getting the url of the publications, change this after we get the yearlevel from the api
                        // $year_level = get_post_meta($id,"year_level",true);
                        $year_level = "Year_12";
                        foreach($post_terms as $term){
                            $_product = wc_get_product( $id );
                            $sku = $_product->get_sku();
                            $ag_book_id = "ID:".substr($sku,4);

                            if($term->slug == "ag-publication-type"){
                                $stds_obj_names_array = array();
                                for($i=0;$i<$values['quantity'];$i++){
                                    $std_obj = array();
                                    $std_obj_name = "std_".$id."_".$i;
                                    $std_obj["ObjectClass"] = "adtf.orm.Student";
                                    $std_obj["Email"] = "ag_dummy_children@gmail.com";
                                    $std_obj["ObjectID"] = $std_obj_name;
                                    $std_obj["FirstName"] = "ag";
                                    $std_obj["SchoolYear"] = $year_level;
                                    $std_obj["Gender"] = "FEMALE";
                                    $std_obj["Mobile"] = "0111111111";
                                    $std_obj["IsOtherSchool"] = true;
                                    $std_obj["EnquirySource"] = "BOOK_SHOP";
                                    $std_obj["Note"] = "Test Note";
                                    $std_obj["SchoolTxt"] = "ABC School";
                                    $std_obj["IsCreatedFromFE"] = true;
                                    $std_obj["LastName"] = "children";

                                    $create[$std_obj_name] = $std_obj;
                                    array_push($stds_obj_names_array,$std_obj_name);
                                }


                                $book_obj = array();
                                $book_obj_name = "book_".$id;
                                $book_obj["ObjectClass"] = "adtf.orm.BookingItemNPO";
                                $book_obj["Students"] = $stds_obj_names_array;
                                $book_obj["Qty"] = $values['quantity'];
                                $book_obj["Boook"] = $ag_book_id;
                                $book_obj["Booking"] = "New:1";

                                $create[$book_obj_name] = $book_obj;
                            }
                        }
                    }


                }

            }
            else {
                $std_att["IsAlreadyUser"] = true;
                $std_att["ObjectClass"] = "adtf.orm.BookingNPO";
                $create["New:1"] = $std_att;

                if($this->check_if_cart_contains_exampapers()){
                    $user = wp_get_current_user();
                    if ( !in_array( 'AG_TEACHER', (array) $user->roles ) ) {
                        wc_add_notice( __( 'Your need to login as teacher to purchase the exampaper' ), 'error' );
                        return false;
                    }
                    if ( !isset($_POST["teacher_registration_number"]) || $_POST["teacher_registration_number"] =="" ) {
                        wc_add_notice( __( 'Please Enter the Teacher Registration number' ), 'error' );
                        return false;
                    }
                }

                //book objects for save booking
                if ($this->check_if_cart_contains_books()) {

                    global $woocommerce;
                    $items = $woocommerce->cart->get_cart();

                    foreach ($items as $item => $values) {
                        $id = $values["product_id"];
                        $post_terms = get_the_terms($id, "product_cat");
                        // year level is currently static since we are not getting the url of the publications, change this after we get the yearlevel from the api
                        // $year_level = get_post_meta($id,"year_level",true);
                        $year_level = "Year_12";
                        foreach ($post_terms as $term) {
                            $_product = wc_get_product($id);
                            $sku = $_product->get_sku();
                            $ag_book_id = "ID:" . substr($sku, 4);

                            if ($term->slug == "ag-publication-type") {
                                $stds_obj_names_array = array();
                                for ($i = 0; $i < $values['quantity']; $i++) {
                                    $std_obj = array();
                                    $std_obj_name = "std_" . $id . "_" . $i;
                                    $std_obj["ObjectClass"] = "adtf.orm.Student";
                                    $std_obj["Email"] = "ag_dummy_children@gmail.com";
                                    $std_obj["ObjectID"] = $std_obj_name;
                                    $std_obj["FirstName"] = "ag";
                                    $std_obj["SchoolYear"] = $year_level;
                                    $std_obj["Gender"] = "FEMALE";
                                    $std_obj["Mobile"] = "0111111111";
                                    $std_obj["IsOtherSchool"] = true;
                                    $std_obj["EnquirySource"] = "BOOK_SHOP";
                                    $std_obj["Note"] = "Test Note";
                                    $std_obj["SchoolTxt"] = "ABC School";
                                    $std_obj["IsCreatedFromFE"] = true;
                                    $std_obj["LastName"] = "children";

                                    $create[$std_obj_name] = $std_obj;
                                    array_push($stds_obj_names_array, $std_obj_name);
                                }


                                $book_obj = array();
                                $book_obj_name = "book_" . $id;
                                $book_obj["ObjectClass"] = "adtf.orm.BookingItemNPO";
                                $book_obj["Students"] = $stds_obj_names_array;
                                $book_obj["Qty"] = $values['quantity'];
                                $book_obj["Boook"] = $ag_book_id;
                                $book_obj["Booking"] = "New:1";

                                $create[$book_obj_name] = $book_obj;
                            }
                        }
                    }


                }

                //courses objects for save booking
                if ($this->check_if_cart_contains_courses()) {
                    global $woocommerce;
                    $items = $woocommerce->cart->get_cart();

                    foreach ($items as $item => $values) {
                        $id = $values["product_id"];
                        $post_terms = get_the_terms($id, "product_cat");
                        $product = new WC_Product($id);
                        $ag_course_id = "ID:".substr($product->get_sku(),4);
                        $stds_obj_names_array = array();
                        foreach ($post_terms as $term) {
                            if ($term->slug == "ag-course-type") {
                                $selected_stds_for_course = $_POST["selected_std_lists_for_course_" . $id];
                                $std_list_array = json_decode(stripslashes($selected_stds_for_course));

                                if($values['quantity']!=count($std_list_array)){

                                    wc_add_notice(__("Please select the student for each course unit", "woocommerce"), 'error'); 
                                    return false;


                            }

                                foreach($std_list_array as $std){
                                    $std->ObjectID = 'ST_NO'.$std->id;
                                    if(substr($std->ObjectID,0,2)=="ID"){
                                        $std_obj_name = substr($std->ObjectID,3);
                                        array_push($stds_obj_names_array,$std_obj_name);
                                    }
                                    else{
                                        //if new std object is not created, i.e same new std is used for only one course, if same new std is used for multiple courses then it we add new std obj only once and used obj name only for other courses
                                        if(!isset($create[$std->ObjectID])){
                                            $std_obj = array();
                                            $std_obj["ObjectClass"] = "adtf.orm.Student";
                                            $std_obj["Email"] = $std->Email;
                                            $std_obj["ObjectID"] = $std->ObjectID;
                                            $std_obj["FirstName"] = $std->FirstName;
                                            $std_obj["SchoolYear"] = $std->SchoolYear;
                                            $std_obj["Gender"] = $std->Gender;
                                            $std_obj["Mobile"] = $std->Mobile;
                                            $std_obj["IsOtherSchool"] = true;
                                            $std_obj["EnquirySource"] = "BOOK_SHOP";
                                            $std_obj["Note"] = "Test Note";
                                            $std_obj["SchoolTxt"] = "ABC School";
                                            $std_obj["IsCreatedFromFE"] = true;
                                            $std_obj["LastName"] = $std->LastName;

                                            $create[$std->ObjectID] = $std_obj;
                                        }

                                        array_push($stds_obj_names_array,$std->ObjectID);
                                    }
                                }
//                                            wc_add_notice( __( json_encode($std_list_array) ), 'error' );

                                $book_obj = array();
                                $book_obj_name = "course_" . $id;
                                $book_obj["ObjectClass"] = "adtf.orm.BookingItemNPO";
                                $book_obj["Students"] = $stds_obj_names_array;
                                $book_obj["Qty"] = $values['quantity'];
                                $book_obj["SessionSubject"] = $ag_course_id;
                                $book_obj["Booking"] = "New:1";

                                $create[$book_obj_name] = $book_obj;
                            }

                        }

                    }
                }
            }
            $data = array("create"=>$create);
            $AG_API_Connector =  new AG_API_Connector();

            $user = wp_get_current_user();
            if (in_array( 'AG_PUBLIC_LIBRARY', (array) $user->roles ) || in_array( 'AG_BOOKSHOP', (array) $user->roles ) ) {
                $req_result = $AG_API_Connector->api_request('svc/Save',"POST",$data);
            }
            else{
                $req_result = $AG_API_Connector->api_request('svc/SaveBooking',"POST",$data);

            }

            if(array_key_exists("status", $req_result)){
                if($req_result->status=="error"){
                    $message = str_replace(array('[',']'), '',json_encode($req_result->errorDetails) );
                    wc_add_notice( __( 'API ERROR : '.$message ), 'error' );
                }
            }
            elseif(array_key_exists("result", $req_result)){
                if($req_result->result!="ok"){
                    wc_add_notice( __( 'something went wrong from api side' ), 'error' );
                }
            }
        }

        public function ag_custom_checkout_field_update_order_meta($order){
            if(isset($_POST['teacher_registration_number'])){
                $order->update_meta_data( '_teacher_reg_no',  $_POST['teacher_registration_number'] );
            }
        }

        function my_custom_checkout_field_display_admin_order_meta($order){
            if(get_post_meta( $order->get_id(), '_teacher_reg_no', true )!=false){
                echo '<p><strong>'.__('Teacher Registration no').':</strong> <br/>' . get_post_meta( $order->get_id(), '_teacher_reg_no', true ) . '</p>';
            }
        }

        public function woocommerce_custom_price_to_cart_item( $cart_object ) {
            if( !WC()->session->__isset( "reload_checkout" )) {
                foreach ( $cart_object->cart_contents as $key => $value ) {
                    if( isset( $value["custom_price"] ) ) {
                        //for woocommerce version lower than 3
                        //$value['data']->price = $value["custom_price"];
                        //for woocommerce version +3
                        $value['data']->set_price($value["custom_price"]);
                    }
                }
            }
        }

        public function wc_register_form_password_repeat($checkout_fields){
            if ( get_option( 'woocommerce_registration_generate_password' ) == 'no' ) {
                $checkout_fields['account']['account_password2'] = array(
                    'type'              => 'password',
                    'label'             => __( 'Confirm password', 'woocommerce' ),
                    'required'          => true,
                    'placeholder'       => _x( 'Confirm Password', 'placeholder', 'woocommerce' ),
                    'input_class'             => array("form-control")
                );
            }

            return $checkout_fields;
        }

        public function registration_errors_validation($posted){
            $checkout = WC()->checkout;
            if ( ! is_user_logged_in() && ( $checkout->must_create_account || ! empty( $posted['createaccount'] ) ) ) {
                if ( strcmp( $posted['account_password'], $posted['account_password2'] ) !== 0 ) {
                    wc_add_notice( __( 'Passwords do not match.', 'woocommerce' ), 'error' );
                }
            }
        }

    }
}

$AG_Wocommerce_Hooks = new AG_Wocommerce_Hooks();


?>