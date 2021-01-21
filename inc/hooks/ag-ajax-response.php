<?php

class AG_Ajax_responses extends AG_utility
{
    public function __construct()
    {
        parent::__construct();
        $this->define_all_hooks();
    }

    public function define_all_hooks()
    {

        //ajax request to sync all data from the AG portal
        add_action("wp_ajax_ag_sync_all_data", array($this, "ag_sync_all_data"));
        add_action("wp_ajax_nopriv_ag_sync_all_data_cron", array($this,"ag_sync_all_data"));
        add_action("wp_ajax_nopriv_ag_sync_courses", array($this,"ag_sync_courses"));
        add_action("wp_ajax_nopriv_ag_sync_publications", array($this,"ag_sync_publications"));

        add_action("wp_ajax_ag_sync_all_dimens_weight", array($this,"ag_sync_all_dimension_weight_"));
        

        // ajax request to add to cart
        add_action("wp_ajax_ag_add_to_cart", array($this, "ag_add_to_cart")); //for wordpress logged in users only
        add_action("wp_ajax_nopriv_ag_add_to_cart", array($this, "ag_add_to_cart")); //for non logged in users


        // login the api users to wp (login / register user and login)
        // add_action("wp_ajax_login_user_to_wp", array($this,"login_user_to_wp_function")); //for wordpress logged in users only
        add_action("wp_ajax_nopriv_login_user_to_wp", array($this, "login_user_to_wp_function")); //for non logged in wordpressusers

        //register the new guardian to ag api
        add_action("wp_ajax_nopriv_register_new_guardian_to_ag", array($this, "_register_new_guardian_to_ag")); //for non logged in wordpressusers

        //for log purpose
        add_action("wp_ajax_ag_req_res_log", array($this, "ag_req_res_log"));
        add_action("wp_ajax_nopriv_ag_req_res_log", array($this, "ag_req_res_log"));

        add_action("wp_ajax_get_ag_req_res_log", array($this, "get_ag_req_res_log"));

        add_action("wp_ajax_nopriv_filter_ag_courses_html", array($this, "filter_ag_courses_html"));
        add_action("wp_ajax_filter_ag_courses_html", array($this, "filter_ag_courses_html"));

    }

    public function filter_ag_courses_html(){
       echo $this->course_filter_html();
        exit;
    }
    public function course_filter_html(){
        $all_venues = json_decode(get_option("ag_venues"));
        $all_subjects = json_decode(get_option("ag_subjects"));
        //data filtered section
        $filter_args = array();

        if(!isset($_GET['term'])) {
            echo '';
            return;
        }
        if (isset($_GET['term'])) {
            array_push($filter_args, array(
                'key' => 'course_term',
                'value' => $_GET['term'],
                'compare' => 'IN',
            ));

        }
        if (isset($_GET['venue'])) {
            array_push($filter_args, array(
                'key' => 'venue',
                'value' => $_GET['venue'],
                'compare' => 'IN',
            ));
        }

        if (isset($_GET['year'])) {
            array_push($filter_args, array(
                'key' => 'subject_year',
                'value' => $_GET['year'],
                'compare' => 'IN',
            ));
        }
        if (isset($_GET['subject'])) {
            array_push($filter_args, array(
                'key' => 'subject',
                'value' => $_GET['subject'],
                'compare' => 'IN',
            ));
        }
        $tax_args = array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => 'ag-course-type',
        );
        if (isset($_GET['course_type'])) {
            $tax_args = array(
                'taxonomy' => 'course_type',
                'field' => 'slug',
                'terms' => $_GET['course_type']
            );
        }

        $args = array(
            "post_type" => "product",
            "posts_per_page" => -1,
            "post_status" => "publish",
            'tax_query' => array(
                $tax_args,
            ),
            'meta_query' => array(
                'relation' => 'AND',
                $filter_args
            )
        );


