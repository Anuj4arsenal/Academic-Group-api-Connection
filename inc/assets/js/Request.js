class requestApi {
    getUserDetailByEmail(username){
        let data = {
            username:username,
            environment:'TEST',
            queryType: "All",
            queryParams: {
                Details: username
            },
            assocs: [
                "BillingAddress",
                "HomeAddress",
                "User",
                "Child",
                "GuardianStudentLinks",
                "GuardianStudentLinks.Student"
            ]
        };
        let that = this;
        return new Promise(function (resolve, reject) {
            that.requestServer('svc/Guardians', 'post', data, resolve,reject);
        });
    }
    login(username,password){
        let data = {username:username,password:password,environment:'TEST'};
        let that = this;
        return new Promise(function (resolve, reject) {
            that.requestServer('svc/Login', 'post', data, resolve,reject);
        });
    }
    getStudent(username){
        let that = this;
        let data = {
            environment: "TEST",
            queryParams: {
                Details: username
            },
            assocs: [
                "GuardianStudentLinks",
                "GuardianStudentLinks.Student"
            ],
            queryType: "All"
        }
        return new Promise(function (resolve, reject) {
            this.requestServer('svc/Guardians', 'post', data);
        });
    }
    create_save_booking(create_booking){
        let data = {
            environment: "TEST",
            create: create_booking
        }
        let that = this;
        console.log(data);
        return new Promise(function (resolve, reject) {
            that.requestServer('svc/SaveBooking', 'post', data , resolve,reject);
        });
    }
    requestServer(requestEndpoint,requestType='get',data={},resolve,reject){
        let that = this;
        if(requestType == "get" && !jQuery.isEmptyObject(data)){
            let query = jQuery.param(data)
            var url = "https://vm10.uat01.oneit.com.au/adtf_api/"+requestEndpoint+"?"+query;
        }else{
            var url = "https://vm10.uat01.oneit.com.au/adtf_api/"+requestEndpoint;
        }
        if (requestType == 'get') {
            axios.get(url)
                .then(function (response) {
                    resolve(response);
                })
                .catch(function (error) {
                    reject(error);
                })

        } else if (requestType == 'post') {
            axios.post(url, data)
                .then(function (response) {
                    resolve(response);
                    that.reqResLog(url,JSON.stringify(data),JSON.stringify(response))
                })
                .catch(function (error) {
                    reject(error);
                    that.reqResLog(url,JSON.stringify(data),error)
                })
        }
    }

    reqResLog(url,req,res) {
        const config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        }
        var bodyFormData = new FormData();
        bodyFormData.set('url',url);
        bodyFormData.set('req',req);
        bodyFormData.set('res',res);
        axios.post("/wp-admin/admin-ajax.php?action=ag_req_res_log", bodyFormData,config)
            .then(function (response) {
                console.log(response);
            })
            .catch(function(error) {
                console.log(error);
            })
    }

}