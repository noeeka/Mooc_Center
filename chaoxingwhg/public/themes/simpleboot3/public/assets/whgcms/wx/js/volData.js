//初始化页面
request({
    url:'/api/user/volun_profile',
    type:'post',
    dataType:'json',
    success:function(res){
        if(res.status == 1){
            setDefault(res.data);
        }else{
            noLogin(res.code, res.msg);
        }
    }
}, true);
$.ajax({
    url:'/api/area/volun_index',
    dataType:'json',
    success:function(res){
        if(res.data.length > 0){
            var nameEl = document.getElementById('address');
            var data = [];
            var index = [];
            for(var i = 0 ; i < res.data.length; i++){
                data[data.length] = {
                    text:res.data[i]['name'],
                    value:res.data[i]['id'],
                }
                index[i] = i;
            }
            var picker = new Picker({
                data: [data],
                selectedIndex: index
                // title: '选择性别'
            });
            picker.on('picker.select', function(selectedVal, selectedIndex) {
                nameEl.innerText = data[selectedIndex[0]].text;
            });

            picker.on('picker.valuechange', function(selectedVal, selectedIndex) {
                saveInfo({area:selectedVal[0]});
            });

            nameEl.addEventListener('click', function() {
                picker.show();
            });
            $('body').on('click', '.picker-mask', function() {
                picker.hide();
            })
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
//设置默认值
function setDefault(data){
    //名称
    $('#name span:last-child').text(data.user_realname);
    //性别
    if(data.sex == 1){
        $('#sex').text('男');
    }else if(data.sex == 2){
        $('#sex').text('女');
    }else{
        $('#sex').text('保密');
    }
    //出生日期
    $('#birthday').val(data.format_birthday);
    //民族
    $('#nation').attr('href', '/wx/my/volDataSet?key=nation&val='+data.nation);
    $('#nation span:last-child').text(data.nation);
    //手机号
    $('#tel').attr('href', '/wx/my/volDataSet?key=tel&val='+data.mobile);
    $('#tel span:last-child').text(data.mobile);
    //活动区域
    if(data.area.length > 0){
        $('#address').text(data.area[0]['name']);
    }
    //才艺照片
    $('#photos').attr('href', '/wx/my/volDataPhoto?id='+data.id);
    if(data.img == undefined){
        data.img = [];
    }
    data.img = data.img.slice(0, 3);
    for(var i in data.img){
        $('#photos').append('<div style="background-image: url('+data.img[i]+');"></div>');
    }
    //特长备注
    $('#speciality').attr('href', '/wx/my/volDataSetEdit?key=speciality&val='+data.speciality);
    // $('#speciality').attr('href', '/wx/my/volDataSetEdit?key=speciality&val='+data.speciality_html);
    $('#speciality span:last-child').text(data.speciality);
}

//调用日期插件
// $('#birthday').date();
var calendar = new datePicker();
calendar.init({
    'trigger': '#birthday',
    /*按钮选择器，用于触发弹出插件*/
    'type': 'date',
    /*模式：date日期；datetime日期时间；time时间；ym年月；*/
    // 'minDate': '1900-1-1',
    /*最小日期*/
    // 'maxDate': '2100-12-31',
    /*最大日期*/
    'onSubmit': function() { /*确认时触发事件*/
        var theSelectData = calendar.value;
        saveInfo({birthday:theSelectData});
    },
    'onClose': function() { /*取消时触发事件*/ }
});