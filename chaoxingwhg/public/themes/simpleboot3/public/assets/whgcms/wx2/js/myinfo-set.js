$(function(){
    request({
        url:'/api/my/index',
        type: 'GET',
        dataType: 'json',
        data: {},
        success: function(response) {
            if (response.status == 1) {
                $('input').val(response.data.user_nickname);
            }
        }
    },true)
    $('.closeBtn').click(function(){
        var nickname=$('input').val();
        console.log(nickname);
        request({
            url: '/api/my/save',
            data: {
                nickname: nickname
            },
            dataType: 'json',
            type: 'post',
            success: function (res) {
                if (res.status == 1) {
                    window.location.href='/wx/my/personalinfo';
                } else {
                    // alert('更新失败');
                    noLogin(res.code,res.msg);
                }
                return false;
            }
        },true)
    })

})