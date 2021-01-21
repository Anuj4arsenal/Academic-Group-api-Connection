<?php 
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	class My_List_Table extends WP_List_Table {

		function __construct(){
	        parent::__construct( array(
	            'ajax'      => false        //does this table support ajax?
		    ) );
		
	    }
		function get_data(){
			 global $wpdb;
            $table_name = $wpdb->prefix . 'log_manager';
            //$log_info = $wpdb->get_results("SELECT * FROM $table_name ORDER BY time DESC");
            $log_info = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC", ARRAY_A);
			
			return $log_info;
		}

		function get_columns(){
			$columns = array(
				'datetime' => 'Datetime',
				'url' => 'Url',
				'request' => 'Request',
				'logged_in_user' => 'Logged User',
				'status' => 'Status',
				'response' => 'Response Message',
				'trigger' => 'Action'
				
				);
				return $columns;
			}

			function prepare_items(){
				$columns = $this->get_columns();
				$hidden = array();
				$sortable =array();
				$this->_column_headers = array($columns, $hidden, $sortable);
				$this->items = $this->get_data();

				$data = $this->get_data();
				usort( $data, array( &$this, 'sort_data' ) );
				$perPage = 20;
				$currentPage = $this->get_pagenum();
				$totalItems = count($data);
				$sortable = $this->get_sortable_columns();
				$this->set_pagination_args( array(
				'total_items' => $totalItems,
				'per_page' => $perPage
				) );
				$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
				$this->_column_headers = array($columns, $hidden, $sortable);
				$this->items = $data;
			}

			function column_default( $item, $column_name ) {
				switch( $column_name ) {
				case 'datetime':
				case 'url':
				case 'request':
				case 'logged_in_user':
				case 'status':
				case 'response':
				case 'trigger':

				return $item[ $column_name ];
				default:
				return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
				}
			}
				 

		    function column_trigger($item) {
		    	if(is_admin())
				$actions = array(
				'trigger'      => sprintf('<a href="?page=%s&action=%s&log_id=%s&log_datetime=%s">View Details</a>', $_GET['page'], 'log-details', $item['id'], $item['datetime']),
				
				);
				return sprintf('%1$s %2$s', $item['trigger'], $this->row_actions($actions) );
				
			}

			function new_parse_ini($f)
				{

				    // if cannot open file, return false
				    if (!is_file($f))
				        return false;

				    $ini = file($f);

				    // to hold the categories, and within them the entries
				    $cats = array();

				    foreach ($ini as $i) {
				        if (@preg_match('/\[(.+)\]/', $i, $matches)) {
				            $last = $matches[1];
				        } elseif (@preg_match('/(.+)=(.+)/', $i, $matches)) {
				            $cats[$last][$matches[1]] = $matches[2];
				        }
				    }

				    

				    return $cats;

				}

		
	}


