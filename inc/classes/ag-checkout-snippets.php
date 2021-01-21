<?php
class Ag_Custom_Chekout_Snippets{

	public function ag_teacher_registration_number(){
		$variable = 
			'<p class="form-row form-row-wide address-field validate-required woocommerce-validated" >
                    <label for="billing_city" class="">Registration Number * </label>
                    <span class="woocommerce-input-wrapper">
                        <input type="text" class="input-text form-control" name="teacher_registration_number" id="teacher_registration_number" placeholder="Teacher Reg. no" required="">
                    </span>
                </p>';
			return $variable;

	}

	public function select_student(){
		$htmlToAdd = '';
		$htmlToAdd .= '<div class="class-list">';
        $htmlToAdd .=  '<h3>Courses/ Class</h3>';
        $htmlToAdd .=   '<ul class="cl-list">';

                        global $woocommerce;
                        $items = $woocommerce->cart->get_cart();
                        
                        $Users_module = new Users_module();
                        $current_children_list = $Users_module->get_childrens_of_current_user_from_ag();

                        foreach($items as $item => $values) {
                            $id = $values["product_id"];
                            $_product =  wc_get_product( $id);
                            $post_terms = get_the_terms( $id, "product_cat" );
                            $product_quantity = $values['quantity'];

                            $all_venues_obj = json_decode(get_option('ag_venues'));
                            //convert std object into array
                            $all_venues_array = json_decode(json_encode($all_venues_obj), true);

                            $all_subjects = json_decode(get_option("ag_subjects"));
                            //convert std object into array
                            $subjects_detail_array = json_decode(json_encode($all_subjects), true);

                            $course_venue_id = get_post_meta($id,"venue",true);
                            if(!empty($all_venues_array) && !empty($course_venue_id)){
                                if(array_key_exists($course_venue_id,$all_venues_array)){
                                    $venue_details = $all_venues_array[$course_venue_id];
                                    $AG_utility = new AG_utility();
                                    $start_time = $AG_utility->get_post_field($id,"session_start_time");
                                    $end_time = $AG_utility->get_post_field($id,"session_end_time");
                                    $subject_year = $AG_utility->get_post_field($id,"subject_year");

                                    $week_start_date = $AG_utility->get_post_field($id,"weekstart_date");
                                    $start_date_obj = strtotime($week_start_date);

                                    $week_end_date = $AG_utility->get_post_field($id,"weekend_date");
                                    $end_date_obj = strtotime($week_end_date);
                                }
                            }

                            foreach($post_terms as $term){
                                if($term->slug == "ag-course-type"){

                                   $htmlToAdd .= '<input type="hidden" id="selected_std_lists_for_course_'.$id.'" name="selected_std_lists_for_course_'.$id.'" >';

                                   $htmlToAdd .= '<li class="cll-item">';
                                    $htmlToAdd .= '<div class="row">';
                                    $htmlToAdd .= '<div class="col-sm-6">';
                                    $htmlToAdd .=  '<p>'.$_product->get_name().'';
                                    $htmlToAdd .=  '<p class="cll-item-quantity">Quantity : <b>'.$product_quantity.'</b></p>';
                                                    $year_int = substr($subject_year,9);
                                    $htmlToAdd  .=  '</div>';
                                            
                                            
                                    $htmlToAdd .=  '<div class="col-sm-6">';
                                    $htmlToAdd .=   '<div style="width:100%;display:none;padding:10px">';
                                    $htmlToAdd .=  '<form id="abc'.$id.'" >';
                                    $htmlToAdd .=    '<input type="text" name="first_name" placeholder="first name">';
                                    $htmlToAdd .=       '</form>';
                                    $htmlToAdd .=     '</div>';
                                    $htmlToAdd .=  '<select class="form-control guardian-childrens-list" id="ag_select_std_'.$id.'">';
                                    $htmlToAdd .=  '<option value="">Add Student</option>';
                                    					foreach($current_children_list as $std_obj){
                                                        if($std_obj->FirstName!="ag" && $std_obj->LastName!="children"){
                                                         $htmlToAdd .=   '<option value='.json_encode($std_obj).'> '.$std_obj->FirstName." ".$std_obj->LastName;'</option>';
                                                        }
                                                    }
                                                    
                                          $htmlToAdd .= '</select>';
                                           $htmlToAdd .= '<a href="javascript:void(0)" onclick="change_current_selected_form('.$id.')"';
                                           $htmlToAdd .= 'data-toggle="modal" data-target="#addStudentModalAg" class="btn btn-blue">';
                                           $htmlToAdd .= '<i class="fa fa-plus"> </i> Add Student</a>';
                                            $htmlToAdd .= '</div>';

                                            /*include script*/
                                            
                                        $htmlToAdd .= '</div>';
                                        $htmlToAdd .= '<div style="display:none" id="std_table'.$id.'" class="cstm-tbl">';
                                        $htmlToAdd .=  '<form action="">';
                                        $htmlToAdd .=  '<table>';
                                        $htmlToAdd .=  '<colgroup>';
                                        $htmlToAdd .=  '<col width="40px">';
                                        $htmlToAdd .=  '<col>';
                                        $htmlToAdd .=  '<col width="25%">';
                                        $htmlToAdd .=  '</colgroup>';
                                        $htmlToAdd .=  '<thead>';
                                        $htmlToAdd .=  '<tr>';
                                        $htmlToAdd .=  '<th class="title-row">&nbsp;</th>';
                                        $htmlToAdd .=  '<th class="title-row">Students</th>';
                                        $htmlToAdd .=  '<th class="title-row">Students No.</th>';
                                        $htmlToAdd .=  '</tr>';
                                        $htmlToAdd .=  '</thead>';
                                        $htmlToAdd .=  '<tbody id="std_selected_table'.$id.'">';
                                        $htmlToAdd .=  '<tr><td></td><td colspan="">Empty</td><td></td></tr>';
                                        $htmlToAdd .=  '</tbody>';
                                        $htmlToAdd .=   '</table>';
                                        $htmlToAdd .=   '</form>';
                                       $htmlToAdd  .= '</div>';
                                    $htmlToAdd .=      '</li>';
                                }
                            }
                        }
                    $htmlToAdd .= '</ul>';
                $htmlToAdd .= '</div>';
        return $$htmlToAdd;
	}


