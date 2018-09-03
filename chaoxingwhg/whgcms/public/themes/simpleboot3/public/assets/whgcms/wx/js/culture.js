$(function () {
    window.pages = 1;
    // var performtype = []
    // 默认查询条件
    var term_default = { len: 6, type: 1 };


    // 获取地址
    $.ajax({
        url: "/api/filter/index",
        type: "get",
        data: {},
        success: function (res) {
            // console.log(res);
            Handlebars.registerHelper('venue_helper', function () {
                return new Handlebars.SafeString(
                    "<li onclick='grade1(this)' data-id='" + this.id + "'>" + this.name + "</li>"
                )
            })
            Handlebars.registerHelper('area_helper', function () {
                return new Handlebars.SafeString(
                    "<li onclick='Categorytw(this)' data-id='" + this.id + "'>" + this.name + "</li>"
                )
            })
            Handlebars.registerHelper('pro_helper', function () {
                return new Handlebars.SafeString(
                    '<li onclick="Categoryt(this)" data-id=' + this.id + '>' + this.name + '</li>'
                )
            })



            if (res.status == 1) {
                res = res.data;
                // 渲染场馆
                var venueHtml = $('#venue').html();
                var venueCpl = Handlebars.compile(venueHtml);
                var venueTpl = venueCpl(res);
                $('#gradew').append(venueTpl);

                $('.grade-w').on('click', 'li', function () {
                    var id = $(this).attr('data-id');
                    $('.venueHot span').attr('data-id', id);

                    // 查询渲染列表
                    pages = 1;
                    var term = $.extend(term_default, getOpts(), {
                        venue: id,
                        page: 1
                    });
                    getDiandan(term);
                    pages++;
                })
                // 渲染区域
                var areaHtml = $('#area').html();
                var areaCpl = Handlebars.compile(areaHtml);
                var areaTpl = areaCpl(res);
                $('#Categorytw').append(areaTpl);
                

                
                // 点击城市
                $('.Category-w').on('click', 'li', function () {
                    var areaId = $(this).attr('data-id');
                    var pro = $('#province').html();
                    var proCpl = Handlebars.compile(pro);
                    if (areaId === "all") {
                        $('#Categoryt').html('');
                        $('#Categoryt').append($('<li onclick="Categoryt(this)" data-id="0">全部</li>'));
                    } else {
                        $.each(res.area, function (index, item) {
                            if (areaId == item.id) {
                                $('#Categoryt').html('');
                                var proTpl = proCpl(item);
                                $('#Categoryt').html(proTpl);
                            }
                        })
                    }
                })
                // 点击区域
                $('.Category-t').on('click', 'li', function () {
                    var id = $(this).attr('data-id');
                    if (id != $('.region span').attr('data-id')) {
                        $('.region span').attr('data-id', id);
                        // 查询渲染列表
                        pages = 1;
                        var term = $.extend(term_default, getOpts(), {
                            area: id,
                            page: 1
                        });
                        getDiandan(term);
                        pages++;
                    }
                })
            }
        }

    })

    // 获取类型
    $.ajax({
        url: "http://menhu.com/api/performtype/index",
        type: 'get',
        success: function (res) {
            // console.log(res)
            if (res.status == 1) {
                console.log(res)

                Handlebars.registerHelper('sort_helper', function () {
                    return new Handlebars.SafeString(
                        '<li onclick="Sorts(this)" data-id="' + this.id + '">' + this.name + '</li>'
                    )
                })

                // 渲染类型
                var sortHtml = $('#sort').html();
                var sortCpl = Handlebars.compile(sortHtml);
                var sortTpl = sortCpl(res);
                $('#Sort-Sort').append(sortTpl);

                $('.Sort-Sort').on('click', 'li', function () {
                    var id = $(this).attr('data-id');
                    $('.venueType span').attr('data-id', id);

                    // 查询渲染列表
                    pages = 1;
                    var term = $.extend(term_default, getOpts(), {
                        typeid: id,
                        page: 1
                    });
                    getDiandan(term);
                    pages++;
                })

            }

        }
    })

    // 排序
    $('.screenTj').on('click', 'li', function () {
        var id = $(this).attr('data-id');
        pages = 1;
        if (id == 0) {
            var term = $.extend(term_default, getOpts(), { sort: 0, page: 1 });
            getDiandan(term)
        } else if (id == 1) {
            var term = $.extend(term_default, getOpts(), { sort: 0, page: 1 });
            getDiandan(term)
        } else if (id == 2) {
            var term = $.extend(term_default, getOpts(), { sort: 1, page: 1 });
            getDiandan(term)
        }
        pages++;
        console.log(term)
    })

    // dropload
    function addDropload(){
        $('.huodong').dropload({
            scrollArea: window,
            loadDownFn: function (me) {
                var opts = $.extend(term_default, getOpts(), {
                    page: pages
                });
                // console.log(opts)
                $.ajax({
                    type: 'GET',
                    url: 'http://menhu.com/api/culture/index',
                    data: opts,
                    success: function (res) {
                        if (res.status == 1) {
                            res = res.data;
                            var item = $('#listItem').html();
                            var itemCpl = Handlebars.compile(item);
                            var itemTpl = itemCpl(res);
                            $('.huodongList').append(itemTpl);
                            if (pages * term_default.len < res.count) {
                                pages++;
                            } else {
                                me.lock();
                                me.noData();
                            }
    
    
                            // 每次数据加载完，必须重置                        
                            me.resetload();
                        }
    
                    },
                    error: function (xhr, type) {
                        // 即使加载出错，也得重置
                        me.resetload();
                    }
                });
            }
        });
    }
    

    // 获取点单信息
    getDiandan({ venue: 0, area: 0, page: 1, len: 6, type: 1, sort: 0 })
    pages++;

    // 获取点单方法
    function getDiandan(opt) {
        $(".dropload-down").remove(); 
        $.ajax({
            url: "http://menhu.com/api/culture/index",
            type: "get",
            data: opt,
            success: function (res) {
                console.log(res)

                Handlebars.registerHelper('item_img', function () {
                    return new Handlebars.SafeString(
                        '<img class="mui-media-object mui-pull-left" src="' + this.thumb + '">'
                    )
                })


                if (res.status == 1) {
                    res = res.data;
                    var item = $('#listItem').html();
                    var itemCpl = Handlebars.compile(item);
                    var itemTpl = itemCpl(res);

                    $('.huodongList').html(itemTpl);
                    addDropload();
                }
            }
        })
    }

    // 获取查询条件
    function getOpts() {
        var venue = $('.venueHot span').attr('data-id') == null ? 0 : $('.venueHot span').attr('data-id');
        var area = $('.region span').attr('data-id') == null ? 0 : $('.region span').attr('data-id');
        var type = $('.venueType span').attr('data-id') == null ? 0 : $('.venueType span').attr('data-id');
        var sort = $('.screenTj .active').attr('data-id') == null ? 0 : $('.screenTj .active').attr('data-id');
        return { venue: venue, area: area, typeid: type, sort: sort }
    }


})