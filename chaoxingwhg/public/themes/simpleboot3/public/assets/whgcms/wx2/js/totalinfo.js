$(function () {
    var comment_limit = 6;
    var page = 1; //当前页数
    var uid = 0;
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
        if(timestamp >= start_time && timestamp <= end_time) {
            return options.fn(this);
        } else  {
            return options.inverse(this);
        }
    });

    var swiper = new Swiper('.swiper-container', {
        autoplay: 3000,
        loop: true,
        pagination: '.swiper-pagination',
        paginationClickable: true
    });


    request({
        url: '/api/venue/read/id/' + id,
        type: 'post',
        dataType: 'json',
        async: false,
        success: function (result) {
            if (result.status == 1) {
                var template = Handlebars.compile($("#figure").html());
                $(".figure").html(template(result.data));

                var template1 = Handlebars.compile($("#detail").html());
                $(".detail").html(template1(result.data));

                var template2 = Handlebars.compile($("#introduction").html());
                $(".introduction").html(template2(result.data));
                if(result.data.yibaoming == 1){
                    $('.yibaoming').css({'background':'#acacac'});
                }

                if (result.data.comment_status == 1) {
                    $('.comments').show();
                    $('.send').show();
                    $('.test').css('margin-bottom', '0.5rem');
                    uid = getUid();
                    dropload = buildDropload();
                }
            }else{
                $('body').html('<p>内容不存在</p>');
            }
        }
    }, true);

    request({
        url: '/api/my/index',
        type: 'post',
        dataType: 'json',
        async: false,
        success: function (res) {
            if (res.status == 1) {
                $('#comm').attr('placeholder','请输入评论');
            }else{
                $('#comm').attr('placeholder','登陆后方可评论');
            }
        },
        error: function (res) {

        }
    }, true);

    $(".dianzan").click(function () {
        dianzan(id);
    })

    //场馆点赞
    function dianzan(venue_id) {
        request({
            url: '/api/venue/like',
            type: 'post',
            dataType: 'json',
            data: {'id': venue_id},
            success: function (result) {
                if (result.status == 1) {
                    if(result.code=='13100'){
                        $(".dianzan").css('background-color','#EB6877');
                    }else{
                        $(".dianzan").css('background-color','#b9afb0');
                    }
                } else {
                    var regex = /^1000[4-9]$/;
                    if(regex.test(result.code)){
                        alert('未登录，请先登录且实名认证后方可报名！');
                    }
                }
            }
        }, true)
    }

    $('.comments').on('click', '.deleteBtn', function () {
        var id = $(this).data('id');
        request({
            url: '/api/venuecomments/delete',
            type: 'post',
            dataType: 'json',
            data: {parentid: id},
            success: function (res) {
                if (res.status == 1) {
                    alert('删除成功' ,window.location.href);
                } else {
                    alert('删除失败');
                }
            },
            error: function (res) {
                console.log('ajax error');
            }
        }, true);
    });

    //绑定点击事件
    $('.comments').on('click', '.replyBtn', function () {
        var id = $(this).data('id');
        $('input[name=comment_content]').attr('placeholder', '回复:管理员');
        $('input[name=parentid]').val(id);
    });

    //绑定点击事件
    $('.send button').click(function () {
        var parentid = $('input[name=parentid]').val();
        var content = $('input[name=comment_content]').val().trim();
        request({
            url: '/api/venuecomments/comment',
            type: 'post',
            dataType: 'json',
            data: {parentid: parentid, venueid: id, content: content},
            beforeSend: function () {
                if (content == '') {
                    alert('评论不能为空');
                    return false;
                }
            },
            success: function (res) {

                if (res.status == 1) {
                    alert('您的评论已提交审核，通过后将显示');
                } else {
                    noLogin(res.code,res.msg);
                }
            },
            error: function () {
                console.log('ajax error');
            }
        }, true)
    });

    function getUid() {
        var uid = 0;
        request({
            url: '/api/my/index',
            type: 'post',
            dataType: 'json',
            async: false,
            success: function (res) {
                if (res.status == 1) {
                    uid = res.data.id;
                }
            }
        }, true);
        return uid;
    }

    function parseComment(data) {
        var html = '';
        for (var i in data) {
            var lv1 = data[i];
            lv1['user_nickname'] = lv1['user_nickname'] == '' ? '未设置昵称' : lv1['user_nickname'];
            var thumb = lv1['avatar'] == '' ? '/themes/simpleboot3/public/assets/whgcms/images/my/avatar1.png' : lv1['avatar_url'];
            html += '<li class="clearfix">';
            html += '<div class="img f-left" style="background-image: url(' + thumb + ');width:0.4rem;height:0.4rem"></div><div class="f-left" style="width: calc(100% - 0.48rem);"><div class="reply">';
            html += '<h3 class="clearfix">' + lv1['user_nickname'];
            if (lv1['userid'] == uid) {
                html += '<img class="deleteBtn" data-id="' + lv1['id'] + '" src="/themes/simpleboot3/public/assets/whgcms/wx/images/clear.png" alt="" style="float: right; width:0.16rem;height:0.16rem;">';
            }
            html += '</h3><p>' + lv1['comment'] + '</p></div>';
            for (var j in lv1.list) {
                var lv2 = lv1.list[j];
                html += '<div class="reply2">'
                if (lv2['userid'] == lv1['userid']) {
                    html += '<h3 class="clearfix" style="border-left: 2px solid #999;line-height: 0.15rem;padding-left: 0.05rem;">' + lv1['user_nickname'] + '回复管理员';
                    if (lv1['userid'] == uid) {
                        html += '<img class="deleteBtn" src="/themes/simpleboot3/public/assets/whgcms/wx/images/clear.png" alt="" data-id="' + lv2['id'] + '" style="float: right; width:0.16rem;height:0.16rem;"></h3>';
                    } else {
                        html += '</h3>';
                    }
                } else {
                    html += '<h3 class="clearfix" style="border-left: 2px solid #999;line-height: 0.15rem;padding-left: 0.05rem;">管理员回复';
                    if (lv1['userid'] == uid) {
                        html += '<img class="replyBtn" src="/themes/simpleboot3/public/assets/whgcms/wx/images/reply.png" alt=""  data-id="' + lv2['id'] + '" style="float: right; width:0.16rem;height:0.13rem;"></h3>';
                    } else {
                        html += '</h3>';
                    }
                }
                html += '<p>' + lv2['comment'] + '</p></div>';
            }
            html += '</div></li>';
        }
        $('.comments').append(html);
    }

    //构建dropload对象
    function buildDropload() {
        return $('.test1').dropload({
            scrollArea: window,
            domDown:{
                domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
            },
            loadDownFn: function (me) {
                request({
                    url: '/api/venuecomments/index',
                    type: 'post',
                    dataType: 'json',
                    data: {venueid: id, page: page, len: comment_limit},
                    success: function (res) {
                        if (res.status == 1) {
                            page++;
                            if (res.data.list.length < comment_limit) {
                                loadEnd = true;
                                me.lock();
                                me.noData();
                            }
                            parseComment(res.data.list);
                        } else {
                            loadEnd = true;
                            me.lock();
                            me.noData();
                        }
                        me.resetload();
                    },
                    error: function () {
                        console.log('ajax error');
                        me.resetload();
                    }
                });
            }
        });
    }

})