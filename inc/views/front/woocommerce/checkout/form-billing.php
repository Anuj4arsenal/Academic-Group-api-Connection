<?php
/**
 * Checkout billing information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-billing.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;
?>


<div class="woocommerce-billing-fields temp-form">
    <?php
/**
 * Checkout billing information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-billing.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/** @global WC_Checkout $checkout */

?>
<div class="woocommerce-billing-fields">
    <?php if ( wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ) : ?>

        <h3><?php _e( 'Parent Details', 'woocommerce' ); ?></h3>

    <?php else : ?>

        <h3><?php _e( 'Parent Details', 'woocommerce' ); ?></h3>

    <?php endif; ?>

    <?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

    <div class="woocommerce-billing-fields__field-wrapper">
        <?php
            $fields = $checkout->get_checkout_fields( 'billing' );

            foreach ( $fields as $key => $field ) {
                if ( isset( $field['country_field'], $fields[ $field['country_field'] ] ) ) {
                    $field['country'] = $checkout->get_value( $field['country_field'] );
                }
                woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
            }
        ?>
    </div>

    <?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
</div>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
    <div class="woocommerce-account-fields">
        <?php if ( ! $checkout->is_registration_required() ) : ?>

            <p class="form-row form-row-wide create-account">
                <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                    <input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ) ?> type="checkbox" name="createaccount" value="1" /> <span><?php _e( 'Create an account?', 'woocommerce' ); ?></span>
                </label>
            </p>

        <?php endif; ?>

        <?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

        <?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>

            <div class="create-account">
                <?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
                    <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
                <?php endforeach; ?>
                <div class="clear"></div>
            </div>

        <?php endif; ?>

        <?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
    </div>
<?php endif; ?>



</div>
<?php
    // $nonce = wp_create_nonce("create_new_guardian_to_ag");
    // $link = admin_url('admin-ajax.php');
?>
<script>
    /*function register_new_user_to_ag_frm_checkout(){
        if(jQuery('#createaccount').is(':checked')){
                let username = jQuery("#account_username").val();
                let pw = jQuery("#account_password").val();
                let confirm_pw = jQuery("#account_password2").val();
                let email = jQuery("#billing_email").val();
                let fn = jQuery("#billing_first_name").val();
                let ln = jQuery("#billing_last_name").val();
                let country = jQuery("#billing_country").val();
                let address = jQuery("#billing_address_1").val();
                // let city = jQuery("#billing_city").val();
                 //let state = jQuery("#billing_state").val();
                let postal_code = jQuery("#billing_postcode").val();
                let phone = jQuery("#billing_phone").val();

                if(pw =="" || confirm_pw =="" || email =="" || fn =="" || ln =="" || country == "" || address == "" || postal_code =="" || phone==""){
                    alert("please fill all the required fields");
                    return false;
                }
                if(!isEmail(email)){
                    alert("Invalid Email Format !");
                    return false;
                }

                if(pw !=confirm_pw){
                    alert("Password and Confirm Password doesn’t match !");
                    return false;
                }
                jQuery("#save_reg_btn_loader").show();
                jQuery.ajax({
                    type : "post",
                    dataType : "json",
                    data : {
                        action: "register_new_guardian_to_ag",
                        nonce: '<?php echo $nonce;?>',
                        GuardianFirstName:fn,
                        GuardianLastName:ln,
                        GuardianHomePhone:phone,
                        GuardianMobile:phone,
                        GuardianEmail:email,
                        StreetAddress:address,
                        StreetCountry:country,
                        PostalAddress:postal_code,
                        PostalCountry:country,
                        Password:pw
                    },
                    url : '<?php echo $link?>',
                    success: function(response) {
                        //console.log(response.datas.PostalAddress);
                        
                        //console.log(JSON.stringify(response));
                        jQuery("#save_reg_btn_loader").hide();
                        if(response.status==true){
                            alert("Guardian created successfully !");
                            location.reload();
            
                        }
                        else{
                            if(response.message){
                                alert(response.message);
                            }
                            else{
                                alert("something went wrong");
                            }
                        }
                    }
                });

            function isEmail(email) {
                var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                return regex.test(email);
            }

        }
        else{
            alert("please select Create an account?");
        }
    }*/
</script>


