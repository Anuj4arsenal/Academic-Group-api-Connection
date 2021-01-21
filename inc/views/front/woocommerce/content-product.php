<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woothemes.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.6.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $product;

// Ensure visibility
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}
?>

<div class="col-md-3 col-sm-6">
    <div class="pw-item">
        <a href="<?php echo $product->get_permalink();?>">
            <figure>
                <?php 
                    if(class_exists("AG_utility")){
                        $AG_utility = new AG_utility();
                        $img_url = $AG_utility->get_publication_image_url(get_the_id());
                    }else{
                        $img_url = "https://database.academicgroup.com.au/generated/30RPURGZEGWTH75_AA+ECONOMICS+Study+Guide+YR12_ScaleWithin_500x0_noup.png";
                    }
                ?>
                <img src="<?php echo $img_url;?>" alt="">
            </figure>
        </a>
        <div class="pwi-detail">
            <?php
            $product_name = strlen($product->get_name())>50 ? substr($product->get_name(),0,50).".." : $product->get_name();
            ?>
            <a href="<?php echo $product->get_permalink();?>">
                <h6 title="<?php echo $product->get_name();?>"><?php echo $product_name;?></h6>
            </a>
            <?php
            $author = get_post_meta(get_the_id(),"author",true);
            if($author){
                $author_name = strlen($author)>20 ? substr($author,0,20).".." : $author;
                ?>
                <p title="<?php echo $author;?>">by <?php echo $author_name;?></p>
                <?php
            }
            ?>
            <p class="pwi-price"><?php echo $product->get_price_html();?></p>
            <?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
             <!-- <a href="<?php echo home_url('purchase-books/?add-to-cart='.$product->get_id().'');?>" class="btn btn-blue"><i class="fa fa-shopping-cart"> </i> Add to Cart</a>  -->
        </div>
    </div>
</div>