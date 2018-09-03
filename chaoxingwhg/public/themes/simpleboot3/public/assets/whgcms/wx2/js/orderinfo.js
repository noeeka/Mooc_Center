$(function () {
    $.ajax({
        url: '/api/culture/read?id=' + getUrl('id'),
        type: 'get',
        success: function (res) {
            Handlebars.registerHelper('status', function (value, data) {
                var data =data.data.root;
                var timestamp = new Date().getTime() / 1000;
                if (timestamp < data['start_time']) {
                    return "我要点单";
                } else if (timestamp > data['end_time']) {
                    return "已过期";
                } else {
                    return "我要点单";
                }
            });

            if(res.status == 1){
                res = res.data;   
                var startTime1 = data_format(res.start_time,'-',true);
                var endTime1 = data_format(res.end_time,'-',true);
                var data = $.extend(res,{
                    start_time1:startTime1,
                    end_time1:endTime1
                })
                // 点单简介
                var info = $('#info_detail').html();
                var infoCpl = Handlebars.compile(info);
                var infoTpl = infoCpl(data);
                $('#info').html(infoTpl);

                // 详情
                $('#introduction').append($(data.content));

                // 介绍
                $('#thumb').attr('src',res.thumb);
                var introduce = $('#introduce_tpl').html();
                var introduceCpl = Handlebars.compile(introduce);
                var introduceTpl = introduceCpl(data);
                $('#introduce').html(introduceTpl);
                if ($("#info .baomingBtn").html() == "已过期") {
                    $("#info a").attr("href","#");
                    $('.baomingBtn').css('background-color', '#acacac');
                }

            }
        }
    })


    var swiper = new Swiper('.swiper-container', {
        autoplay: 3000,
        loop: true,
        pagination: '.swiper-pagination',
        paginationClickable: true
    });
})