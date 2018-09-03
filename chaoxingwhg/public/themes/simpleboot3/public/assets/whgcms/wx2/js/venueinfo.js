$(function () {


    request({
        url: '/api/room/read/id/' + id,
        type: 'post',
        dataType: 'json',
        async: false,
        success: function (result) {
            if (result.status == 1) {
                 var template = Handlebars.compile($("#banner-list").html());
                 $(".banner .swiper-wrapper").html(template(result.data.banner));

                var template1 = Handlebars.compile($("#detail").html());
                $(".detail").html(template1(result.data));

                var template2 = Handlebars.compile($("#introduction").html());
                $(".introduction").html(template2(result.data));

                var template3 = Handlebars.compile($("#details").html());
                $(".details").html(template3(result.data));
                var swiper = new Swiper('.swiper-container', {
                    autoplay: 3000,
                    loop: true,
                    pagination: '.swiper-pagination',
                    paginationClickable: true
                });
                //隐藏显示详情
                getint();
                $('.address p').text(result.data.full_address)
            }
        }
    }, true);
    //隐藏显示详情
    function getint(){
        var len = 55;
        var text1 = $('.introduction p').html();
        // console.log(text1)
        var jianjie = $('.introduction .jianjie');
        // console.log(jianjie)
        var btn = $('.introduction .btn');
        // console.log(btn)
        jianjie.html(text1.substring(0, len));
        btn.html(text1.length > len ? "【展开】" : "");
        // btn.href = "javascript:void(0)"
        // btn.classList.add("fontcolor");
        btn.click(function() {
            if(btn.html().indexOf("展开") > 0) { //如果a中含有"展开"则显示"收起"
                btn.html("【收起】");
                jianjie.html(text1);
            } else {
                btn.html("【展开】");
                jianjie.html(text1.substring(0, len));
            }
        })
    }
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
                    location.href="/wx2/venue/yuyue?id="+room_id;
                } else {
                    //未登录
                    var regex = /^1000[4-9]$/;
                    if(regex.test(result.code)){
                        alert('未登录，请先登录','/wx2/login/login');
                    }else{
                        alert(result.msg);
                    }
                }
            }
        }, true)
    }

})