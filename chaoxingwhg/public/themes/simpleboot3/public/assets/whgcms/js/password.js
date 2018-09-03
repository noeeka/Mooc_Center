$(function(){
    $('.see,#verify_img').click(function() {
        changeCaptcha();
    });
    //手机验证正则
    var phoneReg = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
    //step1
    $('#submitx').click(function() {
        console.log(1111)
        var phone = $('#user').val().trim();
        var code = $('#regcode').val().trim();
        var picture=$('#validation').val().trim();
        $.ajax({
            url: '/api/code/check',
            type: 'POST',
            data: { mobile: phone,type: 'reset_psd',code: code},
            beforeSend: function() {
                if ($('#user').val() == '') {
                    // getdialog("手机号不能为空");
                    $('.regnumber').css('display','block');
                    $('.regnumber').html('手机号不能为空！');
                    $('#user').focus(function(){
                        $('.regnumber').css('display','none');
                    })
                    return false;
                } else if (!phoneReg.test($('#user').val())) {
                    // getdialog('请输入正确的手机号');
                    $('.regnumber').css('display','block');
                    $('.regnumber').html('请输入正确的手机号！');
                    $('#user').focus(function(){
                        $('.regnumber').css('display','none');
                    })
                    return false;
                };
                if ($('#regcode').val() == '') {
                    // getdialog("验证码不能为空");
                    $('.regcode').css('display','block');
                    $('.regcode').html('短信验证码不能为空!');
                    $('#regcode').focus(function(){
                        $('.regcode').css('display','none');
                    })
                    return false;
                }

            },
            success: function(response) {
                console.log(response)

                if(response.status == 1){
                    $('.confirm').hide();
                    $('.reset').show();

                }
            }
        })

    });
    //step2
    $('#submit2').click(function() {
        // var formData = $('#ajaxForm').serialize();
        var password = $('#resetpassword').val().trim();
        var repassword = $('#confirmpassword').val().trim();
        var phone = $('#user').val().trim();
        var code = $('#regcode').val().trim();
        request({
            url: '/api/user/savePassword',
            type: 'POST',
            data: {password:password,repeat:repassword,yzm:code,mobile:phone},
            beforeSend: function() {

                if ($('#resetpassword').val() == '') {
                    // getdialog("密码不能为空");
                    $('.resetpassword').css('display','block');
                    $('.resetpassword').html('密码不能为空！');
                    $('#resetpassword').focus(function(){
                        $('.resetpassword').css('display','none');
                    })
                    return false;
                } else if ($('#resetpassword').val().length < 8) {
                    // getdialog('密码不能少于8位');
                    $('.resetpassword').css('display','block');
                    $('.resetpassword').html('密码不能少于8位！');
                    $('#resetpassword').focus(function(){
                        $('.resetpassword').css('display','none');
                    })
                    return false;
                };
                if ($('#confirmpassword').val() == '') {
                    // getdialog("重复密码不能为空");
                    $('.confirmpassword').css('display','block');
                    $('.confirmpassword').html('重复密码不能为空！');
                    $('#confirmpassword').focus(function(){
                        $('.confirmpassword').css('display','none');
                    })
                    return false;
                } else if ($('#confirmpassword').val() != $('#resetpassword').val()) {
                    $('.confirmpassword').css('display','block');
                    $('.confirmpassword').html('两次密码不一致！');
                    $('#confirmpassword').focus(function(){
                        $('.confirmpassword').css('display','none');
                    })
                    // getdialog('两次密码不一致');
                    return false;
                };
            },
            success: function(response) {
                if(response.status == 1){
                    $('.reset').hide();
                    $('.complete').show();

                }
            }
        })

    });
    $('#submit3').click(function(){
        setTimeout(function() {
            window.location.href = 'login';
        }, 1000);
    })
// 发送验证码
        $('.codebtn').on('click', function() {
            // 缓存外部this
            var that = $(this);
            if (that.hasClass('disabled')) {
                return false;
            }

            // 获取手机号
            var mobile = $('#user').val();
            // 发起请求
            $.ajax({
                type: 'post',
                url: '/api/code/mobile',
                dataType: 'json', // 用来约束服务器返回的数据类型
                data: { mobile: mobile, type: 'reset_psd' },
                // timeout: 2000, // 设置超时
                beforeSend: function() {
                    if (!phoneReg.test(mobile)) {
                        //提示
                        // getdialog('手机号码格式不正确')
                        $('.regcode').css('display','block');
                        $('.regcode').html('手机号码格式不正确');
                        return false;
                    }
                    if ($('#validation').val() == '') {
                        changeCaptcha();
                        /* getdialog('请输入验证码');*/
                        $('.phonecode').css('display','block');
                        $('.phonecode').html('图片验证码不能为空！');
                        $('#validation').focus(function(){
                            $('.phonecode').css('display','none');
                        })
                        return false;
                    }
                    else {

                        return checkImg();
                    }


                },
                success: function(info) {
                    if(info.status==1){
                        var ses = 60;
                        var t = setInterval(function() {
                            that.html(ses-- + '秒后重新获取')
                                .addClass('disabled');

                            if (ses < 0) {
                                clearInterval(t);
                                that.html('获取验证码')
                                    .removeClass('disabled');
                            }
                        }, 1000);
                    }else{
                        alert(info.msg);
                    }

                    // console.log(info);
                }
            });
        })

    function changeCaptcha() {
        var ts = (new Date).getTime();
        $('#verify_img').attr("src", "/captcha?id=" + ts);
    }

    function checkImg() {

        var code = $('#validation').val().trim();
        var src = $('#verify_img').attr("src").split('?');
        var id = '';
        if (src[1] != undefined) {
            id = src[1].split('=')[1];
        }
        var ret = false;
        request({
            url: '/api/site/checkImg',
            data: { id: id, code: code },
            dataType: 'json',
            type: 'get',
            async: false,
            success: function(res) {
                if (res.status == 0) {
                    changeCaptcha();
                    // getdialog('验证码错误');
                    $('.phonecode').css('display','block');
                    $('.phonecode').html('请输入正确的图片验证码!');
                    $('#validation').focus(function(){
                        $('.phonecode').css('display','none');
                    })
                    return ret;
                } else {

                    ret = true;
                }
            }
        });
        return ret;
    }


})