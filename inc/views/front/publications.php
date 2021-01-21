<?php
//for teacher's exampapers
$subcategory = $_GET["subcategory"] ? $_GET["subcategory"] : "";
if($subcategory!="" && $subcategory=="SUBCAT_School_Exam_Papers"){
    $args = array(
        "post_type"=>"product",
        "posts_per_page"=>-1,
        "post_status"=>"publish",
        'tax_query' => array(
            array (
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => 'ag-exampaper-type',
            )
        )
    );
}
//for book publications
else{
    $search_key = $_GET["search_key"] ? $_GET["search_key"] : "";
    $category = $_GET["category"] ? $_GET["category"] : "";
    $sub_category = $_GET["sub_category"] ? $_GET["sub_category"] : "";
    $year_level = $_GET["year_level"] ? $_GET["year_level"] : "";
    $book_subject = $_GET["book_subject"] ? $_GET["book_subject"] : "";
    $open_for_purchase = $_GET["open_for_purchase"] ? $_GET["open_for_purchase"] : "";
    $author = $_GET["author"] ? $_GET["author"] : "";

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

    if(!empty($search_key)){
        $args['s'] = $search_key;
    }

    $meta_quires_array = array();

    if(!empty($category)){
        $cat_query = array(
            'key' => 'book_category',
            'value' => $category, //array
            'compare' => '=',
        );
        array_push($meta_quires_array, $cat_query);
    }

    if(!empty($sub_category)){
        $sub_cat_query = array(
            'key' => 'book_subcategory',
            'value' => $sub_category, //array
            'compare' => '=',
        );
        array_push($meta_quires_array, $sub_cat_query);
    }

    if(!empty($year_level)){
        $year_level_query = array(
            'key' => 'year_level',
            'value' => $year_level, //array
            'compare' => '=',
        );
        array_push($meta_quires_array, $year_level_query);
    }

    if(!empty($book_subject)){
        $book_subj_query = array(
            'key' => 'book_subject',
            'value' => $book_subject, //array
            'compare' => '=',
        );
        array_push($meta_quires_array, $book_subj_query);
    }

    if(!empty($author)){
        $author_query = array(
            'key' => 'author',
            'value' => $author, //array
            'compare' => '=',
        );
        array_push($meta_quires_array, $author_query);
    }


    if(!empty($meta_quires_array)){
        $args['meta_query'] = $meta_quires_array;
    }
}




$query =  new wp_query($args);

?>


<?php
$publication_subjects = json_decode(get_option("publication_subjects"));
$subject_year_levels = json_decode(get_option("subject_year_levels"));

$AG_utility = new AG_utility();
$other_filters = $AG_utility -> get_publcations_additional_filter_datas();
//    echo "<pre>";
//    print_r($other_filters );

?>
<div class="publication-wrapper">
    <?php //woocommerce_show_messages(); ?>
    <div class="pw-list">
        <div class="row" style="margin-bottom:10px">
            <form action="" method="get">
                <div class="col-md-3 col-xs-12" style="padding:10px 0xp 20px 0px">
                    <div class="form-group">
                        <input placeholder="Search Key" name="search_key" type="text" class="form-control inline" value="<?php echo $search_key;?>">
                    </div>
                </div>
                <div class="col-md-2 col-xs-12">
                    <div class="form-group">
                        <select name="book_subject" class="form-control">
                            <option value="">Select Subject</option>
                            <?php
                            foreach($publication_subjects as $ps){
                                ?>
                                <option <?php if($book_subject==$ps->Value) echo "selected";?> value="<?php echo $ps->Value;?>"><?php echo $ps->Description;?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 col-xs-12">
                    <div class="form-group">
                        <select name="year_level" class="form-control">
                            <option value="">Select Year Level</option>
                            <?php
                            foreach($subject_year_levels as $syl){
                                ?>
                                <option <?php if($year_level==$syl->Value) echo "selected";?> value="<?php echo $syl->Value;?>"><?php echo $syl->Description;?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 col-xs-12">
                    <div class="form-group">
                        <select name="author" class="form-control">
                            <option value="">Select Author</option>
                            <?php
                            foreach($other_filters["authors"] as $_author){
                                ?>
                                <option <?php if($author==$_author) echo "selected";?> value="<?php echo $_author;?>"><?php echo $_author;?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <!--                <div class="col-xs-2">-->
                <!--                    <div class="form-group">-->
                <!--                        <select name="book_type" class="form-control">-->
                <!--                            <option value="">Select Book Type</option>-->
                <!--                        </select>-->
                <!--                    </div>-->
                <!--                </div>-->
                <div class="col-md-1 col-xs-12">
                    <button type="submit" class="btn btn-danger defaultButton btn-search">Search</button><input type="hidden" id="searchpurchaseBooksForm">
                </div>
            </form>

        </div>
        <div class="row">
            <?php
            //looping and displaying all the products
            if(!empty($query->have_posts())){
                while($query->have_posts()) : $query->the_post();
                    $product = wc_get_product( get_the_id() );
                    ?>

                    <?php
                    $default_img = content_url()."/uploads/woocommerce-placeholder-300x300.png";
                    ?>
                    <?php wc_get_template_part( 'content', 'product' );?>

                <?php
                endwhile;
            }
            ?>
        </div>
    </div>
</div>

<?php
wp_reset_query();
?>



