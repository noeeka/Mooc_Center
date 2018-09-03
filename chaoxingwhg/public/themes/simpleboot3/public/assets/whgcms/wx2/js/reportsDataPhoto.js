$(function(){
    var photos = [];
    var article_id = getUrl('id');
    var total_num = 9;
    init();
    function init(){
        //获取当前已上传图片数量
        request({
            url:'/api/volunarticle/get_my_photo',
            type:'post',
            dataType:'json',
            data:{id:article_id},
            success:function(res){
                var num_limit = total_num - res.data;
                if(num_limit > 0){
                    buildUploader(num_limit);
                }else{
                    $('#addphoto').click(function(){
                       alert('很抱歉，你目前不能上传照片');
                    });
                }
                //添加点击事件
                $('.closeBtn').click(function(){
                    if(photos.length > 0){
                        request({
                            url:'/api/volunarticle/photo_add',
                            type:'post',
                            dataType:'json',
                            data:{cid:article_id, photos:photos},
                            success:function(res){
                                if(res.status != 1){
                                    noLogin(res.code, res.msg);
                                }else{
                                    alert('上传成功', undefined, function(){
                                        history.back(-1);
                                    });
                                }
                            },
                            error:function(){
                                console.log('ajax error');
                            }
                        }, true);
                    }else{
                        history.back(-1);
                    }
                });
            },
            error:function(){
                console.log('ajax error');
            }
        }, true);
    }
    function buildUploader(num_limit){
        var uploader = WebUploader.create({
            pick: {
                id: '#addphoto',
                label: false,
                multiple: true,
            },
            timeout: 0,
            formData: {
                app: 'portal',
                filetype: 'image'
            },
            accept: {
                extensions: 'png,jpg'
            },
            swf: '/static/js/webuploader/Uploader.swf',
            chunked: true,//开启分片
            auto: true,
            chunkSize: 2 * 1024 * 1024,// 单位B
            compress: false,
            server: "/user/asset/webuploader?_ajax=1",
            // 禁掉全局的拖拽功能。这样不会出现图片拖进页面的时候，把图片打开。
            disableGlobalDnd: true,
            fileNumLimit: num_limit,
            runtimeOrder: 'html5,flash',
            //fileSizeLimit: 200 * 1024 * 1024,    // 200 M
            fileSingleSizeLimit: 200 * 1024 * 1024      // 单位B
        });
        uploader.on('uploadSuccess', function(file, res){
            if(res._raw != undefined && res._raw == '非法上传！'){
                alert('请重新登陆后重试！');
            }else{
                if (res.code == 1) {
                    $('#addphoto').before('<img class="f-left" src="'+res.data.url+'" alt="">');
                    photos[photos.length] = res.data.filepath;
                }else{
                    alert(res.msg);
                }
            }
        });
        uploader.on('uploadError', function(res){
            console.log(res);
        });
        uploader.on('error', function(res, file){
            if(res == 'Q_EXCEED_NUM_LIMIT'){
                alert('最多上传20张照片');
            }
            if(res == 'Q_TYPE_DENIED'){
                alert('部分文件类型错误');
            }
            if(res == 'Q_EXCEED_SIZE_LIMIT'){
                alert('文件过大');
            }
        });
    }
});