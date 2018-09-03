$(function(){
    // var kv = $('input').val();

    var nameEl = document.getElementById('sex');//选择性别

    var data1 = [
        {
            text: '男',
            value: 1
        }, {
            text: '女',
            value: 2
        },{
            text: '保密',
            value: 0
        }
    ];
    var picker = new Picker({
        data: [data1],
        selectedIndex: [0, 1, 2]
        // title: '选择性别'
    });

    request({
        url:'/api/my/index',
        type: 'GET',
        dataType: 'json',
        data: {},
        success: function(response) {
            if (response.status == 1) {
                console.log(response)
                $('#nickname').html(response.data.user_nickname);
                $('#name').html(response.data.user_realname);

                if (response.data.sex == 1) {//男
                    $('#sex').html('男');
                } else if (response.data.sex == 2) {//女
                    $('#sex').html('女');
                } else {//保密
                    $('#sex').html('保密');
                }
                $('#birthday').val(response.data.format_birthday);
                $('#address').html(response.data.address);

            }
        }
    },true)
    // picker.on('picker.change', function (index, selectedIndex) {
    //     console.log(index);
    //     console.log(selectedIndex);
    // });
    picker.on('picker.select', function (selectedVal, selectedIndex) {
        nameEl.innerText = data1[selectedIndex[0]].text;
    })
    picker.on('picker.valuechange', function (selectedVal, selectedIndex) {
        // console.log(selectedVal);
        // console.log(selectedIndex);
        if($('#sex').html()=='女'){
            var sex=2;
        }else if($('#sex').html()=='男'){
            var sex=1;
        }else{
            var sex=0;
        }
        request({
            url: '/api/my/save',
            data: {
                sex: sex,
            },
            dataType: 'json',
            type: 'post',
            success: function (res) {
                if (res.status == 1) {
                    alert('更新成功');

                } else {
                    // alert('更新失败');
                    noLogin(res.code,res.msg);
                }
                return false;
            }
        },true)
    });

    nameEl.addEventListener('click', function () {
        picker.show();
    });
    $('body').on('click','.picker-mask',function(){
        picker.hide();
    })

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
            // var theSelectData = calendar.value;
            var birthday= $('#birthday').val();
            request({
                url: '/api/my/save',
                data: {
                    birthday: birthday,
                },
                dataType: 'json',
                type: 'post',
                success: function (res) {
                    if (res.status == 1) {
                        alert('更新成功');
                    } else {
                        // alert('更新失败');
                        noLogin(res.code,res.msg);
                    }
                    return false;
                }
            },true)
        },
        'onClose': function() { /*取消时触发事件*/ },
    });


})

    // $('body').click('click','.confirm',function(){
    //     var sex= $('#sex').html();
    //     console.log(sex)
    //
    //     request({
    //         url: '/api/my/save',
    //         data: {
    //             sex: sex,
    //         },
    //         dataType: 'json',
    //         type: 'post',
    //         success: function (res) {
    //             if (res.status == 1) {
    //                 alert('更新成功');
    //             } else {
    //                 alert('更新失败');
    //             }
    //             return false;
    //         }
    //     },true)
    // })





