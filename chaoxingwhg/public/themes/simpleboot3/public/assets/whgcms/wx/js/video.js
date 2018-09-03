$(function () {
    var article_id = getUrl('id');
    var from = getUrl('from');
    var comment_limit = 6;
    var dropload = null;//dropload对象
    var loadEnd = false;//dropload构建对象时不获取数据
    var page = 1; //当前页数
    var uid = 0;
    var iniWindowHeight = $(window).height();
    if(from == 'reply'){
        $('.return').attr('href', 'javascript:history.go(-2)');
    }
    //注册handlebars 助手函数
    Handlebars.registerHelper('date', function (value) {
        var date = new Date();
        date.setTime(value * 1000);
        return date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
    });
    //监听resize事件滚动
    $(window).resize(function () {
        var h = iniWindowHeight - $(window).height();
        var top = $(window).scrollTop();
        $(window).scrollTop(h + top);
    });
    //判断是否登录
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
    //获取文章
    request({
        url: '/api/video/read',
        type: 'post',
        dataType: 'json',
        data: {id: article_id},
        success: function (res) {
            if (res.status == 1) {
                var tpl = $('#figure').html();
                var template = Handlebars.compile(tpl);
                $('.figure').html(template(res.data));
                //设置标题
                $('title').text(res.data.title);
                //收藏按钮样式
                if (res.data.is_collect == 1) {
                    // console.log
                    $('.collectBtn img').attr('src', '/themes/simpleboot3/public/assets/whgcms/wx/images/like2.png');
                }
                if (res.data.comment_status == 1) {
                    $('.comments').show();
                    $('.send').show();
                    $('.test').css('margin-bottom', '0.5rem');
                    uid = getUid();
                    dropload = buildDropload();
                }

                var videoObject = {
                    container: '.video', //“#”代表容器的ID，“.”或“”代表容器的class
                    variable: 'player', //该属性必需设置，值等于下面的new chplayer()的对象
                    poster: res.data.thumb, //封面图片
                    video: [
                        [res.data.files, 'video/*', '中文标清', 0]
                    ] //视频地址
                };

                console.log(videoObject);
                var player = new ckplayer(videoObject);
            } else {
                $('body').html('<p>内容不存在</p>');
                // noLogin(res.code,res.msg);
            }
        }
    }, true);

    //绑定点击事件
    $('.comments').on('click', '.deleteBtn', function () {
        var id = $(this).data('id');
        request({
            url: '/api/comments/delete',
            type: 'post',
            dataType: 'json',
            data: {parentid: id},
            success: function (res) {
                if (res.status == 1) {
                    alert('删除成功' ,window.location.href);
                   // location.reload();
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
            url: '/api/comments/comment',
            type: 'post',
            dataType: 'json',
            data: {parentid: parentid, articleid: article_id, content: content},
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

    $('.figure').on('click', '.collectBtn', function () {
        request({
            url: '/api/article/collect',
            dataType: 'json',
            data: {id: article_id},
            type: 'post',
            success: function (res) {
                if (res.status == 1) {
                    var collect_num = parseInt($('.collectBtn span').text());
                    if (res.code == 13102) {
                        // 收藏成功
                        $('.collectBtn img').attr('src', '/themes/simpleboot3/public/assets/whgcms/wx/images/like2.png');
                        collect_num++;
                        $('.collectBtn span').text(collect_num);

                    } else {
                        // 取消收藏
                        $('.collectBtn img').attr('src', '/themes/simpleboot3/public/assets/whgcms/wx/images/like1.png');
                        if (collect_num > 0) {
                            collect_num--;
                            $('.collectBtn span').text(collect_num);
                        }
                    }
                    alert(res.msg);
                } else {
                    // if (res.code == 10005) {
                    //     alert('请先登录再收藏');
                    // }
                    noLogin(res.code,res.msg);
                }
            },
            error: function (res) {
                console.log('ajax error');
            }
        }, true);
    });

    //构建dropload对象
    function buildDropload() {
        return $('.test1').dropload({
            scrollArea: window,
            domDown:{
                domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
            },
            loadDownFn: function (me) {
                request({
                    url: '/api/comments/index',
                    type: 'post',
                    dataType: 'json',
                    data: {articleid: article_id, page: page, len: comment_limit},
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
            //var thumb = lv1['avatar'] == '' ? '/themes/simpleboot3/public/assets/whgcms/wx/images/1.png' : lv1['avatar_url'];
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

});