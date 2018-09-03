/*get cooike*/
// var uid = getCookie('uid');
// var token = getCookie('token');
// var salt = getCookie('salt');
//初始化导航
// $.ajax({
//     url:C.interface.menuindex,
//     dataType:'json',
//     success:function(res){
//         if(res.status == 1){
//             var html = '';
//             for(var i = 0; i < res.data.length; i++){
//                 var j = res.data[i];
//                 if(i == res.data.length - 1){
//                     html += '<li class="nav-last"><a href="'+j['resource']+'" ';
//                 }else{
//                     html += '<li><a href="'+j['resource']+'" ';
//                 }
//                 if(j.type != 0){
//                     html += 'target="_blank" ';
//                 }
//                 html += '>'+j.name+'</a></li>';
//             }
//             $('.head .nav').html(html);
//         }
//     }
//     // error:function(){
//     //     console.log('ajax error');
//     // }
// });
// if (token == '') {
//     $('.denglu').show();
//     $('.zhuce').hide();
// } else {
//     $('.denglu').hide();
//     $('.zhuce').show();
//     request({
//         url:C.interface.readUser,
//         type: 'POST',
//         dataType: 'json',
//         success: function(response) {
//             var picTpl = $("#pic").html();
//             var picCmp = Handlebars.compile(picTpl);
//             var picFun = picCmp(response.data);
//             if(response.status==1){
//                 $('#xx').html(picFun);
//                 if(response.data.avatar==''){
//                 $('.tlogin img').attr('src','/pc/assets/images/图层25.png');
//              }
//              var nickname=response.data.nickname;
//              var mobile=response.data.mobile;
//              if(nickname!=''){
//                 $('.treg a').html(nickname.slice(0,5)+'...');
//              }
//             }
//
//         }
//     }, true)
//
//     $(".zhuce").hover(function() {
//         $(".out").css("display", "block");
//     }, function() {
//         $(".out").css("display", "none");
//     });
//
// }
// title
function title1() {
    var title = $('.navlist .active a').text();
    $('title').text(title);
}

//添加当前状态
function nowActive(dom, domClass) {
    $(dom).click(function () {
        $(this).siblings().removeClass(domClass);
        $(this).parents('ul').find('a').removeClass(domClass)
        $(this).addClass(domClass);
    })
}

//获取url？后面的参数
function getUrl(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return decodeURI(r[2]);
    return "Please input keywords here";
}

//发送请求
function request(ajax, sign) {
    if (sign != undefined) {
        if (ajax.data == undefined) ajax.data = {};
        ajax.data.timestamp = ((new Date()).getTime()) / 1000;
        ajax.data.token = token;
        ajax.data.sign = hex_sha1(token + salt + ajax.url.toLowerCase() + ajax.data.timestamp);
    }
    $.ajax(ajax);
}


//补零
function ling(val) {
    return val < 10 ? '0' + val : val;
}

/**
 *   timestamp 时间戳 秒
 *   decollator分隔符 默认 '-'
 *   bulin 是否补零 默认补零
 */
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

function getdialog(msg, url) {
    var href = window.open;
    layui.use('layer', function () {
        layer.open({
            title: '提示',
            content: msg,
            btn: ['确认', '关闭'],

            yes: function (index, layero) {
                console.log(111);
                if (url != undefined) {
                    layer.closeAll();
                    href = window.open(url);
                } else {
                    layer.closeAll();
                }


            },
            btn2: function (index, layero) {
                layer.closeAll();
            },
            cancel: function () {
                return false;
            },
            btnAlign: 'c',
            anim: 1,
            shade: 0.3,
            scrollbar: false
        });
    });
}


$('.out').click(function (event) {
    request({
        url: C.interface.logout,
        type: 'post',
        dataType: 'json',
        success: function (response) {
            if (response.status == 1) {
                setCookie('uid', "", -1);
                setCookie('token', "", -1);
                setCookie('salt', "", -1);
                $('.denglu').show();
                $('.zhuce').hide();
            }
        }
    }, true)
});

function setHash(k, v) {
    location.hash = location.hash.substr(1).split('&').map(function (item) {
        var arr = item.split('=');
        return arr[0] == k ? arr[0] + '=' + v : item;
    }).join('&');
}

function getHash(k){
    var hash = location.hash.match("(^|#)"+k+"=([^&]*)(&|$)");
    return hash == null ? null : decodeURI(hash[2]);
}
console.log(getHash('dsada'));
setHash('dsada', 22222)