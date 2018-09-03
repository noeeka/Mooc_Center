var page = 1;
var len = 10;
var pageEnd = false;
var tpl = $('#item').html();
var template = Handlebars.compile(tpl);
Handlebars.registerHelper('date', function (value) {
    var date = new Date();
    date.setTime(value * 1000);
    var month = (date.getMonth() + 1);
    var day = date.getDate();
    month = month < 10 ? '0'+month : month;
    day = day < 10 ? '0'+day : day;
    return date.getFullYear() + '-' + month + '-' + day;
});
var dropload = $('.mould3').dropload({
    scrollArea: window,
    domDown:{
        domNoData:'<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
    },
    loadDownFn:function(me){
        if(!pageEnd){
            $.ajax({
                url:'/api/volunarticle/index',
                data:{page:page, len:len},
                dataType:'json',
                success:function(res){
                    if(res.status == 1){
                        $('.mould3-list').append(template(res.data));
                        if (res.data.list.length < len) {
                            pageEnd = true;
                            me.lock();
                            me.noData();
                        }
                        page++;
                    }else{
                        // console.log('获取数据失败');
                        pageEnd = true;
                        me.lock();
                        me.noData();
                    }
                    me.resetload()
                },
                error:function(error){
                    console.log('ajax error');
                    pageEnd = true;
                    me.resetload();
                }
            });
        }
    }
});