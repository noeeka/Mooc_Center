$(function () {
var id = getUrl('id');
var token = getCookie('token');
console.log(token)
//获取当前时间转为时间戳
var nowdata = Date.parse(new Date(getNowFormatDate()))/1000;
console.log(id)
    request({
        url: '/api/Volunrecruit/read/id/' + id,
        type: 'post',
        dataType: 'json',
        async: false,
        success: function (result) {
            console.log(result)
            if (result.status == 1) {
                var data = result.data;
                var timestamp = data.baoming_end_time;
                data.baoming_end_time = timestampToTime(data.baoming_end_time);
                data.baoming_start_time = timestampToTime(data.baoming_start_time);
                data.start_time = timestampToTime(data.start_time);
                data.end_time = timestampToTime(data.end_time);
                var template = Handlebars.compile($("#figure").html());
                $(".figure").html(template(data));

                var template1 = Handlebars.compile($("#detail").html());
                $(".detail").html(template1(data));

                var template2 = Handlebars.compile($("#introduction").html());
                $(".introduction").html(template2(data));
                if(data.yibaoming == 1){
                    $('.baoming').text('已报名').css({'background':'#acacac'});
                }else if(data.yibaoming == 2){
                    $('.baoming').text('审核中').css({'background':'#acacac'});
                }else if(nowdata > timestamp){
                    $('.baoming').text('已过期').css({'background':'#acacac'});
                }else{
                    $('.baoming').click(function(){
                        if(token == null){
                            alert('请登录后注册为志愿者再操作!');
                        }else if(data.user_role != 2){
                            alert('请注册为志愿者再操作!');
                        }else if(data.num == data.max_num){
                            alert('报名人数已满');
                        }else{
                            var baomingnum = data.num;
                            console.log(baomingnum)
                            request({
                                url: '/api/volunrecruit/baoming',
                                type: 'post',
                                dataType: 'json',
                                data:{
                                    id:id
                                },
                                async: false,
                                success: function (res) {
                                    if(res.status == 1){
                                        alert('报名成功');
                                        $('.baoming').text('审核中').css({'background':'#acacac'});
                                    }
                                }
                            },true)
                        }
                    })
                }
            }
        }
    },true);

})