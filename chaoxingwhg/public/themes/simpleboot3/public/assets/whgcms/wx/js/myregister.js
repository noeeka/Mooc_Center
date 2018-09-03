$(function () {
    //手机验证正则
    var phoneReg = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
    //注册验证
    $('.register-btn').click(function() {
        // 缓存外部this
        var _this = $(this);

        // 禁止重复提交
        if (_this.hasClass('disabled')) {
            return false;
        }

        // 获取表单数据
        // 表单必须要name属性值，否则会被忽略
        var formData = $('#registForm').serialize();

        console.log(formData);

        // 发起请求行
        $.ajax({
            type: 'post',
            url: '/api/passport/regist?agreement=1&',
            data: formData,
            // 发起请求前就调用
            beforeSend: function() {

                if ($('#mobile').val() == '') {
                    // 友好提示
                    alert('手机号不能为空！')
                    // 可终止请求发起
                    return false;
                } else if ($('#password').val() == '') {
                    alert('密码不能为空！')
                    return false;
                } else if ($('#confirm_psd').val() == '') {
                    alert('确认密码不能为空！')
                    return false;
                } else if ($('#confirm_psd').val() != $('#password').val()) {

                    alert('确认密码与密码不一致！')
                    return false;
                } else if ($('#verify_code').val() == '') {
                    alert('验证码不能为空！')
                    return false;
                } else if (!phoneReg.test($('#mobile').val())) {
                    alert('手机号码格式不正确！')
                    return false;
                } else if ($('#password').val().length < 10) {
                    alert('请填写10~16位密码!')
                    return false;
                }

                // Loading状态
                _this.val('正在提交...').addClass('disabled');

            },
            success: function(info) {
                console.log(info);
                if (info.status != 1) {
                    alert(info.msg);
                    return;
                }
                alert('注册成功，请登录','/wx/login/login/');
                // location.href = '/wx/login/login/';

            },
            complete: function() { // 请求完成时会调用
                // 还原状态
                _this.val('注册').removeClass('disabled');
            }
        });
    })

    // 获取验证码
    $('.verification-btn').on('click', function() {
        // 缓存外部this
        var _this = $(this);
        if (_this.hasClass('disabled')) {
            return false;
        }

        // 获取手机号
        var _mobile = $('#mobile').val();
        // console.log(_mobile)
        // 发起请求
        $.ajax({
            type: 'post',
            url: '/api/code/mobile',
            dataType: 'json', // 用来约束服务器返回的数据类型
            data: { mobile: _mobile, type: 'regist' },
            // timeout: 2000, // 设置超时
            beforeSend: function() {
                if (_mobile == '') {
                    alert('手机号不能为空');
                    return false;
                }
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
                            .removeClass('disabled');
                    }
                }, 1000);
            },
            success: function(info) {
                console.log(info);

            }
        });
    })


})
