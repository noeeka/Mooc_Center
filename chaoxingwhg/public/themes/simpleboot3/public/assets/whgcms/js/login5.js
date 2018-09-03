$(function() {
    $('.tab li').click(function() {
        $('.tab li').removeClass('active');
        $('form').css('display', 'none');
        var index = $(this).index();
        $(this).addClass('active');
        $('form').eq(index).css('display', 'block');
    });
    $(".duoxuan img").click(function() {
        if ($('.duoxuan img').attr('src') == '/themes/simpleboot3/public/assets/whgcms/images/my/yes.png') {
            $('.duoxuan img').attr('src', '/themes/simpleboot3/public/assets/whgcms/images/my/no.png');
        } else {
            $('.duoxuan img').attr('src', '/themes/simpleboot3/public/assets/whgcms/images/my/yes.png');
        }
    });
    //手机验证正则
    var phoneReg = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
    //邮箱验证正则
    var emailReg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
    /*登录*/
    $('#submitx').click(function() {
        var phone = $('#user_id').val().trim();
        var password = $('#password').val().trim();
        // 发起请求行
        $.ajax({
            type: 'post',
            url: '/api/passport/login',
            data: { user_id: phone, password: password },
            // 发起请求前就调用
            beforeSend: function() {
                if ($('#user_id').val() == '') {
                    /* getdialog("手机号不能为空");*/
                    $('.phonenumber').css('display', 'block');
                    $('.phonenumber').html('手机号不能为空！');
                    $('#user_id').focus(function() {
                        $('.phonenumber').css('display', 'none');
                    })
                    return false;
                } else if (!phoneReg.test($('#user_id').val().trim())) {
                    /* getdialog('请输入正确的手机号');*/
                    $('.phonenumber').css('display', 'block');
                    $('.phonenumber').html('请输入正确的手机号！');
                    $('#user_id').focus(function() {
                        $('.phonenumber').css('display', 'none');
                    })
                    return false;
                }
                if ($('#password').val() == '') {
                    $('.phonepassword').css('display', 'block');
                    $('#password').focus(function() {
                        $('.phonepassword').css('display', 'none');
                    })
                    return false;
                }
                if ($('#validation').val() == '') {
                    changeCaptcha();
                    /* getdialog('请输入验证码');*/
                    $('.phonecode').css('display', 'block');
                    $('.phonecode').html('验证码不能为空！');
                    $('#validation').focus(function() {
                        $('.phonecode').css('display', 'none');
                    })
                    return false;
                } else {
                    return checkImg();
                }
                if (token != '' && phone == uid) {
                    getdialog('该账户已登录');
                    return false;
                }
            },
            success: function(info) {
                if (info.status == 1) {
                    setCookie('token', info.data.token, info.data.expire_time);
                    setCookie('uid', $('#user_id').val(), info.data.expire_time);
                    setCookie('salt', info.data.salt, info.data.expire_time);
                    window.location.href = "/portal5";
                } else if (info.status == 0) {
                    getdialog(info.msg);
                    changeCaptcha();
                    return false;
                }

            }
        });
    });
    $('.see,#verify_img').click(function() {
        changeCaptcha();
    });
    /*注册*/
    $('#submit').click(function() {
        // var formData = $('#ajaxForm').serialize();
        var phone = $('#user').val().trim();
        var code = $('#regcode').val().trim();
        var password = $('#regpassword').val().trim();
        var repassword = $('#regconfirm').val().trim();
        var service = $('.duoxuan img').attr('src');
        if ($('.duoxuan img').attr('src') == "/themes/simpleboot3/public/assets/whgcms/images/my/yes.png") {
            service = 1;
        } else if ($('.duoxuan img').attr('src') == "/themes/simpleboot3/public/assets/whgcms/images/my/no.png") {
            service = 0;
        }
        $.ajax({
            url: '/api/passport/regist',
            type: 'POST',
            data: { mobile: phone, password: password, confirm_psd: repassword, verify_code: code, agreement: service },
            beforeSend: function() {
                if ($('#user').val() == '') {
                    // getdialog("手机号不能为空");
                    $('.regnumber').css('display', 'block');
                    $('.regnumber').html('手机号不能为空！');
                    $('#user').focus(function() {
                        $('.regnumber').css('display', 'none');
                    })
                    return false;
                } else if (!phoneReg.test($('#user').val())) {
                    // getdialog('请输入正确的手机号');
                    $('.regnumber').css('display', 'block');
                    $('.regnumber').html('请输入正确的手机号！');
                    $('#user').focus(function() {
                        $('.regnumber').css('display', 'none');
                    })
                    return false;
                };
                if ($('#regpassword').val() == '') {
                    // getdialog("密码不能为空");
                    $('.regpassword').css('display', 'block');
                    $('.regpassword').html('密码不能为空！');
                    $('#regpassword').focus(function() {
                        $('.regpassword').css('display', 'none');
                    })
                    return false;
                } else if ($('#regpassword').val().length < 10) {
                    // getdialog('密码不能少于10位');
                    $('.regpassword').css('display', 'block');
                    $('.regpassword').html('请填写10~16位密码!');
                    $('#regpassword').focus(function() {
                        $('.regpassword').css('display', 'none');
                    })
                    return false;
                };
                if ($('#regconfirm').val() == '') {
                    // getdialog("重复密码不能为空");
                    $('.regconfirm').css('display', 'block');
                    $('.regconfirm').html('重复密码不能为空！');
                    $('#regconfirm').focus(function() {
                        $('.regconfirm').css('display', 'none');
                    })
                    return false;
                } else if ($('#regconfirm').val() != $('#regpassword').val()) {
                    $('.regconfirm').css('display', 'block');
                    $('.regconfirm').html('两次密码不一致！');
                    $('#regconfirm').focus(function() {
                        $('.regconfirm').css('display', 'none');
                    })
                    // getdialog('两次密码不一致');
                    return false;
                };
                if ($('#regcode').val() == '') {
                    // getdialog("验证码不能为空");
                    $('.regcode').css('display', 'block');
                    $('.regcode').html('验证码不能为空!');
                    $('#regcode').focus(function() {
                        $('.regcode').css('display', 'none');
                    })
                    return false;
                }
                // else{
                //     $('.regcode').css('display','block');
                //     $('.regcode').html('请输入正确的验证码!');
                //     $('#regcode').focus(function(){
                //         $('.regcode').css('display','none');
                //     })
                // }
                if ($(".duoxuan img").attr("src") == "/themes/simpleboot3/public/assets/whgcms/images/my/no.png") {
                    // getdialog("请选择服务条约");
                    $('.service').css('display', 'block');
                    // $('.regcode').html('两次密码不一致！');
                    // $('.duoxuan').click(function(){
                    //     console.log(1111)
                    // })
                    return false;
                };
            },
            success: function(response) {
                console.log(response)
                if (response.code == 11012) {
                    $('.regcode').css('display', 'block');
                    $('.regcode').html('验证码过期!');
                    $('#regcode').focus(function() {
                        $('.regcode').css('display', 'none');
                    })
                    return;
                }
                if (response.code == 11011) {
                    $('.regcode').css('display', 'block');
                    $('.regcode').html('请输入正确的验证码!');
                    $('#regcode').focus(function() {
                        $('.regcode').css('display', 'none');
                    })
                    return;
                }

                if (response.code == 12001) {
                    $('.regcode').css('display', 'block');
                    $('.regcode').html('验证码发送失败!');
                    $('#regcode').focus(function() {
                        $('.regcode').css('display', 'none');
                    })
                    return;
                }
                if (response.code == 11013) {
                    getdialog('该账号已被其他用户注册');
                    return;
                }

                if (response.status == 1) {
                    getdialog('注册成功');
                    setTimeout(function() {
                        $('#form').css('display', 'none')
                        $('.main-left .reg').removeClass('active');
                        $('.main-left .login').addClass('active');
                        $('#ajaxForm').css('display', 'block')
                    }, 1000)

                }
            }
        })

    });
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
            data: { mobile: mobile, type: 'regist' },
            // timeout: 2000, // 设置超时
            beforeSend: function() {
                if (!phoneReg.test(mobile)) {
                    //提示
                    // getdialog('手机号码格式不正确')
                    $('.regcode').css('display', 'block');
                    $('.regcode').html('手机号码格式不正确');
                    return false;
                }

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
            },
            success: function(info) {
                console.log(info);
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
        $.ajax({
            url: '/api/site/checkImg',
            data: { id: id, code: code },
            dataType: 'json',
            type: 'get',
            async: false,
            success: function(res) {
                if (res.status == 0) {
                    changeCaptcha();
                    // getdialog('验证码错误');
                    $('.phonecode').css('display', 'block');
                    $('.phonecode').html('请输入正确的验证码!');
                    $('#validation').focus(function() {
                        $('.phonecode').css('display', 'none');
                    })
                    return ret;
                } else {
                    ret = true;
                }
            }
        });
        return ret;
    }
/*二维码*/
$.ajax({
        url: '/api/baseinfo/read',
        type: 'GET',
        dataType: 'json',
        data: {},
        success: function(res) {
            if (res.status == 1) {
                var html = '';
                var count = 0;
                for (var i in res.data.ewm) {
                    if (res.data.ewm[i]['img'] != '') {
                        html += '<div class="gongzhonghao"><img src=" ' + res.data.ewm[i].img + '" alt=""><h3>' + res.data.ewm[i].title + '</h3></div>'
                        count++;
                    }
                }
                if (count == 1) {
                    $('.main-right').css('margin-top', '100px');
                }
                $('.main-right').html(html);
            }
        }
    })

})
//set cookie
function setCookie(c_name, value, expiredays) {
    var exdate = new Date();
    exdate.setTime(exdate.getTime() + expiredays * 1000);
    document.cookie = c_name + "=" + escape(value) +
        ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString()) + ";path=/";
}
//获取cookie
function getCookie(c_name) {
    if (document.cookie.length > 0) {
        var c_start = document.cookie.indexOf(c_name + "=")
        if (c_start != -1) {
            c_start = c_start + c_name.length + 1
            var c_end = document.cookie.indexOf(";", c_start)
            if (c_end == -1) c_end = document.cookie.length
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return "";
}
