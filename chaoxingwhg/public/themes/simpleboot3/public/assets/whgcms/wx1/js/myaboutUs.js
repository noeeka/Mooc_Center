$(function () {
        $.ajax({
            url: '/api/category/wxAbout',
            type: 'post',
            dataType: 'json',
            data: {},
            success: function (res) {
                console.log(res);
                if(res.status == 1){
                    $('img').attr('src',res.data.thumb);
                    $('.content').html(res.data.abstract);

                }else{
                    alert(res.msg);
                }
            }
        });
});
