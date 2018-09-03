$(function () {
    getavatar();

    function getavatar(){
        request({
            url: '/api/my/index',
            type: 'post',
            dataType: 'json',
            async: false,
            success: function (res) {
                if (res.status == 1) {
                    $('.loginRegister').html(res.data.user_nickname);
                    $('.loginRegister').attr('href', 'javascript:;');
                    if (res.data.avatar != '') {
                        $('.photo').css('background-image', 'url(' + res.data.avatar_url + ')');
                    }
                }else{
                    // $('.mui-table-view a').click(function(){
                    //     alert('请先登录');
                    //     return false;
                    // });
                    $('.mui-table-view a:not("#about"),#photo').click(function(){
                        location.href = '/wx/login/login';
                        return false;
                    });

                }
            },
            error: function (res) {

            }
        }, true);
    }

    //上传头像
    var touxiangUploader = new uploader(
        '#photo',
        function (file, res) {
            if (res.code == 1) {
                saveAvatar(res.data, this);
            } else {
                alert(res.msg);
            }
        },
        function (res) {
            getdialog('上传照片失败了');
        },
        null,
        null,
        function(){
            $('#photo').css({'background-image':"url(/themes/simpleboot3/public/assets/whgcms/wx/images/load.gif)",'background-size': '30%,30%', 'background-repeat': 'no-repeat', 'background-position': 'center center'});
        }
    );

    function saveAvatar(data, uploader){
        request({
            url:'/api/my/save',
            data:{avatar:data.url},
            dataType:'json',
            type:'post',
            beforeSend:function(){
                // $('#photo').css({'background-image':"url(/themes/simpleboot3/public/assets/whgcms/wx/images/load.gif)",'background-size': '30%,30%', 'background-repeat': 'no-repeat', 'background-position': 'center center'});
            },
            success:function(res){
                if(res.status == 1){
                    $('#photo').css({'background-image':"url("+data.preview_url+")",'background-size': 'cover','background-repeat': 'round'});
                }
                // alert(res.msg);
                noLogin(res.code,res.msg);
                uploader.reset();
            },
            error:function(){
                console.log('ajax error')
            }
        }, true);
    }


    function uploader(id, successFun, errorFun, uploadId, previewId, fileQueuedFun) {
        var uploader = {};
        var auto = uploadId == undefined ? true : false;
        if (uploadId != undefined) {
            $(uploadId).click(function () {
                uploader.webuploder.upload();
            });
        }
        uploader.webuploder = WebUploader.create({
            pick: {
                id: id,
                label: '选择照片',
                multiple: false,
            },
            timeout: 0,
            formData: {
                app: 'portal',
                filetype: 'image'
            },
            accept: {
                extensions: 'png,jpg,jpeg,heic,mov'
            },
            swf: '/static/js/webuploader/Uploader.swf',
            chunked: true,//开启分片
            auto: auto,
            chunkSize: 2 * 1024 * 1024,// 单位B
            compress: false,
            server: "/user/asset/webuploader?_ajax=1",
            // 禁掉全局的拖拽功能。这样不会出现图片拖进页面的时候，把图片打开。
            disableGlobalDnd: true,
            fileNumLimit: 1,
            runtimeOrder: 'html5,flash',
            //fileSizeLimit: 200 * 1024 * 1024,    // 200 M
            fileSingleSizeLimit: 10 * 1024 * 1024      // 单位B
        });
        console.log(uploader.webuploder);
        uploader.webuploder.on('uploadSuccess', successFun);
        uploader.webuploder.on('uploadError', errorFun);
        uploader.webuploder.on('fileQueued', fileQueuedFun);
        if (previewId != undefined) {
            uploader.webuploder.on('fileQueued', function (file) {
                uploader.webuploder.makeThumb(file, function (error, src) {   //webuploader方法
                    if (error) {
                        alert('图片预览失败');
                    } else {
                        // $(previewId).attr('src', src);
                        $(previewId).css({'background-image':"url("+src+")",'background-repeat': 'round',});
                    }
                });
            });
        }
        uploader.webuploder.on('error', function (res, file) {
            // console.log(res)
            if (res == 'Q_EXCEED_NUM_LIMIT') {
                alert('只能上传一个文件');
            }
            if (res == 'Q_TYPE_DENIED') {
                alert('文件类型错误，请上传png,jpg,jpeg,heic格式图片');
            }
            if (res == 'F_EXCEED_SIZE') {
                alert('文件过大,最大限制10M');
            }
        });
    }




});