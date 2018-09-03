//添加当前状态
function nowActive(dom, domClass) {
    $(dom).click(function() {
        $(this).siblings().removeClass(domClass);
        $(this).parents('ul').find('a').removeClass(domClass)
        $(this).addClass(domClass);
    })
}

//设置cookie
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
// $.ajax({
//  url:'axxxx',
//  type:'post',
//  dataType:
// });

//var page = 0;
// request(
//  {
//      url:'axxxx',
//      type:'post',
//      dataType:...
//      data:{id:'1111'},
//      success:function(res){
//          page = res.data.page;
//          html(res)
//      },
//      error:function(){

//      }
//  },
//  true
// );
// function html(res){
//  $('.div').append();
// }
// console.log(111);


// function request(object, sign) {
//     if (sign == true) {
//         $token = getCookie('token');
//         if (token == '') {
//             timestamp = Math.ceil((new Date()).getTime() / 1000);
//             object.data['token'] = token;
//             object.data['timestamp'] = timestamp;
//         }
//     }
//     $.ajax(object);
// }

//获取url？后面的参数
function getUrl(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return "Please input keywords here";
}

//验证cookie
function request(ajax, sign) {
    if (sign != undefined) {
        if (ajax.data == undefined) ajax.data = {};
        if (typeof(ajax.data) != 'object') {
            ajax.data = strtoobj(ajax.data);
        }
        ajax.data.timestamp = ((new Date()).getTime()) / 1000;
        var token = getCookie('token');
        var salt = getCookie('salt');
        ajax.data.token = token;
        ajax.data.sign = hex_sha1(token + salt + ajax.url + ajax.data.timestamp);
    }
    // console.log(ajax.data);
    // console.log(ajax);
    // return;
    $.ajax(ajax);
}

function strtoobj(str) {
    var arr = str.split('&');
    var res = {};
    for (var i = 0; i < arr.length; i++) {
        var temp = arr[i].split('=');
        res[temp[0]] = temp[1] == undefined ? null : temp[1];
    }
    return res;
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
//base64加密解密
function Base64() {

    // private property    
    _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

    // public method for encoding    
    this.encode = function(input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;
        input = _utf8_encode(input);
        while (i < input.length) {
            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);
            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;
            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }
            output = output +
                _keyStr.charAt(enc1) + _keyStr.charAt(enc2) +
                _keyStr.charAt(enc3) + _keyStr.charAt(enc4);
        }
        return output;
    }

    // public method for decoding    
    this.decode = function(input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;
        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
        while (i < input.length) {
            enc1 = _keyStr.indexOf(input.charAt(i++));
            enc2 = _keyStr.indexOf(input.charAt(i++));
            enc3 = _keyStr.indexOf(input.charAt(i++));
            enc4 = _keyStr.indexOf(input.charAt(i++));
            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;
            output = output + String.fromCharCode(chr1);
            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }
        }
        output = _utf8_decode(output);
        return output;
    }

    // private method for UTF-8 encoding    
    _utf8_encode = function(string) {
        string = string.replace(/\r\n/g, "\n");
        var utftext = "";
        for (var n = 0; n < string.length; n++) {
            var c = string.charCodeAt(n);
            if (c < 128) {
                utftext += String.fromCharCode(c);
            } else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            } else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }
        return utftext;
    }

    // private method for UTF-8 decoding    
    _utf8_decode = function(utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;
        while (i < utftext.length) {
            c = utftext.charCodeAt(i);
            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            } else if ((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            } else {
                c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }
        }
        return string;
    }
}
