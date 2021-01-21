<?php

class AG_Synchronization extends AG_utility{
    private $_Publication_Module,$_Courses_Module;

    public function __construct(){
        parent::__construct();
        $this->_Publication_Module = new Publication_module();
        $this->_Course_Module = new Program_module();
    }

    public function sync_all_publication_filter_options(){
        $publication_subjects_sync_status = false;
        $publication_year_level_sync_status = false;

        $Publication_module =  new Publication_module();

        $subjects = $Publication_module->get_all_publication_subjects();
        if(!empty($subjects)){
            if(!get_option('publication_subjects')){
                $publication_subjects_sync_status = add_option( "publication_subjects", json_encode($subjects));
            }
            else{
                $publication_subjects_sync_status = update_option( "publication_subjects", json_encode($subjects));
            }
        }

        $subject_year_levels = $Publication_module->get_all_publication_year_levels();

        if(!empty($subject_year_levels)){
            if(!get_option('subject_year_levels')){
                $publication_year_level_sync_status = add_option( "subject_year_levels", json_encode($subject_year_levels));
            }
            else{
                $publication_year_level_sync_status = update_option( "subject_year_levels", json_encode($subject_year_levels));
            }
        }


        return array("publication_subjects_sync_status"=>$publication_subjects_sync_status,"publication_year_level_sync_status"=>$publication_year_level_sync_status);

    }

