$(function() {
    $('.layui-nav-item>a').click(function() {
        $('.layui-nav-item>a').removeClass('active');
        $(this).addClass('active');
        var title = $(this).html();
        $('.program .title').html(title);
    });
    $('.layui-nav-child a').click(function() {
        var title = $(this).html();
        $('.program .title').html(title);
    });
    $('.tab1 li').click(function() {
        $('.tab1 li').removeClass('active1');
        // var index1 = $(this).attr('data-index');
        // $('.tab1 li').eq(index1).addClass('active1');
        $(this).addClass('active1');
    });
    layui.use('laypage', function() {
        var laypage = layui.laypage;

        //执行一个laypage实例
        laypage.render({
            elem: 'test1', //注意，这里的 test1 是 ID，不用加 # 号
            count: 1000, //数据总数，从服务端得到
            next: '>'

        });
    });
})