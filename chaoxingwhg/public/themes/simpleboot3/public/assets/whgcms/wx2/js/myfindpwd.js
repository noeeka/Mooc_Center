$(function() {
    //手机验证正则
    var phoneReg = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
    //邮箱验证正则
    //var emailReg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
    //更改密码
    $('.reset-btn').click(function() {
        // 缓存外部this
        var _this = $(this);

        // 禁止重复提交
        if (_this.hasClass('disabled')) {
            return false;
        }

        // 获取表单数据
        // 表单必须要name属性值，否则会被忽略
        var formData = $('#resetForm').serialize();

        console.log(formData);

        // 发起请求行
        //修改密码
        $.ajax({
            type: 'post',
            url:  '/api/user/savepassword',
            data: formData,
            // 发起请求前就调用
            beforeSend: function() {
                // console.log($('#password1').val().length);
                if ($('#telphone').val() == '') {
                    // 友好提示
                    alert('手机号不能为空！')
                    // 可终止请求发起
                    return false;
                } else if ($('#password').val() == '') {
                    alert('密码不能为空！')
                    return false;
                } else if ($('#repeat').val() == '') {
                    alert('确认密码不能为空！')
                    return false;
                } else if ($('#repeat').val() != $('#password').val()) {
                    alert('确认密码与密码不一致！')
                    return false;
                } else if ($('#validate').val() == '') {
                    alert('验证码不能为空！')
                    return false;
                } else if (!phoneReg.test($('#telphone').val())) {
                    alert('手机号码格式不正确！')
                    return false;
                } else if ($('#password').val().length < 8) {
                    alert('密码小于于8位!')
                    return false;
                }

                // Loading状态
                // _this.text('正在提交...').addClass('disabled');
                _this.addClass('disabled');

            },
            success: function(info) {
                console.log(info);
                if (info.status != 1) {
                    alert(info.msg);
                    return;
                }
                alert('修改成功！')
                // location.href = info.result;
                window.location.href = "/wx/login/login/";
            },
            complete: function() { // 请求完成时会调用
                // 还原状态
                _this.val('注册').removeClass('disabled');
            }
        });
    })

    // 获取验证码
    $('.validateBtn').on('click', function() {
        // 缓存外部this
        var _this = $(this);
        if (_this.hasClass('disabled')) {
            return false;
        }

        // 获取手机号
        var _mobile = $('#telphone').val();
        // console.log(_mobile)
        // 发起请求
        $.ajax({
            type: 'post',
            url: '/api/code/mobile',
            dataType: 'json', // 用来约束服务器返回的数据类型
            data: { mobile: _mobile, type: 'reset_psd' },
            // timeout: 2000, // 设置超时
            beforeSend: function() {
                if (!phoneReg.test(_mobile)) {
                    //提示
                    alert('手机号码格式不正确！')
                    return false;
                }

                var ses = 60;
                var t = setInterval(function() {
                    _this.val(ses-- + '秒后重新获取')
                        .addClass('disabled').css('background','#999');

                    if (ses < 0) {
                        clearInterval(t);
                        _this.val('获取验证码')
                            .removeClass('disabled').css('background','#B93421');
                    }
                }, 1000);
            },
            success: function(info) {
                console.log(info);

            }
        });
    })
})
