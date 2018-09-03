$(function () {

    //手机验证正则
    var phoneReg = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
    // 登录验证
    $('.login-btn').click(function() {
        var formData = $('#loginForm').serialize();
        // 发起请求行
        $.ajax({
            type: 'post',
            url: '/api/passport/login/',
            data: formData,
            // 发起请求前就调用
            beforeSend: function() {
                if ($('#user_id').val() == '') {
                    // 友好提示
                    alert('用户名不能为空！')
                    // 可终止请求发起
                    return false;
                } else if (!phoneReg.test($('#user_id').val())) {
                    alert('请填入正确的账号！')
                    return false;
                }
                if ($('#password').val() == '') {
                    alert('请填入密码!')
                    return false;
                }
            },
            success: function(info) {
                console.log(info);
                if (info.status == 1) {
                    setCookie('token', info.data.token, info.data.expire_time);
                    setCookie('uid', $('#user_id').val(), info.data.expire_time);

                    setCookie('salt', info.data.salt, info.data.expire_time);
                    window.location.href = "/wx/index/";
                } else {
                    alert(info.msg);
                }
            }
        });
    });



})