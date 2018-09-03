$(function(){
    //初始化页面
    request({
        url:'/api/user/volun_profile',
        type:'post',
        dataType:'json',
        success:function(res){
            if(res.status == 1){
                $('.froala-element').html(res.data.speciality);
            }else{
                noLogin(res.code, res.msg);
            }
        }
    }, true);
    $('.closeBtn').click(function(){
        // var val1 = $('#val').val();
        var val1 = $('.froala-element').val();
        // return false;
        if(val1 == ''){
            alert('特长备注不能为空');
        }else{
            data = {};
            data['speciality'] = val1;
            saveInfo(data);
            location.href='/wx/my/volData';
        }
    });
    function saveInfo(data){
        var ret = false;
        request({
            url:'/api/user/modify_volun_profie',
            data:data,
            type:'post',
            dataType:'json',
            async:false,
            success:function(res){
                ret = true;
            },
            error:function(){
                console.log('ajax error');
            }
        }, true);
        return ret;
    }
});
