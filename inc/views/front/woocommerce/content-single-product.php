<?php
	global $product;
	$author = get_post_meta(get_the_id(),"author",true);
?>

<div class="publication-wrapper">
    <!-- <div class="msg-success">
        <p>Items added to cart successfully</p>
        <a href=""><i class="fa fa-shopping-cart"> </i> View Cart</a>
    </div> -->
    <div class="pw-description">
        <div class="row">
            <div class="col-sm-4">
                <figure>
                    <img src="https://database.academicgroup.com.au/generated/30RPURGZEGWTH75_AA+ECONOMICS+Study+Guide+YR12_ScaleWithin_500x0_noup.png"
                         alt="">
                </figure>
            </div>
            <div class="col-sm-8">
                <div class="pwd-info">
                    <div class="pwd-cat">
                        <h4>Study Guide</h4>
                    </div>
                    <article>
                        <h2><?php echo get_the_title();?></h2>
                        <h2>
                        	<strong>
                        		<?php
									wc_get_template( 'single-product/price.php' );
                        		?>
                        	</strong>
                        </h2>
                        <p><strong>by <?php echo $author;?> </strong></p>
                        <p>ISBN : <?php echo get_post_meta(get_the_id(),"isbn",true) ? get_post_meta(get_the_id(),"isbn",true) : "N/A" ?></p>
                    </article>
                    <?php
                    	do_action( 'woocommerce_' . $product->get_type() . '_add_to_cart' );
                    ?>
                </div>
            </div>
        </div>
        <div class="pwd-tab">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item active" role="presentation">
                    <a class="nav-link " id="nav-description-tab" data-toggle="tab"
                       href="#nav-description" role="tab" aria-controls="nav-description"
                       aria-selected="true">Description</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="nav-additional-tab" data-toggle="tab" href="#nav-additional"
                       role="tab" aria-controls="nav-additional" aria-selected="false">Additional Information</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link"  id="nav-rating-tab" data-toggle="tab" href="#nav-rating" role="tab"
                       aria-controls="nav-rating" aria-selected="false">Rating</a>
                </li>
            </ul>

            <div class="tab-content">
                    <div class="tab-pane active" id="nav-description" role="tabpanel"
                         aria-labelledby="nav-description-tab">
                        <div class="pwd-tab-desc">
                            <?php echo $product->get_description();?>
                        </div>
                    </div>
                    <div class="tab-pane" id="nav-additional" role="tabpanel" aria-labelledby="nav-additional-tab">
                        <?php echo $product->get_short_description();?>
                    </div>
                    <div class="tab-pane" id="nav-rating" role="tabpanel" aria-labelledby="nav-rating-tab">
                    	<?php
                    		// If comments are open or we have at least one comment, load up the comment template.
			                if (comments_open() || get_comments_number()) :
			                    comments_template();
			                endif;
                    	?>
                    </div>
            </div>

            <script>
                $(function () {
                    $('#myTab li:last-child a').tab('show')
                })
            </script>
        </div>
    </div>

    <!--    upsell products as related products-->
    <?php
        if(function_exists("woocommerce_upsell_display")){
            woocommerce_upsell_display();
        }

    ?>

</div>

