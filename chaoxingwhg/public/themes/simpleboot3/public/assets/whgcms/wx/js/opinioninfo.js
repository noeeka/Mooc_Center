$(function() {

        var page = 1;
        var size = 4;

        $('form div').click(function(event) {
            $('form span').removeClass('active');
            $('form div').removeClass('active1');
            $(this).children('span').addClass('active');
            $(this).addClass('active1');
        });
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

        $('.comments textarea').focus(function(){
            $('.comments button').css('background-color','#F40556');
        });
        $('.comments textarea').blur(function(){
            $('.comments button').css('background-color','#2F343B');
        });

        function opinionRead(){}
        opinionRead.prototype.data = { 'opinion_id' : 0 , 'page' : 1, 'size' : 10 };
        opinionRead.prototype.nav_id = 32;
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
                $('.figure').html(html);

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
            postData.allow_public = $('#commentForm').find('span.active').attr('data-value');
            requestRead({
                url: '/api/opinion_idea/submitIdea',
                dataType: 'json',
                data: postData,
                type:'POST',
                beforeSend: function () {
                    if (content == '') {
                        alert('请输入内容');
                        return false;
                    }
                },
                success: function (res) {
                    if (res.status == 1 && res.code == 200) {
                        $("textarea[name='content']").val('');
                        alert('您的评论已提交审核，通过后将显示');
                    } else if(res.status == 1 && res.code == 2005) {
                        alert(res.msg);
                    } else if(res.status == 0 && res.code == 2006) {
                        alert(res.msg);
                    }else {
                        if(res.code==10005){
                            alert('请先登录再评论');
                        }else{
                            alert('发表评论失败');
                        }
                    }
                }
            }, true , '/api/opinionidea/submitidea');
        };
        //初始化绑定
        opinionRead.prototype.initBind = function () {
            var self = this;
            $('.commentBtn').on('click' , function () {
                self.submitIdea();
            });
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
            }else{
                var html = '';
            }
            return html;
        };
        //获取评论列表
        opinionRead.prototype.getCommentList = function (page) {
            var parent = this;

            this.data.opinion_id = $("input[name='opinion_id']").val();
            var res = this.ajax('/api/opinion_idea/getOpinionIdeaList' , this.data);
            if(res.status == 1) {
                var html = this.parseItem(res);

                $('.section').html(html);
            }
        };

        var op = new opinionRead();
        op.getInfo();
        op.initBind();

        var dropload = $('#comments').dropload({
            scrollArea: window,
            domDown:{
                domNoData : '<div class="dropload-noData">已经到底啦~(>_<)~~</div>'
            },
            threshold:10,
            loadDownFn: function(me) {
                // 加载按默认和最新发布排序的数据
                $.ajax({
                    url:'/api/opinion_idea/getOpinionIdeaList',
                    type:'post',
                    data:{ "opinion_id" : $("input[name='opinion_id']").val(), 'page' : page, 'size' : size},
                    dataType:'json',
                    success:function (res) {
                        if(res.status ==1){
                            page++; //页数加1

                            if (res.data.data.length < size) {
                                // 锁定
                                me.lock();
                                // 显示无数据
                                me.noData();

                            }

                            setTimeout(function () {
                                var html = op.parseItem(res);
                                $('.section').append(html);
                                me.resetload();
                            } , 1000);


                        } else  {
                            me.lock();
                            me.noData();
                            me.resetload();
                        }
                    }
                })
            }
    });


});