    public function sync_all_publications($username=null,$password=null){
        if(!is_null($username) && !is_null($password)){
            $AG_API_Connector = new AG_API_Connector();
            $jsessionid = $AG_API_Connector->login($username,$password);
        }

        $total_count = 0;
        $success_count = 0;
        $failed_count = 0;

        $all_publications = $this->_Publication_Module->get_publications()['references'];
        $ag_pub_ids = array();
        if(!empty($all_publications)) {
            foreach ($all_publications as $pub) {
                array_push($ag_pub_ids,substr($pub->ObjectID,3));
            }
        }

        $all_publications_with_details = $this->_Publication_Module->get_publications_details($ag_pub_ids)['references'];

        $active_publication_ids = array();

        if(!empty($all_publications_with_details)){
            foreach($all_publications_with_details as $pub){
                $total_count++;

                $product_name = $pub->Name ? $pub->Name : "";
                $product_desc = $pub->Description ? $pub->Description : "";
                $product_sku = $pub->ObjectID ? "pub-".substr($pub->ObjectID,3) : "";
                $product_regular_price = $pub->Price ? $pub->Price : "";

                $if_product_exists = wc_get_product_id_by_sku($product_sku);

                //if product doesnt exists, add product
                if(!$if_product_exists){
                    $Product_obj = new WC_Product();
                }
                //else update the product
                else{
                    $Product_obj = new WC_Product($if_product_exists);
                }

                $Product_obj->set_status("publish");
                $Product_obj->set_name($product_name);
                $Product_obj->set_catalog_visibility('visible');
                $Product_obj->set_description($product_desc);
                $Product_obj->set_sku($product_sku);
                $Product_obj->set_price($product_regular_price);
                $Product_obj->set_regular_price($product_regular_price);
                $Product_obj->set_manage_stock(true);
                $Product_obj->set_stock_quantity(10);
                $Product_obj->set_stock_status('instock');
                $Product_obj->set_backorders('no');
                $Product_obj->set_reviews_allowed(true);
                // $Product_obj->set_sold_individually(false);

                //save the product
                $product_id = $Product_obj->save();
                if($product_id){
                    $active_publication_ids[] = $product_id;
                    $success_count++;
                    //set product term of AG_Product type to product ("publication" or "course")
                    if(taxonomy_exists("product_cat")){
                        $term = get_term_by('slug', 'ag-publication-type', 'product_cat');
                        if($term){
                            $term_id = $term->term_id;
                            wp_set_post_terms($product_id, $term_id, "product_cat");
                        }
                    }

                    if($pub->ObjectID){
                        if(metadata_exists('post', $product_id, 'product_ag_id')){
                            update_post_meta( $product_id, "product_ag_id", $pub->ObjectID );
                        }
                        else{
                            add_post_meta( $product_id, "product_ag_id", $pub->ObjectID,true );
                        }
                    }

                    //set ISBN
                    if(isset($pub->ISBN)){
                        if(metadata_exists('post', $product_id, 'isbn')){
                            update_post_meta( $product_id, "isbn", $pub->ISBN );
                        }
                        else{
                            add_post_meta( $product_id, "isbn", $pub->ISBN,true );
                        }
                    }

                    //set book category
                    if($pub->Category->Name){
                        if(metadata_exists('post', $product_id, 'book_category')){
                            update_post_meta( $product_id, "book_category", $pub->Category->Name );
                        }
                        else{
                            add_post_meta( $product_id, "book_category", $pub->Category->Name,true );
                        }
                    }

                    //set book subcategory
                    if($pub->SubCategory->Name){
                        if(metadata_exists('post', $product_id, 'book_subcategory')){
                            update_post_meta( $product_id, "book_subcategory", $pub->SubCategory->Name );
                        }
                        else{
                            add_post_meta( $product_id, "book_subcategory", $pub->SubCategory->Name,true );
                        }
                    }

                    //set product image
                    if($pub->CoverImage && $pub->CoverImage->URI){
                        $AG_API_Connector = new AG_API_Connector();

                        //if cron request for image
                        if(isset($jsessionid) && !empty($jsessionid)){
                            $img_contents = $AG_API_Connector->download_image_from_ag(get_option('api_url').$pub->CoverImage->URI,$jsessionid);
                        }

                        //if wp request for image
                        else{
                            $img_contents = $AG_API_Connector->download_image_from_ag(get_option('api_url').$pub->CoverImage->URI,null);
                        }

                        // finds the last URL segment for image name
                        $urlArray = parse_url($pub->CoverImage->URI, PHP_URL_PATH);
                        $segments = explode('/', $urlArray);
                        $numSegments = count($segments);
                        $lastSegment = $segments[$numSegments - 1];
                        // echo $img_contents;
                        if (!file_exists(wp_upload_dir()["basedir"].'/ag_uploads/img/'.urldecode($lastSegment))) {
                            $img = wp_upload_dir()["basedir"].'/ag_uploads/img/'.urldecode($lastSegment);
                            file_put_contents($img,$img_contents); //first time image will be created but show invalid image
                            file_put_contents($img,$img_contents); //second time image will be overwritten an the file will be valid

                            $image_url = urlencode(wp_upload_dir()["baseurl"].'/ag_uploads/img/'.urldecode($lastSegment));
                            if(metadata_exists('post', $product_id, 'image_url')){
                                update_post_meta( $product_id, "image_url", $image_url );
                            }
                            else{
                                add_post_meta( $product_id, "image_url", $image_url,true );
                            }
                        }
                    }

                    //set book author
                    if($pub->Author){
                        if(metadata_exists('post', $product_id, 'author')){
                            update_post_meta( $product_id, "author", $pub->Author );
                        }
                        else{
                            add_post_meta( $product_id, "author", $pub->Author,true );
                        }
                    }

                    //set yearlevel
                    if($pub->YearLevel && is_object($pub->YearLevel)){
                        if(metadata_exists('post', $product_id, 'year_level')){
                            update_post_meta( $product_id, "year_level", $pub->YearLevel->Value );
                        }
                        else{
                            add_post_meta( $product_id, "year_level", $pub->YearLevel->Value,true );
                        }
                    }

                    //set yearlevel
                    if($pub->BookSubject && is_object($pub->BookSubject)){
                        if(metadata_exists('post', $product_id, 'book_subject')){
                            update_post_meta( $product_id, "book_subject", $pub->BookSubject->Value );
                        }
                        else{
                            add_post_meta( $product_id, "book_subject", $pub->BookSubject->Value,true );
                        }
                    }

                    //set weight
                    if($pub->WeightInKg){
                        if(metadata_exists('post', $product_id, '_weight')){
                            update_post_meta( $product_id, "_weight", $pub->WeightInKg );
                        }
                        else{
                            add_post_meta( $product_id, "_weight", $pub->WeightInKg,true );
                        }
                    }
                    // set default weight
                    else{
                        if(metadata_exists('post', $product_id, '_weight')){
                            add_post_meta( $product_id, "_weight", "0.67",true );
                        }
                    }

                    //set default dimesnisons before syncing from the csv file
                    if(!metadata_exists('post', $product_id, '_length')){
                        add_post_meta( $product_id, "_length", "29.7",true );
                    }

                    if(!metadata_exists('post', $product_id, '_width')){
                        add_post_meta( $product_id, "_width", "21",true );
                    }

                    if(!metadata_exists('post', $product_id, '_height')){
                        add_post_meta( $product_id, "_height", "1.1",true );
                    }

                }
                else{
                    $failed_count++;
                }

            }
        }

        $this->update_wc_product_status($active_publication_ids,"'ag-publication-type'");

        return array("status"=>true,
            "total"=>$total_count,
            "success"=>$success_count,
            "failed"=>$failed_count
        );

    }

