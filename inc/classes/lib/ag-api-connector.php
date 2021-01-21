<?php

class AG_API_Connector extends  AG_utility {

    public function __construct(){
        // echo "api connected";
    }

    public function api_request($request_endpoint,$request_type = "GET",$data=array(),$cookie = ''){
        $headers = array(
            'Content-Type: application/json',
            'Accept' => 'application/json',
        );
        $data["environment"] = get_option('api_environment');

        if($request_type == "GET" && !empty($data)){
            $query = http_build_query($data, '', '&');
            $url = get_option('api_url').$request_endpoint."?".$query."";
        }else{
            $url = get_option('api_url').$request_endpoint."";
        }
        $ccurl = curl_init($url);

        if($request_type == "POST"){
            curl_setopt($ccurl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        //send login cookie if user is already login
        if($cookie == '') {
            $user = wp_get_current_user();
            $user_id = $user->ID;
            $cookie = get_user_meta($user_id, 'ag_cookie', true);
        }
        if($cookie){
            curl_setopt($ccurl, CURLOPT_COOKIE, "JSESSIONID=".$cookie);
        }
        curl_setopt($ccurl, CURLOPT_CUSTOMREQUEST, $request_type);
        curl_setopt($ccurl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ccurl, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt($ccurl, CURLOPT_RETURNTRANSFER, 1 );
        $result = curl_exec($ccurl);
        $httpcode = curl_getinfo($ccurl, CURLINFO_HTTP_CODE);
        curl_close($ccurl);
        $decoded_result = json_decode($result);
        if($decoded_result->result != 'ok'){
            $this->sent_email_error_log('Error Url '.$url.' <br> Error Response : '.$result);
        }
        $this->write_cron_req_res_log($url,json_encode($data),$result);

        return $decoded_result;
    }

    public function login($username,$password)
    {
        $headers = array(
            'Content-Type: application/json',
            'Accept' => 'application/json',
        );
        $data = ['environment' => 'TEST', 'username' => $username, 'password' => $password];
        $ccurl = curl_init(get_option('api_url').'svc/Login');
        curl_setopt($ccurl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ccurl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ccurl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ccurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ccurl, CURLOPT_HEADER, 1);
        curl_setopt($ccurl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ccurl);
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi',
            $result, $match_found);
        $cookies = array();
        foreach ($match_found[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        curl_close($ccurl);
        $body = explode("\r\n\r\n", $result, 2);
        $decoded_result = json_decode($body[1]);
        if($decoded_result->result != 'ok'){
            $this->sent_email_error_log('Error Url : Login url <br> Error Response : '.$result);
        }
        if(isset($decoded_result->loggedin) && $decoded_result->loggedin == 1) {
            return $cookies['JSESSIONID'];
        }
        else{
            return '';
        }

    }

    public function download_image_from_ag($url,$jsessionid=null)
    {
        $headers = array(
            'Content-Type: application/json',
            'Accept' => 'application/json',
        );
        $user = wp_get_current_user();
        $user_id = $user->ID;

        //for cron requests
        if(!is_null($jsessionid)){
            $cookie = $jsessionid;
        }

        //for wp ajax requests
        else{
            $cookie = get_user_meta($user_id,'ag_cookie',true);
        }

        $ccurl = curl_init($url);
        if($cookie){
            curl_setopt($ccurl, CURLOPT_COOKIE, "JSESSIONID=".$cookie);
        }
        curl_setopt($ccurl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ccurl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ccurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ccurl, CURLOPT_HEADER, 0);
        curl_setopt($ccurl, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ccurl);
        return $result;
    }
    public function sent_email_error_log($message){
        //$to = 'bikash@professionalwebsolutions.com.au';
        $to = get_option('ag_email_address');
        $subject = 'Error Log from Academic Group  '.home_url();
        $headers = 'Content-Type: text/html; charset=UTF-8';
        $send_email = wp_mail( $to, $subject, $message, $headers );
    }
}

?>
