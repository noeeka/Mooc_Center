$(function () {

    window.onerror = function(){
      $.ajax({
          url:'/api/baseinfo/uploaderror',
          data:JSON.stringify(arguments),
          success:function(res){
              alert('upload error ok');
          },
          error:function () {
              alert('ajax error');
          }
      });
    };
    Handlebars.registerHelper("compare",function(start_time,end_time, options){
        var timestamp = Date.parse(new Date()) / 1000;
        // console.log(start_time);
        // console.log(end_time);
        // console.log(timestamp);
        if(timestamp >= start_time && timestamp <= end_time) {
            return options.fn(this);
        } else  {
            return options.inverse(this);
        }
    });

    var swiper = new Swiper('.swiper-container', {
        // autoplay: 3000,
        loop: true,
        pagination: '.swiper-pagination',
        paginationClickable: true
    });


    request({
        url: '/api/activity/read/id/' + id,
        type: 'post',
        dataType: 'json',
        async: false,
        success: function (result) {
            if (result.status == 1) {
                // var template = Handlebars.compile($("#figure").html());
                // $(".figure").html(template(result.data));

                var template1 = Handlebars.compile($("#detail").html());
                $(".detail").html(template1(result.data));

                var template2 = Handlebars.compile($("#introduction").html());
                $(".introduction").html(template2(result.data));
                if(result.data.yibaoming == 1){
                    $('.yibaoming').css({'background':'#acacac'});
                }

                var template3 = Handlebars.compile($("#baoming").html());

                $("#baomingdiv").html(template3(result.data));

                $('.traffic').html(result.data.traffic)
            }
        }
    }, true);


    $(".baoming").click(function () {
        if (!$(".baoming").hasClass('yibaoming')) {
            // enroll(id);
            window.location.href = "/wx2/activity/baoming?id="+id;
        }
    })

    //活动预约
    function enroll(activity_id) {
        request({
            url: '/api/activity/enroll',
            type: 'post',
            dataType: 'json',
            data: {'id': activity_id},
            success: function (result) {
                if (result.status == 1) {
                    layer.open({
                        title: [
                            '提示',
                            'font-size:0.21rem'
                        ],
                        yes: function (index, layero) {
                            // alert(1111);
                            window.location.reload();
                            layer.closeAll();
                        },
                        content:'报名成功',
                        btn: '确定',
                        style: 'font-size:0.18rem;border-radius:18px;'
                    });

                } else {
                    var regex = /^1000[4-9]$/;
                    if(regex.test(result.code)){
                        alert('未登录，请先登录且实名认证后方可报名！');
                    }else{
                        if (result.code == 13003) {
                            //已报名
                            $('.baoming').css('background-color', '#acacac');
                            $('.baoming').addClass('yibaoming');
                            alert(result.msg);
                        }else if(result.code == 13004) {
                            //未认证
                            alert(result.msg,'/wx/my/attestation');

                        }else if(result.code == 13005) {
                            //人数已满
                            $('.baoming').css('background-color', '#acacac');
                            $('.baoming').addClass('yibaoming');
                            alert(result.msg);
                        }else{
                            alert(result.msg);
                        }
                    }
                }
            }
        }, true)
    }

})