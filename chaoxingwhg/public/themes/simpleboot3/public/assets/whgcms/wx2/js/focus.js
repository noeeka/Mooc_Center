$(function () {
    var phoneReg = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
    var active_id = getUrl('id');
    $.ajax({
        url: '/api/activity/read/id/' + active_id,
        type: 'post',
        dataType: 'json',
        async: false,
        success: function (result) {
            if (result.status == 1) {

                $('#title').text(result.data.title);
                $('#address').text(result.data.address);

                var date = new Date();
                date.setTime(result.data.start_time * 1000);
                var start_time =  date.getFullYear() + '.' + (date.getMonth() + 1) + '.' + date.getDate() + ' ' + date.getHours()+':'+date.getMinutes();

                var date = new Date();
                date.setTime(result.data.end_time * 1000);
                var end_time =  date.getFullYear() + '.' + (date.getMonth() + 1) + '.' + date.getDate() + ' ' + date.getHours()+':'+date.getMinutes();

                $('#time').text(start_time +' ~ ' + end_time);

                $('input[name="sign_type"]').val(result.data.sign_type);
                if (result.data.sign_type == 3) {
                    $('.type3').show();
                }
            }
        }
    });


    $('.sub').on('click' , function () {
        sign(mobile);
    });

    function sign(mobile) {
        var sign_type = $('input[name="sign_type"]').val();
        var data = {}
        if (sign_type == 1) {
            data = {
                "activityId" : active_id ,
                'id_card' : $('#id_card').val(),
            };
        } else if(sign_type == 3) {
            var mobile = $('#mobile').val();
            var name = $('#name').val();
            var id_card = $('#id_card').val();
            if (!phoneReg.test(mobile)) {
                alert('手机号码格式不正确！')
                return false;
            }
            data = {
                "activityId" : active_id ,
                'mobile' : mobile,
                'id_card' :id_card,
                'name':name
            };
        }
        $.ajax({
            url: '/api/activity/signIn',
            type: 'post',
            dataType: 'json',
            async: false,
            data:data,
            success: function (result) {
                if (result.status == 1) {
                    window.location.href = '/wx/focus/signsuccess'+'?time='+((new Date()).getTime());
                   // alert('签到成功')
                } else if ( parseInt(result.code) == 10009) {
                    window.location.href = '/wx/focus/signover'+'?time='+((new Date()).getTime());
                    //alert('重复签到')
                } else if ( parseInt(result.code) == 10012) {
                    alert('签到已结束')
                } else if ( parseInt(result.code) == 10013) {
                    alert('签到未开始')
                } else if ( parseInt(result.code) == 10007) {
                    alert(result.msg)
                }else  {
                    window.location.href = '/wx/focus/signerror'+'?time='+((new Date()).getTime());
                    //alert('签到失败')
                }
                $('#mobile').val('');
            }
        });
    }
});