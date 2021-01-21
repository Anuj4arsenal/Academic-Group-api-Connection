<?php
$log_id = $_GET['log_id']; //get id of current log
$log_datetime = $_GET['log_datetime']; //get log datetime of current log

/*query to show current log details form wp db*/
global $wpdb;
$table_name = $wpdb->prefix . 'log_manager';
$log_data = $wpdb->get_row("SELECT * FROM $table_name WHERE id = '".$log_id."'");

//read file of current log 
$dir = get_home_path().'api_logs/' .$log_datetime. '_cron_req_res.txt';
$file_data = file_get_contents($dir);


//retrigger log
if(isset($_POST['re-trigger'])){
	$data = new Program_module();
    $data->re_trigger($log_data->url, $log_data->request);
} 

?>
<style type="text/css">
	
.request-response{
	display: flex;
	justify-content: space-between;
	margin-top: 30px;
}
.request-box{
	border: 1px solid grey;
	height: 200px;
	width: 500px;
	background-color: #fff;
	padding: 5px;
}

.full-response{
	margin-top: 30px;
}

</style>
<form method="post" accept="">
 	<button  class="primary button-primary pull-right" name="re-trigger" style="margin: 10px 5px 10px 10px;">RETRIGGER</button>
</form>   
<div class="container">
	<div class="row">
  		<div class="col-md-12">
			<table class="wp-list-table widefat fixed striped posts table table-bordered" style="background-color: #fff;">
	    		<thead>
	      			<tr>
	        			<th>DATETIME</th>
	        			<th><?php echo $log_data->datetime; ?></th>
	      			</tr>
	    		</thead>
	    		<tbody>
	      			<tr>
	        			<td>URL</td>
	        			<td><?php echo $log_data->url; ?></td>
	      			</tr>
	      			<tr>
	        			<td>Logged In User</td>
	        			<td><?php echo $log_data->logged_in_user ?></td>
	      			</tr>
	    		</tbody>
	  		</table>
  		</div>
	</div>
</div>
<div class="request-response">
	<div class="request">
	   	<strong>REQUEST</strong>
	   	<div class="request-box"><?php echo $log_data->request; ?></div>
	</div>
	<div class="response">
	   	<strong>RESPONSE</strong>
	   	<div class="request-box"><?php echo $log_data->response; ?></div>
	</div>
</div>
<div class="full-response">
	<div class="col-xs-12">
		<textarea style="width: 80%;height: 70vh"><?php echo $file_data; ?></textarea>
	</div>
</div>

	