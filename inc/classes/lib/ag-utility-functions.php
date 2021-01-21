<?php
class AG_utility{
    public function __construct(){
    }
    public function get_post_field($post_id,$field_name){
        $result = "";
        if(get_post_meta($post_id,$field_name) && get_post_meta($post_id,$field_name)[0]){
            $result = get_post_meta($post_id,$field_name)[0];
            // $venueid = (int)substr(get_post_meta($id,"venue")[0],3);
        }
        return $result;
    }

    public function sort_dates($sdates){

        if(!function_exists("date_sort")){
            function date_sort($a, $b) {
                return strtotime($a["week_start_date"]) - strtotime($b["week_start_date"]);
            }
        }
        usort($sdates, "date_sort");
        return $sdates;
    }
    // public function re_trigger($url, $request){
    //     $this->api_request($url,)
    // }
    public function clear_old_log(){
        $days = get_option('ag_clearout_duration');  
        $path = './api_logs/';  
          
        // Open the directory  
        if ($handle = opendir($path))  
        {  
            // Loop through the directory  
            while (false !== ($filed = readdir($handle)))  
            {  
                // Check the file we're doing is actually a file  
                if (is_file($path.$filed))  
                {  
                    // Check if the file is older than X days old  
                    if (filemtime($path.$filed) < ( time() - ( $days * 24 * 60 * 60 ) ) )  
                    {  
                        // Do the deletion  
                        unlink($path.$filed);  
                    }  
                }  
            }  
        }  
    }

    public function delete_log_form_db(){
        global $wpdb;
        $days = 1; 
        $sql = "DELETE FROM wpri_log_manager WHERE datetime < (now() - INTERVAL ".$days." DAY)";

        $dataa = $wpdb->query($sql);
        
    }
    
    public function write_cron_req_res_log($url,$request,$response){
        global $wpdb;
        $current_time_stamp = date("Y-m-d H:i:s");
        $current_user = wp_get_current_user();
        $current_date_stamp = date("Y-m-d");
        $file = get_home_path() . 'api_logs/' . $current_time_stamp . '_cron_req_res.txt';

            
        if (file_exists($file)) {
            mkdir($file, 0777, true);
            $current = file_get_contents($file);
        } else {
            $current = '';
        }


        /*store log to wp custom table*/
        $table_name = $wpdb->prefix . 'log_manager';
        $log_data = $wpdb->insert($table_name, array('datetime' => $current_time_stamp, 'url' => $url, 'request'=>$request, 'response' => substr($response, 1, 150), 'status' => 'success', 'logged_in_user' => $current_user->user_email));

        $lastid = $wpdb->insert_id;
        $current .= "--".$lastid."--\n"."url: " . $url . "\n Request: " . $request . "\n Response: " . $response . "\n Time: " . $current_time_stamp . "--end".$lastid."--\n"." \n \n";

        file_put_contents($file, $current);
        //$wpdb->query("DELETE FROM `wpri_log_manager` WHERE datetime < (now() - INTERVAL 5 DAY");
        //clear log after ceratin time
        $this->clear_old_log();
        $this->delete_log_form_db();
           

        //delete log form database after certain time
        //$sql = 'DELETE FROM `wpri_log_manager` WHERE datetime < (now() - INTERVAL '.$ag_log_delete_duration.' DAY';
        //$log_delete = $wpdb->query($wpdb->prepare($sql));

        // $sql = 'DELETE FROM `wpri_log_manager` WHERE datetime < (now() - INTERVAL 5 DAY';
        // $log_delete = $wpdb->delete($sql);
        // $table = 'wpri_log_manager';
        // $where = 'where datetime < (now() - INTERVAL 5 DAY';
        // $wpdb->delete( $table, $where);

        // $wpdb->query(
        //      "DELETE FROM {$table_name}
        //           WHERE datetime < (now() - INTERVAL {$ag_log_delete_duration} DAY)"
        
                
    }
    public function write_cron_log($title,$message){
        $current_time_stamp = date("Y-m-d H:i:s");
        $current_date_stamp = date("Y-m-d");
        $file = get_home_path() . 'api_logs/' . $current_date_stamp . '_cron_summery.txt';
        if (file_exists($file)) {
            $current = file_get_contents($file);
        } else {
            $current = '';
        }
        $current .= "" . $title . ": " . $message . " Time: " . $current_time_stamp . " \n";

        file_put_contents($file, $current);
        
        
    }

