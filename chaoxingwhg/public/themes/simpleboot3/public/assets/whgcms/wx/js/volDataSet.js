var key = getUrl('key');
var val = getUrl('val');
var kv = {nation:'民族',tel:'手机号',speciality:'特长备注'};
$('#val').val(val);
$('.closeBtn').click(function(){
    var val1 = $('#val').val();
    if(val1 == ''){
        alert(kv[key]+'不能为空');
    }else{
        if(key == 'tel' && !/^[1-9][0-9]{10}$/.test(val1)){
            alert('手机号格式不正确');
        }else{
            data = {};
            data[key] = val1;
            saveInfo(data);
            location.href = '/wx/my/volData';
        }
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