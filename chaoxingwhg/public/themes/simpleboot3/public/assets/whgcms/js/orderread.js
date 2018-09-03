$(function () {
    selectNav(31);

    var id = getParam('id', 0);
    request({
        url: '/api/culture/read',
        data: {id: id},
        dataType: 'json',
        success: function (res) {
            if (res.status == 1) {
                console.log(res);
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

                Handlebars.registerHelper('status', function (value, data) {
                    var data = data.data.root;
                    var timestamp = new Date().getTime() / 1000;
                    if (timestamp < data['start_time']) {
                        if (data["state"] == 0) {
                            return "点单";
                        } else {
                            return "已点单";
                        }
                    } else if (timestamp > data['end_time']) {
                        return "已过期";
                    } else {
                        if (data["state"] == 0) {
                            return "点单";
                        } else {
                            return "已点单";
                        }
                    }
                });
                $('title').text(res.data.title);
                $('select[name=city]').html(res.data.leafs);
                layui.use('form', function () {
                    var form = layui.form.render('select');
                });
                var tpl = $('#detail').html();
                var template = Handlebars.compile(tpl);
                $('.details').html(template(res.data));

                if ($(".diandan").html() == "已过期" || $(".diandan").html() == "已点单") {
                    $('.diandan').removeClass('activity-active');
                    $('.diandan').addClass('expire');
                }

            } else {
                getdialog('获取点单内容失败');
            }
        },
        error: function (res) {
            console.log('ajax error');
        }
    }, true);

    $('body').on('click', '.diandan', function () {
        if ($(".diandan").html() == "点单") {
            layer.open({
                type: 1,
                content: $('form'), //这里content是一个普通的String
                // shade: [0.1, '#fff'],
                shadeClose: true,
                closeBtn: 0,
                area: '500px',
                end: function () {
                    layer.closeAll();
                    $('form').hide();
                }
            });
        }
    });
    $('body').on('click', '.want', function () {
        request({
            url: '/api/culture/add',
            data: {performid: id, area: $('select[name=city]').val()},
            dataType: 'json',
            success: function (res) {
                if (res.status == 1) {
                    $('form').hide();
                    layer.open({
                        type: 1,
                        content: $('.success'), //这里content是一个普通的String
                        // shade: [0.1, '#fff'],
                        shadeClose: true,
                        closeBtn: 0,
                        area: '500px',
                        end: function () {
                            layer.closeAll();
                            // $('.success').hide();
                        }
                    });

                } else {
                    $('form').hide();
                    noLogin(res, true);
                }
            },
            error: function (res) {
                console.log('ajax error');
            }
        }, true);

    });

    $('body').on('click', '.success', function () {
        layer.closeAll();
        $('.success').hide();
        $('.diandan').removeClass('activity-active');
        $('.diandan').addClass('expire');
        $('.diandan').html('已点单');

    });
    hot();

    function hot() {
        var id = getParam('id');
        console.log(id)
        $.ajax({
            url: '/api/culture/index',
            type: 'GET',
            dataType: 'json',
            data: {
                venue: 0,
                area: 0,
                page: 1,
                len: 3,
                type: 1,
                sort: 0
            },
            success: function (response) {
                if (response.status == 1) {
                    var hotTpl = $("#hot-template").html();
                    var hotCmp = Handlebars.compile(hotTpl);
                    var hotFun = hotCmp(response.data);
                    $('#Hot').html(hotFun);
                }
            }
        })
    }

    // hotread();
    //
    // function hotread() {
    //     var id = getParam('id');
    //     $.ajax({
    //         url: '/api/article/index',
    //         type: 'GET',
    //         dataType: 'json',
    //         data: {len: 7, cid:8, sort:'hot'},
    //         success: function (response) {
    //             var res = response.data.list.slice(3);
    //             if (response.status == 1) {
    //                 var readTpl = $("#read-template").html();
    //                 var readCmp = Handlebars.compile(readTpl);
    //                 var readFun = readCmp(res);
    //                 $('#read').html(readFun);
    //             }
    //         }
    //     })
    // }

})