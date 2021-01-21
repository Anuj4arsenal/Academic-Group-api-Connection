<?php

if ( ! class_exists( 'ADD_IMAGE_FIELD' ) ) {

//    adding image/file upload field to taxonomy "course_type"
class ADD_IMAGE_FIELD {

  public function __construct() {
    //
  }

 /*
  * Initialize the class and start calling our hooks and filters
  * @since 1.0.0
 */
 public function init() {
   add_action( 'course_type_add_form_fields', array ( $this, 'add_course_type_image' ), 10, 2 );
   add_action( 'created_course_type', array ( $this, 'save_course_type_image' ), 10, 2 );
   add_action( 'course_type_edit_form_fields', array ( $this, 'update_course_type_image' ), 10, 2 );
   add_action( 'edited_course_type', array ( $this, 'updated_course_type_image' ), 10, 2 );
   add_action( 'admin_enqueue_scripts', array( $this, 'load_media' ) );
   add_action( 'admin_footer', array ( $this, 'add_script' ) );
 }

public function load_media() {
 wp_enqueue_media();
}

 /*
  * Add a form field in the new course_type page
  * @since 1.0.0
 */
 public function add_course_type_image ( $taxonomy ) { ?>
   <div class="form-field term-group">
     <label for="course_type-image-id">Preview Image</label>
     <input type="hidden" id="course_type-image-id" name="course_type-image-id" class="custom_media_url" value="">
     <div id="course_type-image-wrapper"></div>
     <p>
       <input type="button" class="button button-secondary ct_tax_media_button" id="ct_tax_media_button" name="ct_tax_media_button" value="<?php _e( 'Add Image', 'hero-theme' ); ?>" />
       <input type="button" class="button button-secondary ct_tax_media_remove" id="ct_tax_media_remove" name="ct_tax_media_remove" value="<?php _e( 'Remove Image', 'hero-theme' ); ?>" />
    </p>
   </div>

 <?php
 }

 /*
  * Save the form field
  * @since 1.0.0
 */
 public function save_course_type_image ( $term_id, $tt_id ) {
   if( isset( $_POST['course_type-image-id'] ) && '' !== $_POST['course_type-image-id'] ){
     $image = $_POST['course_type-image-id'];
     add_term_meta( $term_id, 'course_type-image-id', $image, true );
   }
 }

 /*
  * Edit the form field
  * @since 1.0.0
 */
 public function update_course_type_image ( $term, $taxonomy ) { ?>
   <tr class="form-field term-group-wrap">
     <th scope="row">
       <label for="course_type-image-id">Preview Image</label>
     </th>
     <td>
       <?php $image_id = get_term_meta ( $term -> term_id, 'course_type-image-id', true ); ?>
       <input type="hidden" id="course_type-image-id" name="course_type-image-id" value="<?php echo $image_id; ?>">
       <div id="course_type-image-wrapper">
         <?php if ( $image_id ) { ?>
           <?php echo wp_get_attachment_image ( $image_id, 'thumbnail' ); ?>
         <?php } ?>
       </div>
       <p>
         <input type="button" class="button button-secondary ct_tax_media_button" id="ct_tax_media_button" name="ct_tax_media_button" value="<?php _e( 'Add File', 'hero-theme' ); ?>" />
         <input type="button" class="button button-secondary ct_tax_media_remove" id="ct_tax_media_remove" name="ct_tax_media_remove" value="<?php _e( 'Remove File', 'hero-theme' ); ?>" />
       </p>
     </td>
   </tr>
 <?php
 }

/*
 * Update the form field value
 * @since 1.0.0
 */
 public function updated_course_type_image ( $term_id, $tt_id ) {
   if( isset( $_POST['course_type-image-id'] ) && '' !== $_POST['course_type-image-id'] ){
     $image = $_POST['course_type-image-id'];
     update_term_meta ( $term_id, 'course_type-image-id', $image );
   } else {
     update_term_meta ( $term_id, 'course_type-image-id', '' );
   }
 }

/*
 * Add script
 * @since 1.0.0
 */
 public function add_script() { ?>
   <script>
     jQuery(document).ready( function($) {
       function ct_media_upload(button_class) {
         var _custom_media = true,
         _orig_send_attachment = wp.media.editor.send.attachment;
         $('body').on('click', button_class, function(e) {
           var button_id = '#'+$(this).attr('id');
           var send_attachment_bkp = wp.media.editor.send.attachment;
           var button = $(button_id);
           _custom_media = true;
           wp.media.editor.send.attachment = function(props, attachment){
             if ( _custom_media ) {
               $('#course_type-image-id').val(attachment.id);
               $('#course_type-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
               $('#course_type-image-wrapper .custom_media_image').attr('src',attachment.url).css('display','block');
             } else {
               return _orig_send_attachment.apply( button_id, [props, attachment] );
             }
            }
         wp.media.editor.open(button);
         return false;
       });
     }
     ct_media_upload('.ct_tax_media_button.button');
     $('body').on('click','.ct_tax_media_remove',function(){
       $('#course_type-image-id').val('');
       $('#course_type-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
     });
     // Thanks: http://stackoverflow.com/questions/15281995/wordpress-create-course_type-ajax-response
     $(document).ajaxComplete(function(event, xhr, settings) {
       var queryStringArr = settings.data.split('&');
       if( $.inArray('action=add-tag', queryStringArr) !== -1 ){
         var xml = xhr.responseXML;
         $response = $(xml).find('term_id').text();
         if($response!=""){
           // Clear the thumb image
           $('#course_type-image-wrapper').html('');
         }
       }
     });
   });
 </script>
 <?php }

  }

$ADD_IMAGE_FIELD = new ADD_IMAGE_FIELD();
$ADD_IMAGE_FIELD -> init();
}

    if ( ! class_exists( 'ADD_PDF_FIELD' ) ) {

//    adding image/file upload field to taxonomy "course_type"
        class ADD_PDF_FIELD {

            public function __construct() {
                //
            }

            /*
             * Initialize the class and start calling our hooks and filters
             * @since 1.0.0
            */
            public function init() {
                add_action( 'course_type_add_form_fields', array ( $this, 'add_course_type_image' ), 10, 2 );
                add_action( 'created_course_type', array ( $this, 'save_course_type_image' ), 10, 2 );
                add_action( 'course_type_edit_form_fields', array ( $this, 'update_course_type_image' ), 10, 2 );
                add_action( 'edited_course_type', array ( $this, 'updated_course_type_image' ), 10, 2 );
                add_action( 'admin_enqueue_scripts', array( $this, 'load_media' ) );
                add_action( 'admin_footer', array ( $this, 'add_script' ) );
            }

            public function load_media() {
                wp_enqueue_media();
            }

            /*
             * Add a form field in the new course_type page
             * @since 1.0.0
            */
            public function add_course_type_image ( $taxonomy ) { ?>
                <div class="form-field term-group">
                    <label for="course_type-pdf_upload">Catalog File (pdf)</label>
                    <input type="hidden" id="course_type-pdf_upload" name="course_type-pdf_upload" class="custom_media_url" value="">
                    <div id="course_type-image-wrapper"></div>
                    <p>
                        <input type="button" class="button button-secondary ct_tax_media_button_pdf" id="ct_tax_media_button_pdf" name="ct_tax_media_button_pdf" value="<?php _e( 'Add Image', 'hero-theme' ); ?>" />
                        <input type="button" class="button button-secondary ct_tax_media_remove_pdf" id="ct_tax_media_remove_pdf" name="ct_tax_media_remove_pdf" value="<?php _e( 'Remove Image', 'hero-theme' ); ?>" />
                    </p>
                </div>

                <?php
            }

            /*
             * Save the form field
             * @since 1.0.0
            */
            public function save_course_type_image ( $term_id, $tt_id ) {
                if( isset( $_POST['course_type-pdf_upload'] ) && '' !== $_POST['course_type-pdf_upload'] ){
                    $image = $_POST['course_type-pdf_upload'];
                    add_term_meta( $term_id, 'course_type-pdf_upload', $image, true );
                }
            }

            /*
             * Edit the form field
             * @since 1.0.0
            */
            public function update_course_type_image ( $term, $taxonomy ) { ?>
                <tr class="form-field term-group-wrap">
                    <th scope="row">
                        <label for="course_typed_uload">Catalog File (pdf)</label>
                    </th>
                    <td>
                        <?php $image_id = get_term_meta ( $term -> term_id, 'course_type-pdf_upload', true ); ?>
                        <input type="hidden" id="course_type-pdf_upload" name="course_type-pdf_upload" value="<?php echo $image_id; ?>">
                        <div id="course_type-pdf-wrapper">
                            <?php if ( $image_id ) { ?>
                                <?php echo wp_get_attachment_image ( $image_id, 'thumbnail' ); ?>
                            <?php } ?>
                        </div>
                        <p>
                            <input type="button" class="button button-secondary ct_tax_media_button_pdf" id="ct_tax_media_button_pdf" name="ct_tax_media_button_pdf" value="<?php _e( 'Add Image', 'hero-theme' ); ?>" />
                            <input type="button" class="button button-secondary ct_tax_media_remove_pdf" id="ct_tax_media_remove_pdf" name="ct_tax_media_remove_pdf" value="<?php _e( 'Remove Image', 'hero-theme' ); ?>" />
                        </p>
                    </td>
                </tr>
                <?php
            }

            /*
             * Update the form field value
             * @since 1.0.0
             */
            public function updated_course_type_image ( $term_id, $tt_id ) {
                if( isset( $_POST['course_type-pdf_upload'] ) && '' !== $_POST['course_type-pdf_upload'] ){
                    $image = $_POST['course_type-pdf_upload'];
                    update_term_meta ( $term_id, 'course_type-pdf_upload', $image );
                } else {
                    update_term_meta ( $term_id, 'course_type-pdf_upload', '' );
                }
            }

            public function add_script() { ?>
                <script>
                    jQuery(document).ready( function($) {
                        function ct_media_upload(button_class) {
                            var _custom_media = true,
                                _orig_send_attachment = wp.media.editor.send.attachment;
                            $('body').on('click', button_class, function(e) {
                                var button_id = '#'+$(this).attr('id');
                                var send_attachment_bkp = wp.media.editor.send.attachment;
                                var button = $(button_id);
                                _custom_media = true;
                                wp.media.editor.send.attachment = function(props, attachment){
                                    if ( _custom_media ) {
                                        $('#course_type-pdf_upload').val(attachment.id);
                                        $('#course_type-pdf-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                                        $('#course_type-pdf-wrapper .custom_media_image').attr('src',attachment.url).css('display','block');
                                    } else {
                                        return _orig_send_attachment.apply( button_id, [props, attachment] );
                                    }
                                }
                                wp.media.editor.open(button);
                                return false;
                            });
                        }
                        ct_media_upload('.ct_tax_media_button_pdf.button');
                        $('body').on('click','.ct_tax_media_remove_pdf',function(){
                            $('#course_type-pdf_upload').val('');
                            $('#course_type-pdf-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                        });
                        // Thanks: http://stackoverflow.com/questions/15281995/wordpress-create-course_type-ajax-response
                        $(document).ajaxComplete(function(event, xhr, settings) {
                            var queryStringArr = settings.data.split('&');
                            if( $.inArray('action=add-tag', queryStringArr) !== -1 ){
                                var xml = xhr.responseXML;
                                $response = $(xml).find('term_id').text();
                                if($response!=""){
                                    // Clear the thumb image
                                    $('#course_type-pdf-wrapper').html('');
                                }
                            }
                        });
                    });
                </script>
            <?php }

        }

        $ADD_PDF_FIELD = new ADD_PDF_FIELD();
        $ADD_PDF_FIELD -> init();

}