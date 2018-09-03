$(function(){

    var page1 = 1;
    var dropload = $('.fengcai').dropload({
        scrollArea: window,
        domDown:{
            domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
        },
        loadDownFn: function(me) {
            $.ajax({
                type: 'get',
                url: '/api/volunteer/getMien/page/' + page1,
                dataType: 'json',
                async: false,
                success: function(data) {
                    console.log(data)
                    if (data.status == 1) {
                        page1++; //页数加1
                        var data = data.data
                        // for(var i = 0; i<data.length; i++){
                        //     data[i].baoming_end_time = data_format(data[i].baoming_end_time);
                        // }
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
                                // newsData = newsData.slice(0,2);
                                data[i].published_time = data_format(data[i].published_time);
                                if(data[i].imgs != null){
                                    data[i].imgs = data[i].imgs[0]
                                }
                            }
                            // 加载 插入到原有 DOM 之后
                            var fengcaiTpl = $('#fengcai-item').html();
                            var fengcaiCmp = Handlebars.compile(fengcaiTpl);
                            $('.fencailist').append(fengcaiCmp(data));
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
            });
        }
    });

})