$(function () {
    //生成左侧菜单
    var category_id = $('input[name=id]').val();
    var data = {cid: category_id, sort: 'new', page: 1, len: 20, venue: -1};
    selectNav(category_id);
    getList();

    Handlebars.registerHelper('date', function (value) {
        var date = new Date();
        date.setTime(value * 1000);
        return date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
    });
    Handlebars.registerHelper('image', function (value) {
        if (value == '') {
            return '/upload/portal/2.png';
        } else {
            return value;
        }
    });

    function getList() {
        $.ajax({
            url: '/api/article/index',
            dataType: 'json',
            data: data,
            success: function (res) {
                if (res.status == 1) {
                    var tpl = $('#left-slide').html();
                    var template = Handlebars.compile(tpl);
                    $('#left-menu').html(template(res.data));
                    if (res.data.list.length > 0) {
                        $('#left-menu').find('a').eq(0).addClass('active');
                        //设置面包屑
                        $('.title').html($('#left-menu').find('a').eq(0).html()).show();
                        article_read(res.data.list[0]['id']);
                    }
                }
            },
            error: function (res) {
                console.log(res.msg);
            }
        })
    }

    $('#left-menu').on('click', '.layui-nav-item>a', function () {
        $('.layui-nav-item>a').removeClass('active');
        $(this).addClass('active');
        if ($(this).parent().hasClass('layui-nav-itemed')) {
            $(this).parent().removeClass('layui-nav-itemed');
        } else {
            $('.layui-nav-item').removeClass('layui-nav-itemed');
            $(this).parent().addClass('layui-nav-itemed');
        }
        var title = $(this).html();
        $('.program .title').html(title);
        id = $(this).data('id');
        article_read(id);
    });

    function article_read(id) {
        request({
            url: '/api/article/read',
            data: {id: id},
            type: 'post',
            dataType: 'json',
            success: function (res) {
                //设置标题
                var tpl = $('#detail').html();
                var template = Handlebars.compile(tpl);
                $('.details').html(template(res.data));
            }
        });
    }

    $('.tab1 li').click(function () {
        $('.tab1 li').removeClass('active1');
        // var index1 = $(this).attr('data-index');
        // $('.tab1 li').eq(index1).addClass('active1');
        $(this).addClass('active1');
        data.sort = $(this).data('index') == 5 ? 'hot' : 'new';
        data.page = 1;
        getList();
    });
    title1();
})