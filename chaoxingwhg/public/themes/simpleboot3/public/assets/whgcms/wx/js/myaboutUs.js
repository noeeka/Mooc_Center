$(function () {
        $.ajax({
            url: '/api/aboutus/read',
            type: 'post',
            dataType: 'json',
            data: {},
            success: function (res) {
                console.log(res);
                if(res.status == 1){
                    $('img').attr('src',res.data.thumb);
                    $('.content').html(res.data.abstract);
                    // $('.tel').html(res.data.tel);

                }else{
                    alert(res.msg);
                }
            }
        });
});