	public function display_all_book_publications_of_cart_snippet(){
		$htmlToAdd = '';
		$htmlToAdd .= '<div class="book-public">';
        $htmlToAdd .= '<h3>Publication/ Books</h3>';
        $htmlToAdd .= '<div class="cstm-tbl">';
        $htmlToAdd .= '<form action="">';
        $htmlToAdd .=  '<table>';
        $htmlToAdd .=  '<colgroup>';
        $htmlToAdd .=  '<col width="15%">';
        $htmlToAdd .=   '<col>';
        $htmlToAdd .=   '<col width="85%">';
        $htmlToAdd .=   '</colgroup>';
        $htmlToAdd .=   '<tbody>';

                                global $woocommerce;
                                $items = $woocommerce->cart->get_cart();
                                foreach($items as $item => $values) {
                                    $id = $values["product_id"];
                                    $_product =  wc_get_product( $id);
                                    $post_terms = get_the_terms( $id, "product_cat" );
                                    if(class_exists("AG_utility")){
                                        $AG_utility = new AG_utility();
                                        $img_url = $AG_utility->get_product_image_url($id);
                                    }else{
                                        $img_url = "";
                                    }
                                    foreach($post_terms as $term) {
                                        if ($term->slug == "ag-publication-type") {
                                            
                                            $htmlToAdd .= '<tr><td class="product-img">';
                                            $htmlToAdd .=        '<figure>';
                                            $htmlToAdd .=            '<img src="'.$img_url.'" alt="">';
                                            $htmlToAdd .=        '</figure>';
                                            $htmlToAdd .=    '</td>';
                                            $htmlToAdd .=    '<td class="product-name">';
                                            $htmlToAdd .=        '<a href="'.$_product->get_permalink().'">'.$_product->get_title();
                                            $htmlToAdd .=        '</a>';
                                            $htmlToAdd .=    '</td>';
                                            $htmlToAdd .= '</tr>';
                                        }
                                    }
                                }

                                $htmlToAdd .= '</tbody>';
                            $htmlToAdd .= '</table>';
                        $htmlToAdd .= '</form>';
                    $htmlToAdd .= '</div>';
                $htmlToAdd .= '</div>';

                return $htmlToAdd;
	}


	public function show_student_add_form(){
		$htmlToAdd = '';
		$htmlToAdd .= '<div class="modal fade" id="addStudentModalAg" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">';
            $htmlToAdd .=  '<div class="modal-dialog" role="document">
					          <div class="modal-content">
				               <div class="modal-header">
				                <h4 class="modal-title" id="exampleModalLongTitle">Create New Student</h4>
				              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				               <span aria-hidden="true">&times;</span>
				               </button>
				               </div>
				               <div class="modal-body temp-form">
				               <div class="row">
				               <div class="col-sm-6">
				               <div class="form-group">
				               <label>First Name*</label>
				               <input type="text" class="form-control" id="sFirstName" placeholder="First Name" required>
				               </div>
				               </div>
				               <div class="col-sm-6">
				               <div class="form-group">
				               <label>Last Name*</label>
				               <input type="text" class="form-control" id="sLastName" placeholder="Last Name" required>
				               </div>
				               </div>
				               <div class="col-sm-12">
				               <div class="form-group">
				               <label>Email Address</label>
				               <input type="email" class="form-control" id="sEmail" placeholder="Email">
				               </div>
				               </div>';
                            $year_levels = json_decode(get_option("year_levels"));
            $htmlToAdd .=   '<div class="col-sm-12">';
            $htmlToAdd .=   '<div class="form-group">
                                            <label>School Year</label>
                                            <select class="form-control" id="sSchoolYear">
                                            <option value="0" selected disabled >Select school year</option>';
                                                    if(!empty($year_levels)){
                                                        foreach($year_levels as $yl){
                                                            $htmlToAdd .= '<option value="'.$yl->Name.'">Year '.$yl->Description.'</option>';
                                                      
                                                        }
                                                    }
                                                
                                           $htmlToAdd .= '</select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Gender</label>
                                            <select class="form-control" id="sGender">
                                                <option value="MALE">Male</option>
                                                <option value="FEMALE">Female</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Mobile</label>
                                            <input type="number" class="form-control" id="sMobile" placeholder="Mobile">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>School Text</label>
                                            <input type="text" class="form-control" id="sSchoolTxt" placeholder="SchoolTxt">
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer">
                                <button id="add_new_student" type="button" class="btn btn-primary">Save changes</button>
                            </div>
                        </div>
                    </div>
                </div>';

                return $htmlToAdd;

	}
}
