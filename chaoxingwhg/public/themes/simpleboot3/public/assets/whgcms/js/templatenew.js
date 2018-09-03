$(function() {
    $('.classification').show();
    $('.hot').show();
    $('.area').show();
    $('.type').show();
    $('.logo').click(function() {
        window.location.href = "/";
    })
    // $('.search .sousuo').click(function() {
    //     /* Act on the event */
    //     $('.search input').animate({
    //         width: '115px'
    //     }, 'slow', function() {
    //         $('.search .close').css('display', 'block');

    //     });
    // });
    // $('.search .close').click(function() {
    //     /* Act on the event */
    //     $('.search input').animate({
    //         width: 0
    //     }, 'slow', function() {
    //         $('.search .close').css('display', 'none');

    //     });
    // });
    //加载网站基本信息
    $.ajax({
        url: '/api/baseinfo/read',
        dataType: 'json',
        success: function(res) {
            if (res.status == 1) {
                $('.site_title').text(res.data.site_title);
                $('.venue_tel').text(res.data.venue_tel);
                $('.venue_addr').text(res.data.venue_addr);
                $('.venue_addr').text(res.data.venue_addr);
                $('.logo-parent .logo').attr('src', res.data.second_page_logo);
            }
        }
    });


    //加载友链
    $.ajax({
        url: '/api/link/index',
        dataType: 'json',
        async: true,
        success: function(res) {
            if (res.status == 1) {
                var html = '';
                for (var i in res.data) {
                    var item = res.data[i];
                    html += '<li><a href="' + item['url'] + '" target="' + item['target'] + '">' + item['name'] + '</a></li>';
                }
                $('.friendly').html(html);
            }
        }
    });
    search();

    function search() {
        $('body').on('click', '.sousuo', function() {
            var searchval = $('#sousuoinput').val() || '';
            searchval = searchval.trim();
            if (searchval == '') {
                return false;
            }
            window.location.href = "/portal/search/index/?kv=" + searchval;
        });
        //回车键
        $(window).keydown(function(event) {
            if (event.keyCode == 13) {
                var searchval = $('#sousuoinput').val() || '';
                searchval = searchval.trim();
                if (searchval != '') {
                    window.location.href = "/portal/search/index/?kv=" + searchval;
                }
            }
        })
    }
});

//set cookie
function setCookie(c_name, value, expiredays) {
    var exdate = new Date();
    exdate.setTime(exdate.getTime() + expiredays * 1000);
    document.cookie = c_name + "=" + escape(value) +
        ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString()) + ";path=/";
}

//选中导航栏
function selectNav(id) {
    $('.nav a').removeClass('active');
    $('#nav' + id).addClass('active');
}

//获取地址栏参数
function getParam(k, d) {
    var kv = location.search.substring(1).split('&').map(function(v) {
        return v.split('=')
    })
    var params = {};
    for (var i in kv) {
        var val = kv[i];
        if (val[0] == k) {
            return val[1] == undefined ? d : decodeURI(val[1]);
        }
    }
    return d;
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

//发送请求
function request(ajax, sign) {
    if (sign != undefined) {
        if (ajax.data == undefined) ajax.data = {};
        var salt = getCookie('salt');
        var token = getCookie('token');
        ajax.data.timestamp = ((new Date()).getTime()) / 1000;
        ajax.data.token = token;
        ajax.data.sign = hex_sha1(token + salt + ajax.url.toLowerCase() + ajax.data.timestamp);
    }
    $.ajax(ajax);
}

function getdialog(msg, url, func) {

    var href = window.open;
    layui.use('layer', function() {
        layer.open({
            title: '提示',
            content: msg,
            btn: ['确认', '取消'],
            yes: function(index, layero) {
                if (func != undefined) {
                    func();
                }
                if (url != undefined) {
                    layer.closeAll();
                    href = window.open(url);
                } else {
                    layer.closeAll();
                }
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


checkLogin();

function checkLogin() {
    var token = getCookie('token');
    /*登录*/
    if (token == '') {
        $('.denglu').show();
        $('.zhuce').hide();
    } else {
        $('.denglu').hide();
        $('.zhuce').show();
        request({
            url: '/api/user/read',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status == 1) {
                    if (response.data.avatar != '') {
                        $('.tlogin img').attr('src', response.data.avatar_url);
                    }
                    var nickname = response.data.user_nickname;
                    var mobile = response.data.mobile;
                    if (nickname != '' && nickname.length > 5) {
                        $('.treg a').html(nickname.slice(0, 5) + '...');
                    } else if (nickname != '' && nickname.length <= 5) {
                        $('.treg a').html(nickname);
                    } else if (nickname == '') {
                        $('.treg a').html(mobile);
                    }
                } else {
                    $('.denglu').show();
                    $('.zhuce').hide();
                }

            }
        }, true)

        $(".zhuce").hover(function() {
            $(".out").css("display", "block");
        }, function() {
            $(".out").css("display", "none");
        });

        $('.exit').click(function(event) {
            request({
                url: '/api/passport/logout',
                type: 'post',
                dataType: 'json',
                success: function(response) {
                    if (response.status == 1) {
                        setCookie('uid', "", -1);
                        setCookie('token', "", -1);
                        setCookie('salt', "", -1);
                        if (location.pathname.search('/portal/my') != -1) {
                            location.href = '/';
                            return;
                        }
                        $('.denglu').show();
                        $('.zhuce').hide();
                    }
                }
            }, true)
        });

    }
}


//时间戳
function data_format(timestamp, decollator, buling) {
    var date = new Date();
    date.setTime(timestamp * 1000);
    var year = date.getFullYear();
    var month = date.getMonth() + 1;
    var day = date.getDate();
    decollator = decollator == undefined ? '-' : decollator;
    if (buling == false) {
        return year + decollator + month + decollator + day;
    } else {
        return year + decollator + ling(month) + decollator + ling(day);
    }
}

//补零
function ling(val) {
    return val < 10 ? '0' + val : val;
}

//判断是否登录
// 10004 请求认证失败
// 10005 token不能为空
// 10006 签名不能为空
// 10007 时间戳不能为空
// 10008 时间戳错误
// 10009 签名异常
// 10010 登陆过期
function noLogin(res, isRedirectLogin) {
    var regex = /^1000[4-9]$/;
    var url = isRedirectLogin == true ? '/portal/login/login' : undefined;
    if (regex.test(res.code)) {
        getdialog('请登录后再操作', url);
    } else if (res.code == 10010) {
        getdialog('登录过期，请重新登录', url);
    } else {
        getdialog(res.msg);
    }

}

//截取字符串，超出num省略号显示
function setstr(str, num) {
    if (str.length > num) {
        str = str.substring(0, num) + "...";
    }
}