        $query = new wp_query($args);
        $filtered_data = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $id = get_the_id();
                $product = wc_get_product($id);
                $course_name = $product->get_name();
                $venue = get_post_meta($id, 'venue', true);
                $filtered_data[$venue] = $filtered_data[$venue] ? $filtered_data[$venue] : [];
                $date = get_post_meta($id, "weekstart_date", true);
                $filtered_data[$venue][$date] = $filtered_data[$venue][$date] ? $filtered_data[$venue][$date] : [];
                $time = get_post_meta($id, 'session_start_time', true) . '-' . get_post_meta($id, 'session_end_time', true);
                $filtered_data[$venue][$date][$time] = $filtered_data[$venue][$date][$time] ? $filtered_data[$venue][$date][$time] : [];
                array_push($filtered_data[$venue][$date][$time], $id);
            endwhile;
        }
//echo '<pre>';
//print_r($subjects);
//die();
        wp_reset_query();
        ?>
            <div class="cstm-tbl">
                <?php foreach ($filtered_data as $venue=>$dates) {?>
                    <div class="venue-lvl">
                        <div id="<?php echo str_replace('ID:','',$venue); ?>" class="tbl-heading venue-head">
                            <span class="calcField" ><?php echo $all_venues->{$venue}->Description?> <span style="float:right" class="icon"><i class="fa fa-chevron-down"> </i></span></span>
                        </div>
                        <div id="venue-body-<?php echo str_replace('ID:','',$venue);; ?>" >
                            <?php foreach ($dates as $date=>$times){ ?>
                                <div class="date-lvl">
                                    <div class="tbl-sub-heading">
                                        <span class="calcField"><?php echo date('l d M Y',strtotime($date));?></span>
                                    </div>
                                    <table class="table" style="display: table;">
                                        <colgroup>
                                            <col width="33.33%">
                                            <col width="33.33%">
                                            <col width="33.33%">
                                        </colgroup>
                                        <thead>
                                        <tr>
                                            <th class="title-row text-center">Time</th>
                                            <th class="title-row text-center" colspan="2">Subjects</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($times as $time=>$subjects) {
                                            list($part1, $part2) = array_chunk($subjects, ceil(count($subjects) / 2));
                                            ?>
                                            <tr class="sessonGroup">
                                                <td class="timeSlot"><span class="calcField"><?php echo $time ?></span></td>
                                                <td class="cstm-radio">
                                                    <ul>
                                                        <?php foreach ($part1 as $id) {?>
                                                            <li>
                                                                <input onclick="oncheck_radio_button(this,<?php echo str_replace('ID:','',$venue);; ?>)" class="ag-sub-check" value="<?php echo $id?>"  id="sub-<?php echo $id?>" type="radio" name="sub-<?php echo $time?>">
                                                                <label for="sub-<?php echo $id?>"><span class="calcField"><?php echo $all_subjects->{get_post_meta($id,'subject',true)}->DisplayName;?></span></label>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                </td>
                                                <td class="cstm-radio">
                                                    <ul>
                                                        <?php foreach ($part2 as $id) {?>
                                                            <li>
                                                                <input onclick="oncheck_radio_button(this,<?php echo str_replace('ID:','',$venue);; ?>)" class="ag-sub-check" value="<?php echo $id?>"  id="sub-<?php echo $id?>" type="radio" name="sub-<?php echo $time?>">
                                                                <label for="sub-<?php echo $id?>"><span class="calcField"><?php echo $all_subjects->{get_post_meta($id,'subject',true)}->DisplayName?></span></label>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        <?php ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } ?>
                            <center>
                                <button onclick="addTocart()" id="add-cart-btn-<?php echo str_replace('ID:','',$venue);; ?>" class="cart-btn btn btn-blue m-3" style="display: none"><i class="fa fa-shopping-cart"> </i> Add to cart</button>
                            </center>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php
    }
    function ag_req_res_log()
    {
        //global $wpdb;
        $url = $_POST['url'];
        $request = $_POST['req'];
        $response = $_POST['res'];
        $current_time_stamp = date("Y-m-d H:i:s");
        $current_date_stamp = date("Y-m-d");
        $file = get_home_path() . 'api_logs/' . $current_date_stamp . '_ag_req_res.txt';
        
        if (file_exists($file)) {
            $current = file_get_contents($file);
        } else {
            $current = '';
        }

        $current .= "url: " . $url . " Request: " . $request . " Response: " . $response . " Time: " . $current_time_stamp . " Date: ".$current_date_stamp." \n";
        

        file_put_contents($file, $current);
    }

    function get_ag_req_res_log()
    {

        /*global $wpdb;
        $table_name = $wpdb->prefix . 'log_manager';
        $log_info = $wpdb->get_results("SELECT * FROM $table_name");
        
        foreach($log_info as $log){
            $data['date'] = $log->date;
            $data['time'] = $log->time;
            $data['response'] = $log->response;
            $data['logged_in_user'] = $log->logged_in_user;
            $data['status'] = $log->status;
        }

        return json_encode($data);
        */
        $file_name = $_POST['file_name'];
        $file = get_home_path() . 'api_logs/' . $file_name;
        $data = file_get_contents($file);
        
        
        wp_send_json(array("data" => $data), 200);
    }

    function ag_sync_all_data()
    {
        //Synchronization of publications
        $AG_Synchronization = new AG_Synchronization();
        $this->write_cron_log('Start Cron','');

        $this->write_cron_log('Start Publications','');
        $publication_sync_status = $AG_Synchronization->sync_all_publications();
        $this->write_cron_log('End Publications','');

        $this->write_cron_log('Start Publication filter Options','');
        $publication_filter_sync_status = $AG_Synchronization->sync_all_publication_filter_options();
        $this->write_cron_log('End Publication filter Options','');

        $this->write_cron_log('Start Course','');
        $courses_sync_status = $AG_Synchronization->sync_all_courses();
        $this->write_cron_log('End Course','');

        $this->write_cron_log('Start course filter options','');
        $courses_filter_option_sync_status = $AG_Synchronization->sync_all_course_filter_options();
        $this->write_cron_log('End course filter options','');
        // echo "<pre>";
        // print_r($courses_sync_status);

        $result = array(
            "publications_sync_status" => $publication_sync_status,
            "publication_filter_sync_status" => $publication_filter_sync_status,
            "courses_filter_option_sync_status" => $courses_filter_option_sync_status,
            "courses_sync_status" => $courses_sync_status,
        );
        $this->write_cron_log('End Cron','');
        echo json_encode($result);
    }
    public function ag_sync_courses(){
        $AG_Synchronization = new AG_Synchronization();
        $this->write_cron_log('Start Course','');
        $courses_sync_status = $AG_Synchronization->sync_all_courses();
        $this->write_cron_log('End Course','');
        $this->write_cron_log('Start course filter options','');
        $courses_filter_option_sync_status = $AG_Synchronization->sync_all_course_filter_options();
        $this->write_cron_log('End course filter options','');
    }
    public function ag_sync_publications(){
        $AG_Synchronization = new AG_Synchronization();
        $this->write_cron_log('Start Publications','');
        if(isset($_GET["username"]) &&  $_GET["password"]){
            $publication_sync_status = $AG_Synchronization->sync_all_publications($_GET["username"],$_GET["password"]);
        }
        else{
            $publication_sync_status = $AG_Synchronization->sync_all_publications(null,null);
        }
        $this->write_cron_log('End Publications','');
    }

    function ag_add_to_cart()
    {

        if (!wp_verify_nonce($_REQUEST['nonce'], "ag_add_to_cart_nonce")) {
            exit("No naughty business please");
        }
        if ($_REQUEST['product_type'] && $_REQUEST['product_type'] == "course") {

        } else {
            $ag_product_id = $_REQUEST['product'];
            $AG_Cart_module = new AG_Cart_module("publications", $ag_product_id);
            $result = $AG_Cart_module->add_product_to_cart($_REQUEST['quantity']);
            if (is_string($result)) {
                echo json_encode(array("status" => "true"));
            } else {
                echo json_encode(array("status" => "false"));
            }
        }

        die();
    }


//    function login_user_to_wp_function()
//    {
//        if (!wp_verify_nonce($_REQUEST['nonce'], "login_api_user_to_wordpress")) {
//            exit("No naughty business please");
//        }
//        $email = $_POST["email"] ? $_POST["email"] : "";
//        $password = $_POST["password"] ? $_POST["password"] : "";
//        // print_r($password);
//        $Users_module = new Users_module();
//        $login_status = $Users_module->log_the_user_to_ag_api($email, $password);
//        if ($login_status == true) {
//            echo json_encode(array("status" => true));
//        } else {
//            echo json_encode(array("status" => false));
//        }
//        die();
//    }

        /*function update_userData_to_wp($formData){
            // echo "<pre>";
            // print_r($formData);

            // $PostalAddress = $formData['PostalAddress'];
            // $PostalCountry = $formData['PostalCountry'];
            // $StreetAddress = $formData['StreetAddress'];
            // return formData;

            //insert user details
            $user_info = wp_insert_user( array(
                'billing_country' => $formData['PostalCountry'],
                'billing_address_1' => $formData['StreetAddress'],
                'billing_postcode' => $formData['PostalAddress'],

            ));
            

            


        }*/

        function _register_new_guardian_to_ag(){
            if (!wp_verify_nonce($_REQUEST['nonce'], "create_new_guardian_to_ag")) {
                exit("No naughty business please");
            }
            $form_data = $_POST;
            unset($form_data["action"]);
            unset($form_data["nonce"]);

            $form_data["ConfirmPassword"] = $_POST["Password"];
            $form_data["ObjectClass"] = "adtf.orm.BookingNPO";
            $form_data["IsAlreadyUser"] = false;
            $form_data["PersonType"] = "PARENT";

            $data["create"] = array("New:1"=>$form_data);
            // echo "<pre>";
            // print_r($data);
            $AG_API_Connector =  new AG_API_Connector();
            $result = $AG_API_Connector->api_request("svc/SaveBooking","POST",$data);

            $return_data = array("status"=>false);
            if(isset($result->result)){
                if($result->result=="ok"){
                    //$this->update_userData_to_wp($form_data);
                    //login to wordpress
                    $credentials = array();
                    $credentials['user_login'] = $form_data["GuardianEmail"];
                    $credentials['user_password'] = $form_data["Password"];
                    $credentials['remember'] = true;
                    $user = wp_signon($credentials, false);
                    if (is_wp_error($user)) {
                        $return_data = array("status"=>false,"message"=>"failed to login");
                    } else {
                        $return_data = array("status"=>true);
                    }
                }
            }else{
                if(isset($result->errorMoreInfo) && isset($result->errorDetails)){
                    if(isset($result->errorDetails[0])){
                        $return_data = array("status"=>false,"message"=>$result->errorDetails[0]);
                    }
                }
            }
            // $return = array();
            // $return['msg'] = $return_data;
            // $return['datas'] = $form_data;
            // echo json_encode($return);
             echo json_encode($return_data);
            die();
        }

        function ag_sync_all_dimension_weight_(){
            if (!wp_verify_nonce($_REQUEST['nonce'], "ag_sync_all_dimension_weight")) {
                exit("No naughty business please");
            }
            $Publication_module = new Publication_module();
            $result = $Publication_module->syn_all_dimension_weigt_from_csv();
            if($result == true){
                echo json_encode(array("status"=>true));
            }
            die();
        }
}
