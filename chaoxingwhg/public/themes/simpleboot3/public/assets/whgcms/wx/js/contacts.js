$(function () {

    getContacts();
    function getContacts(){
        request({
            url: '/api/contacts/getContacts',
            type: 'post',
            dataType: 'json',
            data :{'page':1 , size:20},
            async: false,
            success: function (res) {
                if (res.status == 1 && res.data.length> 0) {
                    console.log(res.data)
                }
            },
            error: function (res) {
            }
        }, true);
    }
});