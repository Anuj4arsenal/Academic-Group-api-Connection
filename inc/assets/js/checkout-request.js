if (undefined !== window.jQuery) {

    jQuery("#billing_email").blur(function () {
        jQuery(".email-success-msg").remove();
        //check if user already exists in the api
        if_user_exists_in_api(jQuery("#billing_email").val());
    });


    function if_user_exists_in_api(email) {
        // jQuery("#loader_image").show();
        let verifyObj = new requestApi();
        let ag_response = verifyObj.getUserDetailByEmail(email);
        ag_response.then(function (success) {
            console.log(success);
            if (success.data.results && success.data.results.length > 0) {
                if (!jQuery(".email-success-msg")[0]) {
                    // Do something if class does not exist
                    jQuery('.create_an_new_account_section_ag').hide();
                    jQuery("#billing_email").after("<p class='email-success-msg'>A user with this email address already exists.</p>");
                }
            }

        })
            .catch(function (error) {
                console.log(error);
                //do nothing
                // alert("failed to login");
            })
    }


}
//});
/*creating unique number after first name of student*/
jQuery(document).ready(function () {
    function IDGenerator() {
        let length = 4;
        let timestamp = +new Date;
        var _getRandomInt = function (min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
        var ts = timestamp.toString();
        var parts = ts.split("").reverse();
        var id = "";

        for (var i = 0; i < length; ++i) {
            var index = _getRandomInt(0, parts.length - 1);
            id += parts[index];
        }
        return parseInt(id);
    }
    jQuery('.ag-addStudent').on('click', function(){
        window.activeCousrse = jQuery(this).attr('data-course');
        jQuery('#addStudentModalAg').modal('show');
    })

    /*modal form submission*/
    jQuery('#add_new_student').on('click', function () {
        let FirstName = jQuery("#sFirstName").val();
        let LastName = jQuery("#sLastName").val();
        let Email = jQuery("#sEmail").val() ? jQuery("#sEmail").val() : IDGenerator() + FirstName+'@example.com';
        let SchoolYear = jQuery("#sSchoolYear").val();
        let Gender = jQuery("#sGender").val();
        let Mobile = jQuery("#sMobile").val();
        let SchoolTxt = jQuery("#sSchoolTxt").val();
            
            if(FirstName =="" || LastName == ""){
                alert("FirstName and LastName is Mandatory !");
                return false;
            }
            
        if (!isEmail(Email)) {
            alert("Invalid Email Address !");
            return false;
        }
        let studentId = IDGenerator();

        let new_student = {};
        new_student["FirstName"] = FirstName;
        new_student["LastName"] = LastName;
        new_student["Email"] = Email;
        new_student["SchoolYear"] = SchoolYear;
        new_student["Gender"] = Gender;
        new_student["Mobile"] = Mobile;
        new_student["SchoolTxt"] = SchoolTxt;
        new_student['id'] = studentId;

        //check if std name already exists
        if (in_array_check(allStudent(),{FirstName:FirstName,LastName:LastName})) {
            alert("Student With same first name and last name  already exists !");
            return false;
        }

        add_new_student_local(new_student);
        selectStudentForCourse(window.activeCousrse,studentId);

        //reset form values
        jQuery("#sFirstName").val("");
        jQuery("#sLastName").val("");
        jQuery("#sEmail").val("");
        jQuery("#sMobile").val("");
        jQuery("#sSchoolTxt").val("");

        jQuery('#addStudentModalAg').modal('hide');
        return false;

    });

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    //for initial set up set up
    loadedFunction();


});

/*local storage for student details*/
function loadedFunction() {
    updateSelectOptions();
    updateCourseFromStorage();
}

function in_array_check(data, niddle) {
    for (var i in data) {
        let isMatch = false;
        let andCondition = true;
       for(var j in niddle){
           if(data[i][j] == niddle[j]){
               isMatch = true;
           }
           else{
               andCondition = false
           }
       }
       if(isMatch && andCondition){
           return true;
       }
    }
    return false;
}

function add_new_student_local(new_student) {
    let prev_storage = sessionStorage.getItem("ag_local_students") ? JSON.parse(sessionStorage.getItem("ag_local_students")) : [];
    prev_storage.push(new_student);
    sessionStorage.setItem("ag_local_students", JSON.stringify(prev_storage));
    updateSelectOptions();
}

let allStudent = ()=>{
    let std_from_local = sessionStorage.getItem("ag_local_students") ? sessionStorage.getItem("ag_local_students") : '[]';
    std_from_local = JSON.parse(std_from_local);
    let std_from_server = sessionStorage.getItem("ag_server_students") ? sessionStorage.getItem("ag_server_students") : '[]';
    std_from_server = JSON.parse(std_from_server);
    return std_from_server.concat(std_from_local);

}
function updateSelectOptions(){
    let students = allStudent();
    jQuery('.guardian-childrens-list').html('<option value="0" selected disabled>Select Student</option>');
    for(var i in students) {
        jQuery('.guardian-childrens-list').append(jQuery('<option>', {
            value: students[i].id,
            text: students[i].FirstName + " " + students[i].LastName
        }));
    }
}
function selectStudentForCourse(courseId,studentId){
    jQuery('#ag_select_std_'+courseId).val(studentId);
    let qty =  parseInt(jQuery('#total_qty_'+courseId).val());
    let prev_storage = sessionStorage.getItem("ag_selected_student") ? JSON.parse(sessionStorage.getItem("ag_selected_student")) : [];
    let count = 0;
    for(var i in prev_storage){
        if(prev_storage[i].courseId == courseId && prev_storage[i].studentId == studentId){
            return false;
        }
        else{
            if(prev_storage[i].courseId == courseId){
                count++;
            }
        }
    }
    if(qty <= count){
        alert('Your are allowed to add only '+qty+' student'+(qty == 1 ? '' : 's'));
        return;
    }

    prev_storage.push({courseId:courseId,studentId:studentId});
    sessionStorage.setItem("ag_selected_student",JSON.stringify(prev_storage));
    updateCourseFromStorage();
    jQuery('#ag_select_std_'+courseId).val(0);
}
function updateCourseFromStorage(){
    let data = JSON.parse(sessionStorage.getItem("ag_selected_student"));
    jQuery('.ag-course-selection-table').html('');
    jQuery('.student-table-block').hide();
    for(var i in data){
        let student = getStudentById(data[i].studentId);
    jQuery('#std_selected_table'+data[i].courseId).append('<tr>'+
       '<td class="product-remove">' +
       '<a onclick="deleteStudentFromCourse('+data[i].courseId+','+data[i].studentId+')" href="javascript:void(0)">'+
       '<i class="fa fa-trash"></i>'+
       '</a>'+
       '</td>'+
       '<td class="product-name">'+
       '<a href="">'+
       '<i class="fas fa-user"> </i> '+student.FirstName+' '+student.LastName+
       '</a>'+
       '</td>'+
       '<td class="product-number">'+
        data[i].studentId+
       '</td>'+
       '</tr>')
    jQuery('#student-table-block-'+data[i].courseId).show();
    };
    fillSelectedHiddenFormField();
}
function deleteStudentFromCourse(courseId,studentId){
    let prev_storage = sessionStorage.getItem("ag_selected_student") ? JSON.parse(sessionStorage.getItem("ag_selected_student")) : [];
    let newData = prev_storage.filter(function(data){
        if(data.courseId == courseId && parseInt(data.studentId) == studentId){
           return false;
        }
        else{
            return true;
        }
    });
    sessionStorage.setItem("ag_selected_student",JSON.stringify(newData));
    updateCourseFromStorage();
}
function fillSelectedHiddenFormField(){
    jQuery('.selected_std_lists_for_course').val('');
    jQuery('.ag-qty-msg').show();
    let data = JSON.parse(sessionStorage.getItem("ag_selected_student"));
    let cDatas = [];
    for(let i in data){
        cDatas[data[i].courseId] = cDatas[data[i].courseId] ? cDatas[data[i].courseId] : [];
        cDatas[data[i].courseId].push(getStudentById(data[i].studentId));
    }
    for(var key in cDatas){
        let qty = parseInt(jQuery('#total_qty_'+key).val());
        let count = cDatas[key].length;
        if(qty == count){
            jQuery('#ag-qty-msg-'+key).hide();
        }
        else{
            jQuery('#ag-qty-msg-'+key).show();
        }
        jQuery('#selected_std_lists_for_course_'+key).val(JSON.stringify(cDatas[key]));
    }

}
function getStudentById(id){
    let data = allStudent();
    for(var i in data){
        if(data[i].id == id){
            return data[i];
        }
    }
    return [];
}
