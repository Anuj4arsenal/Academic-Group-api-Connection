<?php
$all_course_types = json_decode(get_option("ag_course_types"));
$all_terms = json_decode(get_option("ag_terms"));
$all_venues = json_decode(get_option("ag_venues"));
$subject_years = json_decode(get_option("ag_subject_years"));
$subjects = json_decode(get_option("ag_subjects"));
$ajx_res = new AG_Ajax_responses();
$utility = new AG_utility();
if (isset($_GET['course_type'])) {
    $course_name = get_term_by('slug', $_GET['course_type'], 'course_type');

    $filter_args = array();
    if (isset($_GET['year'])) {
        array_push($filter_args, array(
            'key' => 'subject_year',
            'value' => $_GET['year'],
            'compare' => 'IN',
        ));
    }
    $args = array(
        "post_type" => "product",
        "posts_per_page" => -1,
        "post_status" => "publish",
        'tax_query' => array(
            array(
                'taxonomy' => 'course_type',
                'field' => 'slug',
                'terms' => $_GET['course_type']),
        ),
        'meta_query' => array(
            'relation' => 'AND',
            $filter_args
        )
    );
    $query = new wp_query($args);
    $filtered_terms = array();
    $filtered_venues = array();
    $filtered_years = array();
    $filtered_subjects = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            array_push($filtered_terms, get_post_meta(get_the_ID(), 'course_term', true));
            array_push($filtered_venues, get_post_meta(get_the_ID(), 'venue', true));
            array_push($filtered_years, get_post_meta(get_the_ID(), 'subject_year', true));
            array_push($filtered_subjects, get_post_meta(get_the_ID(), 'subject', true));
        endwhile;
    }
    $filter = true;

}

//for cart handling
if(isset($_POST['cart_ids'])){
    $products = array_filter(explode("/",$_POST['cart_ids']));
    global $woocommerce;
    $cart_msg = [];
    foreach ($products as $product){
        $wc_product_id = $product;
        $wc_product = wc_get_product($wc_product_id);
        if($wc_product->get_stock_quantity()<1){
            $msg = ''.$wc_product->get_name().' class is near capacity, please <a style="color:#fff !important;text-decoration: underline;" href="'.home_url().'/contact-us" >contact us </a>';
            array_push($cart_msg,['type'=>'failure','msg'=>$msg]);
            wc_clear_notices(); // clear the error notice in the cart page
        }
        else{
            $msg = ''.$wc_product->get_name().' successfully added to cart';
            array_push($cart_msg,['type'=>'success','msg'=>$msg]);
        }

        $start_date = get_post_meta($wc_product_id, "weekstart_date" ,true);
        if($start_date!=false) {
            $start_date = substr($start_date, 0, strpos($start_date, "T"));
            $later_date = date('Y-m-d');
            $earlier_date = $start_date;
            $earlier = new DateTime($earlier_date);
            $later = new DateTime($later_date);
            $diff = $later->diff($earlier)->format("%r%a");
            if ($diff < 1) {     //if start date has already exceeded
                $remaining_course_cost = $utility->get_remaining_course_sessions_price($wc_product_id);
                $cart_item_data = array('custom_price' => $remaining_course_cost);
                $result = $woocommerce->cart->add_to_cart($wc_product_id, 1, null, null, $cart_item_data);
                $woocommerce->cart->calculate_totals();
            }
            else{
                $woocommerce->cart->add_to_cart($product);
            }
        }
        else {
            $woocommerce->cart->add_to_cart($product);
        }
        //print_r($product);
    }
    $cart_add = true;
}


if($cart_add){
    foreach ($cart_msg as $msg){
        if($msg['type'] == 'failure'){
        ?>
        <div style="margin-top:5px;background:red" class="alert alert-danger">
            <?php echo $msg['msg']; ?>
        </div>
        <?php }
        else{ ?>
            <div style="margin-top:15px;min-height: 85px;" class="alert alert-success banner-text notifySection">
                <div class="col-md-9"><?php echo $msg['msg']; ?></div>
                <div class="col-md-3"><a target="_parent" id="cartLink" href="<?php echo home_url() ?>/cart" style="color:#fff" class="pull-right"><i class="fa fa-shopping-bag"></i> View Cart</a></div>

            </div>
        <?php }}} ?>

