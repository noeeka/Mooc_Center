$(function () {
    //变量初始化
    var id = $('input[name=id]').val();//父级ID
    var lock = new Lock();//dropload tab页锁
    var page = new Page();//tab页分页位置
    var active_id = getUrl('active_id');//初始化选中的分类
    var limit = 2;//分页数量
    var dropload = null;//dropload对象
    var firstEnd = false;//dropload构建对象时不获取数据

    //初始化三种模板
    var mould1 = Handlebars.compile($('#infContent-item1').html());
    var mould2 = Handlebars.compile($('#infContent-item2').html());
    var mould3 = Handlebars.compile($('#infContent-item3').html());

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

    //点击nav，显示对应的内容
    $('.navigation').on('click', 'li:not(:first-child)', function () {
        CategoryActive($(this).data('id'));
    });

    //初始化
    function init() {
        $.ajax({
            url:'/api/category/read',
            data:{id:id},
            dataType:'json',
            success:function(res){
                if(res.status == 1){
                    if(res.data.type == 2){
                        //单个文章
                        getSingleArtilceList(id);
                    }else if(res.data.type ==3){
                        //文章列表
                        $('.classification').show();
                        getMultiArticle();
                    }else{
                        //其他
                        showError('出错啦')
                    }
                }else{
                    showError('未知分类')
                }
            },
            error:function(){
                console.log('ajax error')
            }
        })
    }

    function showError(text){
        $('.error span').text(text);
        $('.error').show()
    }

    function getSingleArtilceList(){
        $('.infContent').append('<div class="mould1" data-id="' + id + '" style="display: block"><ul class="mui-table-view newsContent mould1-list"></ul></div>')
        active_id = id;
        dropload = buildDropload();
        firstEnd = true;
        CategoryActive(active_id);
    }

    function parseSingleTemplate(data, cid){
        if(data.length == 0){
            var html = '<div class="error" style="text-align: center;margin-top: 1rem;"><span>页面飞到外太空了</span>~(&gt;_&lt;)~~</div>';
        }else{
            var article = data[0];
            var tpl = $('#single-article').html();
            var template = Handlebars.compile(tpl);
            var html = template(article);
        }
        if(cid == id){
            $('section').html(html);
        }else{
            var div = $('.infContent>div[data-id="' + cid + '"]');
            div.html(html);
        }
    }

    function getMultiArticle(){
        $.ajax({
            url: '/api/category/child',
            data: {id: id, max_depth: 1},
            dataType: 'json',
            success: function (res) {
                if (res.status == 1) {
                    var tpl = $('#navigation-item').html();
                    var template = Handlebars.compile(tpl);
                    for (var i in res.data) {
                        //往navigation中添加li
                        $('.navigation').append(template(res.data[i]));
                        //生成tab页dom
                        if (i == 0) {
                            $('.infContent').append('<div class="mould1" data-id="' + res.data[i]['id'] + '" style="display: block"><ul class="mui-table-view newsContent mould1-list"></ul></div>')
                        } else if (i == 1) {
                            $('.infContent').append('<div class="mould2" data-id="' + res.data[i]['id'] + '" style="display: none"><ul class="mui-table-view mui-grid-view mould2-list"></ul></div>');
                        } else {
                            $('.infContent').append('<div class="mould3" data-id="' + res.data[i]['id'] + '" style="display: none"><ul class="mould3-list"></ul></div>');
                        }
                    }
                    if (res.data.length > 0) {
                        if (active_id == null) {
                            active_id = $('.navigation li').eq(1).data('id');
                        }
                        dropload = buildDropload();
                        firstEnd = true;
                        CategoryActive(active_id);
                    }
                }
            }
        });
    }

    //解析模板
    function parseTemplate(data, id) {
        var div = $('.infContent>div[data-id="' + id + '"]');
        if (div.hasClass('mould1')) {
            var html = mould1(data);
        } else if (div.hasClass('mould2')) {
            var html = mould2(data);
        } else {
            var html = mould3(data);
        }
        div.find('ul').append(html);
    }

    //构建dropload对象
    function buildDropload() {
        return $('.test1').dropload({
            scrollArea: window,
            domDown:{
                domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
            },
            loadDownFn: function (me) {
                if (firstEnd) {
                    $.ajax({
                        url: '/api/article/index',
                        dataType: 'json',
                        data: {cid: active_id, sort: 'new', page: page.getPage(active_id), len: limit},
                        success: function (res) {
                            if (res.status == 1) {
                                page.inc(active_id);
                                if (res.data.list.length < limit) {
                                    lock.lock(active_id);
                                    me.lock();
                                    me.noData();
                                }
                                // 加载 插入到原有 DOM 之后
                                parseTemplate(res.data, active_id);
                            } else {
                                lock.lock(active_id);
                                // 锁定
                                me.lock();
                                // 显示无数据
                                me.noData();
                            }
                            // 每次数据加载完，必须重置
                            me.resetload();
                        },
                        error: function () {
                            console.log('ajax error');
                            me.resetload();
                        }
                    });
                }

            }
        });
    }

    /*
    * 定义锁定类
    * */
    function Lock() {
        var lock = {};
        var idArr = [];
        lock.lock = function (id) {
            if (idArr.indexOf(id) == -1) {
                idArr[idArr.length] = id;
            }
        }
        lock.unlock = function (id) {
            var index = idArr.indexOf(id);
            if (index !== -1) {
                idArr.splice(index);
            }
        }
        lock.hasLock = function (id) {
            return idArr.indexOf(id) !== -1;
        }
        return lock;
    }

    /*
    * 定义页码类
    * */
    function Page() {
        var page = {};
        var obj = {};
        page.getPage = function (id) {
            if (obj[id] == undefined) {
                obj[id] = 1;
            }
            return obj[id];
        }
        page.inc = function (id) {
            if (obj[id] == undefined) {
                obj[id] = 0;
            }
            obj[id]++;
        }
        return page;
    }

    //分类切换操作
    function CategoryActive(id) {
        active_id = id;
        $('.navigation li').removeClass('active');
        $('.navigation li[data-id="' + id + '"]').addClass('active');
        $('.infContent>div').hide();
        $('.infContent>div[data-id="' + id + '"]').show();
        if (lock.hasLock(id)) {
            // 锁定
            dropload.lock('down');
            dropload.noData();
        } else {
            // 解锁
            dropload.unlock();
            dropload.noData(false);
        }
        dropload.resetload();
    }
})

