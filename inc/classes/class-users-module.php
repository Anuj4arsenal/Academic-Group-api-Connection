<?php
class Users_module extends AG_utility{
    public $apiObj;
    public function __construct(){
        $this->apiObj = new AG_API_Connector();

    }

    function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }
    // get currently logged in user data
    public function get_current_user(){
        $logged_in_data = array();
        if(!empty(wp_get_current_user())){
            $wp_user = wp_get_current_user();
            if($wp_user->data && !empty($wp_user->data)){
                $logged_in_data["user_id"] = $wp_user->data->ID;
                $logged_in_data["user_login"] = $wp_user->data->user_login;
                $logged_in_data["user_email"] = $wp_user->data->user_email;
                $logged_in_data["display_name"] = $wp_user->data->display_name;
            }
            if($wp_user->roles && !empty($wp_user->roles)){
                $logged_in_data["role_slug"] = $wp_user->roles[0];
            }
        }
        return $logged_in_data;
    }

    // get user role by email
    public function get_user_role($email){
        $user_role = 0;
        $wp_user = get_user_by( "email", $email );
        if($wp_user->roles && !empty($wp_user->roles)){
            $user_role = $wp_user->roles[0];
        }
        return $user_role;
    }

    public function get_user_details_from_ag($email){
            $data = array (
                'queryType' => 'All',
                'environment' => 'TEST',
                'queryParams' =>
                    array (
                        'Details' => $email,
                    ),
                'assocs' =>
                    array (
                        0 => 'BillingAddress',
                    ),
            );
            return $this->apiObj->api_request('svc/Guardians','POST',$data);
    }
    public function is_ag_admin($cookie){
            $data = array (
                'environment' => 'TEST',
            );
            $ad_data = $this->apiObj->api_request('svc/EnvironmentInformation','POST',$data,$cookie);
            $roles = $ad_data->Privileges;
            return in_array('AT_isAdmin',$roles);
    }

    public function get_guardian_user_details($email){
            $return_data = false;
            $data = array (
                'queryType' => 'All',
                'environment' => 'TEST',
                'queryParams' =>array (
                                    'Details' => $email
                                ),
                'assocs' =>array (
                                "BillingAddress",
                                "HomeAddress"
                            )
            );
            $response = $this->apiObj->api_request('svc/Guardians','POST',$data);
            $guardian_detail = new stdClass();
            if(isset($response->results) && isset($response->result) && isset($response->references)){
                $detail_obj_name = $response->results[0];
                $guardian_obj = $response->references->{$detail_obj_name};

                $billing_detail_obj_name = $guardian_obj->BillingAddress;
                $billing_detail_obj = $response->references->{$billing_detail_obj_name};

                $guardian_detail->firstname = $guardian_obj->FirstName;
                $guardian_detail->lastname = $guardian_obj->LastName;
                $guardian_detail->email = $guardian_obj->Email;
                $guardian_detail->phone = $guardian_obj->Phone;

                $guardian_detail->billing_country = $billing_detail_obj->Country->Name;
                $guardian_detail->address = $billing_detail_obj->Address;

                $return_data = $guardian_detail;
            }
        return $return_data;
    }

    public function get_organization_details_from_ag($email){
        $data = array (
            'queryType' => 'All',
            'environment' => 'TEST',
            'queryParams' =>
                array (
                    'Details' => $email,
                ),
            'assocs' =>
                array (
                    0 => 'PhysicalAddSuburb',
                    1 => 'OrganisationContacts',
                ),
        );
        return $this->apiObj->api_request('svc/Organisations','POST',$data);
    }

    public function get_organization_user_details($email){
            $return_data = false;
             $data = array (
                    'queryType' => 'All',
                    'environment' => 'TEST',
                    'queryParams' =>
                        array (
                            'Details' => $email,
                        ),
                    'assocs' =>
                        array (
                            0 => 'PhysicalAddSuburb',
                            1 => 'OrganisationContacts',
                        ),
                );
            $response = $this->apiObj->api_request('svc/Organisations','POST',$data);
            $org_details = new stdClass();
            if(isset($response->results) && isset($response->result) && isset($response->references)){
                $detail_obj_name = $response->results[0];
                $org_detail_obj = $response->references->{$detail_obj_name};

                $billing_detail_obj_name = $org_detail_obj->BillingAddress;
                $billing_detail_obj = $response->references->{$billing_detail_obj_name};

                $org_details->firstname = $org_detail_obj->Name;
                $org_details->email = $org_detail_obj->Email;
                $org_details->phone = $org_detail_obj->Phone;

                $return_data = $org_details;
            }
        return $return_data;
    }

    public function get_childrens_of_current_user_from_ag(){
            $return_data = false;
            if(is_user_logged_in()){
                global $current_user;
                get_currentuserinfo();
                $email = (string) $current_user->user_email;
                $data = array (
                    'queryType' => 'All',
                    'environment' => 'TEST',
                    'queryParams' =>
                        array (
                            'Details' => $email,
                        ),
                    'assocs' =>
                        array (
                            'BillingAddress',
                            'GuardianStudentLinks.Student',
                            'GuardianStudentLinks'
                        ),
                );
                $return_data = $this->apiObj->api_request('svc/Guardians','POST',$data);
                // echo "<pre>";
                // print_r($return_data);

                $stds_objs_list = array();
                if(isset($return_data->result) && isset($return_data->references) && isset($return_data->results)){
                    if($return_data->result=="ok"){
                        $parent_obj_name = $return_data->results[0];
                        $parent_obj = $return_data->references->{$parent_obj_name};
                        $stds_obj_names_list = $parent_obj->GuardianStudentLinks;
                        if($stds_obj_names_list){
                            foreach($stds_obj_names_list as $std_obj_name){
                                $std_main_obj_name = $return_data->references->{$std_obj_name}->Student;
                                $std_obj = $return_data->references->{$std_main_obj_name};
                                $stds_objs_list[$std_main_obj_name] = $std_obj;
//                            array_push($stds_objs_list,$std_obj);
                            }
                        }

                        $return_data = $stds_objs_list;
                    }
                }
            }
            return $return_data;

    }
    public function handle_ag_login($username,$password,$cookie,$user_details,$type){
        $data["username"] = $username;
        $data["password"] = $password;
        $result = $user_details->results[0];
        $ag_details = $user_details->references->{$result};
        $user = get_user_by("email",$username);
        if($user){
            $user_id = $user->ID;
            wp_set_password($password,$user_id);
        }
        else{
            $user_data = array(
                'ID' => '',
                'user_pass' => $password,
                'user_login' => $username,
                'user_nicename' => $username,
                'user_url' => '',
                'user_email' => $username,
                'display_name' => $type== 'user' ? $ag_details->FirstName : $ag_details->Name,
                'nickname' => $type== 'user' ? $ag_details->FirstName : $ag_details->Name,
                'first_name' => $type== 'user' ? $ag_details->FirstName : $ag_details->Name,
                'last_name' => $type== 'user' ? $ag_details->FirstName : '',
                'role' => $type == 'user' ? 'AG_'.$ag_details->GuardianType->Value : 'AG_'.$ag_details->OrganisationType->Value
            );
            $user_id = wp_insert_user( $user_data );
        }
        update_user_meta($user_id,'ag_cookie',$cookie);

        //for address settings
        $details = [];
        if($type == 'user'){
            $details = $this->get_guardian_user_details($username);
        }
        else if($type == 'org'){
            $details = $this->get_organization_user_details($username);
        }
        $this->setAddressMeta($user_id,$details);

        return $user_id;
    }
    public function setAddressMeta($user_id,$user_details){
        if($user_details) {
            if (metadata_exists('user', $user_id, 'user_details')) {
                update_user_meta($user_id, 'user_details', json_encode($user_details));

                update_user_meta($user_id, 'billing_first_name', $user_details->firstname, true);
                update_user_meta($user_id, 'billing_last_name', $user_details->lastname, true);
                update_user_meta($user_id, 'billing_email', $user_details->email, true);
                update_user_meta($user_id, 'billing_phone', $user_details->phone, true);
                update_user_meta($user_id, 'billing_country', $user_details->billing_country, true);
                update_user_meta($user_id, 'billing_address_1', $user_details->address, true);
            } else {
                add_user_meta($user_id, 'user_details', json_encode($user_details), true);

                //billing details
                add_user_meta($user_id, 'billing_first_name', $user_details->firstname, true);
                add_user_meta($user_id, 'billing_last_name', $user_details->lastname, true);
                add_user_meta($user_id, 'billing_email', $user_details->email, true);
                add_user_meta($user_id, 'billing_phone', $user_details->phone, true);
                add_user_meta($user_id, 'billing_country', $user_details->billing_country, true);
                add_user_meta($user_id, 'billing_address_1', $user_details->address, true);
            }
        }
    }
    public function create_ag_admin($username,$password,$cookie){
        $data["username"] = $username;
        $data["password"] = $password;
        $user = get_user_by("email",$username);
        if($user){
            $user_id = $user->ID;
            wp_set_password($password,$user_id);
        }
        else{
            $user_data = array(
                'ID' => '',
                'user_pass' => $password,
                'user_login' => $username,
                'user_nicename' => $username,
                'user_url' => '',
                'user_email' => $username,
                'role' => 'administrator'
            );
            $user_id = wp_insert_user( $user_data );
        }
        update_user_meta($user_id,'ag_cookie',$cookie);
        return $user_id;
    }


    private function check_if_password_valid(){
        $message = true;
        if($_POST["ConfirmPassword"] != $_POST["Password"]){
            $message = "Passwords doesnt meet";
        }
        else{
            if(strlen($_POST["Password"])<8){
                $message = "Password must at least 8 characters";
            }
        }
        return $message;
    }
}

?>
