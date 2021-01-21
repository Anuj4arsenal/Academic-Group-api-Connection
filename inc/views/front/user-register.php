<?php

    global $woocommerce;
    $countries_obj   = new WC_Countries();
    $countries   = $countries_obj->__get('countries');
    //$default_country = $countries_obj->get_base_country();
    //$default_county_states = $countries_obj->get_states( $default_country );
   //print_r($states  );
?>


<div id="content" class="site-content">
    <div style="padding:20px 0;">
    <main class="my-form">
        <div class="cotainer">
            <div class="row justify-content-center" style="display: flex;justify-content:center">
                <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <form action="" method="post">

                                    <div class="form-group row">
                                        <label for="email" class="col-md-4 col-form-label text-md-right">E-Mail Address/Username *</label>
                                        <div class="col-md-6">
                                            <input type="email" id="__billing_email" class="form-control" name="email" required="">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="firstname" class="col-md-4 col-form-label text-md-right">First Name *</label>
                                        <div class="col-md-6">
                                            <input type="text" id="billing_first_name" class="form-control" name="firstname" required="">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="lastname" class="col-md-4 col-form-label text-md-right">last Name</label>
                                        <div class="col-md-6">
                                            <input type="text" id="billing_last_name" class="form-control" name="lastname">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>
                                        <div class="col-md-6">
                                            <input type="password" id="account_password" name="password" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="password" class="col-md-4 col-form-label text-md-right">Confirm Password</label>
                                        <div class="col-md-6">
                                            <input type="password" id="account_password2" name="password" class="form-control">
                                        </div>
                                    </div>

                                     <div class="form-group row">
                                        <label for="phone_number" class="col-md-4 col-form-label text-md-right">Country *</label>
                                        <div class="col-md-6">
                                            <select id="billing_country" name="phone_number" class="form-control">
                                            <option value="select_country" selected="">Select Country</option>
                                                <?php
                                                    foreach($countries as $key=>$country){
                                                        ?>
                                                        <option value="<?php echo $key;?>"><?php echo $country;?></option>
                                                        <?php
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="phone_number" class="col-md-4 col-form-label text-md-right">Address *</label>
                                        <div class="col-md-6">
                                            <input type="text" id="billing_address_1" name="phone_number" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="phone_number" class="col-md-4 col-form-label text-md-right">Suburb *</label>
                                        <div class="col-md-6">
                                            <input type="text" id="suburb_address_1" name="phone_number" class="form-control">
                                        </div>
                                    </div>

                                  <!-- <div class="form-group row">
                                        <label for="phone_number" class="col-md-4 col-form-label text-md-right">States *</label>
                                        <div class="col-md-6">
                                            <select id="billing_state" name="phone_number" class="form-control">
                                            <option value="select_state" selected="">Select State</option>
                                                <?php
                                                    foreach($default_county_states as $key=>$state){
                                                        ?>
                                                        <option value="<?php echo $key;?>"><?php echo $state;?></option>
                                                        <?php
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>  -->

                                    <div class="form-group row">
                                        <label for="phone_number" class="col-md-4 col-form-label text-md-right">Postal Code *</label>
                                        <div class="col-md-6">
                                            <input type="text" id="billing_postcode" name="phone_number" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="phone_number" class="col-md-4 col-form-label text-md-right">Phone Number</label>
                                        <div class="col-md-6">
                                            <input type="number" id="billing_phone" name="phone_number" class="form-control">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                        </div>
                                        <div class="col-md-3">
                                            <button type="button" onclick="register_new_user_to_ag_frm_checkout()" type="button" class="btn btn-primary" value="Register">
                                                Register
                                                <i id="register_processing_spinner" style="display: none"  class="fa fa-spinner fa-spin"></i>
                                            </button>
                                        </div>
                                    </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                </div>
            </div>
        </div>

    </main>

</div>

<?php
$nonce = wp_create_nonce("create_new_guardian_to_ag");
$link = admin_url('admin-ajax.php');
?>
<script>
    function register_new_user_to_ag_frm_checkout(){
            // let username = jQuery("#account_username").val();
            let pw = jQuery("#account_password").val();
            let confirm_pw = jQuery("#account_password2").val();
            let email = jQuery("#__billing_email").val();
            let fn = jQuery("#billing_first_name").val();
            let ln = jQuery("#billing_last_name").val();
            let country = jQuery("#billing_country").val();
            //let billing_state = jQuery("#billing_state").val();
            let address = jQuery("#billing_address_1").val();
            let sub_address = jQuery("#suburb_address_1").val();
            // let city = jQuery("#billing_city").val();
            // let state = jQuery("#billing_state").val();
            let postal_code = jQuery("#billing_postcode").val();
            let phone = jQuery("#billing_phone").val();

            if(pw =="" || confirm_pw =="" || email =="" || fn =="" || ln =="" || country == "" || address == "" || postal_code =="" || phone ==""){
                alert("Please fill all the required fields !");
                return false;
            }
            if(!isEmail(email)){
                alert("Invalid Email Address !");
                return false;
            }

            if(pw !=confirm_pw){
                alert("Password and Confirm Password doesn’t match !");
                return false;
            }
            jQuery("#register_processing_spinner").show();
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
                    console.log(response);
                    jQuery("#register_processing_spinner").hide();
                    if(response.status==true){
                        alert("User registerd successfully. Please Login !");
                        window.location.replace("<?php echo home_url();?>");
                    }
                    else{
                        if(response.message){
                            alert(response.message);
                        }
                        else{
                            alert("Something went wrong !");
                        }
                    }
                }
            });

            function isEmail(email) {
                var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                return regex.test(email);
            }

        }
</script>


<script>
    //stop from multiple submission on reload the tab
    if ( window.history.replaceState ) {
      window.history.replaceState( null, null, window.location.href );
    }
</script>