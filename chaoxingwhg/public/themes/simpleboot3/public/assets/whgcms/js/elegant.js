$(function() {
    selectNav(81);
    hot();
    //hotread();

    function hot() {
        var id = getParam('id');
        $.ajax({
            url: '/api/volunteer/getMien/',
            type: 'post',
            dataType: 'json',
            data: {},
            success: function (response) {
                if (response.status == 1) {
                    if(response.data.length>3){
                        dat = response.data.slice(0,3);
                    }else{
                        dat = response.data;
                    }
                    for (var i in dat) {
                        if (dat[i].imgs != null) {
                            dat[i].imgs =  dat[i].imgs[0];
                        }
                    }
                    var hotTpl = $("#hot-template").html();
                    var hotCmp = Handlebars.compile(hotTpl);
                    var hotFun = hotCmp(dat);
                    $('#Hot').html(hotFun);
                }
            }
        })
    }
    var id = getParam('id', 0);
    $.ajax({
        url: '/api/volunteer/get_single_mien',
        type: 'GET',
        dataType: 'json',
        data: { id: id },
        success: function(res) {
            if (res.status == 1) {
                $('#elegant img').attr('src', res.data.img[0]);
                $('#elegant .title').html(res.data.user_realname);
                $('#elegant p').html(res.data.speciality_html);
                var html = '';
                for (var i in res.data.img) {
                    html += ' <div style="width:250px;height:140px;overflow:hidden; margin-top: 16px;" class="f-left"><img src="' + res.data.img[i] + '" alt="" data-index="' + i + '"></div>';
                }
                $('#elegant .fengcai').html(html);
                var slider = '';
                for (var j in res.data.img) {
                    // slider += '<div style="width:798px;height:447px;overflow:hidden;"><img src="' + res.data.img[j] + '" alt=""></div>';
                    slider += ' <div class="swiper-slide" style="width:798px;height:447px;overflow:hidden;"><img src="' + res.data.img[j] + '" alt=""></div>';
                }
                // $('#carousel').html(slider);
                $('.swiper-wrapper').html(slider);
            }
        }
    })

    $('body').on('click', '#elegant .fengcai>div', function() {
        var index = 0;
        var that = $(this);
        layer.open({
            type: 1,
            content: $('#swiper'),
            area: ['1200px', '447px'],
            shade: [0.8, '#010101'],
            shadeClose: true,
            success: function() {
                index = that.index();
                var swiper = new Swiper('.swiper-container', {
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    initialSlide: index,
                });
                // layui.use('carousel', function() {
                //     var carousel = layui.carousel;
                //     //建造实例
                //     carousel.render({
                //         elem: '#banner',
                //         width: '1200px', //设置容器宽度
                //         height: '447px',
                //         autoplay: false,
                //         index: index,
                //         indicator: 'none',
                //         arrow: 'always' //始终显示箭头
                //     });
                // });
            },
            end: function() {
                $('#swiper').hide();
            }
        });

    })


})