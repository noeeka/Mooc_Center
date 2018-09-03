$(function(){
    var id = getUrl('id');
   var activeId = 0;
   var page = 1;
   var limit = 2;
   var sort ='new';//排序
    var tab1LoadEnd = false;

    var resourcetpl = $('#resource-item').html();
    var resourcecom = Handlebars.compile(resourcetpl);
    //注册handlebars 助手函数
    Handlebars.registerHelper('date', function (value) {
        var date = new Date();
        date.setTime(value * 1000);
        return date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
    });
    getMultiArticle();
    buildDropload();
    function getMultiArticle(){
        $.ajax({
            url: '/api/category/child',
            data: {id: id, max_depth: 1},
            dataType: 'json',
            async: false,
            success: function (res) {
                // console.log(res)
                if (res.status == 1) {
                    var tpl = $('#navigation-item').html();
                    var template = Handlebars.compile(tpl);
                    for (var i in res.data) {
                        //往navigation中添加li
                        $('.navigation').append(template(res.data[i]));
                        if(i == 0){
                            $('.navigation li').eq(i).addClass('active');
                            activeId = res.data[i].id;
                        }
                    }
                }
            }
        });
    }
    //构建dropload对象
    function buildDropload() {
         dropload = $('.test1').dropload({
            scrollArea: window,
            domDown:{
                domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
            },
            loadDownFn: function (me) {
                    $.ajax({
                        url: '/api/article/index',
                        dataType: 'json',
                        data: {cid: activeId, sort:sort , page: page, len: limit},
                        async: false,
                        success: function (res) {
                            if (res.status == 1) {
                                page++;
                                if (res.data.list.length < limit) {
                                    // 再往下已经没有数据
                                    tab1LoadEnd = true;
                                    // 锁定
                                    me.lock();
                                    // 显示无数据
                                    me.noData();
                                }
                                // 为了测试，延迟1秒加载
                                    $("#resourceList").append(resourcecom(res.data.list))
                                    // 每次数据加载完，必须重置
                                    me.resetload();
                            }

                        },
                        error: function () {
                            console.log('ajax error');
                            me.resetload();
                        }
                    });


            }
        });
    }
    //获取不同的数据
    $('body').on('click','.navigation li',function(){
        $('.navigation li').removeClass('active');
        $(this).addClass('active');
        activeId = $(this).data('id');
        // console.log(activeId)
        tab1LoadEnd = false;
        // 如果数据没有加载完
        if (!tab1LoadEnd) {
            // 解锁
            dropload.unlock();
            dropload.noData(false);
        } else {
            // 锁定
            dropload.lock('down');
            dropload.noData();
        }
        page = 1;
        getdata();

    })
    //排行
    $('.screenTj li').click(function(){
        $('.screenTj li').removeClass('active');
        $(this).addClass('active');
        var index = $(this).index();
        // console.log(index);
        if(index == 0 || index == 1){
            sort = 'new';
        }if(index == 2){
            sort = 'hot';
        }
        tab1LoadEnd = false;
        // 如果数据没有加载完
        if (!tab1LoadEnd) {
            // 解锁
            dropload.unlock();
            dropload.noData(false);
        } else {
            // 锁定
            dropload.lock('down');
            dropload.noData();
        }
        page = 1;
        getdata();

    })
    //获取数据
    function getdata(){
        $.ajax({
            url: '/api/article/index',
            dataType: 'json',
            data: {cid: activeId, sort: sort, page: page, len: limit},
            async: false,
            success: function (res) {
                if (res.status == 1) {

                    $("#resourceList").html('');
                    dropload.resetload();
                }else if(res.status == 0){
                    $("#resourceList").html('');
                    dropload.resetload();
                }

            },
            error: function () {
                console.log('ajax error');
            }
        });
    }

})