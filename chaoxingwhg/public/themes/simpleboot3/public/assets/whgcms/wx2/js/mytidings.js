$(function() {
    //列表模板
    var tidingsTpl = $('#tidings-template').html();
    var tidingsCmp = Handlebars.compile(tidingsTpl);
    var page = 1;
    var dropload = $('.tidings').dropload({
        scrollArea: window,
        domDown:{
            domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
        },
        loadDownFn: function(me) {
            // 加载菜单一的数据
            // if (itemIndex == '0') {
            request({
                type: 'post',
                url: '/api/sysmessage/index',
                dataType: 'json',
                data: {
                    page: page,
                    len:10
                },
                success: function(data) {
                    console.log(data)
                    if (data.status == 1) {
                        // console.log(data)
                        page++; //页数加1
                        // console.log(page);
                        // var data = data.data;
                        var content = data.data.list;
                        // var num = data.num
                        //转换时间戳
                        for (var i = 0; i < content.length; i++) {
                            content[i].create_time = data_format(content[i].create_time);
                        }
                        if (content.length < 10) {
                            // 再往下已经没有数据
                            // tab1LoadEnd = true;
                            // 锁定
                            me.lock();
                            // 显示无数据
                            me.noData();
                        }
                        // 为了测试，延迟1秒加载
                        setTimeout(function() {
                            // 加载 插入到原有 DOM 之后
                            $('#tidingsContent').append(tidingsCmp(content));
                            // 每次数据加载完，必须重置
                            me.resetload();
                        }, 500);

                    } else {
                        // 再往下已经没有数据
                        // tab1LoadEnd = true;
                        // 锁定
                        me.lock();
                        // 显示无数据
                        me.noData();
                        // 为了测试，延迟1秒加载
                        setTimeout(function() {
                            // 加载 插入到原有 DOM 之后
                            // 每次数据加载完，必须重置
                            me.resetload();
                        }, 500);

                    }

                },
                error: function(xhr, type) {
                    // alert('Ajax error!');
                    // 即使加载出错，也得重置
                    me.resetload();
                }
            },true);

            // }
        }
    });






})
