$(function(){
    $('.passport').click(function(){
        request({
            url:'/api/passport/logout',
            type:'post',
            dataType:'json',
            success:function(res){

                    setCookie('token', "", -1);
                    setCookie('uid', 0, -1);
                    setCookie('salt', "" , -1);
                    //alert('退出登陆成功！','/wx/my');
                layer.open({
                    title: [
                        '提示',
                        'font-size:0.21rem'
                    ],
                    yes: function (index, layero) {
                        window.location.href = updateUrl('/wx/my');
                        layer.closeAll();
                    },
                    content:"退出登录成功",
                    btn: '确定',
                    style: 'font-size:0.18rem;border-radius:0.18rem;height:23%'
                });
            },
            error:function(){
                console.log('ajax error');
            }
        }, true);
    });
});

function updateUrl(url,key){
    var key= (key || 't') +'=';  //默认是"t"
    var reg=new RegExp(key+'\\d+');  //正则：t=1472286066028
    var timestamp=+new Date();
    if(url.indexOf(key)>-1){ //有时间戳，直接更新
        return url.replace(reg,key+timestamp);
    }else{  //没有时间戳，加上时间戳
        if(url.indexOf('\?')>-1){
            var urlArr=url.split('\?');
            if(urlArr[1]){
                return urlArr[0]+'?'+key+timestamp+'&'+urlArr[1];
            }else{
                return urlArr[0]+'?'+key+timestamp;
            }
        }else{
            if(url.indexOf('#')>-1){
                return url.split('#')[0]+'?'+key+timestamp+location.hash;
            }else{
                return url+'?'+key+timestamp;
            }
        }
    }
}
