<?php

class Program_module extends AG_API_Connector{

        public function __construct(){
            parent::__construct();
        }

        public function get_all_course_types(){
                $result = $this->api_request("svc/CourseTypes","POST");
                $data = array();
                if($result->result=="ok" && !empty($result->results)){
                    $data = $result->results;
                }
                return $data;
        }

        public function get_terms(){
                $result = $this->api_request("svc/Terms","POST", array("queryType"=>"All"));
                $data = array();
                if($result->result=="ok" && !empty($result->references)){
                    $data = $result->references;
                }
                return $data;
        }

        public function get_venues(){
                $result = $this->api_request("svc/Venues","POST", array("queryType"=>"All"));
                $data = array();
                if($result->result=="ok" && !empty($result->references)){
                    $data = $result->references;
                }
                return $data;
        }

        public function get_subject_years(){
                $result = $this->api_request("svc/SubjectYears","POST", array("queryType"=>"All"));
                $data = array();
                if($result->result=="ok" && !empty($result->references)){
                    $data = $result->references;
                }
                return $data;
        }

        public function get_subjects(){
                $result = $this->api_request("svc/Subjects","POST", array("queryType"=>"All"));
                $data = array();
                if($result->result=="ok" && !empty($result->references)){
                    $data = $result->references;
                }
                return $data;
        }

        public function get_courses(){
                $result = $this->api_request("svc/Courses","POST", array("queryType"=>"All","assocs"=>array("Subject","Venue")));
                $return_data = array();
                if($result->result=="ok" && !empty($result->references)){
                    $return_data["references"] = $result->references;
                }
                return $return_data;
        }

        //student year levels    
        public function get_year_levels(){
                $result = $this->api_request("svc/SchoolYears","POST", array("onlyEnabled"=>"true"));
                $return_data = array();
                if($result->result=="ok"){
                    $return_data = $result->results;
                }
                return $return_data;
        }

        public function re_trigger($url, $request){
            $request_type = "POST";
            $request_endpoint = substr($url, strlen(get_option('api_url')));
            $data = json_decode($request, true);

            $result = $this->api_request($request_endpoint, $request_type, $data);

            return $result;
        }


}

?>