<div class="row">
    <div class="col-md-10">
        <div class="filter-section">
            <div style="text-align: center">
                <?php echo($course_name ? '<h3><p>' . $course_name->name . '</p></h3>' : ''); ?>
                <h4> <?php echo ( $query->found_posts == 0 && $filter ? 'There is currently no availability, please <a href="'.home_url().'/contact-us" >contact us </a>' : 'Please make a term selection to begin the enrolment process.'); ?></h4>
            </div>
            <form id="course-filter-from">
                <?php
                        $terms = $all_terms;
                        if ($filter) {
                            $terms = $utility->filter_course_data_with_value($all_terms, $filtered_terms, 'Value');
                        }
                        if (!empty($terms)) { ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2 layout_label">
                        <div class="title-box">
                            Term
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10 layout_field">
                       <?php
                            foreach ($terms as $term) {
                                ?>
                                <div class="styled_enum_checkboxes_col">
                                    <div class="row">
                                        <div class="col-xl-4 col-lg-4 col-md-6 col-xs-12">
                                            <input onchange="changeFilter(this.value,this.id,'term')"
                                                   name="term_selected" id="radio_TERM_<?php echo $term->Value; ?>"
                                                   type="radio"
                                                   value="<?php echo $term->Value; ?>" <?php echo(in_array($term->Value, $_GET['term']) ? 'checked' : '') ?>>
                                            <label for="radio_TERM_<?php echo $term->Value; ?>"><span
                                                        class="calcField"><?php echo $term->Description; ?></span></label>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }

                        ?>
                    </div>
                </div>
                <?php } ?>
                <?php
                $venues = $all_venues;
                if ($filter) {
                    $venues = $utility->filter_course_data($all_venues, $filtered_venues);
                }
                if (!empty($venues)) {
                    ?>
                    <div class="row">
                        <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2 layout_label">
                            <div class="title-box">
                                Venues
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10 layout_field">
                            <div class="multiAssocCheckboxes  row">
                                <?php
                                foreach ($venues as $key => $venue) {
                                    $venue_id = $key;
                                    ?>
                                    <div class="col-xl-4 col-lg-4 col-md-6 col-xs-12">
                                        <input onchange="changeFilter(this.value,this.id,'venue')" name="selected_venue"
                                               id="option_<?php echo str_replace('ID:', '', $venue_id); ?>"
                                               type="checkbox"
                                               value="<?php echo $venue_id; ?>" <?php echo(in_array($venue_id, $_GET['venue']) ? 'checked' : '') ?>>
                                        <label for="option_<?php echo str_replace('ID:', '', $venue_id); ?>"
                                               class="optionText" style="font-weight:normal">
                                            <?php echo $venue->Description; ?> </label>
                                    </div>
                                    <?php
                                }
                                ?>

                            </div>

                        </div>
                    </div>
                <?php } ?>
                <?php
                $subjectYears = $subject_years;
                if ($filter) {
                    $subjectYears = $utility->filter_course_data_with_value($subject_years, $filtered_years, 'Value');
                }
                if (!empty($subjectYears)) {
                    ?>
                    <div class="row">
                        <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2 layout_label">
                            <div class="title-box">
                                Years
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10 layout_field">
                            <div class="multiAssocCheckboxes row">
                                <?php
                                foreach ($subjectYears as $key => $sYear) {
                                    ?>
                                    <div class="col-xl-4 col-lg-4 col-md-6 col-xs-12">
                                        <input onchange="changeFilter(this.value,this.id,'year')" name="selected_year"
                                               id="option_<?php echo $sYear->Value; ?>" type="checkbox"
                                               value="<?php echo $sYear->Value; ?>" <?php echo(in_array($sYear->Value, $_GET['year']) ? 'checked' : '') ?>>
                                        <label for="option_<?php echo $sYear->Value; ?>" class="optionText"
                                               style="font-weight:normal">
                                            <?php echo $sYear->Description; ?> </label>
                                    </div>
                                    <?php
                                }
                                ?>

                            </div>

                        </div>
                    </div>
                <?php } ?>
                <?php
                if ($filter) {
                    $subjects = $utility->filter_course_data($subjects, $filtered_subjects);
                }
                if (!empty($subjects)) {
                    ?>
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
                                foreach ($subjects as $key => $subject) {
                                    $sbj_id = $key;
                                    ?>
                                    <div class="col-xl-4 col-lg-4 col-md-6 col-xs-12">
                                        <input onchange="changeFilter(this.value,this.id,'subject')"
                                               name="selected_subject"
                                               id="option_<?php echo str_replace('ID:', '', $sbj_id); ?>"
                                               type="checkbox"
                                               value="<?php echo $sbj_id; ?>"
                                               data-year="SUB_YEAR_12" <?php echo(in_array($sbj_id, $_GET['subject']) ? 'checked' : '') ?>>
                                        <label for="option_<?php echo str_replace('ID:', '', $sbj_id); ?>"
                                               class="optionText" style="font-weight:normal">
                                            <span class="calcField"><?php echo $subject->DisplayName ?></span>
                                            <span class="subjectYearSpan"> -
                                            <span class="calcField">Yr 12</span>
                                        </span>
                                        </label>
                                    </div>
                                    <?php
                                }
                                ?>

                            </div>
                        </div>
                        <script>
                            function subject_show_hide() {
                                var x = document.getElementById("subjectDiv");
                                if (x.style.display === "none") {
                                    x.style.display = "block";
                                } else {
                                    x.style.display = "none";
                                }
                            }
                        </script>
                    </div>
                <?php } ?>

            </form>
        </div>
        <div class="course-container" style="min-height:200px">
            <div class="ag-loader" style="display: none">
                <div class="loader"></div>
            </div>
            <div class="course-section">
                <?php $ajx_res->course_filter_html(); ?>
            </div>
        </div>
        <form id="cart-ids" method="post">

        </form>
    </div>
    <div class="col-md-2">
        <?php
        if($course_name){
            $image = wp_get_attachment_url(get_term_meta($course_name->term_id,'course_type-image-id',true));
            $file = wp_get_attachment_url(get_term_meta($course_name->term_id,'course_type-pdf_upload',true));
            ?>
            <div style="margin-top: 50px">
                <a target="_blank" href="<?php echo $file;?>" ><img src="<?php echo $image; ?>"/></a>
            </div>
        <?php } ?>
    </div>
