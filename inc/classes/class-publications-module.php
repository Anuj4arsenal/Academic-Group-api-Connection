<?php

class Publication_module extends AG_API_Connector {

    private $AG_utility;

	public function __construct(){
			parent::__construct();
			$this->AG_utility = new AG_utility();
	}

	public function get_all_categories(){
			$result = $this->api_request("svc/BookCategories","POST");
			$data = array();
			if($result->result=="ok" && !empty($result->results)){
				$data = $result->results;
			}
			return $data;
	}

	public function get_all_subcategories(){
			$result = $this->api_request("svc/BookSubCategories","POST");
			$data = array();
			if($result->result=="ok" && !empty($result->results)){
				$data = $result->results;
			}
			return $data;
	}

	public function get_all_publication_subjects(){
			$result = $this->api_request("svc/BookSubjects","POST");
			$data = array();
			if($result->result=="ok" && !empty($result->results)){
				$data = $result->results;
			}
			return $data;
	}

    public function get_all_publication_year_levels(){
        $result = $this->api_request("svc/YearLevels","POST");
        $data = array();
        if($result->result=="ok" && !empty($result->results)){
            $data = $result->results;
        }
        return $data;
    }

	public function get_publications($details="",$category="",$subcategory="",$year_level="",$book_subject="",$open_for_purchase=""){
			$data = $this->_map_publication_filters($details,$category,$subcategory,$year_level,$book_subject,$open_for_purchase);
			$data["queryType"]="All";
			$result = $this->api_request("svc/Publications","POST",$data);

            $this->AG_utility->write_cron_log("publication response", json_encode($result));

			$return_data = array();
			if($result->result=="ok" && !empty($result->results) && !empty($result->references)){
				$return_data["references"] = $result->references;
				$return_data["ids"] = $result->results;
			}
			return $return_data;
	}

	private function _map_publication_filters($details="",$category="",$subcategory="",$year_level="",$book_subject="",$open_for_purchase=""){
			$data=array();
			if(!empty($details)){
				$data["queryParams"]["Details"]=$details;
			}
			if(!empty($category)){
				$data["queryParams"]["Category"]=$category;
			}
			if(!empty($subcategory)){
				$data["queryParams"]["SubCategory"]=$subcategory;
			}
			if(!empty($year_level)){
				$data["queryParams"]["YearLevel"]=$year_level;
			}
			if(!empty($book_subject)){
				$data["queryParams"]["BookSubject"]=$book_subject;
			}
			if(!empty($open_for_purchase)){
				$data["queryParams"]["OpenForPurchase"]=$open_for_purchase;
			}
			return $data;
	}

	//get book details by id
	public function get_publications_details($pubication_ids){
			$data["queryParams"] = array("id"=>$pubication_ids);
			$data["queryType"] = "ByID";
			$result = $this->api_request("svc/Publications","POST",$data);
			$return_data = array();
			if($result->result=="ok" && !empty($result->results) && !empty($result->references)){
				$return_data["references"] = $result->references;
				$return_data["ids"] = $result->results;
			}
			return $return_data;
	}

	public function syn_all_dimension_weigt_from_csv(){
            $csv_file_url = get_option('dimensions_weight_csv_file');
            $attachment_id = attachment_url_to_postid($csv_file_url );
            $attachement_path = get_attached_file( $attachment_id );
            $csvFile = file($attachement_path);
            $data = [];
            foreach ($csvFile as $line) {
                $data[] = str_getcsv($line);
            }

            foreach($data as $d){
                $isbn = $d[0];
                global $wpdb;
                $tbl = $wpdb->prefix.'postmeta';
                $prepare_guery = $wpdb->prepare( "SELECT post_id FROM $tbl where meta_key ='isbn' and meta_value = %s", $isbn);
                $get_values = $wpdb->get_col( $prepare_guery );
                // print_r($get_values);
                if(!empty($get_values)){                    
                    $product_id = $get_values[0];
                    if(metadata_exists('post', $product_id, '_weight')){
                        update_post_meta( $product_id, "_weight", $d[5]/1000 );
                    }
                    else{
                        add_post_meta( $product_id, "_weight", $d[5],true );
                    }

                    if(metadata_exists('post', $product_id, '_length')){
                        update_post_meta( $product_id, "_length", $d[2]/10 );
                    }
                    else{
                        add_post_meta( $product_id, "_length", $d[2]/10,true );
                    }

                    if(metadata_exists('post', $product_id, '_width')){
                        update_post_meta( $product_id, "_width", $d[3]/10 );
                    }
                    else{
                        add_post_meta( $product_id, "_width", $d[3]/10,true );
                    }

                    if(metadata_exists('post', $product_id, '_height')){
                        update_post_meta( $product_id, "_height", $d[4]/10 );
                    }
                    else{
                        add_post_meta( $product_id, "_height", $d[4]/10,true );
                    }
                }
            }
            return true;
	}

}

?>