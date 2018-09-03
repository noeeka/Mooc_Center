$(function() {
    //最新新闻渲染
    // $.ajax({
    //     // url: '/v1/article/news',
    //     url: C.interface.news,
    //     type: 'get',
    //     dataType: 'json',
    //     success: function(res) {
    //         var newsTpl = $('#news-template').html();
    //         var newsCmp = Handlebars.compile(newsTpl);
    //         if (res.status == 1) {
    //             $('#news').append(newsCmp(res.data));
    //         }
    //     }
    // })
    /* var newsTpl = $('#news-template').html();
     var newsCmp = Handlebars.compile(newsTpl);
     var tab1LoadEnd = false;
     var page = 1;
     var dropload = $('.news-notice').dropload({
         scrollArea: window,
         loadDownFn: function(me) {
             // 加载菜单一的数据
             // if (itemIndex == '0') {
             $.ajax({
                 type: 'post',
                 url: '/v1/article/news',
                 dataType: 'json',
                 data: {
                     page: page,
                 },
                 async: false,
                 success: function(res) {
                     // console.log(data)
                     if (res.status == 1) {
                         // console.log(data)
                         page++; //页数加1
                         // console.log(page);
                         var data = res.data;
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
                             // 加载 插入到原有 DOM 之后
                             // $('.noticelist').append(html);
                             $('#news').append(newsCmp(res.data));
                             // console.log(num);
                             // $('#searchNum').text(num);
                             // 每次数据加载完，必须重置
                             me.resetload();
                         }, 500);

                     } else {
                         // 再往下已经没有数据
                         tab1LoadEnd = true;
                         // 锁定
                         me.lock();
                         // 显示无数据
                         me.noData();
                         // 为了测试，延迟1秒加载
                         setTimeout(function() {
                             // 加载 插入到原有 DOM 之后
                             // $('.noticelist').append(html);
                             //   $('.noticelist').append(newsItemCmp(data));
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
             }, true);

             // } 
         }
     });*/
    //最新动态数据模板
    var newsTpl = $('#news-template3').html();
    var newsCmp = Handlebars.compile(newsTpl);
    $.ajax({
        type: 'get',
        url: 'https://nanchangxian-szwhg.chaoxing.com/api/index/getIndexData',
        dataType: 'json',
        async: false,
        success: function (res) {
            if (res.status == 1) {
                //最新动态
                var newsData = res.data.article;
                newsData = newsData.slice(0, 10);
                for (var i = 0; i < newsData.length; i++) {
                    newsData[i].published_time = data_format(newsData[i].published_time);
                }
                $('#news').html(newsCmp(newsData));
                for (var i = 0; i < newsData.length; i++) {
                    if (newsData[i].is_top == 1) {
                        $('.zhiding').eq(i).show();
                    } else {
                        $('.zhiding').eq(i).hide();
                    }
                }
            }
        }
    });
})
