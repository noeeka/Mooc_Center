window.onload = function() {
    $('.nav>li a').removeClass('active');

    /*判断*/
    // if ($('input').val() != '关键字进行搜索' || $('input').val() != '') {


    var kv = getParam('kv', '');
    //if(kv != ''){
    //    $('#searchinput').val(decodeURI(kv));
        gettotal();
        getcount();
    //}
    // }

    // setTimeout(function
    // sessionStorage.removeItem('search')
    // $('#search1').val() == '关键字进行搜索';
    // }, 1000)
    /*点击搜索*/
    $('#search1').click(function() {
        var kv = $('#searchinput').val();
        gettotal();
        getcount();
    });

    //回车键
    $(window).keydown(function(event) {
        if (event.keyCode == 13) {
            gettotal();
            getcount();
        }
    })

    function click(page, kv) {
        $.ajax({
            url: '/api/site/z_search',
            type: 'GET',
            dataType: 'json',
            data: { kv: kv,page: page, len:10},
            success: function(response) {
                if (response.status == 1) {
                    console.log(response);
                    var Time = response.data.list;
                    for (var i in Time){
                        Time[i].time = data_format(Time[i].time)
                    }
                    var particularsTpl = $("#particulars").html();
                    var particularsCmp = Handlebars.compile(particularsTpl);
                    var particularsFun = particularsCmp(response.data);
                    $('#list').html(particularsFun);
                    page++;
                }
            }
        })
    }

    function getcount() {
        $.ajax({
            url: '/api/site/z_search',
            type: 'GET',
            dataType: 'json',
            data: { kv: kv,len:10},
            success: function(response) {
                if (response.status == 1) {
                    console.log(response)
                    var Time = response.data.list;
                    for (var i = 0; i < Time.length; i++) {
                        Time[i].time = data_format(Time[i].time);
                    }
                    var particularsTpl = $("#particulars").html();
                    var particularsCmp = Handlebars.compile(particularsTpl);
                    var particularsFun = particularsCmp(response.data);
                    $('#list').html(particularsFun)
                        layui.use('laypage', function() {
                            var laypage = layui.laypage;
                            laypage.render({
                                elem: 'test1',
                                next: '>',
                                count: response.data.num,
                                limit:5,
                                jump: function(obj, first) {
                                    click(obj.curr,kv);
                                }
                            })
                        })

                } else {
                    // var particularsFun = particularsCmp(response.data.detail);
                    $('#list').html('');
                    // layui.use('laypage', function() {
                    //     var laypage = layui.laypage;
                    //     laypage.render({
                    //         elem: 'test1',
                    //         count: 0
                    //     })
                    // })
                    $('.jilu').html('0条记录')
                    // getdialog('搜索结果为空');
                    $('.no').show();
                }
            }
        })
    }

    function gettotal() {
        //var kv = $('#searchinput').val();
        $.ajax({
            url: '/api/site/z_search',
            type: 'GET',
            dataType: 'json',
            data: { kv: kv},
            success: function(response) {
                console.log(response)
                var countTpl = $("#count").html();
                var countCmp = Handlebars.compile(countTpl);
                if (response.status == 1) {
                    var countFun = countCmp(response.data);
                    $('#total').html(countFun);
                } else {
                    $('#total').html('0条搜索结果');
                }
            }
        })
    }
    hot();

    function hot() {
        var id = getParam('id');
        console.log(id)
        $.ajax({
            url: '/api/article/index',
            type: 'GET',
            dataType: 'json',
            data: { len: 3 },
            success: function(response) {
                if (response.status == 1) {
                    var hotTpl = $("#hot-template").html();
                    var hotCmp = Handlebars.compile(hotTpl);
                    var hotFun = hotCmp(response.data);
                    $('#Hot').html(hotFun);
                }
            }
        })
    }
     hotread();

    function hotread() {
        var id = getParam('id');
        $.ajax({
            url: '/api/article/index',
            type: 'GET',
            dataType: 'json',
            data: { len: 7 },
            success: function(response) {
                var res = response.data.list.slice(3);
                if (response.status == 1) {
                    var readTpl = $("#read-template").html();
                    var readCmp = Handlebars.compile(readTpl);
                    var readFun = readCmp(res);
                    $('#read').html(readFun);
                }
            }
        })
    }
}

