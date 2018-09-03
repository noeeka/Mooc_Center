$(function(){

            var page1 = 1;
            var dropload = $('.huodong').dropload({
                scrollArea: window,
                domDown:{
                    domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
                },
                loadDownFn: function(me) {
                        $.ajax({
                            type: 'get',
                            url: '/api/Volunteer/getrecurit/page/' + page1,
                            dataType: 'json',
                            async: false,
                            success: function(data) {
                                if (data.status == 1) {
                                    page1++; //页数加1
                                    var data = data.data
                                    // for(var i = 0; i<data.length; i++){
                                    //     data[i].baoming_end_time = data_format(data[i].baoming_end_time);
                                    // }
                                    if (data.length < 10) {
                                        // 再往下已经没有数据
                                        // tab1LoadEnd = true;
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
                                        }
                                        // 加载 插入到原有 DOM 之后
                                        var zhaomuTpl = $('#zhaomu-item').html();
                                        var zhaomuCmp = Handlebars.compile(zhaomuTpl);
                                        $('.zhaomulist').append(zhaomuCmp(data));
                                        //获取当前时间转为时间戳
                                        var nowdata = Date.parse(new Date(getNowFormatDate()))/1000;
                                        // console.log(nowdata)
                                        for(var i = 0; i<data.length; i++){
                                            var timestamp = data[i].baoming_end_time;
                                            if(nowdata > timestamp){
                                                $('.baomingBtn').eq(i).text('已过期').css('background-color','#ddd');
                                            }else{
                                                $('.baomingBtn').eq(i).text('报名').css('background-color','#EB6877');

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
                        });
                }
            });

})