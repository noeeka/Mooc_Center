$(function() {
    layui.use('form', function() {
        var form = layui.form;

        //各种基于事件的操作，下面会有进一步介绍
    });
    $('.tab li').click(function(event) {
        /* Act on the event */
        $('.tab li').removeClass('zhutise');
        $('.main-right>div').addClass('hidden');
        var index = $(this).index();
        $(this).addClass('zhutise');
        $('.main-right>div').eq(index).removeClass('hidden');
        $('.program .title').html($(this).html());
    });
    layui.use('laypage', function() {
        var laypage = layui.laypage;
        //执行一个laypage实例
        laypage.render({
            elem: 'test1', //注意，这里的 test1 是 ID，不用加 # 号
            count: 10, //数据总数，从服务端得到
            next: '>',
            limit: 8,
            // jump: function(obj, first) {
            //     recurite(obj.curr, obj.limit);
            // }
        });
    });
})