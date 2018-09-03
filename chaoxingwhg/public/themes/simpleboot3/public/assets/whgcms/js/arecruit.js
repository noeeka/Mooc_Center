$(function() {
    var height1 = $('.hot ul').height() + 57;
    $('.hot .classification').css('height', height1);
    var height2 = $('.area ul').height() + 57;
    $('.area .classification').css('height', height2);
    var height3 = $('.type ul').height() + 57;
    $('.type .classification').css('height', height3);
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