$(function () {
    $('.feedback').click(function () {
        var content = $('#textarea').val().trim();
        var mobile = $('input[name=mobile]').val().trim();
        var name = $('input[name=name]').val().trim();
        var sex = $('input[name=sex]:checked').val();
        var qq = $('input[name=qq]').val().trim();
        $.ajax({
            url: '/api/feedback/save',
            type: 'post',
            dataType: 'json',
            data: {name: name, mobile: mobile, content: content,sex:sex,qq:qq},
            beforeSend: function () {
                if(content == ''){
                    alert('内容不能为空');
                    return false;
                }
                if(mobile == ''){
                    alert('手机号不能为空');
                    return false;
                }
            },
            success: function (res) {
                if(res.status == 1){
                    alert('我们会尽快处理，感谢您的反馈!');
                    $('#textarea').val("");
                    $('input[name=mobile]').val("");
                    $('input[name=name]').val("");
                }else{
                    alert(res.msg);
                }
            },
            error: function () {
                console.log('ajax error');
            }
        });
    });
});