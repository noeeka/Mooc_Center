$(function() {
    selectNav(81);
    var id = getParam('id', -1);
    hot();
    //hotread();
    var token = getCookie('token');

    function hot() {
        var id = getParam('id');
        $.ajax({
            url: '/api/volunrecruit/index',
            type: 'post',
            dataType: 'json',
            data: {},
            success: function(response) {
                if (response.status == 1) {
                    dat = response.data.list.slice(0, 3);
                    console.log(dat)
                    var hotTpl = $("#hot-template").html();
                    var hotCmp = Handlebars.compile(hotTpl);
                    var hotFun = hotCmp(dat);
                    $('#Hot').html(hotFun);
                }
            }
        })
    }
    request({
        url: '/api/volunrecruit/read',
        type: 'POST',
        dataType: 'json',
        data: { id: id },
        success: function(res) {
            if (res.status == 1) {
                $('title').text(res.data.title);
                var tpl = $("#recruiteread-template").html();
                //预编译模板
                var template = Handlebars.compile(tpl);
                var time = res.data;
                var nowdata = new Date().getTime() / 1000;
                time.start_time = getdate(time.start_time);
                time.end_time = getdate(time.end_time);
                time.baoming_start_time = getdate(time.baoming_start_time);
                time.baoming_end_time = getdate(time.baoming_end_time);
                var html = template(time);
                //输入模板
                console.log(nowdata);
                console.log(new Date(time.baoming_end_time).getTime() / 1000)
                $('#recruiteread').html(html);
                if (res.data.yibaoming == 1) {
                    $('#enroll').html('已报名');
                    $('#enroll').removeClass('activity-active');
                    $('#enroll').addClass('expire');
                    return false;
                }
                if (res.data.yibaoming == 2) {
                    $('#enroll').html('审核中...');
                    $('#enroll').removeClass('activity-active');
                    $('#enroll').addClass('expire');
                    return false;
                }
                if (nowdata < new Date(time.baoming_start_time).getTime() / 1000) {
                    $('#enroll').html('未开始');
                    $('#enroll').removeClass('activity-active');
                    $('#enroll').addClass('expire');
                    return false;
                }
                if (nowdata > new Date(time.baoming_end_time).getTime() / 1000) {
                    $('#enroll').html('报名停止');
                    $('#enroll').removeClass('activity-active');
                    $('#enroll').addClass('expire');
                    return false;
                }
                if (nowdata > new Date(time.end_time).getTime() / 1000) {
                    $('#enroll').html('已结束');
                    $('#enroll').removeClass('activity-active');
                    $('#enroll').addClass('expire');
                    return false;
                }
                $('body').on('click', '#enroll', function() {
                    if (token == '') {
                        getdialog('请登录', '/portal/login/login');
                    } else if (res.data.user_role != 2) {
                        getdialog('您还不是志愿者，请先注册');
                    } else if (res.data.num >= res.data.max_num) {
                        getdialog('报名人数已满');
                    } else {
                        baoming();
                    }
                })
            }
        }
    }, true)


    function baoming() {
        request({
            url: '/api/volunrecruit/baoming',
            type: 'POST',
            dataType: 'json',
            data: { id: id },
            success: function(res) {
                if (res.status == 1) {
                    getdialog('报名成功,正在审核...');
                    $('#enroll').html('审核中...');
                    $('#enroll').removeClass('activity-active');
                    $('#enroll').addClass('expire');
                }
            }
        }, true)
    }
})

function getdate(time) {
    var date = new Date();
    date.setTime(time * 1000);
    var month = date.getMonth() + 1;
    month = month > 9 ? month : '0' + month;
    var day = date.getDate();
    day = day > 9 ? day : '0' + day;
    var hour = date.getHours();
    hour = hour > 9 ? hour : '0' + hour;
    var minute = date.getMinutes();
    minute = minute > 9 ? minute : '0' + minute;
    return date.getFullYear() + '-' + month + '-' + day + ' ' + hour + ':' + minute;
}