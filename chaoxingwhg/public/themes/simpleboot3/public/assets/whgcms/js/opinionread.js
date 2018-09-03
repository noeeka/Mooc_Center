$(function() {

    $('#allow_radio').on('click' ,function () {
        var span = $(this).find('span');
        var span_value = span.attr('data-value');

        console.log(span_value);
        $("input[name='allow_public']").val(span_value);
        $('.allow_radio').find('span').removeClass('active');
        span.addClass('active');
    });

    function unserialize(url){
        if(url.indexOf("?") > -1) {
            url = url.substring(1);
        }
        var parts = url.split("&");
        var json = {};
        parts.forEach(function(item){
            if(item.indexOf("=") > -1){
                var itemArray = item.split("=");
                json[itemArray[0]] = itemArray[1];
            }
        });
        return json;
    }

    function requestRead(ajax, sign , url) {
        if (sign != undefined) {
            if (ajax.data == undefined) ajax.data = {};
            var salt = getCookie('salt');
            var token = getCookie('token');
            ajax.data.timestamp = ((new Date()).getTime()) / 1000;
            ajax.data.token = token;
            ajax.data.sign = hex_sha1(token + salt + url + ajax.data.timestamp);
        }
        $.ajax(ajax);
    }


    // $('.comments textarea').focus(function(){
    //     $('.comments button').css('background-color','rgb(48, 87, 196)');
    // });
    // $('.comments textarea').blur(function(){
    //     $('.comments button').css('background-color','#3259C6');
    // });

    $('.comments textarea').keyup(function(){
        var len = $('.comments textarea').val().length;

        if ( parseInt(len) == 0 ) {
            $('.comments button').css('background-color','#acacac');
        } else  {
            $('.comments button').css('background-color','rgb(48, 87, 196)');
        }

    });


    function opinionRead(){}
    opinionRead.prototype.nav_id = 32;
    opinionRead.prototype.data = { 'opinion_id' : 0 , 'page' : 0, 'size' : 10 };
    opinionRead.prototype.ajax = function( url ,data){
        var rsJson = '';
        $.ajax({
            url:url,data:data,type:'POST',dataType:'json',async:false,success:function( data){
                rsJson = data;
            }
        });
        return rsJson;
    };
    //获取意见信息
    opinionRead.prototype.getInfo = function () {
        var self = this;
        var opinionId = this.getParam('id', 0);
        var res = self.ajax('/api/opinion/getOpinionInfo' , {'opinionId' : opinionId});

        if(res.status == 1) {
            $('title').text(res.data.info.title);
            var opinion = this;
            var tpl = $('#info').html();
            Handlebars.registerHelper('create_time', function(items , fn) {
                var value = items.data.root.create_time;
                var date = new Date();
                date.setTime(value * 1000);
                return date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();
            });

            Handlebars.registerHelper('start_time', function(items , fn) {
                var value = items.data.root.start_time;
                var date = new Date();
                date.setTime(value * 1000);
                return date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();
            });

            Handlebars.registerHelper('end_time', function(items , fn) {
                var value = items.data.root.end_time;
                var date = new Date();
                date.setTime(value * 1000);
                return date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();
            });

            var template = Handlebars.compile(tpl);
            var html = template(  res.data.info);

            $("input[name='opinion_id']").val(res.data.info.id);
            $('.details').html(html);

            if (res.data.info.time_type == 1) {
                $("#commentForm").show();
                $("#comments").show();
            }
            if(res.data.info.time_type == 3) {
                $("#comments").show();
            }
        }
    };
    //获取参数
    opinionRead.prototype.getParam = function(k, d) {
        var kv = location.search.substring(1).split('&').map(function (v) {
            return v.split('=')
        });
        var params = {};
        for (var i in kv) {
            var val = kv[i];
            if (val[0] == k) {
                return val[1] == undefined ? d : val[1];
            }
        }
        return d;
    };
    //提交意见
    opinionRead.prototype.submitIdea = function () {
        var opinion_id = $("input[name='opinion_id']").val();
        if(opinion_id == 0) {
            getdialog('系统错误');
            return;
        }
        var content = $("textarea[name='content']").val();
        if (content.length == 0) {
            getdialog('请填写意见内容');
            return;
        }

        var data = $('#commentForm').serialize();
        var postData = unserialize(data);
        requestRead({
            url: '/api/opinion_idea/submitIdea',
            dataType: 'json',
            data: postData,
            type:'POST',
            beforeSend: function () {
                if (content == '') {
                    getdialog('请输入内容');
                    return false;
                }
            },
            success: function (res) {
                if (res.status == 1 && res.code == 200) {
                    $("textarea[name='content']").val('');
                    $('.comments button').css('background-color','#acacac');
                    getdialog('您的评论已提交审核，通过后将显示');
                } else if(res.status == 1 && res.code == 2005) {
                    getdialog(res.msg);
                } else if(res.status == 0 && res.code == 2006) {
                    getdialog(res.msg);
                } else {
                    if(res.code==10005){
                        getdialog('请先登录再评论');
                    }else{
                        getdialog('发表评论失败');
                    }
                }
            }
        }, true , '/api/opinionidea/submitidea');

    };
    //初始化绑定
    opinionRead.prototype.initBind = function () {
        var self = this;
        $('.comments button').on('click' , function () {
            self.submitIdea();
        });
        //绑定公开的radio事件
        //选中导航栏
        $('.nav a').removeClass('active');
        $('#nav' + this.nav_id).addClass('active');
    };

    opinionRead.prototype.initComment = function () {
        this.getCommentList(1);
    };
    //渲染数据
    opinionRead.prototype.parseItem = function (res) {
        html = "";
        if(res.status == 1){
            var tpl = $('#commentlist').html();
            Handlebars.registerHelper('create_time', function(items , fn) {
                var key = items.data.key;
                var value = items.data.root.comment_list[key].create_time;
                var date = new Date();
                date.setTime(value * 1000);
                return date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();
            });
            var template = Handlebars.compile(tpl);
            var html = template({'comment_list' :res.data.data});
            // if(res.data.avatar==''){
            //     $('.comments .portrait').css('background-image','/themes/simpleboot3/public/assets/whgcms/images/my/avatar1.png')
            // }
        }else{
            var html = '';
        }

        //console.log(html);
        $('.section').html(html);
    };
    //获取评论列表
    opinionRead.prototype.getCommentList = function (page) {
        var parent = this;
        this.data.page = page;
        this.data.opinion_id = $("input[name='opinion_id']").val();
        var res = this.ajax('/api/opinion_idea/getOpinionIdeaList' , this.data);
        if(res.status == 1) {
            this.parseItem(res);
            layui.use('laypage', function () {
                var laypage = layui.laypage;
                if(res.nums ==  0) {
                    $("#test1").html("");
                } else {
                    laypage.render({
                        elem: 'test1', //注意，这里的 test1 是 ID，不用加 # 号
                        count: res.data.nums, //数据总数，从服务端得到
                        next: '>',
                        limit: parent.data.size,
                        jump:function(obj, first){
                            if(!first){
                                parent.data.page = obj.curr;
                                var rs = parent.ajax('/api/opinion_idea/getOpinionIdeaList' , parent.data);
                                if(rs.status == 1){
                                    parent.parseItem(rs);
                                }
                            }
                        }
                    });
                }
                //执行一个laypage实例
            });
        }
    };

    var op = new opinionRead();
    op.getInfo();
    op.initBind();
    op.initComment();
 hot();

    function hot() {
        var id = getParam('id');
        console.log(id)
        $.ajax({
            url: '/api/article/index',
            type: 'get',
            dataType: 'json',
            data: { len: 3, cid: 161, sort: 'hot','has_child':1 },
            success: function (response) {
                if (response.status == 1) {
                    var hotTpl = $("#hot-template").html();
                    var hotCmp = Handlebars.compile(hotTpl);
                    var hotFun = hotCmp(response.data);
                    $('#Hot').html(hotFun);
                }
            }
        })
    }
    //  hotread();
    //
    // function hotread() {
    //     var id = getParam('id');
    //     $.ajax({
    //         url: '/api/article/index',
    //         type: 'GET',
    //         dataType: 'json',
    //         data: { len: 7, cid:8, sort:'hot' },
    //         success: function(response) {
    //             var res = response.data.list.slice(3);
    //             if (response.status == 1) {
    //                 var readTpl = $("#read-template").html();
    //                 var readCmp = Handlebars.compile(readTpl);
    //                 var readFun = readCmp(res);
    //                 $('#read').html(readFun);
    //             }
    //         }
    //     })
    // }


});