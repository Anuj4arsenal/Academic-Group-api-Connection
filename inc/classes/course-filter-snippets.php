<?php
class Course_Filter_Snippets{

    public function show_course_filter_message($unavailable_course_subjects, $success_count, $failed_count, $available_course_subjects){
        if(!empty($unavailable_course_subjects)){
            if(count($unavailable_course_subjects)==1){ ?>
                <div style="margin: 10px 0px 10px" class="msg-error">
                    <p><?php
                        echo count($_POST["sessionSubject"])==1 ? "The course you've selected is currently unavailable. " : "One of the course you've selected is currently unavailable. "; ?>
                        Unavailable course :
                    <ul>
                        <?php 
                        foreach ($unavailable_course_subjects as $u_sub){ ?>
                            <li><?php echo $u_sub;?></li>
                            <?php
                        } ?>
                    </ul>
                    </p>
                </div>
                <?php
            } else { ?>
                <div style="margin: 10px 0px 10px" class="msg-error">
                    <p><?php echo count($unavailable_course_subjects);?> of the courses you've selected is currently unavailable. Unavailable courses :
                    <ul>
                        <?php 
                        foreach ($unavailable_course_subjects as $u_sub){ ?>
                            <li><?php echo $u_sub;?></li><?php
                        } ?>
                    </ul>
                    </p>
                </div>
                <?php
            }
        } ?>
        <div style="margin: 10px 0px 10px" class="alert alert-success banner-text notifySection">
            <div class="row">
                <?php
                 //if there is unavailable course in the cart
                if($success_count==1){ ?>
                    <p>Course added to cart successfully.</p><?php
                }
                if($success_count>1){ ?>
                    <p><?php echo $success_count;?> courses added to cart successfully.</p><?php
                }
                if( $failed_count > 0){ ?>
                    <!-- failed message -->
                    <div style="margin: 10px 0px 10px" class="msg-error">
                        <p><?php echo $failed_count;?> courses failed to add to the cart.</p>
                    </div><?php
                } ?>
                <a href="<?php echo wc_get_cart_url();?>" class="pull-right text-success">View Cart<i class="fa fa-shopping-cart"> </i></a>
            </div>
            <div class="row">
                <p>Courses added to cart : </p>
                <ul>
                    <?php
                    foreach ($available_course_subjects as $u_sub){ ?>
                        <li style="color:#3c763d"><?php echo $u_sub;?></li><?php
                    } ?>
                </ul>
            </div>
        </div><?php 
    } //function ends

