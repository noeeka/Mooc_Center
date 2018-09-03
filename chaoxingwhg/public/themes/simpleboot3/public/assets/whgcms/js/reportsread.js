$(function() {
    $('.comments input').keyup(function() {
        var len = $('.comments input').val().length;

        if (parseInt(len) == 0) {
             $('.comments button').removeClass('activity-active');
            $('.comments button').addClass('expire');
        } else {
             $('.comments button').removeClass('expire');
            $('.comments button').addClass('activity-active');
        }

    });

    var token = getCookie('token');
    selectNav(81);
    var id = getParam('id', -1);
    var limit = 15;
    var uid = 0;
    request({
        url: '/api/my/index',
        type: 'post',
        dataType: 'json',
        async: false,
        success: function(result) {
            if (result.status == 1) {
                uid = result.data.id;
            }
        }
    }, true);
    if (token == null) {
        $('button').hide();
    }

    function hot() {
        var id = getParam('id');
        $.ajax({
            url: '/api/volunarticle/index',
            type: 'post',
            dataType: 'json',
            data: {},
            success: function (response) {
                if (response.status == 1) {
                    dat = response.data.list.slice(0,3);
                    var hotTpl = $("#hot-template").html();
                    console.log(hotTpl);
                    var hotCmp = Handlebars.compile(hotTpl);
                    var hotFun = hotCmp(dat);
                    $('#Hot').html(hotFun);
                }
            }
        })
    }

    request({
        url: '/api/volunarticle/read',
        data: { id: id },
        type: 'post',
        dataType: 'json',
        success: function(res) {
            if (res.status == 1) {
                //设置标题
                $('title').text(res.data.detail.post_title);
                Handlebars.registerHelper('date', function(value) {
                    var date = new Date();
                    date.setTime(value * 1000);
                    return date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
                });
                Handlebars.registerHelper('image', function(value) {
                    if (value == '') {
                        return '/upload/portal/2.png';
                    } else {
                        return value;
                    }
                });
                Handlebars.registerHelper('is_collect', function(value) {
                    return value == 0 ? 'like1.png' : 'like2.png';
                });
                var tpl = $('#detail').html();
                var template = Handlebars.compile(tpl);
                $('.details').html(template(res.data.detail));
                if (res.data.detail.photo_status == 0) {
                    $('.photo-wall').hide();
                }
                if (res.data.user_role != 2) {
                    $('.photo-wall button').hide();
                }
                // if (res.data.is_collect == 1) {
                //     $('#collectBtn div').addClass('active');
                //     $('#collectBtn span').addClass('active');
                //     $('#collectBtn span').html('已收藏');
                // }
                // if (res.data.is_collect == 0) {
                //     $('.details').on('mouseover', '#collectBtn div', function() {
                //         $('#collectBtn img').attr('src', '/themes/simpleboot3/public/assets/whgcms/images/read/like3.png');
                //         $('#collectBtn span').addClass('active1');
                //     }).on('mouseout', '#collectBtn div', function() {
                //         $('#collectBtn img').attr('src', '/themes/simpleboot3/public/assets/whgcms/images/read/like1.png');
                //         $('#collectBtn span').removeClass('active1');
                //     })
                // }
                if (res.data.detail.comment_status == 1) {
                    $('.comments').removeClass('hide');
                    //加载评论
                    $.ajax({
                        url: '/api/comments/index',
                        data: { articleid: id, page: 1, len: 15 },
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == 1) {
                                parseHtml(res);
                                layui.use('laypage', function() {
                                    var laypage = layui.laypage;
                                    laypage.render({
                                        elem: 'test1', //注意，这里的 test1 是 ID，不用加 # 号
                                        count: res.data.num, //数据总数，从服务端得到
                                        next: '>',
                                        limit: limit,
                                        jump: function(obj, first) {
                                            if (!first) {
                                                $.ajax({
                                                    url: '/api/comments/index',
                                                    data: { articleid: id, page: obj.curr, len: limit },
                                                    dataType: 'json',
                                                    success: function(res) {
                                                        parseHtml(res);
                                                    },
                                                    error: function() {
                                                        console.log('ajax error');
                                                    }
                                                });
                                            }
                                        }
                                    });
                                });
                            }
                            if ($('.section li').length == 0) {
                                $('.layui-laypage-next').hide();
                            }
                        },
                        error: function(res) {
                            console.log('ajax error');
                        }
                    });
                }
            }
        }
    }, true);

    $('#newBtn').click(function() {
        var content = $('input[name=newContent]').val();
        comment(content, 0);
        $('input[name=newContent]').val('');
        $('#newBtn').removeClass('activity-active');
        $('#newBtn').addClass('expire');
    });
    // $('.details').on('click', '#collectBtn', function(res) {
    //     request({
    //         url: '/api/article/collect',
    //         dataType: 'json',
    //         data: { id: id },
    //         type: 'post',
    //         success: function(res) {
    //             if (res.status == 1) {
    //                 var collect_num = parseInt($('.collect_num').text());
    //                 if (res.code == 13102) {
    //                     // 收藏成功
    //                     $('#collectBtn div').removeClass('active1');
    //                     $('#collectBtn span').removeClass('active1');
    //                     $('#collectBtn div').addClass('active');
    //                     $('#collectBtn span').addClass('active');
    //                     $('#collectBtn img').attr('src', '/themes/simpleboot3/public/assets/whgcms/images/read/like2.png');
    //                     $('#collectBtn span').html('已收藏');
    //                     collect_num++;
    //                     $('.collect_num').text(collect_num);
    //                     // console.log(res.data.is_collect)
    //                     $('.details').off("mouseover", "#collectBtn div").off("mouseout", "#collectBtn div");

    //                 } else {
    //                     // 取消收藏
    //                     $('#collectBtn img').attr('src', '/themes/simpleboot3/public/assets/whgcms/images/read/like1.png');
    //                     $('#collectBtn div').removeClass('active');
    //                     $('#collectBtn span').removeClass('active');
    //                     $('#collectBtn span').html('收藏');
    //                     if (collect_num > 0) {
    //                         collect_num--;
    //                         $('.collect_num').text(collect_num);
    //                         $('.details').on('mouseover', '#collectBtn div', function() {
    //                             $('#collectBtn img').attr('src', '/themes/simpleboot3/public/assets/whgcms/images/read/like3.png');
    //                             $('#collectBtn span').addClass('active1');
    //                         }).on('mouseout', '#collectBtn div', function() {
    //                             $('#collectBtn img').attr('src', '/themes/simpleboot3/public/assets/whgcms/images/read/like1.png');
    //                             $('#collectBtn span').removeClass('active1');
    //                         })
    //                     }
    //                     getdialog(res.msg);
    //                 }


    //                 // getdialog(res.msg);
    //             } else {
    //                 noLogin(res, false);
    //             }
    //         },
    //         error: function(res) {
    //             console.log('ajax error');
    //         }
    //     }, true);
    // })
    // $('.details').off("mouseover","#collectBtn img").off("mouseout","#collectBtn img");
    // $('#collectBtn img').unbind('mouseover').unbind('mouseout');
    function comment(content, pid) {
        content = content.trim();
        request({
            url: '/api/comments/comment',
            dataType: 'json',
            data: { articleid: id, parentid: pid, content: content },
            beforeSend: function() {
                if (content == '') {
                    getdialog('请输入内容');
                    return false;
                }
            },
            success: function(res) {
                if (res.status == 1) {
                    getdialog('您的评论已提交审核，通过后将显示');
                } else {
                    noLogin(res, false);
                }
            }
        }, true);
    }

    function parseHtml(res) {
        var html = '';
        if (res.status == 1) {
            for (var i in res.data.list) {
                var lv1 = res.data.list[i];
                var thumb = lv1['avatar'] == '' ? '/themes/simpleboot3/public/assets/whgcms/images/my/avatar1.png' : lv1['avatar_url'];
                html += '<li class="clearfix" id="comment-item-' + lv1['id'] + '"><div class="portrait f-left" style="background-image: url(' + thumb + ');"></div><div class="f-left comlist"><ul class="clearfix">';
                html += '<li style="color: #1C2438;font-size: 14px;margin-right: 15px;font-weight: bold;">' + lv1['user_nickname'] + '</li>';
                html += '<li style="color:#A2A1A1;font-size: 14px;margin-top:1px;margin-right: 15px;">' + getdate(lv1['create_time']) + '</li>';
                if (lv1['userid'] == uid) {
                    html += '<li  class="deleteBtn" data-id="' + lv1['id'] + '"style="background-image: url(/themes/simpleboot3/public/assets/whgcms/images/read/delete.png);width:15px;height: 15px;cursor: pointer;background-size:cover;background-repeat: no-repeat;background-position: top center;"></li></ul>'
                } else {
                    html += '</ul>';
                }
                html += '<p style="color:#1C2438;margin:18px 0;font-size: 14px;">' + lv1['comment'] + '</p>';
                for (var j in lv1.list) {
                    var lv2 = lv1.list[j];
                    if (lv2['userid'] == lv1['userid']) {
                        //自己
                        html += '<div id="comment-item-' + lv2['id'] + '" style="font-size: 14px;margin:2px 0 14px;"><span style="color:#A0A4AA;margin-right:10px;">' + lv1['user_nickname'] + ':</span>';
                        html += '<p style="color:#A0A4AA;display: inline-block;margin-right: 15px;font-size:12px;">' + lv2['comment'] + '</p>';
                        if (lv1['userid'] == uid) {
                            html += '<div class="deleteBtn" data-id="' + lv2['id'] + '" style="background-image: url(/themes/simpleboot3/public/assets/whgcms/images/read/delete.png);width:15px;height: 15px;cursor: pointer;background-size:cover;background-repeat: no-repeat;background-position: top center;display: inline-block;"></div>';
                        }
                    } else {
                        //管理员
                        html += '<div id="comment-item-' + lv2['id'] + '"  style="font-size: 14px;margin:2px 0 14px;"><span style="color:#0059E5;display: inline-block;margin-bottom:10px;margin-right:10px;">管理员:</span>';
                        html += '<p style="color:#A0A4AA;display: inline-block;margin-right: 15px;line-height: 21px;font-size:12px;">' + lv2['comment'] + '</p>';
                        if (lv1['userid'] == uid) {
                            html += '<div id="reply" data-pid="' + lv1['id'] + '"style="background-image: url(/themes/simpleboot3/public/assets/whgcms/images/read/reply.png);width:13px;height: 13px;cursor: pointer;background-size:cover;background-repeat: no-repeat;background-position: top center;display: inline-block;"></div>';
                        }
                    }
                }
            }
        }
        $('.comment-list ul').html(html);
        if ($('.comment-list .section li').length == 0) {
            $('.comments div:first-child').css('border', '0');
        }
    }

    $('.comment-list').on('click', '#reply', function() {
        var replyinput = '<div style="margin-top:10px;display: none" id="replyinput"><input type="text " placeholder="请回复" style="border:1px solid #d9d9d9;width:705px;"><button style="display: block" class="expire">回复</button></div>';
        if ($(this).hasClass('replyinput_show')) {
            $(this).removeClass('replyinput_show');
            $("#replyinput").slideUp();
        } else {
            $('#replyinput').remove();
            $(this).addClass('replyinput_show');
            $(this).after(replyinput);
            $("#replyinput").slideDown();
            $("#replyinput").attr('data-pid', $(this).data('pid'));
        }
        // $('#replyinput input').focus(function() {
        //     $('#replyinput button').css('background-color', '#F40556');
        //     $('#replyinput button').css('color', '#fff');
        // })
        // $('#replyinput input').blur(function() {
        //     $('#replyinput button').css('background-color', '#3259C6');
        //     $('#replyinput button').css('color', '#2F343A');
        // })
        $('#replyinput input').keyup(function() {
            var len = $('#replyinput input').val().length;

            if (parseInt(len) == 0) {
                $('#replyinput button').removeClass('activity-active');
                $('#replyinput button').addClass('expire');
            } else {
                $('#replyinput button').addClass('activity-active');
                $('#replyinput button').removeClass('expire');
            }

        });
    })

    $('.comment-list').on('click', '#replyinput button', function() {
        var pid = $('#replyinput').data('pid');
        var content = $('#replyinput input').val();
        comment(content, pid);
        $('#replyinput input').val('');
        $('#replyinput button').removeClass('activity-active');
        $('#replyinput button').addClass('expire');
    })

    $('.comment-list').on('click', '.deleteBtn', function() {
        var id = $(this).data('id');
        request({
            url: '/api/comments/delete',
            dataType: 'json',
            data: { parentid: id },
            success: function(res) {
                if (res.status == 1) {
                    getdialogWithFunc('删除评论成功', function() {
                        window.location.reload();
                    });
                } else {
                    getdialog('删除评论失败');
                }
            },
            error: function(res) {
                console.log('ajax error');
            }
        }, true);
    })

    function getdate(time) {
        var date = new Date();
        date.setTime(time * 1000);
        var month = date.getMonth() + 1;
        month = month > 9 ? month : '0' + month;
        var day = date.getDate();
        day = day > 9 ? day : '0' + day;
        var hour = date.getHours();
        hour = hour > 9 ? hour : '0' + hour;
        var minute = date.getMinutes();
        minute = minute > 9 ? minute : '0' + minute;
        return date.getFullYear() + '-' + month + '-' + day + ' ' + hour + ':' + minute;
    }
    hot();
    hotread();

    function getdialogWithFunc(msg, func) {
        var href = window.open;
        layui.use('layer', function() {
            layer.open({
                title: '提示',
                content: msg,
                btn: ['确认', '关闭'],
                yes: function(index, layero) {
                    if (func != undefined) {
                        layer.closeAll();
                        func();
                    } else {
                        layer.closeAll();
                    }
                },
                btn2: function(index, layero) {
                    layer.closeAll();
                },
                cancel: function() {
                    return false;
                },
                btnAlign: 'c',
                anim: 1,
                shade: 0.3,
                scrollbar: false
            });
        });
    }
    /*照片墙*/
    photos();
    function photos(){
       $.ajax({
        url: '/api/volunarticle/read',
        type: 'GET',
        dataType: 'json',
        data: { id: id,is_photo:1 },
        success: function(res) {
            if (res.status == 1) {
                var html = '';
                for (var i in res.data.photo) {
                    html += '<div class="swiper-slide" style="position:relative;cursor: pointer;"><img src="' + res.data.photo[i].img + '" alt="" onclick="javascript:parent.imagePreviewDialog(this.src);"><h4 style="position: absolute;left: 0;bottom: 0;padding: 0 19px;color: #fff;height: 22px;font-size: 14px;background: rgba(0, 0, 0, .5);width: 182px;line-height: 22px;text-align: left;"><span>提供人：</span>' + res.data.photo[i].user_realname + '</h4></div>';
                }
                $('.swiper-wrapper').html(html);
                
                var swiper1 = new Swiper('.swiper-container', {
                    slidesPerView: 3,
                    slidesPerColumn: 2,
                    spaceBetween: 8,
                    observer: true, //修改swiper自己或子元素时，自动初始化swiper
                    observeParents: true, //修改swiper的父元素时，自动初始化swiper
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                });
            }
            if ($('.swiper-slide').length <= 6) {
                    $('.swiper-button-next').hide();
                    $('.swiper-button-prev').hide();
                } else {
                    $('.swiper-button-next').show();
                    $('.swiper-button-prev').show();
                }

        }
    }) 
    }
    


});