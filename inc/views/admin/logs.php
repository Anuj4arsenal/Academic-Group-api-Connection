<?php
require_once(AG_PLUGIN_BASE_PATH.'inc/classes/ag-custom-log-table.php');
if(!is_dir(get_home_path() . 'api_logs/'))
mkdir(get_home_path() . 'api_logs/');
$dir = get_home_path().'/api_logs';
$list = scandir($dir,1);


?>

<button type="button" class="button button-primary trigger">Load New Logs</button>
<?php /*<h2>select log dates</h2>

<div style="display: flex;margin-bottom: 10px;">
<!-- <select name="ag_logs" id="ag-logs">
    <option selected disabled value="0">Select log date</option>
<?php foreach ($list as $l) {
    if(substr($l,0,1) != '.') {
        echo ' <option value="'.$l.'">'.$l.'</option>';
    }
}?>  -->

</select>
<div style="display:none;" class="loader"></div>
</div>

<div class="container">
        <div class="row">
            <div class="col-md-12">
                
            </div>
        </div>
    </div>
<div>
<!-- <textarea id="log-display" style="width: 80%;height: 70vh"></textarea> -->
</div> */ ?>

<script>
    

    jQuery('.trigger').on('click', function(){
        location.reload();
    });
    /*jQuery('#ag-logs').change(function(){
        jQuery('.loader').show();
        let fileName = this.value;
        var posting = jQuery.post('<?php echo home_url(); ?>/wp-admin/admin-ajax.php?action=get_ag_req_res_log',{file_name:fileName});
        // Put the results in a div
        posting.done(function( data ) {
            var r = jQuery.parseJSON(data);
            console.log(r);
            let res = data.data;
            jQuery('.loader').hide();
            jQuery('#log-display').val(res);
        });
    })*/
    /*jQuery(document).ready( function () {
        jQuery('#log-table').DataTable();
    });*/
    //jQuery('#log-table').DataTable();
    
    
</script>
<!-- <style>
    .loader {
        border: 5px solid #f3f3f3;
        border-radius: 50%;
        border-top: 5px solid #3498db;
        width: 20px;
        height: 20px;
        -webkit-animation: spin 2s linear infinite; /* Safari */
        animation: spin 2s linear infinite;
    }

    /* Safari */
    @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style> -->

<?php
$obj = new My_List_Table();
$obj->prepare_items();
$obj->display();

/*$pmc_fs_table = new My_List_Table(); 

$pmc_fs_table->prepare_items();

$pmc_fs_table->views();
echo '<form method="post">';    
echo ' <input type="hidden" name="page" value="pmc_fs_search">';
$pmc_fs_table->search_box( 'search', 'search_id' );
$pmc_fs_table->display();  
echo '</form></div>';*/