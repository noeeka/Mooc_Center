$(function(){
    var uid = getCookie('uid');
    // console.log(uid);
    $('.passport').click(function(){
        var password = $('#password').val().trim();
        var repeat = $('#repeat').val().trim();
        var code = $('#validate').val().trim();
            $.ajax({
            url: '/api/user/savePassword',
            type: 'POST',
            data: { password:password,repeat:repeat,yzm:code,mobile:uid},
                beforeSend:function(){
               if($('#validate').val()==''){
                   alert('短信验证码不能为空');
                   return false;
               }
           },
            success: function(response) {
                if(response.status == 1){
                    alert('密码修改成功');
                    setTimeout(function(){
                        window.location.href='/wx/my';
                    },1000)
                }
            }
        })
    });
    // 发送验证码
    $('.validateBtn').on('click', function() {
        // 缓存外部this
        var that = $(this);
        if (that.hasClass('disabled')) {
            return false;
        }

        // 获取手机号
        // 发起请求
        $.ajax({
            type: 'post',
            url: '/api/code/mobile',
            dataType: 'json', // 用来约束服务器返回的数据类型
            data: { mobile: uid, type: 'reset_psd' },
            beforeSend: function() {
                if($('#password').val()==''){
                    alert('密码不能为空');
                    return false;
                }else if($('#password').val().length < 10){
                    alert('请填写10~16位密码');
                    return false;
                }

                if($('#repeat').val()==''){
                    alert('重复密码不能为空');
                    return false;
                }else if($('#password').val() != $('#repeat').val()){
                    alert('两次密码不一致');
                    return false;
                }


            },
            success: function(info) {
                if(info.status==1){
                    var ses = 60;
                    var t = setInterval(function() {
                        that.val(ses-- + '秒后重新获取')
                            .addClass('disabled');

                        if (ses < 0) {
                            clearInterval(t);
                            that.val('获取验证码')
                                .removeClass('disabled');
                        }
                    }, 1000);
                }else{
                    alert(info.msg)
                }
            }
        });
    })
})
