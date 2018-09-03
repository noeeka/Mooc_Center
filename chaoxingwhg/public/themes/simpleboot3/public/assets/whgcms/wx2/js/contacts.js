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
                    var html = "";
                    for (var i =0 ;i < res.data.length; i ++) {

                        var type = ['未知','成年人' ,'未成年人'];
                        var data = res.data[i];
                        html += '<a href="/wx2/my/changeContacts?id='+data['id']+'">\n' +
                            '            <div class="mui-row">\n' +
                            '                <span class="mui-col-xs-3 mui-ellipsis">'+data['name']+'</span>\n' +
                            '                <span class="mui-col-xs-2">'+ type[data['type']]+'</span>\n' +
                            '                <span class="mui-col-xs-7">'+data['id_card']+'</span>\n' +
                            '            </div>\n' +
                            '    </a>';
                    }

                    $('.contList').append(html);
                }
            },
            error: function (res) {
            }
        }, true);
    }
});