$(function () {
    var id = getUrl('id');
    $.ajax({
        url: '/api/volunteer/get_single_mien/id/' + id,
        type: 'get',
        dataType: 'json',
        async: false,
        success: function (res) {
            console.log(res)
            if (res.status == 1) {
                $('.name').text(res.data.user_realname);
                $('.jianjie p').html(res.data.speciality_html);
                var fcinfoTpl = $('#fcinfo-item').html();
                var fcinfoCmp = Handlebars.compile(fcinfoTpl);
                $('.swiper-wrapper').append(fcinfoCmp(res.data.img));
                var mySwiper = new Swiper ('.swiper-container', {
                    autoplay: 2000,
                    speed: 500,
                    loop: true,
                    // 如果需要分页器
                    pagination: '.swiper-pagination',
                })
            }
        }
    });

})