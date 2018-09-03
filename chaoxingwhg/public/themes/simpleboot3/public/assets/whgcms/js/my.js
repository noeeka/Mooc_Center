$(function() {
    /*判断是否是志愿者*/
    request({
        url: '/api/volunteer/is_volun',
        type: 'POST',
        dataType: 'json',
        data: {},
        success: function(res) {
            if (res.status == 1) {
                $('#volunteer').show();
                $('#last_line').show();
            }
        }
    }, true)

    var index_url = window.location.href;
    var flag = index_url.indexOf('qurenzheng');
    if (flag != -1) {
        $('.content').addClass('hide');
        /* Act on the event */
        $('.main-nav1 .options').removeClass('zhutise');
        $('.content-content').addClass('hide');

        $('.options').eq(2).addClass('zhutise');
        $('.content-content').eq(2).removeClass('hide');
    }
    $('.main-nav1 .options').click(function(event) {
        if ($(this).text() != '修改密码') {
            $('.content').addClass('hide');
            /* Act on the event */
            $('.main-nav1 .options').removeClass('zhutise');
            $('.content-content').addClass('hide');
            // var index = $(this).index();
            var index = $(this).attr('data-index');
            $(this).addClass('zhutise');
            $('.title').html($(this).html());
            $('.content-content').eq(index).removeClass('hide');
        }

    });
    //手机验证正则
    var phoneReg = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;

    //获取用户信息
    get_user_info();
    //获取认证信息
    get_auth_info();
    //保存用户信息
    $("#submitx").click(function() {
        update_user_info();
    })

    //更改验证码
    $('.see,#verify_img').click(function() {
        changeCaptcha();
    });

    function changeCaptcha() {
        var ts = (new Date).getTime();
        $('#verify_img').attr("src", "/captcha?id=" + ts);
    }

    // 发送手机验证码
    $('.codebtn').on('click', function() {
        // 缓存外部this
        var that = $(this);
        if (that.hasClass('disabled')) {
            return false;
        }

        // 获取手机号
        var mobile = $('#mobile').val();
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
    //修改密码 ，下一步
    $('.reset_btn').click(function() {
        var code = $('input[name=xmyzm]').val().trim();
        if (code == '') {
            // getdialog('验证码不能为空');
            $('.regcode').css('display', 'block');
            $('.regcode').html('短信验证码不能为空');
            $('.confirm input').focus(function() {
                $('.regcode').css('display', 'none');
            })
            return false;
        }
        $check = checkMobile();
        if ($check) {
            var mobile = $('#mobile').val().trim();
            $.ajax({
                url: '/api/code/check',
                data: { mobile: mobile, type: 'reset_psd', code: code },
                dataType: 'json',

                success: function(res) {
                    if (res.status == 1) {
                        $('.confirm').hide();
                        $('.reset').show();
                    } else {
                        $('.regcode').css('display', 'block');
                        $('.regcode').html('验证码错误');
                    }
                }
            });
        }
    });

    //step 2
    $('.csmmbtn').click(function() {
        var password = $('input[name=password]').val().trim();
        var repeat = $('input[name=repeat]').val().trim();
        request({
            url: '/api/user/savePassword',
            data: { password: password, repeat: repeat },
            dataType: 'json',
            type: 'post',
            beforeSend: function() {
                if (password == '') {
                    // getdialog('密码不能为空');
                    $('.resetpassword').css('display', 'block');
                    $('.resetpassword').html('密码不能为空');
                    $('#resetpassword').focus(function() {
                        $('.resetpassword').css('display', 'none');
                    })
                    return false;
                }
                if (password != repeat) {
                    // getdialog('两次密码不一致');
                    $('.repeatpassword').css('display', 'block');
                    $('.repeatpassword').html('两次密码不一致');
                    $('#repeatpassword').focus(function() {
                        $('.repeatpassword').css('display', 'none');
                    })
                    return false;
                }
                var passReg = /^\w{8,}$/;
                if (!passReg.test(password)) {
                    // getdialog('密码只接受长度至少8位的字母数字下划线');
                    $('.resetpassword').css('display', 'block');
                    $('.resetpassword').html('密码长度不少于8位');
                    $('#resetpassword').focus(function() {
                        $('.resetpassword').css('display', 'none');
                    })
                    return false;
                }
            },
            success: function(res) {
                if (res.status == 1) {
                    $('.reset').hide();
                    $('.complete').show();
                } else {
                    getdialog('修改密码失败');
                }
            }
        }, true);
    });

    //完成
    $('.wcbtn').click(function() {
        $('.reset,.complete').hide();
        $('.confirm').show();
        $('.confirm input[type=text]').val('');
        $('input[name=xmmobile]').val();
        $('.reset input[type=password]').val('');
    });

    function checkMobile() {
        var mobile = $('input[name=xmmobile]').val().trim();
        var mobileRegExp = /^0?(13|14|15|17|18|19)[0-9]{9}$/
        if (mobile == '') {
            // getdialog('手机号不能为空');
            $('.resetnumber').css('display', 'block');
            $('.resetnumber').html('手机号不能为空');
            $('.confirm input').focus(function() {
                $('.resetnumber').css('display', 'none');
            })

            return false;
        }
        if (!mobileRegExp.test(mobile)) {
            // getdialog('手机号不合法');
            $('.resetnumber').css('display', 'block');
            $('.resetnumber').html('手机号不合法');
            $('.confirm input').focus(function() {
                $('.resetnumber').css('display', 'none');
            })
            return false;
        }
        return mobile;
    }

    layui.use('laydate', function() {
        var laydate = layui.laydate;

        //执行一个laydate实例
        laydate.render({
            elem: '#test2', //指定元素
            theme: '#2F343B'
        });
    });
    layui.use('laypage', function() {
        var laypage = layui.laypage;

        //执行一个laypage实例
        laypage.render({
            elem: 'test1', //注意，这里的 test1 是 ID，不用加 # 号
            count: 1000, //数据总数，从服务端得到
            next: '>'

        });
    });
    $('.picture').click(function(event) {
        /* Act on the event */
        $('.content').removeClass('hide');
        $('.content-content').addClass('hide');
        $('.main-nav1 .options').removeClass('active');
    });




    // layui.use('laypage', function() {
    //     var laypage = layui.laypage;

    //     //执行一个laypage实例
    //     laypage.render({
    //         elem: 'test4', //注意，这里的 test1 是 ID，不用加 # 号
    //         count: 1000, //数据总数，从服务端得到
    //         next: '>'

    //     });
    // });
    // layui.use('laypage', function() {
    //     var laypage = layui.laypage;

    //     //执行一个laypage实例
    //     laypage.render({
    //         elem: 'test5', //注意，这里的 test1 是 ID，不用加 # 号
    //         count: 1000, //数据总数，从服务端得到
    //         next: '>'

    //     });
    // });
    // $('#screening').ganged({ 'data': data, 'width': 100, 'height': 30 });


    //获取用户信息
    function get_user_info() {
        request({
            url: '/api/my/index',
            type: 'post',
            dataType: 'json',
            async: false,
            success: function(result) {
                if (result.status == 1) {
                    $("#nicknameText").html(result.data.user_nickname);
                    if (result.data.avatar != '') {
                        $("input[name=avatar]").val(result.data.avatar);
                        $(".picture").css('background-image', 'url(' + result.data.avatar_url + ')');
                        $('#userAvator').attr('src', result.data.avatar_url);
                    }
                    $("#user_nickname").val(result.data.user_nickname);
                    $("#user_realname").val(result.data.user_realname);

                    if (result.data.sex == 1) { //男
                        $(".sex").eq(0).val(1);
                        $(".sex").eq(0).attr('checked', 'checked');
                    } else if (result.data.sex == 2) { //女
                        $(".sex").eq(1).val(2);
                        $(".sex").eq(1).attr('checked', 'checked');
                    } else { //保密
                        $(".sex").eq(2).val(0);
                        $(".sex").eq(2).attr('checked', 'checked');
                    }
                    $("#test2").val(result.data.format_birthday);
                    $("#address").val(result.data.address);
                    layui.use('form', function() {
                        layui.form.render('radio');
                    });
                }
            }
        }, true);
    }

    function get_auth_info() {
        request({
            url: '/api/user/auth_read',
            type: 'post',
            dataType: 'json',
            async: false,
            success: function(result) {
                if (result.status == 0) {
                    //未提交身份认证
                    $('.weirenzheng').removeClass('hide');
                } else {
                    if (result.data.status == 0 || result.data.status == 2) {
                        //审核中和认证成功显示已认证页面
                        $('.yirenzhengdiv').removeClass('hide');
                        $('.realname').html(result.data.realname);
                        $('.shenfenzheng').html(result.data.shenfenzheng);
                        $('.ft_sfzimg').css('background-image', 'url(' + result.data.server_img[0] + ')');
                        $('.bd_sfzimg').css('background-image', 'url(' + result.data.server_img[1] + ')');
                        $('.status-btn span').text(result.data.status == 0 ? '认证中...' : '认证成功');
                    } else {
                        //审核失败显示审核失败页面
                        $('.renzhengshibai').removeClass('hide');
                        if (result.data.reason == 0) {
                            $('.error').html('失败原因：图片不清晰');
                        } else if (result.data.reason == 1) {
                            $('.error').html('失败原因：真实姓名或身份证号码与上传照片信息不符');
                        } else {
                            $('.error').html('失败原因：' + result.data.reason_text);
                        }
                        var realname = $('input[name=realname]').val(result.data.realname);
                        var shenfenzheng = $('input[name=shenfenzheng]').val(result.data.shenfenzheng);
                        var ft_sfzimg = $('input[name=ft_sfzimg]').val(result.data.img['img']);
                        var bd_sfzimg = $('input[name=bd_sfzimg]').val(result.data.img['img1']);
                        $('#ft_sfzimg').css('background-image', 'url(' + result.data.server_img[0] + ')');
                        $('#bd_sfzimg').css('background-image', 'url(' + result.data.server_img[1] + ')');
                    }
                }
            }
        }, true);
    }

    //点击去认证按钮，隐藏未认证提示，弹出提交认证信息页面
    $('.qurenzhengbtn').click(function() {
        $('.weirenzheng').addClass('hide');
        $('.weirenzhengdiv').removeClass('hide');
    });
    //点击重新认证按钮，隐藏认证失败页面，弹出提交认证信息页面
    $('.chongxinrenzhengbtn').click(function() {
        $('.renzhengshibai').addClass('hide');
        $('.weirenzhengdiv').removeClass('hide');
    });
    //上传身份证正面照
    var ftSfzimgUploader = new uploader(
        '#ft_sfzimg',
        function(file, res) {
            if (res.code == 1) {
                $('#ft_sfzimg').css('background-image', 'url(' + res.data.preview_url + ')');
                $("input[name=ft_sfzimg]").val(res.data.filepath);
                $('#ft_sfzimg .webuploader-pick').hide();
            } else {
                getdialog(res.data.msg);
            }
            this.reset();
        },
        function(res) {
            getdialog('上传身份证正面照失败了');
        }
    );
    //上传身份证反面照
    var bdSfzimgUploader = new uploader(
        '#bd_sfzimg',
        function(file, res) {
            if (res.code == 1) {
                $('#bd_sfzimg').css('background-image', 'url(' + res.data.preview_url + ')');
                $("input[name=bd_sfzimg]").val(res.data.filepath);
                $('#bd_sfzimg .webuploader-pick').hide();
            } else {
                getdialog(res.data.msg);
            }
            this.reset();
        },
        function(res) {
            getdialog('上传身份证反面照失败了');
        }
    );
    //点击提交按钮
    $('.weirenzhengdiv form').submit(function() {
        var realname = $('input[name=realname]').val().trim();
        var shenfenzheng = $('input[name=shenfenzheng]').val().trim();
        var ft_sfzimg = $('input[name=ft_sfzimg]').val();
        var bd_sfzimg = $('input[name=bd_sfzimg]').val();
        request({
            url: '/api/user/auth_real_user',
            data: { realname: realname, shenfenzheng: shenfenzheng, sfz_img: { img: ft_sfzimg, img1: bd_sfzimg } },
            dataType: 'json',
            type: 'post',
            beforeSend: function() {
                if (realname == '') {
                    // getdialog('真实姓名不能为空');
                    $('.name').css('display', 'block');
                    $('#name').focus(function() {
                        $('.name').css('display', 'none');
                    })
                    return false;
                }
                if (shenfenzheng == '') {
                    // getdialog('身份证号不能为空');
                    $('.idnumber').css('display', 'block');
                    $('#idnumber').focus(function() {
                        $('.idnumber').css('display', 'none');
                    })
                    return false;
                }
                if (!/\d{17}[\d|x]|\d{15}/.test(shenfenzheng)) {
                    // getdialog('身份证号不合法');
                    $('.idnumber').css('display', 'block');
                    $('.idnumber').html('身份证号不合法');
                    $('#idnumber').focus(function() {
                        $('.idnumber').css('display', 'none');
                    })
                    return false;
                }
                if (ft_sfzimg == '') {
                    // getdialog('身份证正面照不能为空');
                    $('.idcard').css('display', 'block');
                    $('.idcard').html('身份证正面照不能为空');
                    $('#ft_sfzimg').click(function() {
                        $('.idcard').css('display', 'none');
                    })
                    return false;
                }
                if (bd_sfzimg == '') {
                    // getdialog('身份证反面照不能为空');
                    $('.idcard').css('display', 'block');
                    $('.idcard').html('身份证反面照不能为空');
                    $('#bd_sfzimg').click(function() {
                        $('.idcard').css('display', 'none');
                    })
                    return false;
                }
            },
            success: function(res) {
                if (res.status == 1) {
                    //提交成功需要显示查看身份证模块
                    $('.weirenzhengdiv').addClass('hide');
                    $('.yirenzhengdiv').removeClass('hide');
                    $('.realname').html(realname);
                    $('.shenfenzheng').html(shenfenzheng);
                    var ft_sfzimg = $('#ft_sfzimg').css('background-image');
                    var bd_sfzimg = $('#bd_sfzimg').css('background-image');
                    $('.ft_sfzimg').css('background-image', ft_sfzimg);
                    $('.bd_sfzimg').css('background-image', bd_sfzimg);
                }
                getdialog(res.msg);
            },
            error: function() {
                console.log('ajax error')
            }
        }, true);
        return false;
    });

    //保存用户信息
    function update_user_info() {
        var nickname = $("#user_nickname").val().trim();
        var realname = $("#user_realname").val().trim();
        var sex = $("input[name=sex]:checked").val().trim();
        var birthday = $("#test2").val().trim();
        var address = $("#address").val().trim();
        var avatar = $("input[name=avatar]").val().trim();
        request({
            url: '/api/my/save',
            data: {
                nickname: nickname,
                avatar: avatar,
                realname: realname,
                sex: sex,
                birthday: birthday,
                address: address
            },
            dataType: 'json',
            type: 'post',
            beforeSend: function() {
                if (nickname == '') {
                    getdialog('昵称不能为空');
                    return false;
                }
                if (sex != '' && !$.inArray(sex, [0, 1, 2])) {
                    getdialog('性别设置错误');
                    return false;
                }
            },
            success: function(res) {
                if (res.status == 1) {
                    $('#nicknameText').text(nickname);
                    if (nickname != '' && nickname.length > 5) {
                        $('.treg a').html(nickname.slice(0, 5) + '...');
                    } else if (nickname != '' && nickname.length <= 5) {
                        $('.treg a').html(nickname);
                    }
                    getdialog('更新成功');
                } else {
                    getdialog('更新失败');
                }
                return false;
            }
        }, true);
    }

    var touxiangUploader = new uploader(
        '#touxiangbtn',
        function(file, res) {
            if (res.code == 1) {
                $('.picture').css('background-image', 'url(' + res.data.preview_url + ')');
                $("input[name=avatar]").val(res.data.filepath);
                $(".tlogin img").attr('src', res.data.preview_url);
                $('.touxiang .txuploadbtn').addClass('bg');
                update_user_info();
            } else {
                getdialog(res.data.msg);
            }
            this.reset();
        },
        function(res) {
            getdialog('上传头像失败了');
        },
        '.txuploadbtn',
        '.touxiang img',
        function() {
            $('.touxiang span').removeClass('bg');
        }
    );

    touxiangUploader.on('beforeFileQueued', function() {
        this.reset();
    });


    function uploader(id, successFun, errorFun, uploadId, previewId, fileQueuedFun) {
        var uploader = {};
        var auto = uploadId == undefined ? true : false;
        if (uploadId != undefined) {
            $(uploadId).click(function() {
                uploader.webuploder.upload();
            });
        }
        uploader.webuploder = WebUploader.create({
            pick: {
                id: id,
                label: '选择照片',
                multiple: false,
            },
            timeout: 0,
            formData: {
                app: 'portal',
                filetype: 'image'
            },
            accept: {
                extensions: 'png,jpg,jpeg,heic,mov'
            },
            swf: '/static/js/webuploader/Uploader.swf',
            chunked: true, //开启分片
            auto: auto,
            chunkSize: 2 * 1024 * 1024, // 单位B
            compress: false,
            server: "/user/asset/webuploader?_ajax=1",
            // 禁掉全局的拖拽功能。这样不会出现图片拖进页面的时候，把图片打开。
            disableGlobalDnd: true,
            fileNumLimit: 1,
            runtimeOrder: 'html5,flash',
            //fileSizeLimit: 200 * 1024 * 1024,    // 200 M
            fileSingleSizeLimit: 10 * 1024 * 1024 // 单位B
        });
        uploader.webuploder.on('uploadSuccess', successFun);
        uploader.webuploder.on('uploadError', errorFun);
        uploader.webuploder.on('fileQueued', fileQueuedFun);
        if (previewId != undefined) {
            uploader.webuploder.on('fileQueued', function(file) {
                uploader.webuploder.makeThumb(file, function(error, src) { //webuploader方法
                    if (error) {
                        getdialog('图片预览失败');
                    } else {
                        $(previewId).attr('src', src);
                    }
                });
            });
        }
        uploader.webuploder.on('error', function(res, file) {
            // console.log(res)
            // if (res == 'Q_EXCEED_NUM_LIMIT') {
            //     getdialog('只能上传一个文件');
            // }
            if (res == 'Q_TYPE_DENIED') {
                getdialog('文件类型错误，请上传jpg,png,jpeg,heic格式图片');
            }
            if (res == 'F_EXCEED_SIZE') {
                getdialog('文件过大,最大限制10M');
            }
        });

        return uploader.webuploder;
    }

    //---------------------------
    //--------收藏列表-----------
    //---------------------------
    Handlebars.registerHelper('image', function(value) {
        if (value == '') {
            return '/upload/portal/2.png';
        } else {
            return value;
        }
    });
    Handlebars.registerHelper('date', function(value) {
        var date = new Date();
        date.setTime(value * 1000);
        return date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
    });
    requestCollect(1, function(res) {
        if (res.status == 1) {
            $('.collect-num').html(res.data.count);
            //渲染第一页数据
            var tpl = $('#collect-item').html();
            var template = Handlebars.compile(tpl);
            $('.collect-list').html(template(res.data));
            layui.use('laypage', function() {
                layui.laypage.render({
                    elem: 'collect_page', //注意，这里的 test1 是 ID，不用加 # 号
                    count: res.data.count, //数据总数，从服务端得到
                    next: '>',
                    limit: 6,
                    jump: function(obj, first) {
                        if (!first) {
                            requestCollect(obj.curr, function(res) {
                                var tpl = $('#collect-item').html();
                                var template = Handlebars.compile(tpl);
                                $('.collect-list').html(template(res.data));
                            });
                        }
                    }
                });
            });
            if ($('.list li').length == 0) {
                $('.layui-laypage-next').hide();
            }
        }
    })

    function requestCollect(page, fun) {
        request({
            url: '/api/article/collect_list',
            dataType: 'json',
            type: 'post',
            data: { page: page, len: 6 },
            success: fun
        }, true)
    }

    //---------------------------
    //--------消息列表-----------
    //---------------------------
    initSysMessage();

    function initSysMessage() {
        requestSysMessage(1, function(res) {
            if (res.status == 1) {
                $('.message-num').html(res.data.count);
                //渲染第一页数据
                var tpl = $('#message-item').html();
                var template = Handlebars.compile(tpl);
                $('.message-list').html(template(res.data));
                layui.use('laypage', function() {
                    layui.laypage.render({
                        elem: 'message_page', //注意，这里的 test1 是 ID，不用加 # 号
                        count: res.data.count, //数据总数，从服务端得到
                        next: '>',
                        limit: 6,
                        jump: function(obj, first) {
                            if (!first) {
                                requestSysMessage(obj.curr, function(res) {
                                    var tpl = $('#message-item').html();
                                    var template = Handlebars.compile(tpl);
                                    $('.message-list').html(template(res.data));
                                });
                            }
                        }
                    });
                });
                if ($('.message li').length == 0) {
                    $('.layui-laypage-next').hide();
                }
            }
        })
    }

    function requestSysMessage(page, fun) {
        request({
            url: '/api/sysmessage/index',
            dataType: 'json',
            type: 'post',
            data: { page: page, len: 6 },
            success: fun
        }, true)
    }

    // function click() {
    $('.message').on('click', 'a', function() {
        var url = $(this).attr('href');
        //console.log(window.frames['layui-layer-iframe2'].document.getElementById('url').getAttribute('href'));
        /* Act on the event */
        if (url != '') {
            layer.open({
                type: 2,
                title: false,
                closeBtn: 0,
                shadeClose: true,
                //  btn: ['查看详情', '取消'],
                // yes: function(index, layero) {
                //     // console.log(window.frames['layui-layer-iframe2'].document.getElementById('url').getAttribute('href'));
                //    window.open(window.frames['layui-layer-iframe2'].document.getElementById('url').getAttribute('href'));
                // },
                // btn2: function(index, layero) {
                //     layer.closeAll();
                // },
                area: ['1200px', '50%'],
                content: url
            });
        }


        // console.log('3'+window.frames['layui-layer-iframe2'].document.getElementById('url').getAttribute('href'));
        return false;
    })
    // }

    /*我的预约*/
    layui.use('laydate', function() {
        var laydate = layui.laydate;

        //执行一个laydate实例
        laydate.render({
            elem: '#time1', //指定元素
            theme: '#2F343B'
        });
    });
    layui.use('laydate', function() {
        var laydate = layui.laydate;

        //执行一个laydate实例
        laydate.render({
            elem: '#time2', //指定元素
            theme: '#2F343B'
        });
    });
    var status = 0;
    var venue = 0;
    var address = 0;
    var page = 1;
    var len = 10;
    var start_time = 0;
    var end_time = 0;
    //reservation();
    function reservation() {
        request({
            url: '/api/room/book_filter',
            type: 'post',
            dataType: 'json',
            data: { status: status, venue: venue, address: address, start_time: start_time, end_time: end_time, page: page, len: len },
            success: function(res) {
                if (res.status == 1) {
                    // console.log(res.data.status)
                    $('#count').html(res.data.count)
                    var Time = res.data.list;
                    for (var i = 0; i < Time.length; i++) {
                        Time[i].start_time = data_format(Time[i].start_time);
                        Time[i].end_time = data_format(Time[i].end_time);
                    }
                    var tpl = $('#reservation-item').html();
                    var template = Handlebars.compile(tpl);
                    $('#reservation').html(template(res.data.list));
                    // var nowTime=new Date().getTime();
                    for (var j = 0; j < Time.length; j++) {
                        // var time=new Date(Time[j].end_time).getTime();
                        if (res.data.list[j].status == 1) {
                            $('#reservation .state').eq(j).html('已完成');
                            $('#reservation .operation').eq(j).html('已结束');
                            $('#reservation .operation').eq(j).addClass('expire');
                        }
                    }

                }
            }
        }, true)
    }

    $('#reservation').on('click', '.operation', function() {
        var id = $(this).parent('td').parent('tr').attr('id');
        if ($(this).html() == '取消预约') {
            layer.open({
                type: 1,
                content: $('#tishi'), //这里content是一个普通的String
                shadeClose: true,
                closeBtn: 0,
                area: '500px',
                end: function() {
                    layer.closeAll();
                    $('#tishi').hide();
                }
            });
            $('body').off('click', '#tishi .yesbtn');
            $('body').on('click', '#tishi .yesbtn', function() {
                $('#tishi').hide();
                request({
                    url: '/api/room/cancel',
                    type: 'post',
                    dataType: 'json',
                    data: { id: id },
                    success: function(res) {
                        if (res.status == 1) {
                            layer.open({
                                type: 1,
                                content: $('#queren'), //这里content是一个普通的String
                                shadeClose: true,
                                closeBtn: 0,
                                area: '500px',
                                end: function() {
                                    layer.closeAll();
                                    $('#queren').hide();
                                }
                            });

                        } else {
                            // getdialog(res.msg);
                            noLogin(res);
                            console.log(res.msg)
                        }
                    }
                }, true)
            })
            $('body').on('click', '#queren .yesbtn2', function() {
                layer.closeAll();
                getcount();
            })
            $('body').on('click', '#tishi .nobtn', function() {
                layer.closeAll();
            })
        }

    })
    selected();

    function selected() {
        request({
            url: '/api/filter',
            type: 'post',
            dataType: 'json',
            data: {},
            success: function(res) {
                //区域
                var len = res.data.area.length;
                var city = '<option value="0" data-index="0">区域</option>';
                for (var i = 0; i < len; i++) {
                    city += '<option value=' + res.data.area[i].id + '>' + res.data.area[i].name + '</option>';
                }
                $('#city').html(city);
                $('#city').change(function() {
                    var id = $(this).val();
                    //var son = '<option value="0" >全部</option>';
                    var son = "";
                    if (id == 0) {
                        son += '<option value="0" >区域</option>';
                    } else {
                        son += '<option value="0" >区域</option>';
                        for (var i = 0; i < len; i++) {
                            if (res.data.area[i].id == id) {
                                var sonlen = res.data.area[i].son.length;
                                for (var j = 0; j < sonlen; j++) {
                                    son += '<option value=' + res.data.area[i].son[j].id + '>' + res.data.area[i].son[j].name + '</option>';
                                }
                            }
                        }
                    }
                    $("#son").html(son);
                });

                //场馆
                var length = res.data.venue.length;
                var venue = '<option value="0" >场馆</option>';
                for (var i = 0; i < length; i++) {
                    venue += '<option value=' + res.data.venue[i].id + '>' + res.data.venue[i].name + '</option>';
                }
                $("#venue").html(venue);


            }
        }, true);
    }
    getcount();

    function getcount() {
        request({
            url: '/api/room/book_filter',
            type: 'post',
            data: { status: status, venue: venue, address: address, start_time: start_time, end_time: end_time, page: page, len: len },
            success: function(response) {
                if (response.status == 1) {
                    layui.use('laypage', function() {
                        var laypage = layui.laypage;
                        laypage.render({
                            elem: 'test3',
                            count: response.data.count,
                            limit: 8,
                            next: '>',
                            jump: function(obj, first) {
                                page = obj.curr;
                                //len=obj.limit;
                                reservation();
                            }
                        })
                    })
                    if (response.data.list == 0) {
                        $('.layui-laypage-next').hide();
                    }

                }
            }
        }, true)

    }
    $("#state").change(function() {
        status = $(this).val();
    })
    $("#city").change(function() {
        address = $(this).val();
    })
    $("#son").change(function() {
        address = $(this).val();
    })
    $("#venue").change(function() {
        venue = $(this).val();
    })
    //$("#time1").change(function(){
    //    $(this).val();
    //    console.log($(this).val())
    //})
    $("#find").click(function() {
        if ($('#time1').val() != '') {
            start_time = new Date($('#time1').val() + ' 00:00:00').getTime() / 1000;
        }
        if ($('#time2').val() != '') {
            end_time = new Date($('#time2').val() + ' 23:59:59').getTime() / 1000;
        }
        // start_time = new Date($('#time1').val()).getTime() / 1000;
        // end_time = new Date($('#time2').val()).getTime() / 1000;
        getcount();
    })

    /*我的活动*/
    layui.use('laydate', function() {
        var laydate = layui.laydate;

        //执行一个laydate实例
        laydate.render({
            elem: '#time3', //指定元素
            theme: '#2F343B'
        });
    });
    layui.use('laydate', function() {
        var laydate = layui.laydate;

        //执行一个laydate实例
        laydate.render({
            elem: '#time4', //指定元素
            theme: '#2F343B'
        });
    });
    var zhuangtai = 0;
    var fenye = 1;
    var tiaoshu = 8;
    var kaishishijian = 0;
    var jieshushijian = 0;

    function huodong() {
        request({
            url: '/api/activity/myact',
            type: 'post',
            dataType: 'json',
            data: { status: zhuangtai, start_time: kaishishijian, end_time: jieshushijian, page: fenye, len: tiaoshu },
            success: function(res) {
                if (res.status == 1) {
                    // console.log(res.data.status)
                    $('#activitycount').html(res.data.count)
                    var activityTime = res.data.list;
                    for (var i = 0; i < activityTime.length; i++) {
                        activityTime[i].start_time = data_format(activityTime[i].start_time);
                        activityTime[i].end_time = data_format(activityTime[i].end_time);
                        activityTime[i].baoming_time = data_format(activityTime[i].baoming_time);
                    }
                    var activitiestpl = $('#activities-item').html();
                    var template = Handlebars.compile(activitiestpl);
                    $('#activities').html(template(res.data.list));
                    // var nowTime=new Date().getTime();
                    for (var j = 0; j < activityTime.length; j++) {
                        // var time=new Date(Time[j].end_time).getTime();
                        if (res.data.list[j].status == 1) {
                            //   $('#activities .zhuangtai').eq(j).html('已完成');
                            $('#activities .activitybtn').eq(j).html('已结束');
                            $('#activities .activitybtn').eq(j).addClass('expire');
                        }
                    }

                }
            }
        }, true)
    }

    $('#activities').on('click', '.activitybtn', function() {
        var id = $(this).parent('td').parent('tr').attr('id');
        if ($(this).html() == '取消报名') {
            layer.open({
                type: 1,
                content: $('#xiaoxi'), //这里content是一个普通的String
                shadeClose: true,
                closeBtn: 0,
                area: '500px',
                end: function() {
                    layer.closeAll();
                    $('#xiaoxi').hide();
                }
            });
            $('body').on('click', '#xiaoxi .huodongyesbtn', function() {
                $('#xiaoxi').hide();
                request({
                    url: '/api/activity/cancel',
                    type: 'post',
                    dataType: 'json',
                    data: { id: id },
                    success: function(res) {
                        if (res.status == 1) {
                            layer.open({
                                type: 1,
                                content: $('#huodongqueren'), //这里content是一个普通的String
                                shadeClose: true,
                                closeBtn: 0,
                                area: '500px',
                                end: function() {
                                    layer.closeAll();
                                    $('#huodongqueren').hide();
                                }
                            });

                        } else {
                            // getdialog(res.msg);
                            console.log(res.msg)
                        }
                    }
                }, true)
            })
            $('body').on('click', '#huodongqueren .huodongyesbtn2', function() {
                layer.closeAll();
                getlen();
            })
            $('body').on('click', '#xiaoxi .huodongnobtn', function() {
                layer.closeAll();
            })
        }

    })
    getlen();

    function getlen() {
        request({
            url: '/api/activity/myact',
            type: 'post',
            data: { status: zhuangtai, start_time: kaishishijian, end_time: jieshushijian, page: fenye, len: tiaoshu },
            success: function(response) {
                if (response.status == 1) {
                    layui.use('laypage', function() {
                        var laypage = layui.laypage;
                        laypage.render({
                            elem: 'huodong',
                            count: response.data.count,
                            limit: 8,
                            next: '>',
                            jump: function(obj, first) {
                                page = obj.curr;
                                //len=obj.limit;
                                huodong();
                            }
                        })
                    })
                    if (response.data.list == 0) {
                        $('.layui-laypage-next').hide();
                    }

                }
            }
        }, true)

    }
    $("#zhuangtai").change(function() {
        zhuangtai = $(this).val();
    })

    //$("#time1").change(function(){
    //    $(this).val();
    //    console.log($(this).val())
    //})
    $("#seek").click(function() {
        if ($('#time3').val() != '') {
            kaishishijian = new Date($('#time3').val() + ' 00:00:00').getTime() / 1000;
        }
        if ($('#time4').val() != '') {
            jieshushijian = new Date($('#time4').val() + ' 23:59:59').getTime() / 1000;
        }
        // kaishishijian = new Date($('#time3').val()).getTime() / 1000;
        // jieshushijian = new Date($('#time4').val()).getTime() / 1000;
        getlen();
    })

    /*参与历史*/
    var historypage = 1;
    var historylen = 8;

    function history(page, len) {
        request({
            url: '/api/user/play_history',
            type: 'POST',
            dataType: 'json',
            data: { page: page, len: len },
            success: function(res) {
                console.log(res)
                $('.canjiacount').html(res.data.count);
                $('.jifen').html(res.data.total_score);
                var tpl = $("#history-item").html();
                //预编译模板
                var template = Handlebars.compile(tpl);
                var historytime = res.data.list;
                for (var i in historytime) {
                    historytime[i].start_time = data_format(historytime[i].start_time);
                    historytime[i].end_time = data_format(historytime[i].end_time);

                }
                var html = template(res.data.list);
                $('#history').html(html);
                for (var j in historytime) {
                    $('.number1').eq(j).html(Number(j) + 1);
                }
            }
        }, true)
    }
    request({
        url: '/api/user/play_history',
        type: 'POST',
        dataType: 'json',
        data: {},
        success: function(res) {
            if (res.data.count > 0) {
                layui.use('laypage', function() {
                    var laypage = layui.laypage;
                    //执行一个laypage实例
                    laypage.render({
                        elem: 'test4', //注意，这里的 test1 是 ID，不用加 # 号
                        count: res.data.count, //数据总数，从服务端得到
                        next: '>',
                        limit: 8,
                        jump: function(obj, first) {
                            history(obj.curr, obj.limit);
                        }
                    });
                });
            }
        }
    }, true)
    /*报名结果*/
    var resultpage = 1;
    var resultlen = 8;
    var shenhe = 3;
    $('#shenhe').change(function() {
        shenhe = $(this).val();
        resultcount();
    })

    function result(page, len) {
        request({
            url: '/api/user/baoming_info',
            type: 'POST',
            dataType: 'json',
            data: { page: page, len: len, status: shenhe },
            success: function(res) {
                $('.resultcount').html(res.data.count);
                var tpl = $("#result-item").html();
                //预编译模板
                var template = Handlebars.compile(tpl);
                var historytime = res.data.list;
                for (var i in historytime) {
                    historytime[i].start_time = data_format(historytime[i].start_time);
                    historytime[i].end_time = data_format(historytime[i].end_time);
                    historytime[i].created_at = data_format(historytime[i].created_at);

                }
                var html = template(res.data.list);
                $('#result').html(html);
                for (var j in historytime) {
                    $('.xuhao').eq(j).html(Number(j) + 1);
                    if (res.data.list[j].status == 0) {
                        $('.shenhe').eq(j).html('审核中');
                    }
                    if (res.data.list[j].status == 1) {
                        $('.shenhe').eq(j).html('已通过');
                    }
                    if (res.data.list[j].status == 2) {
                        $('.shenhe').eq(j).html('未通过');
                    }
                }
            }
        }, true)
    }
    resultcount();

    function resultcount() {
        request({
            url: '/api/user/baoming_info',
            type: 'POST',
            dataType: 'json',
            data: { status: shenhe },
            success: function(res) {

                layui.use('laypage', function() {
                    var laypage = layui.laypage;
                    //执行一个laypage实例
                    laypage.render({
                        elem: 'test5', //注意，这里的 test1 是 ID，不用加 # 号
                        count: res.data.count, //数据总数，从服务端得到
                        next: '>',
                        limit: 8,
                        jump: function(obj, first) {
                            result(obj.curr, obj.limit);
                        }
                    });
                });
                if (res.data.list <= 0) {
                    $('.layui-laypage-next').css('display', 'none');
                }
            }
        }, true)
    }

    layui.use('laydate', function() {
        var laydate = layui.laydate;

        //执行一个laydate实例
        laydate.render({
            elem: '#birthday', //指定元素
            theme: '#2F343B'
        });
    });
    //
    volun_profile();

    //志愿者资料管理
    function volun_profile() {
        request({
            url: '/api/user/volun_profile',
            dataType: 'json',
            data: {},
            success: function(res) {
                if (res.status == 1) {
                    defaultTem(res);
                    $.ajax({
                        url: '/api/area/volun_index',
                        success: function(result) {
                            var html = '';
                            for (var i = 0; i < result.data.length; i++) {
                                if (result.data[i]['id'] == res.data.venue) {
                                    html += '<option value="' + result.data[i].id + 'selectd">' + result.data[i].name + '</option>';
                                } else {
                                    html += '<option value="' + result.data[i].id + '">' + result.data[i].name + '</option>';
                                }
                            }
                            $('#volun_area').append(html);
                            layui.use('form', function() {
                                var form = layui.form;
                                form.render('select');
                            });
                        }
                    })



                }
            }


        }, true);
    }

    function defaultTem(res) {
        //积分
        $("#score").html(res.data.score);
        //排名
        $('#order').html(res.data.rank);
        //真实姓名
        $('#real_name').html(res.data.user_realname);
        //性别
        if (res.data.sex == 1) {
            $('.xingbie').eq(0).attr('checked', 'checked');
            layui.use('form', function() {
                var form = layui.form;
                form.render('radio');
            });
        } else {
            $('.xingbie').eq(1).attr('checked', 'checked');
            layui.use('form', function() {
                var form = layui.form;
                form.render('radio');
            });
        }
        //生日
        if (res.data.birthday != 0) {
            var birthday = timetrans(res.data.birthday)
            $('#birthday').val(birthday);
        }
        //民族
        if (res.data.nation != 0) {
            $('#nation').val(res.data.nation);
        }
        //电话
        var phone = $('#mobile_phone').val(res.data.mobile);
        console.log(res.data.mobile);
        //特长
        $('#speciality').val(res.data.speciality);

        //才艺照片
        if (res.data.volun_skill_imgs != '') {
            photos = JSON.parse(res.data.volun_skill_imgs);
        }
        if (res.data.img == undefined) {
            res.data.img = [];
        }
        var html = '';
        for (var i = 0; i < res.data.img.length; i++) {
            html += '<div style="position:relative;width: 170px;height: 109px;margin-right: 20px;margin-bottom:20px;"  class="f-left parent"><div style="width: 170px;height: 109px;border:1px solid #e6e6e6;cursor: pointer;text-align: center;line-height: 160px;color: #2F343B;font-size: 14px;overflow:hidden;" id="shenfenzhengbefore1" onclick="uploadOneImage(\'图片上传\',\'#thumbnail' + i + '\');"><input type="hidden" name="post[thumb]" id="thumbnail' + i + '" value="' + photos[i] + '"><img src="' + res.data.img[i] + '" id="thumbnail' + i + '-preview" style="cursor: pointer;height:109px;" /></div><div class="delete" style="width:15px;height: 15px;position:absolute;top:1px;right:0;background:url(/themes/simpleboot3/public/assets/whgcms/images/read/delete.png) no-repeat;cursor:pointer;"></div></div>';
        }

        $('#volun_photo').html(html);
    }

    $('body').on('click', '.delete', function() {
        $(this).parent().remove();
    })



    //点击提交按钮
    $('#button').click(function() {
        console.log(111);
        var birthday = $('#birthday').val().trim();
        var nation = $('#nation').val().trim();
        var mobile = $('#mobile_phone').val().trim();
        var area = $('#volun_area').val();
        var Speciality = $('#speciality').val().trim();
        var photos = [];
        for (var j = 0; j < $('.volun_photo>div').length; j++) {
            var img = $('.volun_photo input').eq(j).val();
            photos.push(img);
        }
        var imgs = photos.join(',');

        request({
            url: '/api/user/modify_volun_profie',
            type: 'POST',
            dataType: 'json',
            data: { birthday: birthday, nation: nation, tel: mobile, area: area, speciality: Speciality, photos: imgs },
            beforeSend: function() {
                if (mobile == '') {
                    if ($('.prompt').length == 0) {

                        $('.mobile_phone').append('<span class="prompt">电话号码不能为空！</span>');
                    }
                    $('#mobile_phone').focus(function(event) {
                        $('.mobile_phone .prompt').remove();
                    });
                    return false;
                } else if (!phoneReg.test(mobile)) {
                    if ($('.prompt').length == 0) {
                        $('.mobile_phone').append('<span class="prompt">请输入正确格式！</span>');

                    }
                    $('#mobile_phone').focus(function(event) {
                        $('.mobile_phone .prompt').remove();
                    });
                    return false;
                }
            },
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    layui.use('layer', function() {
                        layer.open({
                            title: '提示',
                            content: '信息提交成功',
                            btn: ['确认', '取消'],
                            yes: function(index, layero) {
                                window.location.href = '/portal/my/index';
                            },
                            btn2: function(index, layero) {
                                layer.closeAll();
                            },
                            cancel: function() {
                                return false;
                            },
                            btnAlign: 'c',
                            shade: 0.3,
                            scrollbar: false
                        });
                    });
                }
            }
        }, true)
    });

    //点击取消
    $('.cancel').click(function(event) {
        window.location.href = '/portal/my/index';
    });
});



