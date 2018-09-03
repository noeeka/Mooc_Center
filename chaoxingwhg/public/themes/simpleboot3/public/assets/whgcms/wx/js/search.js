$(function() {
    function getSearchValue() {
        var searchValue = $('.search-content span')
        //点击热门搜索填充到input里
        // searchValue.click(function() {
        $('body').on('click', '.historySearch-content span', function() {
            $('.searchInput input').val($(this).text());
        })

    }
    var historyList = [];
    getSearchValue();

    //搜索历史记录
    var optionss = localStorage.getItem("searchHistory");
    console.log(optionss);
    if (optionss != null) {
        historyList = JSON.parse(optionss);
        var historyNew = [];
        if(historyList.length > 10){
            // historyNew = historyNew.slice(5,11);
            historyList = historyList.slice(5,11);
            var strhistory = JSON.stringify(historyList);
            localStorage.setItem("searchHistory", strhistory);
        }
        //遍历当前数组去重
        for (var i = 0; i < historyList.length; i++) {
            //如果当前数组的第i已经保存进了临时数组，那么跳过，
            //否则把当前项push到临时数组里面
            if (historyNew.indexOf(historyList[i]) == -1) historyNew.push(historyList[i]);
        }

        for (var i = 0; i < historyNew.length; i++) {
            $('.historySearch-content').append('<span>' + historyNew[i] + '</span>')
        }

    }


    //搜索列表模板
    var searchContentTpl = $('#searchContent-template').html();
    var searchContentCmp = Handlebars.compile(searchContentTpl);
    var page = 1;
    var searchVal = '';
    var tab1LoadEnd = false;
    var dropload = $('.searchs').dropload({
        scrollArea: window,
        domDown:{
            domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
        },
        loadDownFn: function(me) {
            // 加载菜单一的数据
            // if (itemIndex == '0') {
            $.ajax({
                type: 'post',
                url: '/api/site/search',
                dataType: 'json',
                data: {
                    kv: searchVal,
                    page: page,
                    len:10
                },
                async: false,
                success: function(data) {
                    // console.log(data)
                    if (data.status == 1) {
                        // console.log(data)
                        page++; //页数加1
                        // console.log(page);
                        var data = data.data;
                        var content = data.list;
                        var num = data.num
                        //转换时间戳
                        for (var i = 0; i < content.length; i++) {
                            content[i].time = data_format(content[i].time);
                        }

                        // var searchVal = $('.searchValue').val();
                        // var searchLoca = window.localStorage.setItem("searchHistory", searchVal);

                        // $('.historySearch-content').append('<span>' + searchVal + '</span>')

                        // html = html1(data);
                        if (content.length < 10) {
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
                            $('#result-content').append(searchContentCmp(content));
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
            });

            // }
        }
    });

    $('.searchIcon').click(function() {
        var searchVal = $('.searchValue').val().trim();
        // console.log(searchVal);
        if (searchVal != '') {
            // console.log(1123);
            $('.search-content').hide();
            $('.search-result').show();
            $('body').css({ 'background': '#F2F2F2' });
            if ($('.searchInput input').val() == '') {
                $('.searchResultValue').text('罗源县文化馆');
            } else {
                $('.searchResultValue').text($('.searchInput input').val());
            }
            if (historyList.length == 0) {
                historyList.push(searchVal);
                var str = JSON.stringify(historyList);
                localStorage.setItem("searchHistory", str);
            } else {
                var optionss = localStorage.getItem("searchHistory");
                // console.log(JSON.parse(optionss));
                historyList = JSON.parse(optionss);
                historyList.push(searchVal);
                var str = JSON.stringify(historyList);
                localStorage.setItem("searchHistory", str);
                // console.log(historyList);
            }
            searchRes();
        }

    });
    $(window).keydown(function(event) {
        if (event.keyCode == 13) {
            var searchVal = $('.searchValue').val().trim();
            if (searchVal != '') {
                $('.search-content').hide();
                $('.search-result').show();
                $('body').css({ 'background': '#F2F2F2' });
                if (historyList.length == 0) {
                    historyList.push(searchVal);
                    var str = JSON.stringify(historyList);
                    localStorage.setItem("searchHistory", str);
                } else {
                    var optionss = localStorage.getItem("searchHistory");
                    // console.log(JSON.parse(optionss));
                    historyList = JSON.parse(optionss);
                    historyList.push(searchVal);
                    var str = JSON.stringify(historyList);
                    localStorage.setItem("searchHistory", str);
                    console.log(historyList);
                }
                searchRes();
            }

        }
    })

    //获取搜索内容
    function searchRes() {
        //填充搜索关键词
        if ($('.searchValue').val().trim() == '') {
            searchVal = '文化馆';
        } else {
            searchVal = $('.searchValue').val().trim();
        }
        tab1LoadEnd = false;
        if (!tab1LoadEnd) {
            // 解锁
            dropload.unlock();
            dropload.noData(false);
        }
        page = 1;
        $.ajax({
            type: 'post',
            // url: '/v1/site/search',
            url: '/api/site/search',
            dataType: 'json',
            data: {
                kv: searchVal,
                page: page,
                len:10
            },
            async: false,
            success: function(data) {
                console.log(data)
                if (data.status == 1) {
                    page++; //页数加1
                    var data = data.data;
                    var content = data.list;
                    var num = data.num
                    //转换时间戳
                    for (var i = 0; i < content.length; i++) {
                        content[i].time = data_format(content[i].time)
                    }
                    $('#result-content').html('');
                    $('#result-content').append(searchContentCmp(content));
                    // console.log(num);
                    $('.searchResultValue').text(searchVal);
                    $('#searchNum').text(num);
                    // 每次数据加载完，必须重置
                    dropload.resetload();

                } else {
                    $('#searchNum').text(0);
                    $('#result-content').html('');
                    dropload.resetload();
                }

            }
        });

    }





})
