$(function() {
    selectNav(81);
    request({
        url: '/api/volunregister/index',
        type: 'POST',
        dataType: 'json',
        data: {},
        success: function(res) {
            if (res.status == 1) {
                if (res.data.status == 2) {
                    $('.realname').html('<div style="line-height:36px;">' + res.data.realname + '</div>');
                    $('.renzhengover').hide();
                    // $('.idcard').html(res.data.shenfenzheng);
                    // $('#shenfenzhengbefore').css('background-image', 'url(' + res.data.imgs[0] + ')')
                    // $('#shenfenzhengafter').css('background-image', 'url(' + res.data.imgs[1] + ')')
                } else if (res.data.status == 0) {
                    $('.realname').html('<div style="line-height:36px;">' + res.data.realname + '</div>');
                    $('.renzhengover').hide();
                    // $('.renzhengover').html('<p style="margin:25px 0  25px 0;color:#1C2438;font-size:16px;margin-left:40px;">实名认证正在审核中...</p>');
                } else {
                    $('.yirenzheng').hide();
                    $('.weirenzheng').show();
                }
            }
        }
    }, true);
    //加载区域
    $.ajax({
        url: '/api/area/volun_index',
        dataType: 'json',
        success: function(res) {
            for (var x in res.data) {
                $('#city').append('<option value="' + res.data[x]['id'] + '">' + res.data[x]['name'] + '</option>');
            }
            layui.form.render('select')
        }
    });
    //手机验证正则
    var phoneReg = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
    $('.button').click(function() {
        if ($('.renzhengover').is(':visible')) {
            var realname = $('#realname').val().trim();

        } else {
            var realname = $('.realname>div').html();
        }
        var sex = $('input[name=sex]:checked').val().trim();
        var birthday = $('#test1').val().trim();
        var nation = $('#nation').val().trim();
        var mobile = $('#mobile').val().trim();
        var ID = $('#idcard').val().trim();
        var sfzimg = [];
        for (var i = 0; i < $('.weirenzheng>div').length; i++) {
            var value = $('.weirenzheng input').eq(i).val();
            sfzimg.push(value);

        }
        var area = $('.layui-this').attr('lay-value');
        var Speciality = $('#Speciality').val().trim();
        var photos = []
        for (var j = 0; j < $('.photos>div').length; j++) {
            var img = $('.photos input').eq(j).val();
            photos.push(img);

        }
        request({
            url: '/api/volunregister/register',
            type: 'POST',
            dataType: 'json',
            data: { realname: realname, sex: sex, birthday: birthday, nation: nation, mobile: mobile, ID: ID, sfzimg: sfzimg, area: area, Speciality: Speciality, photos: photos },
            beforeSend: function() {
                if (realname == '') {
                    if ($('.prompt').length == 0) {
                        $('.realname').append('<span class="prompt">姓名不能为空！</span>');
                    }
                    $('#realname').focus(function(event) {
                        $('.realname .prompt').remove();
                    });
                    return false;
                }
                if (birthday == '') {
                    if ($('.prompt').length == 0) {

                        $('.birthday').append('<span class="prompt">出生日期不能为空！</span>');
                    }
                    $('#test1').focus(function(event) {
                        $('.birthday .prompt').remove();
                    });
                    return false;
                }
                if (mobile == '') {
                    if ($('.prompt').length == 0) {

                        $('.mobile').append('<span class="prompt">电话号码不能为空！</span>');
                    }
                    $('#mobile').focus(function(event) {
                        $('.mobile .prompt').remove();
                    });
                    return false;
                } else if (!phoneReg.test(mobile)) {
                    if ($('.prompt').length == 0) {
                        $('.mobile').append('<span class="prompt">请输入正确格式！</span>');

                    }
                    $('#mobile').focus(function(event) {
                        $('.mobile .prompt').remove();
                    });
                    return false;
                }

                if ($('.renzhengover').is(':visible')) {
                    if (ID == '') {

                        if ($('.prompt').length == 0) {

                            $('.idcard').append('<span class="prompt">身份证号不能为空！</span>');
                        }
                        $('#idcard').focus(function(event) {
                            $('.idcard .prompt').remove();
                        });
                        return false;
                    } else if (!/\d{17}[\d|x]|\d{15}/.test(ID)) {
                        if ($('.prompt').length == 0) {

                            $('.idcard').append('<span class="prompt">身份证号不合法！</span>');
                        }
                        $('#idcard').focus(function(event) {
                            $('.idcard .prompt').remove();
                        });
                        return false;
                    }
                    if (sfzimg[0] == '') {
                        if ($('.prompt').length == 0) {

                            $('.weirenzheng').append('<span class="prompt">身份证正面不能为空！</span>');
                        }
                        $('#shenfenzhengbefore1').click(function(event) {
                            $('.weirenzheng .prompt').remove();
                        });
                        return false;
                    } else if (sfzimg[1] == '') {
                        if ($('.prompt').length == 0) {

                            $('.weirenzheng').append('<span class="prompt">身份证背面不能为空！</span>');
                        }
                        $('#shenfenzhengafter1').click(function(event) {
                            $('.weirenzheng .prompt').remove();
                        });
                        return false;
                    }
                }

                if (photos[0] == '') {
                    if ($('.prompt').length == 0) {

                        $('.photos').append('<span class="prompt">才艺照片至少上传三张！</span>');
                    }
                    $('#photo1').click(function(event) {
                        $('.photos .prompt').remove();
                    });
                    return false;
                } else if (photos[1] == '') {
                    if ($('.prompt').length == 0) {

                        $('.photos').append('<span class="prompt">才艺照片至少上传三张！</span>');
                    }
                    $('#photo2').click(function(event) {
                        $('.photos .prompt').remove();
                    });
                    return false;
                } else if (photos[2] == '') {
                    if ($('.prompt').length == 0) {

                        $('.photos').append('<span class="prompt">才艺照片至少上传三张！</span>');
                    }
                    $('#photo3').click(function(event) {
                        $('.photos .prompt').remove();
                    });
                    return false;
                }
            },
            success: function(res) {
                if(res.status == 1) {
                    $('.weirenzheng strong').hide();
                    $('.photos strong').hide();
                    // window.location.href = '/portal/volunteer/index';
                    layui.use('layer', function() {
                        layer.open({
                            title: '提示',
                            content: '注册成功，等待审核...',
                            btn: ['确认', '取消'],
                            yes: function(index, layero) {
                                window.location.href = '/portal/volunteer/index';
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
                } else  {
                    var errmsg = res.msg;
                    layui.use('layer', function() {
                        layer.open({
                            title: '提示',
                            content: errmsg,
                            btn: ['确认', '取消'],
                            yes: function(index, layero) {
                                layer.closeAll();
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
    $('.cancel').click(function(event) {
        window.location.href = '/portal/volunteer/index';
    });
    layui.use('laydate', function() {
        var laydate = layui.laydate;

        //执行一个laydate实例
        laydate.render({
            elem: '#test1', //指定元素
            theme: '#2F343B'
        });
    });
})