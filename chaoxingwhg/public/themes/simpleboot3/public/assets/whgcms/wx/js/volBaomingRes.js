$(function(){

    var page1 = 1;
    var dropload = $('.list').dropload({
        scrollArea: window,
        domDown:{
            domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
        },
        loadDownFn: function(me) {
            request({
                url: '/api/user/baoming_info',
                type: 'post',
                dataType: 'json',
                data:{page:page1},
                async: false,
                success: function(res) {
                    console.log(res);

                    if (res.status == 1) {
                        page1++; //页数加1
                        var data = res.data.list
                        if (data.length < 10) {
                            // 再往下已经没有数据
                            tab1LoadEnd = true;
                            // 锁定
                            me.lock();
                            // 显示无数据
                            me.noData();
                        }
                        // 为了测试，延迟1秒加载
                        setTimeout(function() {
                            for(var i = 0; i<data.length; i++){
                                data[i].start_time = data_format(data[i].start_time);
                                data[i].end_time = data_format(data[i].end_time);
                                data[i].created_at = data_format(data[i].created_at);
                            }
                            // 加载 插入到原有 DOM 之后
                            var baomingTpl = $('#baoming-item').html();
                            var baomingCmp = Handlebars.compile(baomingTpl);
                            $('#baomingList').append(baomingCmp(data));
                            for(var i = 0; i<data.length; i++){
                                if(data[i].status == 0){
                                    $('.status').eq(i).text('审核中');
                                }else if(data[i].status == 1){
                                    $('.status').eq(i).text('通过');
                                }else if(data[i].status == 2){
                                    $('.status').eq(i).text('未通过');
                                }
                            }
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
                            // 每次数据加载完，必须重置
                            me.resetload();
                        }, 500);

                    }

                },
                error: function(xhr, type) {
                    //alert('Ajax error!');
                    // 即使加载出错，也得重置
                    me.resetload();
                }
            },true);
        }
    });

})