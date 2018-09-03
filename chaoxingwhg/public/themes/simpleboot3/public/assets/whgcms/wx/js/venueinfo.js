$(function () {
    var swiper = new Swiper('.swiper-container', {
        autoplay: 3000,
        loop: true,
        pagination: '.swiper-pagination',
        paginationClickable: true
    });

    request({
        url: '/api/room/read/id/' + id,
        type: 'post',
        dataType: 'json',
        async: false,
        success: function (result) {
            if (result.status == 1) {
                var template = Handlebars.compile($("#introduce").html());
                $(".introduce").html(template(result.data));

                var template1 = Handlebars.compile($("#detail").html());
                $(".detail").html(template1(result.data));

                var template2 = Handlebars.compile($("#introduction").html());
                $(".introduction").html(template2(result.data));

                var template3 = Handlebars.compile($("#details").html());
                $(".details").html(template3(result.data));

            }
        }
    }, true);

    $(".yuyuebtn").click(function () {
        enroll(id);
    })

    //场馆预约
    function enroll(room_id) {
        request({
            url: '/api/room/is_login',
            type: 'post',
            dataType: 'json',
            success: function (result) {
                if (result.status == 1) {
                    //已登陆
                    location.href="/wx//venue/yuyue?id="+room_id;
                } else {
                    //未登录
                    var regex = /^1000[4-9]$/;
                    if(regex.test(result.code)){
                        alert('未登录，请先登录','/wx/login/login');
                    }else{
                        alert(result.msg);
                    }
                }
            }
        }, true)
    }

})