    public function get_remaining_course_sessions_price($wc_product_id){
            $start_date = get_post_meta($wc_product_id, "weekstart_date" ,true);
            $end_date = get_post_meta($wc_product_id, "weekend_date" ,true);

            if($start_date!=false){
                $start_date = substr($start_date, 0, strpos($start_date, "T"));

            }

            if($end_date!=false){
                $end_date = substr($end_date, 0, strpos($end_date, "T"));

            }
            $days_between_sessions = get_post_meta($wc_product_id, "days_between_sessions" ,true);

            if(!empty($start_date) && !empty($end_date) && !empty($days_between_sessions) ){
                $product = wc_get_product( $wc_product_id );
                $product_price = $product->get_price();

                $total_sessions =  $this->get_total_sessions($start_date,$end_date,$days_between_sessions);

                $price_per_sessions = $product_price / $total_sessions;

                $current_date = date('Y-m-d', strtotime("+1 day")); //since remaining sessions will only be count from tommrow

                $remaining_sessions = $this->get_total_sessions($current_date,$end_date,$days_between_sessions);

                $total_price = $remaining_sessions * $price_per_sessions;
                return $total_price;
            }
    }

    public function get_total_sessions($start_date,$end_date,$days_between_sessions){
            $earlier = new DateTime($start_date);
            $later = new DateTime($end_date);

            $diff = $later->diff($earlier)->format("%a");
            $total_sessions = $diff / ($days_between_sessions+1);
            return ceil($total_sessions);
    }

    public function get_product_image_url($product_id){
        $thumbnail_url = "";

        $product = new WC_product($product_id);

        //if feature image is set from woocommerce then use default (in case of exampapers we add product from manually)
        if($product->get_image_id()){
                $thumbnail_url =  wp_get_attachment_url($product->get_image_id());
        }
        else{
                $post_terms = get_the_terms( $product_id, "product_cat" );
                foreach($post_terms as $term){
                    if($term->slug == "ag-publication-type"){
                        $result = $this->get_post_field($product_id,"image_url");
                        if(!empty($result) && $result!=""){
                            $thumbnail_url = urldecode($result);
                        }
                        else{
                            $thumbnail_url = get_option('ag_defualt_publication_thumbnail');
                        }
                    }
                    elseif($term->slug == "ag-course-type"){
                        $thumbnail_url = get_option('ag_defualt_course_thumbnail');
                    }
                    else{
                        $thumbnail_url = get_option('ag_defualt_exampaper_thumbnail');
                    }
                }
        }
        return $thumbnail_url;
    }

    public function get_formatted_date($date_string){
        $start_date_string = substr($date_string, 0, strpos($date_string, "T"));
        $start_date_obj = strtotime($start_date_string);
        return date('D', $start_date_obj)." ".date('d', $start_date_obj)." ".date('M', $start_date_obj)." ".date('Y', $start_date_obj);
    }

    public function get_publcations_additional_filter_datas(){
        $args = array(
            "post_type"=>"product",
            "posts_per_page"=>-1,
            "post_status"=>"publish",
            'tax_query' => array(
                array (
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => 'ag-publication-type',
                )
            )
        );

//        $subjects = array();
//        $year_levels = array();
        $authors = array();

        $query =  new wp_query($args);
        while($query->have_posts()){
            $query->the_post();

            $author = get_post_meta(get_the_id(),"author",true);
            if(!in_array($author, $authors)){
                array_push($authors , $author);
            }
        }
        wp_reset_query();

        return  array("authors"=>$authors);
    }
    public function filter_course_data($alls,$filters){
        $final = array();
        foreach($alls as $key=>$all){
            foreach ($filters as $filter){
                if($filter == $key){
                    $final[$key] = $all;
                }
            }
        }
        return $final;
    }
    public function filter_course_data_with_value($alls,$filters,$value){
        $final = array();
        foreach($alls as $key=>$all){
            foreach ($filters as $filter){
                if($filter == $all->{$value}){
                    $final[$key] = $all;
                }
            }
        }
        return $final;
    }
}