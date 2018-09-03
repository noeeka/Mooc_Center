$(function() {
    $('.classification').show();
    $('.hot').show();
    $('.area').show();
    $('.type').show();
    $('.logo').click(function() {
        window.location.href = "/portal3/index";
    })
    //加载网站基本信息
    $.ajax({
        url: '/api/baseinfo/read',
        dataType: 'json',
        success: function(res) {
            if (res.status == 1) {
                $('.site_title').text(res.data.site_title);
                $('.banquan').text(res.data.copyright);
                $('.venue_tel').text(res.data.venue_tel);
                $('.venue_addr').text(res.data.venue_addr);
            }
        }
    });
    nav(1150, 32);

    function nav(navwidth, outerWidth) {
        $.ajax({
            url: '/api/navigation/index',
            dataType: 'json',
            async: false,
            data:{
            	type:5
            },
            success: function(res) {
                console.log('bbb')
                var liwidth = 0;
                var mr = 0;
                var reg = /\d+/;
                var flag = true;
                if (res.status == 1) {
                    if (window.location.href == 'https://' + document.domain + '/porta3/my/index') {
                        var html = '<li><a id="nav0" href="/portal3/index">首页</a></li>';

                    } else {
                        var html = '<li><a id="nav0" class="active" href="/portal3/index">首页</a></li>';
                    }
                    $('.nav').append(html);
                    liwidth += $('.nav>li:first-child').width() + outerWidth;
                    for (var i in res.data) {
                        var item = res.data[i];
                        if (liwidth > navwidth) {
                            //更多
                            if (flag == true) {
                                //去除当前最后一个nav item 添加更多item后将其加入更多的子列表中
                                // var index = $('.nav>li:last-child').index();
                                html = '<li class="more" style="position: relative;cursor: pointer;"><a href="javascript:;">更多<span class="arrowbot"></span><span class="arrowtop"></span></a><ul class="navmore"></ul></li>';
                                //超出第一个的一级导航
                                var lastchild = '<li>' + $('.nav>li:last-child').html() + '</li>';
                                //清除超出的一级导航
                                $('.nav>li:last-child').remove();
                                //添加更多
                                $('.nav').append(html);
                                lastchild += '<li><a id="nav' + item['id'] + '" href="' + setUrlVal(item['url'], 'nid', item['id']) + '" target="' + item['target'] + '">' + item['name'] + '</a></li>';
                                $('.nav .navmore').append(lastchild);
                                flag = false;
                            } else {
                                //往更多里写
                                html = '<li><a id="nav' + item['id'] + '" href="' + setUrlVal(item['url'], 'nid', item['id']) + '" target="' + item['target'] + '">' + item['name'] + '</a></li>';
                                $('.nav .navmore').append(html)
                            }
                        } else {
                            var subnav = item.sub_nav;
                            if (subnav.length != 0) {
                                html = '<li><a id="nav' + item['id'] + '" href="' + setUrlVal(item['url'], 'nid', item['id']) + '" target="' + item['target'] + '">' + item['name'] + '<span class="arrowbot"></span><span class="arrowtop"></span></a><ul class="subnav slide"></ul></li>';
                                $('.nav').append(html);
                                for (var j in subnav) {
                                    var subitem = subnav[j];
                                    var subnavHtml = '';
                                    //添加一级导航和二级导航
                                    subnavHtml += '<li><a id="subnav' + subitem['id'] + '" href="' + setUrlVal(subitem['url'], 'nid', item['id'] + ',' + subitem['id']) + '" target="' + subitem['target'] + '">' + subitem['name'] + '</a></li>';
                                    $("#nav" + item['id']).next('.subnav').append(subnavHtml);
                                }
                            } else {
                                //添加一级导航
                                html = '<li><a id="nav' + item['id'] + '" href="' + setUrlVal(item['url'], 'nid', item['id']) + '" target="' + item['target'] + '">' + item['name'] + '</a></li>';
                                $('.nav').append(html);
                            }

                        }
                        liwidth += $('.nav>li:last-child').width() + outerWidth;

                    }
                }
            }
        })
        $('body').on('mouseover', '.nav>li', function() {
            $(this).find('.subnav').show();
            $(this).find('.arrowbot').css('display', 'none');
            $(this).find('.arrowtop').css('display', 'inline-block');
        })
        $('body').on('mouseout', '.nav>li', function() {
            $(this).find('.subnav').hide();
            $(this).find('.arrowbot').css('display', 'inline-block');
            $(this).find('.arrowtop').css('display', 'none');
        })
    }
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
                if($('.friendly li').length==0){
                    console.log(111)
                    $('.friendly').siblings('p').hide();
                }
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
            window.location.href = "/porta3/search/index/?kv=" + searchval;
        });
        //回车键
        $(window).keydown(function(event) {
            if (event.keyCode == 13) {
                var searchval = $('#sousuoinput').val() || '';
                searchval = searchval.trim();
                if (searchval != '') {
                    window.location.href = "/porta3/search/index/?kv=" + searchval;
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
    var nid = getParam('nid', '');
    nid.split(',').map(function(v) {
        var first = $('.nav>li>a#nav' + v);
        if (first.length > 0) {
            //一级
            $('.nav>li>a').removeClass('active');
            first.addClass('active');
        } else {
            if ($('.nav .subnav a#subnav' + v).length > 0) {
                //二级
                $('.nav .subnav a').removeClass('active');
                $('.nav .subnav a#subnav' + v).addClass('active');
            } else {
                //更多
                $('.nav>li>a').removeClass('active');
                $('.navmore a').removeClass('active');
                $('.navmore a#nav' + v).addClass('active');
            }
        }
    });
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
                        if (location.pathname.search('/porta3/my') != -1) {
                            location.href = '/portal3/index';
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

$('body').on('mouseout', '.more', function() {
    $('.navmore').hide()
})
$('body').on('mouseover', '.more', function() {
    $('.navmore').show()
})

//时间戳
function data_format(timestamp, decollator, buling, monthDay) {
    var date = new Date();
    date.setTime(timestamp * 1000);
    var year = date.getFullYear();
    var month = date.getMonth() + 1;
    var day = date.getDate();
    decollator = decollator == undefined ? '-' : decollator;
    if (monthDay == true) {
        if (buling == false) {
            return month + decollator + day;
        } else {
            return ling(month) + decollator + ling(day);
        }
    } else {
        if (buling == false) {
            return year + decollator + month + decollator + day;
        } else {
            return year + decollator + ling(month) + decollator + ling(day);
        }
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
    var url = isRedirectLogin == true ? '/portal3/login/login' : undefined;
    if (regex.test(res.code)) {
        getdialog('请登录后再操作', url);
    } else if (res.code == 10010) {
        getdialog('登录过期，请重新登录', url);
    } else {
        getdialog(res.msg);
    }

}

function title1() {
    var title = $('.nav li a.active').text();
    $('title').html(title + '—超星数字文化馆');
}

// hot();

function hot() {
    var id = getParam('id');
    console.log(id)
    $.ajax({
        url: '/api/article/index',
        type: 'GET',
        dataType: 'json',
        data: { len: 3, cid: 8, sort: 'hot' },
        success: function(response) {
            if (response.status == 1) {
                var hotTpl = $("#hot-template").html();
                var hotCmp = Handlebars.compile(hotTpl);
                var hotFun = hotCmp(response.data);
                $('#Hot').html(hotFun);
            }
        }
    })
}

// hotread();

function hotread() {
    var id = getParam('id');
    $.ajax({
        url: '/api/article/index',
        type: 'GET',
        dataType: 'json',
        data: { len: 7, cid: 8, sort: 'hot' },
        success: function(response) {
            var res = response.data.list.slice(3);
            if (response.status == 1) {
                var readTpl = $("#read-template").html();
                var readCmp = Handlebars.compile(readTpl);
                var readFun = readCmp(res);
                $('#read').html(readFun);
            }
        }
    })
}

//截取字符串，超出num省略号显示
function setstr(str, num) {
    if (str.length > num) {
        str = str.substring(0, num) + "...";
    }
}

//设置地址栏参数
function setParam(k, v) {
    history.pushState(null, null, setUrlVal(location.href, k, v));
}

//获取地址栏参数
function getParam(k, def) {
    return getUrlVal(location.href, k, def);
}

//修改某个url中参数的值
function setUrlVal(url, k, v) {
    if (url != undefined) {
        var search = url.split('?')[1] == undefined ? "" : url.split('?')[1];
        if (getUrlVal(url, k) == null) {
            var search = search == "" ? k + '=' + v : search + '&' + k + '=' + v;
            return url.split('?')[0] + '?' + search;
        } else {
            return url.split('?')[0] + '?' + search.split('&').map(function(item) {
                var arr = item.split('=');
                return arr[0] == k ? arr[0] + '=' + v : item;
            }).join('&');
        }
    }
}

//获取某个url中参数的值
function getUrlVal(url, key, def) {
    def = def == undefined ? null : def;
    var search = url.split('?')[1] == undefined ? "" : url.split('?')[1];
    var search = search.match("(^|&)" + key + "=([^&]*)(&|$)");
    return search == null ? def : decodeURI(search[2]);
}

Handlebars.registerHelper('buildHomePageUrl', function(url, more_url, options) {
    return setUrlVal(url, 'nid', getUrlVal(more_url, 'nid', 0));
});

Handlebars.registerHelper('buildSecondUrl', function() {
    var args = [];
    for (var i = 0; i < arguments.length - 1; i++) {
        args[args.length] = arguments[i];
    }
    var url = args.join('');
    var nid = getUrlVal(location.href, 'nid', 0);
    return setUrlVal(url, 'nid', getUrlVal(location.href, 'nid', 0));
});
