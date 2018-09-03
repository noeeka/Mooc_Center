$(function () {
    //变量初始化
    var id = $('input[name=id]').val();//父级ID

    //初始化三种模板
    var mould1 = Handlebars.compile($('#infContent-item1').html());

    //注册handlebars 助手函数
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

    //初始化
    init();

    //初始化
    function init() {
        $.ajax({
            url: '/api/article/index',
            data: {cid: id, sort: 'new', page: 1, len: 20, venue: -1},
            dataType: 'json',
            async: false,
            success: function (res) {
                if (res.status == 1) {
                    parseTemplate(res.data);
                }
            }
        });
    }

    //解析模板
    function parseTemplate(data) {
        var html = mould1(data);
        $('.infContent ul').append(html);
    }
})