    public function update_wc_product_status($active_products,$product_type){
        $args = array(
            "post_type"=>"product",
            "posts_per_page"=>-1,
            "post_status"=>"publish",
            'tax_query' => array(
                array (
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $product_type,
                )
            )
        );
        $query =  new wp_query($args);

        while($query->have_posts()) :
            $query->the_post();
            $product_id = get_the_id();

            if (in_array($product_id, $active_products)){
                $post = array( 'ID' => $product_id, 'post_status' => "publish" );
            }
            else{
                $post = array( 'ID' => $product_id, 'post_status' => "draft" );
            }

            wp_update_post($post);
        endwhile;
        wp_reset_query();

    }

    public function Generate_Featured_Image( $image_url, $post_id  ){
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);
        $filename = basename($image_url);
        if(wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
        else                                    $file = $upload_dir['basedir'] . '/' . $filename;
        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null );
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
        $res2= set_post_thumbnail( $post_id, $attach_id );
    }


    //sync all course types
    //sync all terms
    //sync all venues
    //sync all subject years
    //sync all subject
    //year levels not required for course filter, but used while creating new student for course
    public function sync_all_course_filter_options(){
        $course_type_sync_status = false;
        $terms_sync_status = false;
        $venue_sync_status = false;
        $subject_year_sync_status = false;
        $subject_sync_status = false;

        //year levels not required for course filter, but used while creating new student for course
        $yearl_level_sync_status = false;

        $Program_module =  new Program_module();

        $courses = $Program_module->get_all_course_types();
        if(!empty($courses)){
            if(!get_option('ag_course_types')){
                $course_type_sync_status = add_option( "ag_course_types", json_encode($courses));
            }
            else{
                $course_type_sync_status = update_option( "ag_course_types", json_encode($courses));
            }
        }

        $terms = $Program_module->get_terms();
        if(!empty($terms)){

            if(!get_option('ag_terms')){
                $terms_sync_status = add_option( "ag_terms", json_encode($terms));
            }
            else{
                $terms_sync_status = update_option( "ag_terms", json_encode($terms));
            }
        }

        $venues = $Program_module->get_venues();
        if(!empty($venues)){
            if(!get_option('ag_venues')){
                $venue_sync_status = add_option( "ag_venues", json_encode($venues));
            }
            else{
                $venue_sync_status = update_option( "ag_venues", json_encode($venues));
            }
        }

        $subject_years = $Program_module->get_subject_years();
        if(!empty($subject_years)){
            if(!get_option('ag_subject_years')){
                $subject_year_sync_status = add_option( "ag_subject_years", json_encode($subject_years));
            }
            else{
                $subject_year_sync_status = update_option( "ag_subject_years", json_encode($subject_years));
            }
        }

        $subjects = $Program_module->get_subjects();
        if(!empty($subjects)){
            if(!get_option('ag_subjects')){
                $subject_sync_status = add_option( "ag_subjects", json_encode($subjects));
            }
            else{
                $subject_sync_status = update_option( "ag_subjects", json_encode($subjects));
            }
        }

        //year levels not required for course filter, but used while creating new student for course
        $year_levels = $Program_module->get_year_levels();
        if(!empty($year_levels)){
            if(!get_option('year_levels')){
                $yearl_level_sync_status = add_option( "year_levels", json_encode($year_levels));
            }
            else{
                $yearl_level_sync_status = update_option( "year_levels", json_encode($year_levels));
            }
        }

        return array("course_type_sync_status"=>$course_type_sync_status,"terms_sync_status"=>$terms_sync_status,"venue_sync_status"=>$venue_sync_status,"subject_year_sync_status"=>$subject_year_sync_status,"subject_sync_status"=>$subject_sync_status);

    }

    public function sync_all_courses(){
        $all_courses_details_refrences = $this->_Course_Module->get_courses()['references'];
        $total_count = 0;
        $success_count = 0;
        $failed_count = 0;
        $active_course_ids = array();
        if(!empty($all_courses_details_refrences)){
            foreach($all_courses_details_refrences as $course){
                if($course->SessionSubject){
                    $total_count++;
                    $product_name = $course->CourseName ? $course->CourseName : "";
                    $product_name .= $course->SubjectDescription ? ", ".$course->SubjectDescription.", " : "";
                    if($course->Venue){
                        $venue_desc = $all_courses_details_refrences->{$course->Venue}->Description;
                        $product_name .= $venue_desc." ";
                    }

                    $product_name .= "(".$this->get_formatted_date($course->WeekStartDate)."-";
                    $product_name .= $this->get_formatted_date($course->WeekEndDate).")";

                    $product_name .= "(".$course->SessionStartTime."-";
                    $product_name .= $course->SessionEndTime.")";

                    $product_sku = $course->SessionSubject ? "crs-".substr($course->SessionSubject,3) : "";
                    $product_regular_price = $course->CourseCost ? $course->CourseCost : "";

                    $if_product_exists = wc_get_product_id_by_sku($product_sku);

                    //if product doesnt exists, add product
                    if(!$if_product_exists){
                        $Product_obj = new WC_Product();
                    }
                    //else update the product
                    else{
                        $Product_obj = new WC_Product($if_product_exists);
                    }

                    $Product_obj->set_status("publish");
                    $Product_obj->set_name($product_name);
                    $Product_obj->set_catalog_visibility('visible');
                    // $Product_obj->set_description($product_desc);
                    $Product_obj->set_sku($product_sku);
                    $Product_obj->set_price($product_regular_price);
                    $Product_obj->set_regular_price($product_regular_price);
                    $Product_obj->set_manage_stock(true);

                    if(isset($course->RoomCapacity) && isset($course->NoOfStudentsEnrolled)){
                        if($course->RoomCapacity - $course->NoOfStudentsEnrolled > 0){
                            $Product_obj->set_stock_quantity($course->RoomCapacity - $course->NoOfStudentsEnrolled);
                            $Product_obj->set_stock_status('instock');
                        }
                        else{
                            $Product_obj->set_stock_quantity(0);
                            $Product_obj->set_stock_status('outofstock');
                        }
                    }


                    $Product_obj->set_backorders('no');
                    $Product_obj->set_reviews_allowed(true);
                    // $Product_obj->set_sold_individually(false);


                    $categoires = array();

                    //save the product
                    $product_id = $Product_obj->save();
                    if($product_id){
                        $active_course_ids[] = $product_id;
                        $success_count++;
                        //set product term of AG_Product type to product ("publication" or "course")
                        if(taxonomy_exists("product_cat")){
                            $term = get_term_by('slug', 'ag-course-type', 'product_cat');
                            if($term){
                                $term_id = $term->term_id;
                                wp_set_post_terms($product_id, $term_id, "product_cat");
                            }
                        }

                        //set course type
                        if($course->CourseType && $course->CourseType->Name){
                            $term_id = term_exists( $course->CourseType->Name, "course_type" );
                            if($term_id){
                                wp_set_post_terms($product_id, $term_id, "course_type");
                            }
                            else{
                                $term = wp_insert_term(
                                    $course->CourseType->Description,
                                    'course_type',
                                    array(
                                      'description' => $course->CourseType->Description,
                                      'slug'        => $course->CourseType->Name
                                    )
                                );
                                wp_set_post_terms($product_id, $term["term_id"], "course_type");
                            }
                        }

                        // set course meta data
                        $this->_set_course_metadata($course,$product_id);
                    }
                    else{
                        $failed_count++;
                    }
                }
            }
        }
        $this->update_wc_product_status($active_course_ids,"'ag-course-type'");
        return array("status"=>true,
            "total"=>$total_count,
            "success"=>$success_count,
            "failed"=>$failed_count
        );

    }

    private function _set_course_metadata($course,$product_id){
        //set all courses to virtual product
        if(metadata_exists('post', $product_id, '_virtual')){
            update_post_meta( $product_id, "_virtual", "yes" );
        }
        else{
            add_post_meta( $product_id, "_virtual", "yes" ,true );
        }


        //set course sessionsubject as porduct-ag id
        if($course->SessionSubject){
            if(metadata_exists('post', $product_id, 'product_ag_id')){
                update_post_meta( $product_id, "product_ag_id", $course->SessionSubject );
            }
            else{
                add_post_meta( $product_id, "product_ag_id", $course->SessionSubject,true );
            }
        }
        //set Weekend Date
        if($course->WeekEndDate){
            if(metadata_exists('post', $product_id, 'weekend_date')){
                update_post_meta( $product_id, "weekend_date", $course->WeekEndDate);
            }
            else{
                add_post_meta( $product_id, "weekend_date",$course->WeekEndDate,true );
            }
        }

        //set Weekend Name
        if($course->WeekName){
            if(metadata_exists('post', $product_id, 'weekend_name')){
                update_post_meta( $product_id, "weekend_name", $course->WeekName);
            }
            else{
                add_post_meta( $product_id, "weekend_name",$course->WeekName,true );
            }
        }

        //set Venue Id
        if($course->Venue){
            if(metadata_exists('post', $product_id, 'venue')){
                update_post_meta( $product_id, "venue", $course->Venue);
            }
            else{
                add_post_meta( $product_id, "venue",$course->Venue,true );
            }
        }

        //set Session Subject
        if($course->SessionSubject){
            if(metadata_exists('post', $product_id, 'session_subject')){
                update_post_meta( $product_id, "session_subject", $course->SessionSubject);
            }
            else{
                add_post_meta( $product_id, "session_subject",$course->SessionSubject,true );
            }
        }

        //set Room Capacity
        if($course->RoomCapacity){
            if(metadata_exists('post', $product_id, 'room_capacity')){
                update_post_meta( $product_id, "room_capacity", $course->RoomCapacity);
            }
            else{
                add_post_meta( $product_id, "room_capacity",$course->RoomCapacity,true );
            }
        }

        //set No of Student Enrolled
        if($course->NoOfStudentsEnrolled){
            if(metadata_exists('post', $product_id, 'no_student_endrolled')){
                update_post_meta( $product_id, "no_student_endrolled", $course->NoOfStudentsEnrolled);
            }
            else{
                add_post_meta( $product_id, "no_student_endrolled",$course->NoOfStudentsEnrolled,true );
            }
        }

        //session start time
        if($course->SessionStartTime){
            if(metadata_exists('post', $product_id, 'session_start_time')){
                update_post_meta( $product_id, "session_start_time", $course->SessionStartTime);
            }
            else{
                add_post_meta( $product_id, "session_start_time",$course->SessionStartTime,true );
            }
        }

        //session end time
        if($course->SessionEndTime){
            if(metadata_exists('post', $product_id, 'session_end_time')){
                update_post_meta( $product_id, "session_end_time", $course->SessionEndTime);
            }
            else{
                add_post_meta( $product_id, "session_end_time",$course->SessionEndTime,true );
            }
        }

        //set Subject
        if($course->Subject){
            if(metadata_exists('post', $product_id, 'subject')){
                update_post_meta( $product_id, "subject", $course->Subject);
            }
            else{
                add_post_meta( $product_id, "subject",$course->Subject,true );
            }
        }

        //set course term
        if($course->CourseTerm && $course->CourseTerm->Name){
            if(metadata_exists('post', $product_id, 'course_term')){
                update_post_meta( $product_id, "course_term", $course->CourseTerm->Name);
            }
            else{
                add_post_meta( $product_id, "course_term",$course->CourseTerm->Name,true );
            }
        }

        // set WeekStartDate
        if($course->WeekStartDate){
            if(metadata_exists('post', $product_id, 'weekstart_date')){
                update_post_meta( $product_id, "weekstart_date", $course->WeekStartDate);
            }
            else{
                add_post_meta( $product_id, "weekstart_date",$course->WeekStartDate,true );
            }
        }

        //set course type
        if($course->CourseType && $course->CourseType->Name){
            if(metadata_exists('post', $product_id, 'course_type')){
                update_post_meta( $product_id, "course_type", $course->CourseType->Name);
            }
            else{
                add_post_meta( $product_id, "course_type",$course->CourseType->Name,true );
            }
        }

        //set subject year
        if($course->SubjectYear && $course->SubjectYear->Name){
            if(metadata_exists('post', $product_id, 'subject_year')){
                update_post_meta( $product_id, "subject_year", $course->SubjectYear->Name);
            }
            else{
                add_post_meta( $product_id, "subject_year",$course->SubjectYear->Name,true );
            }
        }

        //set days between session
        if($course->DaysBetweenSessions){
            if(metadata_exists('post', $product_id, 'days_between_sessions')){
                update_post_meta( $product_id, "days_between_sessions", $course->DaysBetweenSessions);
            }
            else{
                add_post_meta( $product_id, "days_between_sessions",$course->DaysBetweenSessions,true );
            }
        }

    }

}

?>