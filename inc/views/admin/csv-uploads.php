<style>

  .button-manual{
    background-color: #3471eb;
    color: #fff;
    border: none;
    margin-left: 215px;
    padding: 10px;
    border-radius: 3px; 
    margin-top: 0px; 

  }

.btn-upload{

  margin-top: 35px;
  margin-left: 70px;
}
</style>
<?php 


 ?>
<div style="margin-left: 40px">
        <h2 align="center">Publication's Weight & Dimension</h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Weight & Dimension CSV File</th>
                <td>
                  <form method="post" action="options.php">
                    <?php settings_fields( 'ag-api-settings-group-csv_uploads' ); ?>
                    <?php do_settings_sections( 'ag-api-settings-group-csv_uploads' ); ?>
                            <label>Filename : <?php echo get_option('dimensions_weight_csv_file'); ?></label>
                            <input class="dimensions_weight_csv_file_url" type="file" accept=".csv" name="dimensions_weight_csv_file" size="60" value="<?php echo get_option('dimensions_weight_csv_file'); ?>">
                            <!-- <div class="btn-upload">
                                <a href="#" class="ag_csv_upload_file_btn" style="border: 2px solid #457ded; padding: 4px; border-radius: 3px; text-decoration:none; color: #6d8efc; margin-top: 30px;">Upload</a>
                            </div> -->
                            <a href="<?php echo home_url(); ?>/wp-content/uploads/2020/08/sample.csv" class="sample_csv" target="_details">View CSV Sample</a>
                            <script>
                                      jQuery(document).ready(function($) {
                                        //jQuery('.sample_csv').hide();
                                          /*$('.ag_csv_upload_file_btn').click(function(e) {
                                              e.preventDefault();

                                              var custom_uploader = wp.media({
                                                  title: 'Custom Csv',
                                                  button: {
                                                      text: 'Upload Csv'
                                                  },
                                                  multiple: false  // Set this to true to allow multiple files to be selected
                                              })
                                              .on('select', function() {
                                                  var attachment = custom_uploader.state().get('selection').first().toJSON();
                                                  $('.dimensions_weight_csv_file').attr('src', attachment.url);
                                                  $('.dimensions_weight_csv_file_url').val(attachment.url);

                                              })
                                              .open();
                                          });*/
                                      });
                            </script>
                            <?php submit_button(); ?>
                      </form>
                </td>
            </tr>
            <tr>
               <td>
                  <button class="button button-primary" onclick="ag_sync_all_dimension_weight_from_csv()">Sync Dimension & Weight</button>
                </td>
            </tr>
        </table>
                

          
</div>

<?php
      $nonce2 = wp_create_nonce("ag_sync_all_dimension_weight");
      $link = admin_url('admin-ajax.php');
?>

<script type="text/javascript">
    function ag_sync_all_dimension_weight_from_csv(){
      /*var selectedText = jQuery(".dimensions_weight_csv_file_url").val();
      console.log(selectedText);

          var extension = selectedText.split('.');
          
          if (extension[1] != "csv") {
            jQuery('.sample_csv').show();
              //$("#IdofTheTextBoxUpload").focus();
              alert("Please choose a .csv file");
              return;
          } */
        // jQuery("#loader_image").show();
              jQuery.ajax({
                  type : "post",
                  dataType : "json",
                  data : {action: "ag_sync_all_dimens_weight", nonce: '<?php echo $nonce2;?>'},
                  url : '<?php echo $link?>',
                  success: function(response) {
                    if(response.status==true){
                      alert("dimension and weight synced successfully");
                    }else{
                      alert("Synchronization failed")
                    }
                  }
              });
  }
</script>