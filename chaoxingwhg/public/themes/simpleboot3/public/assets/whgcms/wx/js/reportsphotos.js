$(function(){
    var page = 1;
    var len = 10;
    var pageEnd = false;
    var article_id = getUrl('id');
    var tpl = $('#item').html();
    var template = Handlebars.compile(tpl);
    $('.addphotoBtn a').attr('href', '/wx/volunteer/reportsAddphoto?id='+article_id);
    var dropload = $('.sources').dropload({
        scrollArea:window,
        domDown:{
            domNoData:'<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
        },
        loadDownFn:function(me){
            if(!pageEnd){
                request({
                    url: '/api/volunarticle/photo_wall',
                    type: 'post',
                    dataType: 'json',
                    data: {id: article_id,page:page, len:len},
                    success:function(res){
                        if(res.status == 1){
                            page++;
                            $('.sources ul').append(template(res.data));
                            if(res.data.length < len){
                                me.lock();
                                me.noData();
                                pageEnd = true;
                            }
                        }else{
                            pageEnd = true;
                            me.lock();
                            me.noData();
                        }
                        me.resetload();
                    },
                    error:function(error){
                        console.log('ajax error');
                        me.resetload();
                    }
                });
            }
        }
    });

});