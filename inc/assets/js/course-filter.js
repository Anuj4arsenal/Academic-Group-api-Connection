/*jQuery(document).ready(function(){


jQuery('.panel-collapse').on('show.bs.collapse', function () {
    jQuery(this).siblings('.panel-heading').addClass('active');
  });

  jQuery('.panel-collapse').on('hide.bs.collapse', function () {
    jQuery(this).siblings('.panel-heading').removeClass('active');
  });
  })*/

var selected_course = [];

    function uncheck_radio_button(id){
        result = selected_course.includes(id);
        if(result){
            selected_course = jQuery.grep(selected_course, function(value) {
                return value != id;
            });
            jQuery("#"+id).attr('checked', false);
        }
        else{
            selected_course.push(id);
        }

        if(jQuery.isEmptyObject(selected_course)){
            jQuery(".addToCart").hide();
        }
        else{
            jQuery(".addToCart").show();
        }
        // console.log(result);
    }


    jQuery(function($) {
        function check_parent_empty(that){
            let data = true
            jQuery(that).find('.cstm-radio>ul>div>div').each(function(){
                if(jQuery(this).attr('style') == 'display: block;'){
                    data = false;
                }
            });
            return data;
        }
        function check_child_empty(that){
            let data = true
            jQuery(that).find('.cstm-radio>ul>div>div>div').each(function(){
                if(jQuery(this).attr('style') == 'display: block;'){
                    data = false;
                }
            });
            return data;
        }
        function clear_empty_field(){
            jQuery('.sessonGroup').each(function(ind){
                let cEmp = check_child_empty(this);
                let pEmp = check_parent_empty(this);
                if(!cEmp && !pEmp){
                    jQuery(this).show();
                }
                else{
                    jQuery(this).hide();
                }
            })
        }
        jQuery('#course-filter-from :input').change(function(){
            
            $("#course-time-slot-tables").show();

            //hide all the filtered data to reset the filter
            $(".ag-filtered-data").hide();



            let selected_term = $('input[name="term_selected"]:checked').val();
            if(selected_term==null){
                $(".term-filter-data").show();
            }
            else{
                $(".term-"+selected_term).show();
            }

            //array of all selected venues
            let selected_venues = [];
            $("input:checkbox[name=selected_venue]:checked").each(function(){
                selected_venues.push($(this).val());
            });
            //if no venue is selected then we set filter as all venue selected
            if(jQuery.isEmptyObject(selected_venues)){
                $(".venue-filter-data").show();
            }
            else{ //if venue are selected then we show data by only selected venues
                jQuery.each(selected_venues, function(index, item) {
                    $(".venue-"+item).show();
                });
            }


            //array of all selected years
            let selected_years = [];
            $("input:checkbox[name=selected_year]:checked").each(function(){
                selected_years.push($(this).val());
            });
            //if no year is selected then we set filter as all years selected
            if(jQuery.isEmptyObject(selected_years)){
                $(".year-filter-data").show();
            }//if years are selected then we show data by only selected years
            else{
                jQuery.each(selected_years, function(index, item) {
                    $(".year-"+item).show();
                });
            }


            //array of all selected subjects
            let selected_subjects = [];
            $("input:checkbox[name=selected_subject]:checked").each(function(){
                selected_subjects.push($(this).val());
            });
            //if no subject is selected then we set filter as all years selected
            if(jQuery.isEmptyObject(selected_subjects)){
                $(".subject-filter-data").show();
            }
            else{//if subject are selected then we show data by only selected subjects
                jQuery.each(selected_subjects, function(index, item) {
                    $(".subject-"+item).show();
                });
            }
            clear_empty_field();

        });
    });
 

    //stop from multiple submission on reload the tab
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }