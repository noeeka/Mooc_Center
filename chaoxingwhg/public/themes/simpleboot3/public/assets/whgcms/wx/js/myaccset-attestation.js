$(function(){
    get_auth_info();


    function  get_auth_info(){
        request({
            url:'/api/user/auth_read',
            type: 'GET',
            dataType: 'json',
            data: {},
            success: function(res) {
                if (res.status == 1) {
                    if(res.data.status ==2 ||res.data.status == 0){
                        //验证中或 审核通过
                        $('#name').val(res.data.realname  );
                        $('#idCards').val(res.data.shenfenzheng);
                        $('.idphoto>label').css("display","none");
                        $("#ft_sfzimg>div:last-child>label").css("display","none");
                        $("#bd_sfzimg>div:last-child>label").css("display","none");
                        $('.idphotoOne').css({'background-image': 'url('+res.data.server_img[0]+')','background-size': '100% 100%'})
                        $('.idphotoTwo').css({'background-image': 'url('+res.data.server_img[1] +')','background-size': '100% 100%'});
                        $(".mui-button-row>button").hide();
                        if(res.data.status == 0){
                            $(".atteState").html("身份认证中");
                        }else{
                            $(".atteState").html("身份认证成功");
                        }
                    }
                    else{
                        //未通过
                        $("#name").removeAttr("disabled");
                        $("#idCards").removeAttr("disabled");
                        $('#name').val(res.data.realname);
                        $('#idCards').val(res.data.shenfenzheng);
                        $('.idphotoOne').css({'background-image': 'url('+res.data.server_img[0]+')','background-size': '100% 100%'})
                        $('.idphotoTwo').css({'background-image': 'url('+res.data.server_img[1] +')','background-size': '100% 100%'});
                        $('input[name=ft_sfzimg]').val(res.data.img.img);
                        $('input[name=bd_sfzimg]').val(res.data.img.img1);
                        $(".atteState").html("身份认证未通过");
                    }
                }else{
                        //未提交身份认证
                        $("#name").removeAttr("disabled");
                        $("#idCards").removeAttr("disabled");
                        $(".idphotoTwo>.webuploader-pick>span").css("display","block")
                        $(".idphotoOne>.webuploader-pick>span").css("display","block")
                        $(".atteState").html("身份未认证");
                }
            },
        },true)
    }

    //上传身份证正面照
    var ftSfzimgUploader = new uploader(
        '#ft_sfzimg',
        function (file, res) {
            if (res.code == 1) {
                $('#ft_sfzimg').css({'background-image': 'url(' + res.data.preview_url + ')','background-size': '100% 100%'});
                $("input[name=ft_sfzimg]").val(res.data.filepath);
                $("#ft_sfzimg>.webuploader-pick>span").css("display","none");
                $("#bd_sfzimg>.webuploader-pick>span").css("display","none");
            } else {
                alert("上传身份证正面照失败了")
            }
            this.reset();
        },
        function (res) {
            alert('上传身份证正面照失败了');
        },
        null,
        null,
        function(){
            $('#ft_sfzimg').css({'background-image':"url(/themes/simpleboot3/public/assets/whgcms/wx/images/load.gif)",'background-size': '10%,10%', 'background-repeat': 'no-repeat', 'background-position': 'center center'});
        }
    );
    //上传身份证反面照
    var bdSfzimgUploader = new uploader(
        '#bd_sfzimg',
        function (file, res) {
            if (res.code == 1) {
                $('#bd_sfzimg').css({'background-image': 'url(' + res.data.preview_url + ')','background-size': '100% 100%'});
                $("input[name=bd_sfzimg]").val(res.data.filepath);
                $("#ft_sfzimg>.webuploader-pick>span").css("display","none");
                $("#bd_sfzimg>.webuploader-pick>span").css("display","none");
            } else {
                alert("上传身份证反面照失败了")
            }
            this.reset();
        },
        function (res) {
            alert('上传身份证反面照失败了');
        },
        null,
        null,
        function(){
            $('#bd_sfzimg').css({'background-image':"url(/themes/simpleboot3/public/assets/whgcms/wx/images/load.gif)",'background-size': '10%,10%', 'background-repeat': 'no-repeat', 'background-position': 'center center'});
        }
    );

    //点击提交按钮
    $('.mui-button-row>button').click(function () {
        var realname = $('input[name=realname]').val().trim();
        var shenfenzheng = $('input[name=idCards]').val().trim();
        var ft_sfzimg = $('input[name=ft_sfzimg]').val();
        var bd_sfzimg = $('input[name=bd_sfzimg]').val();
        request({
            url:'/api/user/auth_real_user',
            data:{realname:realname, shenfenzheng:shenfenzheng,sfz_img: {img:ft_sfzimg,img1:bd_sfzimg}},
            dataType:'json',
            type:'post',
            beforeSend:function(){
                if(realname == ''){
                    alert('真实姓名不能为空');
                    return false;
                }
                if(shenfenzheng == ''){
                    alert('身份证号不能为空');
                    return false;
                }
                if(!/\d{17}[\d|x]|\d{15}/.test(shenfenzheng)){
                    alert('身份证号不合法');
                    return false;
                }
                if(ft_sfzimg == ''){
                    alert('身份证正面照不能为空');
                    return false;
                }
                if(bd_sfzimg == ''){
                    alert('身份证反面照不能为空');
                    return false;
                }
            },
            success:function(res){
                if(res.status == 1){
                    //提交成功需要显示查看身份证模块
                    $("#ft_sfzimg>div:last-child>label").css("display","none");
                    $("#bd_sfzimg>div:last-child>label").css("display","none");
                    $("#name").attr("disabled","disabled");
                    $("#idCards").attr("disabled","disabled");
                    $(".atteState").html("身份认证中");
                    $(".mui-button-row>button").hide();
                }else{
                    noLogin(res.code,res.msg);
                }
            },
            error:function(){
                console.log('ajax error')
            }
        }, true);
        return false;
    });





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
                label: '',
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
            fileSingleSizeLimit: 20 * 1024 * 1024      // 单位B
        });
        uploader.webuploder.on('uploadSuccess', successFun);
        uploader.webuploder.on('uploadError', errorFun);
        uploader.webuploder.on('fileQueued', fileQueuedFun);
        if (previewId != undefined) {
            uploader.webuploder.on('fileQueued', function (file) {
                uploader.webuploder.makeThumb(file, function (error, src) {   //webuploader方法
                    if (error) {
                        alert('图片预览失败');
                    } else {
                        $(previewId).attr('src', src);
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
              alert('文件类型错误，请上传jpg,png,jpeg,heic格式图片');
          }
          if (res == 'F_EXCEED_SIZE') {
              alert('文件过大,最大限制10M');
          }
      });
    }
     


});