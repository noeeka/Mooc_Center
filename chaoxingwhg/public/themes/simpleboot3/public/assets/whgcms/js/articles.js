$(function () {
    // title();
    //当前被选中的分类ID
    var active_id = getParam('cid', 0);
    //生成左侧菜单
    var category_id = $('input[name=id]').val();
    //默认
    var data = {cid: category_id, sort: 'new', page: 1, len: 9, has_child: 0};

    //选中导航
    selectNav(category_id);
    title1();
    //初始化
    init();

    //初始化
    function init() {
        //读取当前分类信息
        $.ajax({
            url: '/api/category/read',
            data: {id: category_id},
            dataType: 'json',
            success: function (res) {
                if (res.status == 1) {
                    if (res.data.type == 2) {
                        //单个文章
                        $('.main-right').css({float: 'none', margin: '0 auto'});
                        getSingleArticle(category_id, function (res) {
                            //暂未开放
                            if(res.data.list.length == 0){
                                $('.single').show();
                                $('.multi').hide();
                                $('.single .no').show();
                            }else{
                                $('.main-content').html('<div class="details">'+getSingleHtml(res)+'</div>');
                            }
                        })
                    } else if (res.data.type == 3) {
                        //文章列表
                        $('.main-left').show();
                        $('.tab1').show();
                        //加载左侧列表
                        getLeftMenu();
                    } else {
                        $('body').html('<h4 style="text-align: center;margin-top: 200px">发生未知错误，请重试～</h4>')
                    }
                } else {

                }
            }
        })
    }

    function getLeftMenu() {
        $.ajax({
            url: '/api/category/child',
            data: {id: category_id, max_depth: 2},
            dataType: 'json',
            success: function (res) {
                if (res.status == 1) {
                    //生成左侧菜单
                    var tpl = $('#left-slide').html();
                    var template = Handlebars.compile(tpl);
                    $('#left-menu').html(template(res));
                    //选中左侧菜单
                    var cid = selectLeftMenu(active_id);
                    if (cid != null) {
                        data.cid = cid;
                        getList();
                    }
                    //各级菜单点击事件
                    $('#left-menu .active img').attr('src', '/themes/simpleboot3/public/assets/whgcms/images/information/white.png');
                    $('#left-menu').on('click', '.layui-nav-item>a', function () {
                        setParam('cid', $(this).data('id'));
                        $('.layui-nav-item>a').removeClass('active');
                        $('.layui-nav-item>a').children('img').attr('src', '/themes/simpleboot3/public/assets/whgcms/images/information/black.png');
                        // $('.program .separated').hide();
                        // $('.program .smalltitle').hide();
                        $(this).addClass('active');
                        $(this).children('img').attr('src', '/themes/simpleboot3/public/assets/whgcms/images/information/white.png');
                        if ($(this).parent().hasClass('layui-nav-itemed')) {
                            $(this).parent().removeClass('layui-nav-itemed');
                            $(this).children('img').attr('src', '/themes/simpleboot3/public/assets/whgcms/images/information/white.png');
                            $('.program .separated').hide();
                            $('.program .smalltitle').hide();
                        } else {
                            $('.layui-nav-item').removeClass('layui-nav-itemed');
                            $(this).parent().addClass('layui-nav-itemed');
                            $(this).children('img').attr('src', '/themes/simpleboot3/public/assets/whgcms/images/information/up.png');
                            $('.program .separated').hide();
                            $('.program .smalltitle').hide();
                        }
                        // if($(this))
                        var title = $(this).children('em').html();
                        $('.program .title').html(title);
                        $('.title').show();
                        data.cid = $(this).data('id');
                        data.page = 1;
                        getList();
                    });
                    $('#left-menu').on('click', '.layui-nav-child a', function () {
                        setParam('cid', $(this).data('id'));
                        $('.program .separated').show();
                        $('.program .smalltitle').show();
                        var title = $(this).html();
                        $('.program .smalltitle').html(title);
                        $('.layui-nav-child dd').removeClass('layui-this');
                        $(this).parent().addClass('layui-this');
                        data.cid = $(this).data('id');
                        data.page = 1;
                        getList();
                    });
                } else {
                    console.log('获取菜单失败');
                }
            },
            error: function (e) {
                console.log(e.msg);
            }
        });
    }

    //选中左侧菜单
    function selectLeftMenu(id) {
        var thisNode = $('[data-id="' + id + '"]');
        if (thisNode.length == 0) {
            //默认选中第一个
            if ($('#left-menu').find('a').eq(0) != undefined) {
                $('#left-menu a').removeClass('active');
                $('#left-menu').find('a').eq(0).addClass('active');
                //显示面包屑文字
                $('.title').html($('#left-menu').find('a').eq(0).find('em').html()).show();
                return $('#left-menu').find('a').eq(0).data('id');
            } else {
                return null;
            }
        } else {
            var tagName = thisNode.parent().get(0).tagName;
            if (tagName == 'DD') {
                //二级菜单选中
                $('#left-menu dd').removeClass('layui-this');
                thisNode.parent().addClass('layui-this');
                //显示面包屑文字
                $('.smalltitle').html(thisNode.html()).show();
                $('.separated').show();
                //一级菜单选中
                $('#left-menu li').removeClass('layui-nav-itemed');
                thisNode.parents('li').addClass('layui-nav-itemed');
                //显示面包屑文字
                $('.title').html(thisNode.find('em').html()).show();
            } else {
                //一级菜单选中
                $('#left-menu a').removeClass('active');
                thisNode.addClass('active');
                //显示面包屑文字
                $('.title').html(thisNode.find('em').html()).show();
                console.log(thisNode)
            }
            return id;
        }
    }

    //获取单个文章，并回调
    function getSingleArticle(category_id, func) {
        $.ajax({
            url: '/api/article/index',
            dataType: 'json',
            data: {cid: category_id, sort: 'new', page: 1, len: 1, has_child: 0},
            success: function (res) {
                func(res);
            },
            error: function () {
                console.log('ajax error');
            }
        });
    }

    //获取数据
    function getList() {
        var type = $('[data-id="' + data.cid + '"]').data('type');
        console.log(type);
        if (type == 2) {
            $('.single').show();
            $('.multi').hide();
            $('.details').html('');
            getSingleArticle(data.cid, function (res) {
                if (res.data.list.length == 0) {
                    $('.details').hide();
                    $('.single .no').show();
                } else {
                    $('.single .no').hide();
                    $('.details').show();
                    $('.details').html(getSingleHtml(res)).show();
                }
            });
        } else if (type == 3) {
            $('.single').hide();
            $('.multi').show();
            getMultiArticle();
        } else {
            $('.single').show();
            $('.multi').hide();
            $('.details').hide();
            $('.single .no').show();
        }
    }

    function getSingleHtml(res) {
        var tpl = $('#detail').html();
        var template = Handlebars.compile(tpl);
        return template(res.data.list[0])
    }

    function getMultiArticle() {
        $.ajax({
            url: '/api/article/index',
            dataType: 'json',
            data: data,
            success: function (res) {
                if (res.status == 1) {
                    parseItem(res);
                    layui.use('laypage', function () {
                        var laypage = layui.laypage;
                        //执行一个laypage实例
                        laypage.render({
                            elem: 'test1', //注意，这里的 test1 是 ID，不用加 # 号
                            count: res.data.num, //数据总数，从服务端得到
                            next: '>',
                            limit: 9,
                            jump: function (obj, first) {
                                if (!first) {
                                    data.page = obj.curr;
                                    $.ajax({
                                        url: '/api/article/index',
                                        dataType: 'json',
                                        data: data,
                                        success: function (res) {
                                            parseItem(res);

                                        },
                                        error: function (res) {
                                            console.log(res.msg);
                                        }
                                    });
                                }
                            }

                        });
                    });
                    if ($('.list li').length == 0) {
                        $('.layui-laypage-next').hide();
                        $('.multi .no').show();
                    } else {
                        $('.multi .no').hide();
                    }
                }
            },
            error: function (res) {
                console.log(res.msg);
            }
        })
    }

    //解析列表
    function parseItem(res) {
        if (res.status == 1) {
            var tpl = $('#item').html();
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
            var template = Handlebars.compile(tpl);
            var html = template(res.data);
        } else {
            var html = '';
        }
        $('#data-list').html(html);
        for (var i = 0; i < res.data.list.length; i++) {
            if (res.data.list[i].is_top == 1) {
                $('.zhiding1').eq(i).show();
            } else {
                $('.zhiding1').eq(i).hide();
            }
        }
    }

    //修改排序
    $('.tab1 li').click(function () {
        $('.tab1 li').removeClass('active1');
        // var index1 = $(this).attr('data-index');
        // $('.tab1 li').eq(index1).addClass('active1');
        $(this).addClass('active1');
        data.sort = $(this).data('index') == 5 ? 'hot' : 'new';
        data.page = 1;
        getList();
    });
    //鼠标滑过改变箭头颜色
    $('body').on('mouseover','.layui-nav-item a',function(){
        if($(this).parent().hasClass('layui-nav-itemed')){
            $(this).find('img').attr('src','/themes/simpleboot3/public/assets/whgcms/images/information/up.png')
        }else{
            $(this).find('img').attr('src','/themes/simpleboot3/public/assets/whgcms/images/information/white.png')
        }
    })
    $('body').on('mouseout','.layui-nav-item a',function(){
        if(!$(this).hasClass('active')){
            $(this).find('img').attr('src','/themes/simpleboot3/public/assets/whgcms/images/information/black.png');
        }
    })
    //点击一级菜单清除二级菜单的当前状态
    $('body').on('click','.layui-nav-item>a',function(){
        $('.layui-nav-child dd').removeClass('layui-this');
    })
});