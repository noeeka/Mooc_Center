$(function(){
    var page = 1;
    var len = 2;
    Handlebars.registerHelper('date', function(value) {
        var date = new Date();
        date.setTime(value*1000);
        return date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();
    });
    var tpl = $('#item').html();
    var template = Handlebars.compile(tpl);
    var dropload = $('.collect').dropload({
        scrollArea:window,
        domDown:{
            domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
        },
        loadDownFn:function(me){
            request({
                url:'/api/article/collect_list',
                type:'post',
                dataType:'json',
                data:{page:page, len:len},
                success:function(res){
                    if(res.status == 1){
                        page++;
                        if(res.data.list.length < len){
                            me.lock();
                            me.noData();
                        }
                        $('.collectList').append(template(res.data));
                    }else{
                        me.lock();
                        me.noData();
                    }
                    me.resetload();
                },
                error:function(){
                    console.log('ajax error');
                    me.resetload();
                }
            }, true);
        }
    });
});