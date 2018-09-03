$(function () {
    //遮罩
    var zhezhaow = $(window).width();
    // console.log($('.Category-eject').height());
    var zhezhaoh = $(window).height() - $('.returnHeader').height() - $('.classification').height();

    var top1 = $('.mui-text-center').height();
    var top2 = $('form .list').height();
    $('.zhezhao').css({ width: zhezhaow, height: zhezhaoh, top: parseInt(top1) + parseInt(top2) });
    // console.log(1)
    $('.grade-eject').css({ top: parseInt(top1) + parseInt(top2) });
    $('.zhezhao').click(function () {
        $('.grade-eject').removeClass('grade-w-roll');
        $('.Category-eject').removeClass('grade-w-roll');
        $('.Sort-eject').removeClass('grade-w-roll');
        $('.paixu-eject').removeClass('grade-w-roll');
        $('.zhezhao').hide();
        $('html,body').removeClass('ovfHiden');
    })



    $('#quyu').click(function (e) {
        e.preventDefault();
        if ($('.grade-eject').hasClass('grade-w-roll')) {
            $('.grade-eject').removeClass('grade-w-roll');
            $('html,body').removeClass('ovfHiden');
            $('.zhezhao').hide();
        } else {
            $('.grade-eject').addClass('grade-w-roll');
            $('html,body').addClass('ovfHiden');
            $('.zhezhao').show();
        }
        // $('.Category-eject').css({ top: parseInt(top1) + parseInt(top2) });


    })

    // 获取点单信息
    $.ajax({
        url: "/api/culture/read",
        type: 'get',
        data: {
            id: getUrl('id')
        },
        success: function (res) {
            console.log(res)
            res = res.data;
            var arr1 = res.leafs.match(/[\u4e00-\u9fa5]+/g);
            var arr2 = res.leafs.match(/[0-9]+/g);
            var arr = [];
            for (var i = 0; i < arr1.length; i++) {
                var obj = {};
                obj.name = arr1[i];
                obj.index = arr2[i];
                arr.push(obj);
            }
            console.log(arr)
            var li = '';
            for (var j = 0; j < arr.length; j++) {
                li += '<li data-id="' + arr[j].index + '">' + arr[j].name + '</li>'
            }
            console.log(li)
            $('#gradew').html(li);
        }
    })


    $('form button').click(function () {
        // console.log(getUrl('id'), $('#quyu').attr('data-id'))
        console.log($('#quyu').attr('data-id') == null)
        if( $('#quyu').attr('data-id') == null){
            alert('请选择区域');
            return false;
        }
        request({
            url: '/api/culture/add',
            type: 'get',
            data: {
                performid: getUrl('id'),
                area: $('#quyu').attr('data-id')
            },
            success: function (res) {
                console.log(res)
                if(res.code == 10005){
                    alert('登录失效，请重新登录。')
                }else if(res.status == 1){
                    alert(res.msg,'/wx/culture/index?navid=nav31');
                    // window.location.href = '/wx/culture/index?navid=nav31';
                }else {
                    // alert(res.msg);
                    noLogin(res.code,res.msg);
                }

            },
            error: function (res) {
                console.log(res)
            }
        }, true)
    })

    $('#gradew').on('click', 'li', function () {
        $('#quyu').val($(this).text()).attr('data-id', $(this).attr('data-id'));

        $('.grade-eject').removeClass('grade-w-roll');
        $('html,body').removeClass('ovfHiden');
        $('.zhezhao').hide();
        $('.grade-eject>ul.grade-w li').css('border-bottom','solid 1px #f1f1f1');
        $(this).css('border-bottom','solid 1px #3F97F0');
    })

})