function timetrans(date) {
    var date = new Date(date * 1000); //如果date为13位不需要乘1000
    var Y = date.getFullYear() + '-';
    var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
    var D = (date.getDate() < 10 ? '0' + (date.getDate()) : date.getDate()) + ' ';
    return Y + M + D;
}

function ling(val) {
    return val < 10 ? '0' + val : val;
}

/**
 *   timestamp 时间戳 秒
 *   decollator分隔符 默认 '-'
 *   bulin 是否补零 默认补零
 */
function data_format(timestamp, buling) {
    var date = new Date();
    date.setTime(timestamp * 1000);
    var year = date.getFullYear();
    var month = date.getMonth() + 1;
    var day = date.getDate();
    var hours = date.getHours();
    var minutes = date.getMinutes();
    // decollator = decollator == undefined ? '-' : decollator;
    if (buling == false) {
        return year + "-" + month + "-" + day + " " + hours + ":" + minutes;
    } else {
        return year + "-" + ling(month) + "-" + ling(day) + " " + ling(hours) + ":" + ling(minutes);
    }
}

contacts_profile()
function contacts_profile() {
    request({
        url: '/api/contacts/getContacts',
        dataType: 'json',
        type: 'POST',
        data: { "type":2,"guardian":"呵呵", "name" :"deshun" ,"id_card" :"120111199402093514","mobile":"13702028462" },
        success: function(res) {
            console.log(res)
        }
    }, true);
}