</div>
<style>
    .tooltip .tooltiptext {
        left: calc(100% + 5px);
    }

    .loader {
        border: 8px solid #f3f3f3;
        border-radius: 50%;
        border-top: 8px solid #3498db;
        width: 60px;
        height: 60px;
        -webkit-animation: spin 2s linear infinite; /* Safari */
        animation: spin 2s linear infinite;
    }

    .course-container {
        position: relative;
    }

    .ag-loader {
        width: 100%;
        height: 100%;
        position: absolute;
        background: rgba(0, 0, 0, .5);
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .filter-section h3 {
        width: auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .filter-section h3 p {
        border: 3px solid #0b89ba;
        color: #0b89bb;
        padding: 20px;
        font-size: 22px;
        font-weight: bold;
        border-radius: 10px;
    }

    /* Safari */
    @-webkit-keyframes spin {
        0% {
            -webkit-transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(360deg);
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 767px) {
        .tooltip .tooltiptext {
            left: 0;
            top: 27px;
        }

        .tooltiptext::after {
            content: "";
            position: absolute;
            top: -5px;
            right: 50%;
            bottom: 100%;
            margin-top: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: transparent transparent #555 transparent;
        }
    }

    div.checkboxes label.subject:hover, div.checkboxes label.info:hover {
        background-color: #00b8f1;
        color: #ffffff;
        text-shadow: none;
        border: none;
    }

    .alert {
        padding: 20px;
        background-color: #4CAF50;
        color: white;
    }

    .closebtn {
        margin-left: 15px;
        color: white;
        font-weight: bold;
        float: right;
        font-size: 22px;
        line-height: 20px;
        cursor: pointer;
        transition: 0.3s;
    }

    .closebtn:hover {
        color: black;
    }
</style>

<script>
    window.selectedCourseList = [];
    jQuery(document).on('click', '.venue-head', function () {
        let id = this.id;
        jQuery('#venue-body-' + id).toggle();
        if (jQuery('#venue-body-' + id).is(":visible")) {
            jQuery('#' + id + ' .icon').html('<i class="fa fa-chevron-down" aria-hidden="true"></i>');
        } else {
            jQuery('#' + id + ' .icon').html('<i class="fa fa-chevron-up" aria-hidden="true"></i>');
        }

    });

    function addTocart() {
        var selectedCourse = '';
        jQuery(".ag-sub-check:checked").each(function () {
            selectedCourse += '/'+this.value;
        })

        jQuery('#cart-ids').html('<input type="hidden" name="cart_ids" value="'+selectedCourse+'" />');
        jQuery('#cart-ids').submit();
    }

    function oncheck_radio_button(that, parent) {
        if (window.selectedCourseList.includes(that.value)) {
            jQuery(that).attr('checked', false);
            window.selectedCourseList = window.selectedCourseList.filter(function (item) {
                return item != that.value;
            });
        } else {
            window.selectedCourseList.push(that.value);
        }
        //add to cart button
        let len = jQuery('#venue-body-' + parent + ' .ag-sub-check:checked').size();
        if (len > 0) {
            jQuery('#add-cart-btn-' + parent).show();
        } else {
            jQuery('#add-cart-btn-' + parent).hide();
        }
    }

    function changeFilter(value, id, type) {
        jQuery('.ag-loader').show()
        let isChecked = jQuery('#' + id).is(":checked");
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        const entries = urlParams.entries();
        let qString = '';
        let alreadyIn = false;
        for (const entry of entries) {
            if (entry[0] == type + '[]' && entry[1] == value) {
                console.log(entry, type, value, isChecked);
                if (isChecked) {
                    qString += entry[0] + '=' + entry[1] + '&';
                }
                alreadyIn = true;
            } else {
                console.log(entry, type, value, isChecked);
                qString += entry[0] + '=' + entry[1] + '&';
            }
        }
        if (!alreadyIn) {
            qString += type + '[]=' + value;
        }
        history.replaceState(null, null, "?" + qString);
        jQuery.ajax({
            type: "post",
            url: "<?php echo home_url(); ?>/wp-admin/admin-ajax.php?action=filter_ag_courses_html&" + qString,
            success: function (msg) {
                jQuery('.course-section').html(msg);
                jQuery('.ag-loader').hide();
            },
            error: function (err) {
                console.log(err);
                jQuery('.ag-loader').hide();
            }
        });
    }
</script>