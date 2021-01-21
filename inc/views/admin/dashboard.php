<div style="margin-left: 40px">
	<h2>Dashboard</h2>
	<button onclick="ag_sync_all_data_from_AG()" class="button button-primary buttonload">
        Synchronize
        <i id="save_reg_btn_loader" style="display: none"  class="fa fa-spinner fa-spin"></i>
    </button>
</div>


<?php
    $nonce = wp_create_nonce("ag_sync_all_data_from_AG");
    $link = admin_url('admin-ajax.php');
?>
<script type="text/javascript">
	function ag_sync_all_data_from_AG(){
				jQuery("#save_reg_btn_loader").show();
	            jQuery.ajax({
	                type : "post",
	                dataType : "json",
	                data : {action: "ag_sync_all_data", nonce: '<?php echo $nonce;?>'},
	                url : '<?php echo $link?>',
	                success: function(response) {
                        jQuery("#save_reg_btn_loader").hide();
	                	if(response.publications_sync_status && response.publications_sync_status.status &&response.publications_sync_status.status==true){
	                		alert(JSON.stringify(response.publications_sync_status));		
	                	}
	                	else{
	                		alert("sync failed");
	                	}
	                	// console.log(response.publications_sync_status.status);
	                }
	            });
	}
</script>