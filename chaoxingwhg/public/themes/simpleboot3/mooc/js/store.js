// JavaScript Document
 var getCookie = function (name) {
       var arr;
       var reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
       if (arr = document.cookie.match(reg))
         return unescape(arr[2]);
       else
         return null;
     };
var usertype = getCookie('user_type');
console.log(usertype)