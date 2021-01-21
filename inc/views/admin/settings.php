 <form method="post" action="options.php">
    <?php settings_fields( 'ag-api-settings-group' ); ?>
    <?php do_settings_sections( 'ag-api-settings-group' ); ?>
    <h3>API Settings</h3>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">API URL :</th>
            <td>
                <input style="width: 50%" class="option-input" type="text" name="api_url" value="<?php echo get_option('api_url'); ?>" />
            </td>
        </tr>
        <tr>
            <th scope="row">API Environment :</th>
            <td>
                <input style="width: 50%" class="option-input" type="text" name="api_environment" value="<?php echo get_option('api_environment'); ?>" />
            </td>
        </tr>
        <tr>
            <th scope="row">Admin Username :</th>
            <td>
                <input style="width: 50%" class="option-input" type="text" name="admin_username" value="<?php echo get_option('admin_username'); ?>" />
            </td>
        </tr>
        <tr>
            <th scope="row">Password :</th>
            <td>
                <input style="width: 50%" class="option-input" type="text" name="admin_password" value="<?php echo get_option('admin_password'); ?>" />
            </td>
        </tr>

        <tr>
            <th scope="row">Emailaddress :</th>
            <td>
                <input style="width: 50%" class="option-input" type="text" name="ag_email_address" placeholder="Email for sending error log" value="<?php echo get_option('ag_email_address'); ?>" />
            </td>
        </tr>

        <tr>
            <th scope="row">Log ClearOut Duration :</th>
            <td>
                <input style="width: 50%" class="option-input" type="number" name="ag_clearout_duration" value="<?php echo get_option('ag_clearout_duration'); ?>" />
            </td>
        </tr>

    </table>
        <?php submit_button(); ?>
</form>

 <h3>AG Products Settings</h3>

    <table class="form-table">
        <tr valign="top">
            <th scope="row">Default Publication Thumbnail</th>
            <td>
                <form method="post" action="options.php">
                    <?php settings_fields( 'ag-api-settings-group_publication_thumbnail' ); ?>
                    <?php do_settings_sections( 'ag-api-settings-group_publication_thumbnail' ); ?>
                        <img style="height: 40px;width: 40px" src="<?php echo get_option('ag_defualt_publication_thumbnail'); ?>">
                        <input style="display:none" class="ag_defualt_publication_thumbnail_url" type="text" name="ag_defualt_publication_thumbnail" size="60" value="<?php echo get_option('ag_defualt_publication_thumbnail'); ?>">
                        <a href="#" class="ag_default_publication_btn button">Upload</a>
                        <script>
                                  jQuery(document).ready(function($) {
                                      $('.ag_default_publication_btn').click(function(e) {
                                          e.preventDefault();

                                          var custom_uploader = wp.media({
                                              title: 'Custom Csv',
                                              button: {
                                                  text: 'Upload Image'
                                              },
                                              multiple: false  // Set this to true to allow multiple files to be selected
                                          })
                                          .on('select', function() {
                                              var attachment = custom_uploader.state().get('selection').first().toJSON();
                                              $('.ag_defualt_publication_thumbnail').attr('src', attachment.url);
                                              $('.ag_defualt_publication_thumbnail_url').val(attachment.url);

                                          })
                                          .open();
                                      });
                                  });
                        </script>
                        <?php submit_button(); ?>
                  </form>
            </td>
        </tr>
    </table>

    <table class="form-table">
        <tr valign="top">
            <th scope="row">Default Course Thumbnail</th>
            <td>
                <form method="post" action="options.php">
                    <?php settings_fields( 'ag-api-settings-group_course_thumbnail' ); ?>
                    <?php do_settings_sections( 'ag-api-settings-group_course_thumbnail' ); ?>
                        <img style="height: 40px;width: 40px" src="<?php echo get_option('ag_defualt_course_thumbnail'); ?>">
                        <input style="display:none" class="ag_defualt_course_thumbnail_url" type="text" name="ag_defualt_course_thumbnail" size="60" value="<?php echo get_option('ag_defualt_course_thumbnail'); ?>">
                        <a href="#" class="ag_default_course_btn button">Upload</a>
                        <script>
                                  jQuery(document).ready(function($) {
                                      $('.ag_default_course_btn').click(function(e) {
                                          e.preventDefault();

                                          var custom_uploader = wp.media({
                                              title: 'Custom Csv',
                                              button: {
                                                  text: 'Upload Image'
                                              },
                                              multiple: false  // Set this to true to allow multiple files to be selected
                                          })
                                          .on('select', function() {
                                              var attachment = custom_uploader.state().get('selection').first().toJSON();
                                              $('.ag_defualt_course_thumbnail').attr('src', attachment.url);
                                              $('.ag_defualt_course_thumbnail_url').val(attachment.url);

                                          })
                                          .open();
                                      });
                                  });
                        </script>
                        <?php submit_button(); ?>
                  </form>
            </td>
        </tr>
    </table>

    <table class="form-table">
        <tr valign="top">
            <th scope="row">Default Exampaper Thumbnail</th>
            <td>
                <form method="post" action="options.php">
                    <?php settings_fields( 'ag-api-settings-group_exampaper_thumbnail' ); ?>
                    <?php do_settings_sections( 'ag-api-settings-group_exampaper_thumbnail' ); ?>
                        <img style="height: 40px;width: 40px" src="<?php echo get_option('ag_defualt_exampaper_thumbnail'); ?>">
                        <input style="display:none" class="ag_defualt_exampaper_thumbnail_url" type="text" name="ag_defualt_exampaper_thumbnail" size="60" value="<?php echo get_option('ag_defualt_exampaper_thumbnail'); ?>">
                        <a href="#" class="ag_default_exampaper_btn button">Upload</a>
                        <script>
                                  jQuery(document).ready(function($) {
                                      $('.ag_default_exampaper_btn').click(function(e) {
                                          e.preventDefault();

                                          var custom_uploader = wp.media({
                                              title: 'Custom Csv',
                                              button: {
                                                  text: 'Upload Image'
                                              },
                                              multiple: false  // Set this to true to allow multiple files to be selected
                                          })
                                          .on('select', function() {
                                              var attachment = custom_uploader.state().get('selection').first().toJSON();
                                              $('.ag_defualt_exampaper_thumbnail').attr('src', attachment.url);
                                              $('.ag_defualt_exampaper_thumbnail_url').val(attachment.url);

                                          })
                                          .open();
                                      });
                                  });
                        </script>
                        <?php submit_button(); ?>
                  </form>
            </td>
        </tr>
    </table>
