/**
 * Created by Administrator on 2017/12/10.
 */

(function (w) {
    //服务器
    // var apiHost = 'http://www.dingxi.com';

    //配置项
    w.C = {};
    //域名
    // C.host = apiHost + '/v1/';
    C.host = '/v1/';

    //是否开启打印日志
    C.debug = true;
    //是否正在维护
    C.isWeihu = false;
    //手机验证正则
    C.phoneReg = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
    //邮箱验证正则
    C.emailReg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
    //安全码验证正则
    C.selfNumberReg = /^[0-9a-zA-Z]{8,16}$/;
    //发送验证码时间
    C.putCodeTime = 60;


    //var token='2017091814287384479dc2fcbc776c4e34a1d9f9ad2b297b9d';
    localStorage.setItem('token', '201710280151445545d498a6fd21d347d7889574756403fc4c');
    C.token = localStorage.getItem('token');

    //强制刷新当前页面
    C.version = Math.random();

    //接口
    C.interface = {
        //新闻列表
        news: 'article/news',
        //分类文章
        category: 'article/category',
        //分类28
        categoryte: 'article/category/cid/28',
        //读取用户信息
        readUser: 'user/read',
        //登录
        login: 'passport/login',
        //注册
        regist: 'passport/regist',
        //获取验证码
        mobileCode: 'code/mobile',
        //文章详情
        readArt: 'article/read',
        //更改用户信息
        updateUser: 'user/update',
        //渲染评论列表
        commentList: 'comment/index',
        //提交评论
        commentSave: 'comment/save',
        //期刊
        journal: 'journal/index',
        //视频
        video: 'video/index',
        //按点击量排序
        dianjiCount: 'article/pageview',
        //按点赞数排序
        likeCount: 'article/likecount',
        //非遗文章点赞
        artLike: 'article/like',
        //期刊点赞
        journalLike: 'journal/like',
        //培训视频点赞
        videoLike: 'video/like',
        //全站搜索
        search: 'site/search',
        //退出账号
        logout: 'passport/logout',
        //热门推荐
        recom: 'article/recom',
        //活动详情
        activityread: 'activity/read',
        //活动报名
        baoming: 'activity/baoming',
        //导航
        menuindex: 'menu/index',
        //全景
        panoramicindex: 'panoramic/index',
        //慕课
        mukeindex: 'muke/index',
        //反馈
        sitefeedback: 'site/feedback',
        //文娱活动
        activityindex: 'activity/index',
        //图片验证
        checkimg: 'site/checkImg',
        //短信验证码
        codecheck: 'code/check',
        //修改密码
        savePassword: 'user/savePassword',
        //文章收藏
        collect: 'article/collect',
        //文章总数
        count: 'article/count',
        //实名认证
        userauthread: 'user/auth_read',
        //用户更新
        userupdate: 'user/update',
        //账号信息收藏文章
        collectindex: 'collect/index',
        //期刊总数
        journalcount: 'journal/count',
        //实名认证
        realuser: 'user/auth_real_user',
        //点单
        diandan: 'diandan/index',
        //点单详情
        diandanread:'diandan/read',
        //点单
        diandandiandan:'diandan/diandan'


    };
    //组合接口地址
    for (k in C.interface) {
        C.interface[k] = C.host + C.interface[k];
    }


})(window);
