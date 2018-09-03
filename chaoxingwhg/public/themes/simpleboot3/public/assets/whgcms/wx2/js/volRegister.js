


$(function(){
    //选择区域
    function quyu(){
        var nameEl = document.getElementById('quyu');
        var data1 = [];
        $.ajax({
            type: 'get',
            url: '/api/area/volun_index',
            dataType: 'json',
            async: false,
            success: function(res) {
                console.log(res)
                if(res.status == 1){
                    data1 = res.data;
                    for(var i = 0;i<data1.length;i++){
                        data1[i].text = data1[i].name;
                        data1[i].value = i;
                    }
                    // console.log(data1)
                }
            }
        })
        // var data1 = [{
        //     text: '北京',
        //     value: 1
        // }, {
        //     text: '天津',
        //     value: 2
        // }];

        var picker = new Picker({
            data: [data1],
            selectedIndex: [0, 1, 2]
            // title: '选择性别'
        });

        picker.on('picker.select', function(selectedVal, selectedIndex) {
            nameEl.innerText = data1[selectedIndex[0]].text;
        })

        // picker.on('picker.change', function (index, selectedIndex) {
        //     console.log(index);
        //     console.log(selectedIndex);
        // });

        picker.on('picker.valuechange', function(selectedVal, selectedIndex) {
            console.log(selectedVal);
            // console.log(selectedIndex);
            area = data1[selectedVal[0]].id;
            // console.log(area)
        });

        nameEl.addEventListener('click', function() {
            picker.show();
        });
        $('body').on('click', '.picker-mask', function() {
            picker.hide();
        })
    }
    quyu()

    //调用日期插件
    var calendar = new datePicker();
    calendar.init({
        'trigger': '#birthday',
        /*按钮选择器，用于触发弹出插件*/
        'type': 'date',
        /*模式：date日期；datetime日期时间；time时间；ym年月；*/
        // 'minDate': '1900-1-1',
        /*最小日期*/
        // 'maxDate': '2100-12-31',
        /*最大日期*/
        'onSubmit': function() { /*确认时触发事件*/
            var theSelectData = calendar.value;
        },
        'onClose': function() { /*取消时触发事件*/ }
    });
    //手机验证正则
    var phoneReg = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
    var uid = 0;
    request({
        type: 'get',
        url: '/api/volunregister/index',
        dataType: 'json',
        async: false,
        success: function(res) {
            console.log(res);
            if(res.status == 1){
                if(res.data.status == 0 || res.data.status == 2){
                    $('#sfzhao').hide();
                    $('#sfzphotos').hide();
                    $('#name').val(res.data.realname);
                    $("#name").attr("disabled","disabled");
                }else {
                    $('#sfzhao').show();
                    $('#sfzphotos').show();
                }
                uid = res.data.user_id;

            }

        }
    },true);
    //注册验证
    $('.subBtn').click(function() {
        // 缓存外部this
        var _this = $(this);

        // 禁止重复提交
        if (_this.hasClass('disabled')) {
            return false;
        }

        // 获取表单数据
        // var formData = $('#registForm').serialize();
        var realname = $('#name').val().trim();
        var sex = $('input[name=sex]:checked').val();
        console.log(sex)
        var birthday = $('#birthday').val().trim();
        var minzu = $('#minzu').val().trim();
        var mobile = $('#mobile').val().trim();
        var sfzid = $('#sfzid').val().trim();
        var ft_sfzimg = $('input[name=ft_sfzimg]').val();
        var bd_sfzimg = $('input[name=bd_sfzimg]').val();
        var sfzimg = [ft_sfzimg,bd_sfzimg];//身份证照片
        // var quyu = $('#quyu').text();
        var techang = $('#techang').val().trim();
        var cyimg1 = $('input[name=cyimg1]').val();
        var cyimg2 = $('input[name=cyimg2]').val();
        var cyimg3 = $('input[name=cyimg3]').val();
        var caiyiImg = [cyimg1,cyimg2,cyimg3];//才艺照片
        // 发起请求行
        request({
            url: '/api/volunregister/register',
            type: 'post',
            data: {
                realname:realname,
                sex:sex,
                birthday:birthday,
                nation:minzu,
                mobile:mobile,
                ID:sfzid,
                sfzimg:sfzimg,
                area:area,
                Speciality:techang,
                photos:caiyiImg,
                uid:uid
            },
            // 发起请求前就调用
            beforeSend: function() {
                if(realname==''){
                    alert('姓名不能为空！');
                    return false;
                }else if(birthday == ''||birthday=='选择生日日期'){
                    alert('请选择出生日期！');
                    return false;
                }else if (mobile == '') {
                    // 友好提示
                    alert('手机号不能为空！')
                    // 可终止请求发起
                    return false;
                }else if (!phoneReg.test(mobile)) {
                    alert('手机号码格式不正确！')
                    return false;
                }
                if($('#sfzhao').is(":visible")){
                    if(sfzid == ''){
                        alert('身份证号不能为空！')
                        return false;
                    }
                    if(!/\d{17}[\d|x]|\d{15}/.test(sfzid)){
                        alert('身份证号不合法');
                        return false;
                    }
                }
                if($('#sfzphotos').is(":visible")){
                    if(ft_sfzimg == ''){
                        alert('身份证正面照不能为空');
                        return false;
                    }
                    if(bd_sfzimg == ''){
                        alert('身份证反面照不能为空');
                        return false;
                    }
                }
                if(cyimg1 == ''||cyimg2 == ''||cyimg3 == ''){
                    alert('请上传才艺照片');
                    return false;
                }

                // Loading状态
                _this.text('正在提交...').addClass('disabled');

            },
            success: function(info) {
                console.log(info);
                if (info.status != 1) {
                    alert(info.msg);
                    return;
                }
                alert(info.msg,'/wx/volunteer/index?navid=nav81');
            },
            complete: function() { // 请求完成时会调用
                // 还原状态
                _this.text('提交').removeClass('disabled');
            }
        },true);
    });
    //上传第一张才艺照片
    var cyimg1Uploader = new uploader(
        '#cyimg1',
        function (file, res) {
            if (res.code == 1) {
                $('#cyimg1').css({'background-image': 'url(' + res.data.preview_url + ')','background-size': '100% 100%'});
                $("input[name=cyimg1]").val(res.data.filepath);
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
            $('#cyimg1').css({'background-image':"url(/themes/simpleboot3/public/assets/whgcms/wx/images/load.gif)",'background-size': '20%,20%', 'background-repeat': 'no-repeat', 'background-position': 'center center'});
        }
    );
    //上传第二张才艺照片
    var cyimg2Uploader = new uploader(
        '#cyimg2',
        function (file, res) {
            if (res.code == 1) {
                $('#cyimg2').css({'background-image': 'url(' + res.data.preview_url + ')','background-size': '100% 100%'});
                $("input[name=cyimg2]").val(res.data.filepath);
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
            $('#cyimg2').css({'background-image':"url(/themes/simpleboot3/public/assets/whgcms/wx/images/load.gif)",'background-size': '20%,20%', 'background-repeat': 'no-repeat', 'background-position': 'center center'});
        }
    );
    //上传第三张才艺照片
    var cyimg3Uploader = new uploader(
        '#cyimg3',
        function (file, res) {
            if (res.code == 1) {
                $('#cyimg3').css({'background-image': 'url(' + res.data.preview_url + ')','background-size': '100% 100%'});
                $("input[name=cyimg3]").val(res.data.filepath);
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
            $('#cyimg3').css({'background-image':"url(/themes/simpleboot3/public/assets/whgcms/wx/images/load.gif)",'background-size': '20%,20%', 'background-repeat': 'no-repeat', 'background-position': 'center center'});
        }
    );
    //上传身份证正面照
    var ftSfzimgUploader = new uploader(
        '#ft_sfzimg',
        function (file, res) {
            if (res.code == 1) {
                $('#ft_sfzimg').css({'background-image': 'url(' + res.data.preview_url + ')','background-size': '100% 100%'});
                $("input[name=ft_sfzimg]").val(res.data.filepath);
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
                // multiple: false
                multiple: true,
            },
            timeout: 0,
            // threads:3,
            formData: {
                app: 'portal',
                filetype: 'image'
            },
            accept: {
                extensions: 'png,jpg'
            },
            swf: '/static/js/webuploader/Uploader.swf',
            chunked: true,//开启分片
            auto: auto,
            chunkSize: 2 * 1024 * 1024,// 单位B
            compress: false,
            server: "/user/asset/webuploader?_ajax=1",
            // 禁掉全局的拖拽功能。这样不会出现图片拖进页面的时候，把图片打开。
            disableGlobalDnd: true,
            fileNumLimit: 3,
            runtimeOrder: 'html5,flash',
            //fileSizeLimit: 200 * 1024 * 1024,    // 200 M
            fileSingleSizeLimit: 200 * 1024 * 1024      // 单位B
        });
        uploader.webuploder.on('uploadSuccess', successFun);
        uploader.webuploder.on('uploadError', errorFun);
        uploader.webuploder.on('fileQueued', fileQueuedFun);
        if (previewId != undefined) {
            uploader.webuploder.on('fileQueued', function (file) {
                uploader.webuploder.makeThumb(file, function (error, src) {   //webuploader方法
                    if (error) {
                        ('图片预览失败');
                    } else {
                        $(previewId).attr('src', src);
                    }
                });
            });
        }
    }
})