    public function show_course_filter_main_form($ag_course_form_handler){ ?>
        <h4>Please make a term selection to begin the enrolment process.</h4>
         <form id="course-filter-from">
        <div class="row">
            <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2 layout_label">
                <div class="title-box">
                    Term
                </div>
            </div>
            <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10 layout_field">
                <?php
                $terms = $ag_course_form_handler->all_terms;
                if(!empty($terms)){
                    foreach($terms as $term){
                        ?>
                        <div class="styled_enum_checkboxes_col">
                            <div class="row">
                                <div class="col-xl-4 col-lg-4 col-md-6 col-xs-12">
                                    <input name="term_selected" id="radio_TERM_<?php echo $term->Value;?>" type="radio" value="<?php echo $term->Value;?>">
                                    <label for="radio_TERM_<?php echo $term->Value;?>"><span class="calcField"><?php echo $term->Description;?></span></label>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2 layout_label">
                <div class="title-box">
                    Venues
                </div>
            </div>
            <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10 layout_field">
                <div class="multiAssocCheckboxes  row">
                    <?php
                    $venues = $ag_course_form_handler->all_venues;
                    foreach($venues as $key=>$venue){
                        $venue_id = substr($key,3);
                        ?>      <div class="col-xl-4 col-lg-4 col-md-6 col-xs-12">
                            <input name="selected_venue" id="option_<?php echo $venue_id;?>" type="checkbox" value="<?php echo $venue_id;?>">
                            <label  for="option_<?php echo $venue_id;?>" class="optionText" style="font-weight:normal">
                                <?php echo $venue->Description; ?> </label>
                        </div>
                        <?php
                    }
                    ?>

                </div>

            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2 layout_label">
                <div class="title-box">
                    Years
                </div>
            </div>
            <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10 layout_field">
                <div class="multiAssocCheckboxes row">
                    <?php
                    $subjectYears = $ag_course_form_handler->subject_years;
                    foreach($subjectYears as $key=>$sYear){
                        ?>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-xs-12">
                            <input name="selected_year" id="option_<?php echo $sYear->Value;?>" type="checkbox" value="<?php echo $sYear->Value;?>">
                            <label for="option_<?php echo $sYear->Value;?>" class="optionText" style="font-weight:normal">
                                <?php echo $sYear->Description; ?> </label>
                        </div>
                        <?php
                    }
                    ?>

                </div>

            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2 layout_label">
                <div class="title-box">
                    Subjects
                </div>
            </div>
            <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10 layout_field">
                <a class="select-btn active" onclick="subject_show_hide()">Select Specified Subjects</a>
                <div style="display: none;" class="multiAssocCheckboxes row" id="subjectDiv">
                    <?php
                    $subjects = $ag_course_form_handler->subjects;
                    foreach($subjects as $key=>$subject){
                        $sbj_id = substr($key,3);
                        ?>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-xs-12">
                            <input name="selected_subject" id="option_<?php echo $sbj_id;?>" type="checkbox" value="<?php echo $sbj_id;?>" data-year="SUB_YEAR_12">
                            <label for="option_<?php echo $sbj_id;?>" class="optionText" style="font-weight:normal">
                                <span class="calcField"><?php echo $subject->DisplayName?></span>
                                </span>
                            </label>
                        </div>
                        <?php
                    }
                    ?>

                </div>
            </div>
            <script>
                function subject_show_hide(){
                    var x = document.getElementById("subjectDiv");
                    if (x.style.display === "none") {
                        x.style.display = "block";
                    } else {
                        x.style.display = "none";
                    }
                }
            </script>
        </div>

    </form>
    <?php 
    } //function ends

    public function show_filter_form_for_course($ag_course_form_handler){ 
         $subjects_detail_array = json_decode(json_encode( $ag_course_form_handler->subjects), true); //converting the objs into array ?>

        <form action="" method="post">
            <div id="course-time-slot-tables" style="display: none" class="cstm-tbl">

                
                <?php

                //sessions count to add unique radiobutton name for each timeSlots
               
                foreach($ag_course_form_handler->all_venues as $key=>$venue){
                    $venueid = (int)substr($key,3);
                    
                    $courses_list_by_venue = $ag_course_form_handler->course_fileter_by_venue($venueid);

                    //all course sessions dates of specific venue
                    //starting dates in accesending order
                    $course_dates = $ag_course_form_handler->course_dates_fileter_by_venue($venueid); ?>
                    <div style="margin-top: 100px" id="Venue_<?php echo $venueid;?>" class="venues venue-<?php echo $venueid;?> ag-filtered-data venue-filter-data">
                        <div class="tbl-heading venueHeading active">
                            <span class="calcField"><?php echo $venue->Description;?></span>
                        </div><?php
                        foreach($course_dates as $date){
                            $schedule_available_times = $ag_course_form_handler->get_times_options_by_venue_and_date($venueid,$date["week_start_date"],$date["week_end_date"]); ?>
                            <div class="tbl-sub-heading">
                                <span class="calcField">
                                    <?php
                                    $start_date_string = substr($date["week_start_date"], 0, strpos($date["week_start_date"], "T"));
                                    $start_date_obj = strtotime($start_date_string);
                                    echo date('D', $start_date_obj)." ".date('d', $start_date_obj)." ".date('M', $start_date_obj)." ".date('Y', $start_date_obj);


                                    ?> -
                                    <?php
                                    $end_date_string = substr($date["week_end_date"], 0, strpos($date["week_end_date"], "T"));
                                    $end_date_obj = strtotime($end_date_string);
                                    echo date('D', $end_date_obj)." ".date('d', $end_date_obj)." ".date('M', $end_date_obj)." ".date('Y', $end_date_obj);

                                    echo " (".$date["week_name"].")";
                                    ?>
                                </span>
                            </div>
                            <table class="table" style="display: table;">
                                <colgroup>
                                    <col width="20%">
                                    <col width="40%">
                                    <col width="40%">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th class="title-row text-center">TIME
                                    </th>
                                    <th class="title-row text-center" colspan="2">SUBJECTS</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php 
                                foreach($schedule_available_times as $time){ ?>
                                    <tr class="sessonGroup">
                                        <td class="timeSlot">
                                                <span class="calcField"><?php echo $time["start_time"];?> - <?php echo $time["end_time"];?>
                                                </span>
                                        </td>
                                        <?php
                                        //                                          now get all courses by venue,date and timeslot
                                        $courses_for_timeslot = $ag_course_form_handler->get_courses_for_timeslot($venueid,$date["week_start_date"],$date["week_end_date"],$time["start_time"],$time["end_time"]);
                                        // echo "<pre>";print_r($courses_for_timeslot);

                                        $first_column_courses = array();
                                        $second_column_courses = array();
                                        foreach($courses_for_timeslot as $key => $course){
                                            if ($key % 2 == 0) {
                                                array_push($first_column_courses,$course);
                                            }
                                            else{
                                                array_push($second_column_courses,$course);
                                            }
                                        } ?>
                                        <td class="cstm-radio">
                                            <ul><?php
                                                foreach($first_column_courses as $course): ?>
                                                    <!-- for term filter -->
                                                    <div class="ag-filtered-data term-<?php echo $course['course_term'];?> term-filter-data">
                                                        <!-- for year filter -->
                                                        <div class="ag-filtered-data year-<?php echo $course['subject_year'];?> year-filter-data">
                                                            <!-- for subject filter -->
                                                            <div class="ag-filtered-data subject-<?php echo $course['subj_id'];?> subject-filter-data">
                                                                <?php                                                                 $subject_name = $subjects_detail_array["ID:".$course['subj_id']]["DisplayName"];
                                                                ?>
                                                                <li>
                                                                    <?php $avaibality =  $ag_course_form_handler->check_if_stock_available($course['product_id']);?>
                                                                    <input class="radio_select_input" onclick="uncheck_radio_button(<?php echo $course['product_id'];?>)" id="<?php echo $course['product_id'];?>" value="<?php echo $course['product_id'];?>" type="radio" name="sessionSubject[<?php echo $sessions_count;?>]">
                                                                    <label title="<?php if($avaibality!=true){echo 'This Course is not available for enrol';}?>" for="<?php echo $course['product_id'];?>">
                                                                        <span class="calcField">
                                                                            <?php echo $subject_name;?>

                                                                        </span>
                                                                    </label>
                                                                </li>
                                                            </div>
                                                        </div>
                                                    </div>


                                                <?php
                                                endforeach;
                                                ?>
                                            </ul>
                                        </td>
                                        <td class="cstm-radio">
                                            <ul><?php
                                                foreach($second_column_courses as $course): ?>
                                                    <!-- for term filter -->
                                                    <div class="ag-filtered-data term-<?php echo $course['course_term'];?> term-filter-data">
                                                        <!-- for year filter -->
                                                        <div class="ag-filtered-data year-<?php echo $course['subject_year'];?> year-filter-data">
                                                            <!-- for subject filter -->
                                                            <div class="ag-filtered-data subject-<?php echo $course['subj_id'];?> subject-filter-data">
                                                                <li>
                                                                    <?php
                                                                    $subject_name = $subjects_detail_array["ID:".$course['subj_id']]["DisplayName"];
                                                                    ?>
                                                                    <?php $avaibality =  $ag_course_form_handler->check_if_stock_available($course['product_id']);?>
                                                                    <input onclick="uncheck_radio_button(<?php echo $course['product_id'];?>)" id="<?php echo $course['product_id'];?>" value="<?php echo $course['product_id'];?>" type="radio" name="sessionSubject[<?php echo $sessions_count;?>]">
                                                                    <label title="<?php if($avaibality!=true){echo 'This Course is not available for enrol';}?>" for="<?php echo $course['product_id'];?>">

                                                                        <span class="calcField"><?php echo $subject_name;?></span></label>
                                                                </li>
                                                            </div>
                                                        </div>
                                                    </div>


                                                <?php
                                                endforeach;
                                                ?>
                                            </ul>
                                        </td>
                                    </tr>
                                    <?php
                                    $sessions_count++;
                                }
                                ?>



                                </tbody>
                            </table>
                            <div class="col-lg-12 text-center">
                                <!-- <span onclick="add_course_to_cart()" class="btn btn-primary btn-custom btn-xs-block margin-top-10 addToCart">Add to Cart</span> -->
                                <input style="text-transform:none!important;display:none" type="submit" valu="true" name="add_to_cart_btn"
                                       class="btn btn-primary btn-custom btn-xs-block margin-top-10 addToCart"
                                       value="Add to Cart">
                            </div>

                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>

            </div>
    </form>
    <?php }
} //class ends