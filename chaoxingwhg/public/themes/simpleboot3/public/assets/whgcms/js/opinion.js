$(function () {

    function opinion() {
    }

    opinion.prototype.ajax = function (url, data) {
        var rsJson = '';
        $.ajax({
            url: url,
            data: data,
            type: 'POST',
            dataType: 'json',
            async: false,
            success: function (data) {
                rsJson = data;
            }
        });
        return rsJson;
    };
    //搜索条件
    opinion.prototype.nav_id = 32;
    opinion.prototype.data = {page: 1, size: 10, type: 3, area_id: 0, venue_id: 0,is_top:1};
    //收集所有条件
    opinion.prototype.collectData = function (pages) {
        this.data.page = pages;
        var type_value = $('.type.clearfix').find('li.oninion-active').attr('data-value');
        var venue_value = $('.hot.clearfix').find('li.oninion-active').attr('data-value');
        var area_value = 0;
        var childAreaVlue = $('.area.clearfix').find('ul .level2.clearfix li.oninion-active').attr('data-value');

        if (typeof childAreaVlue === "undefined") {
            area_value = $('.area.clearfix').find('li.oninion-active').attr('data-value');
        } else {
            area_value = childAreaVlue;
        }

        if (type_value == undefined) {
            type_value = 0;
        }

        if (venue_value == undefined) {
            venue_value = 0;
        }

        if (area_value == undefined) {
            area_value = 0;
        }

        this.data.type = type_value;
        this.data.venue_id = venue_value;
        this.data.area_id = area_value;
        console.log(this.data);
    };
    //获取所有数据
    opinion.prototype.getList = function (pages) {
        this.collectData(pages);
        var parent = this;
        var res = this.ajax('/api/opinion/getOpinionList', this.data);
        if (res.status == 1) {
            // console.log(res)
            // for(var i in res.data.list){
            //     if(res.data.list[i].is_top==1){
            //
            //     }
            // }
            this.parseItem(res);
            layui.use('laypage', function () {
                var laypage = layui.laypage;
                if (res.data.nums == 0) {
                    $("#test1").html("");
                } else {
                    laypage.render({
                        elem: 'test1', //注意，这里的 test1 是 ID，不用加 # 号
                        count: res.data.nums, //数据总数，从服务端得到
                        next: '>',
                        limit: parent.data.size,
                        jump: function (obj, first) {
                            if (!first) {
                                parent.data.page = obj.curr;
                                var rs = parent.ajax('/api/opinion/getOpinionList', parent.data);
                                if (rs.status == 1) {
                                    parent.parseItem(rs);
                                }
                            }
                        }
                    });
                }
                //执行一个laypage实例
            });
        }
        if ($('.list li').length == 0) {
            $('.no').show();
        } else {
            $('.no').hide();
        }

    };
    //渲染数据
    opinion.prototype.parseItem = function (res) {
        html = "";
        if (res.status == 1) {
            var tpl = $('#oplist').html();
            Handlebars.registerHelper('create_time', function (items, fn) {
                var key = items.data.key;
                var value = items.data.root.list[key].create_time;
                var date = new Date();
                date.setTime(value * 1000);
                return date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
            });
            var template = Handlebars.compile(tpl);
            var html = template(res.data);
        } else {
            var html = '';
        }
        $('#opinion-list').html(html);
    };
    //初始化区域和场馆
    opinion.prototype.initTypeWithArea = function () {
        var typeData = {};
        var res = this.ajax('/api/opinion/getVenueWithArea', typeData);
        if (res.status == 1) {
            this.parseTypeWithArea(res);
        }
    };
    //处理区域和场馆
    opinion.prototype.parseTypeWithArea = function (res) {
        if (res.status == 1) {
            //渲染所有的场馆
            if (res.data.venue.length > 0) ;
            this.rendererType(res);
            //渲染所有区域
            if (res.data.area.length > 0) ;
            this.rendererArea(res);
        } else {

        }
    };
    //重新计算category高度
    opinion.prototype.reloadCategoryHight = function () {
        var height1 = $('.hot ul').height() + 57;
        $('.hot .classification').css('height', height1);
        $('.hotvenus li').eq(0).css('margin-bottom', height1 - 61);
        var height2 = $('.area ul').height() + 57;
        $('.area .classification').css('height', height2);
        if ($('.areavenus li').eq(0).hasClass('oninion-active')) {
            $('.areavenus li').eq(0).css('margin-bottom', height2 - 61);
        }
        var height3 = $('.type ul').height() + 57;
        $('.type .classification').css('height', height3);
        $('.typevenus li').eq(0).css('margin-bottom', height3 - 61);

    };
    //初始化分类 添加绑定事件
    opinion.prototype.rendererType = function (res) {
        var tpl = $('#velist').html();
        var opinion = this;
        var template = Handlebars.compile(tpl);
        var html = template({'venuelist': res.data.venue});
        $('.hot.clearfix').find('ul').html(html);
        $('.hot.clearfix').find('ul').on('click', 'li', function () {
            $(this).addClass('oninion-active');
            $(this).addClass('activity-active');
            $(this).siblings().removeClass('oninion-active');
            $(this).siblings().removeClass('activity-active');
            opinion.getList(1);
        })
    };
    //初始化区域 添加绑定事件
    opinion.prototype.rendererArea = function (res) {
        var opinion = this;
        var tpl = $('#area-list').html();
        var template = Handlebars.compile(tpl);
        var html = template({'arealist': res.data.area});
        $('.area.clearfix').find('ul').html(html);

        $('li.parentArea').on('click', function () {
            //先拿到父城市的id
            var parent = $(this);
            var selectVal = parent.attr('data-value');
            var isCheckVal = true;
            //清除二级菜单

            parent.addClass('oninion-active');
            parent.addClass('activity-active');
            parent.siblings().removeClass('oninion-active');
            parent.siblings().removeClass('activity-active');
            $('.area.clearfix').find('ul .level2.clearfix').remove();

            //查找用户下面是否还有
            res.data.area.forEach(function (e) {
                if (e.id == selectVal && e.son.length > 0) {
                    var html = "<ul class='level2 clearfix'>";
                    var tpl = $('#area-child-list').html();
                    var template = Handlebars.compile(tpl);
                    html += template({'childlist': e.son});
                    html += "</ul>";
                    $('.area.clearfix').find('ul').append(html);

                    $('.level2.clearfix').on('click', 'li', function () {
                        $(this).addClass('oninion-active');
                        $(this).addClass('activity-active1');
                        $(this).siblings().removeClass('oninion-active');
                        $(this).siblings().removeClass('activity-active1');
                        opinion.getList(1);
                    });
                    isCheckVal = false;
                }
            });


            parent.addClass('oninion-active');
            parent.addClass('activity-active');
            parent.siblings().removeClass('oninion-active');
            parent.siblings().removeClass('activity-active');
            opinion.getList(1);

            // opinion.reloadCategoryHight();
        })
    };
    //初始化绑定点击事件
    opinion.prototype.initBind = function () {
        var opinion = this;
        $('.type.clearfix').find('li').on('click', function () {
            $(this).addClass('oninion-active');
            $(this).addClass('activity-active');
            $(this).siblings().removeClass('oninion-active');
            $(this).siblings().removeClass('activity-active');
            opinion.getList(1);
        });
        $('.tab1 li').click(function () {
            $('.tab1 li').removeClass('active1');
            // var index1 = $(this).attr('data-index');
            // $('.tab1 li').eq(index1).addClass('active1');
            $(this).addClass('active1');
        });

        //选中导航栏
        selectNav(232);

    };


    var op = new opinion();
    op.getList(1);
    op.initTypeWithArea();
    op.initBind();
    op.reloadCategoryHight();
    hot();

    function hot() {
        var id = getParam('id');
        $.ajax({
            url: '/api/article/index',
            type: 'GET',
            dataType: 'json',
            data: { len: 3, cid: 161, sort: 'hot','has_child':1 },
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
    //         data: { len: 7 },
    //         success: function(response) {
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
    title1();
});
