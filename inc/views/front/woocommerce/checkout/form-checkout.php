<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
    echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
    return;
}

?>



<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

    <?php if ( $checkout->get_checkout_fields() ) : ?>

        <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

        <div class="row" id="customer_details">
            <div class="col-sm-8">
                <?php do_action( 'woocommerce_checkout_billing' ); ?>

                <?php do_action( 'woocommerce_checkout_shipping' ); ?>
            </div>

            <div class="col-sm-4">
                <?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

                <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

                <div id="order_review" class="woocommerce-checkout-review-order">
                    <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                </div>

                <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
            </div>
        </div>


        <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

    <?php endif; ?>



</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>


<!-- ag api connection plugin manupulation -->
<?php
global $current_user;
$email = (string) $current_user->user_email;
?>
<script type="text/javascript">
    //to access all the student details field assigned to each courses items and book items dynamically
    var cart_courses = [];
    var cart_books = [];
</script>
<?php
global $woocommerce;
$items = $woocommerce->cart->get_cart();

foreach($items as $item => $values) {
    $id = $values["product_id"];
    $_product =  wc_get_product( $id);
    $post_terms = get_the_terms( $id, "ag_product_type" );
    foreach($post_terms as $term){
        if($term->slug == "ag-course-type"){
            $sku = $_product->get_sku();
            $SessionSubject = "ID:".substr($sku,4);
            ?>
            <script type="text/javascript">
                var course = [];
                course["wc_product_id"] = "<?php echo $id;?>";
                course["session_subject"] = "<?php echo $SessionSubject;?>";
                course["quantity"] = <?php echo $values['quantity'];?>;
                cart_courses[<?php echo $id;?>] = course;
            </script>
            <?php
        }
        else{
            $sku = $_product->get_sku();
            $ag_book_id = "ID:".substr($sku,4);

//                    year level is currently static since we are not getting the url of the publications, change this after we get the yearlevel from the api
//                    $year_level = get_post_meta($id,"year_level",true);
            $year_level = "Year_12";
            ?>
            <script type="text/javascript">
                var book = [];
                book["wc_product_id"] = "<?php echo $id;?>";
                book["book_id"] = "<?php echo $ag_book_id;?>";
                book["quantity"] = <?php echo $values['quantity'];?>;
                book["year_level"] = '<?php echo $year_level;?>';
                cart_books[<?php echo $id;?>] = book;
            </script>
            <?php

        }
        ?>

        <?php
    }
}
?>
<script type="text/javascript">
    console.log(cart_courses);
</script>



