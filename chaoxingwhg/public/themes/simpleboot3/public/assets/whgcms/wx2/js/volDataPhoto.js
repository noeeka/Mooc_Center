$(function () {
    var article_id = getUrl('id');
    var total_num = 60;
    var photos = [];
    var tpl = $('#photo-item').html();
    var template = Handlebars.compile(tpl);
    //初始化
    init();

    /**
     * 保存信息到服务器
     */
    function saveInfo(data) {
        var ret = false;
        request({
            url: '/api/user/modify_volun_profie',
            data: data,
            type: 'post',
            dataType: 'json',
            async: false,
            success: function (res) {
                ret = true;
            },
            error: function () {
                console.log('ajax error');
            }
        }, true);
        return ret;
    }

    /**
     *  初始化
     */
    function init() {
        //获取当前已上传图片数量
        request({
            url: '/api/user/volun_profile',
            type: 'post',
            dataType: 'json',
            success: function (res) {
                if (res.status == 1) {
                    //数据整理
                    if (res.data.volun_skill_imgs != '') {
                        photos = JSON.parse(res.data.volun_skill_imgs);
                    }
                    if (res.data.img == undefined) {
                        res.data.img = [];
                    }

                    //已上传图片显示，绑定修改图片方法
                    var num_limit = total_num - res.data.img.length;
                    for (var i in res.data.img) {
                        $('#addphoto').before(template({index: i, img: res.data.img[i]}));
                        buildReplaceUploader(i);
                    }

                    //不够9张则生成新增照片上传器。否则提示不能上传
                    if (num_limit > 0) {
                        buildAddUploader(num_limit);
                    } else {
                        $('#addphoto').click(function () {
                            alert('很抱歉，你目前不能上传照片');
                        });
                    }

                    //点击‘完成’事件
                    $('.closeBtn').click(function () {
                        var p1 = [];
                        for (var i in photos) {
                            if (photos[i] != null) {
                                p1[p1.length] = photos[i];
                            }
                        }
                        photos = p1;
                        saveInfo({photos: photos.join(',')});
                        history.back(-1);
                    });
                    //点击‘删除’事件
                    $('.photo').on('click', '.remove', function () {
                        var index = $(this).data('index');
                        photos[index] = null;
                        $('#photo-item-' + index).remove();
                    });
                } else {
                    noLogin(res.code, res.msg);
                }
            },
            error: function () {
                console.log('ajax error');
            }
        }, true);
    }

    /*
    * 照片替换上传器
    */
    function buildReplaceUploader(index) {
        var uploader = WebUploader.create({
            pick: {
                id: '#photo-item-' + index,
                label: false,
                multiple: false,
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
            fileNumLimit: 1,
            runtimeOrder: 'html5,flash',
            //fileSizeLimit: 200 * 1024 * 1024,    // 200 M
            fileSingleSizeLimit: 200 * 1024 * 1024      // 单位B
        });
        uploader.on('uploadSuccess', function (file, res) {
            if (res._raw != undefined && res._raw == '非法上传！') {
                alert('请重新登陆后重试！');
            } else {
                if (res.code == 1) {
                    $('#img-' + index).attr('src', res.data.url);
                    photos[index] = res.data.filepath;
                } else {
                    alert(res.msg);
                }
            }
        });
        uploader.on('uploadError', function (res) {
            console.log(res);
        });
        uploader.on('error', function (res, file) {
            if (res == 'Q_EXCEED_NUM_LIMIT') {
                alert('最多上传60张照片');
            }
            if (res == 'Q_TYPE_DENIED') {
                alert('部分文件类型错误');
            }
            if (res == 'Q_EXCEED_SIZE_LIMIT') {
                alert('文件过大');
            }
        });
    }

    /*
    * 照片新增上传器
    */
    function buildAddUploader(num_limit) {
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
        uploader.on('uploadSuccess', function (file, res) {
            if (res._raw != undefined && res._raw == '非法上传！') {
                alert('请重新登陆后重试！');
            } else {
                if (res.code == 1) {
                    $('#addphoto').before(template({index: photos.length, img: res.data.url}));
                    buildReplaceUploader(photos.length);
                    photos[photos.length] = res.data.filepath;
                } else {
                    alert(res.msg);
                }
            }
        });
        uploader.on('uploadError', function (res) {
            console.log(res);
        });
        uploader.on('error', function (res, file) {
            if (res == 'Q_EXCEED_NUM_LIMIT') {
                alert('最多上传60张照片');
            }
            if (res == 'Q_TYPE_DENIED') {
                alert('部分文件类型错误');
            }
            if (res == 'Q_EXCEED_SIZE_LIMIT') {
                alert('文件过大');
            }
        });
    }
});