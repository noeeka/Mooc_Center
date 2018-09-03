$(function() {
    selectNav(20);
    title1();
    var title2 = $('.nav .zhutise').html();
    $('.nav-title').html(title2);
    
    layui.use('form', function() {
        var form = layui.form;

        //各种基于事件的操作，下面会有进一步介绍
    });
    $.ajax({
        url: '/api/aboutus/read',
        type: 'GET',
        dataType: 'json',
        data: {param1: 'value1'},
        success:function(res){
            if(res.status==1){
                console.log(res.data.abstract);
                $('.about').html(res.data.abstract);
            }
        }
    })
    var phoneReg = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
    
    $('.layui-btn').click(function() {
    var nickname=$('#name').val().trim();
    var mobile=$('#phone').val().trim();
    var sex = $("input[name=sex]:checked").val().trim();
    var qq=$('#qq').val().trim();
    var content=$('#content').val();
    console.log(mobile)
        $.ajax({
        url: '/api/feedback/save',
        type: 'GET',
        dataType: 'json',
        data: {name:nickname,mobile:mobile,sex:sex,qq:qq,content:content},
        beforeSend:function(){
            if($('#phone').val()==''){
                getdialog('手机号码不能为空!');
                return false;
            }else if(!phoneReg.test($('#phone').val().trim())){
                 getdialog('请输入正确的手机号！');
                 return false;
            }
            if($('#content').val()==''){
                getdialog('留言内容不能为空!');
                return false;
            }
        },
        success:function(res){
            if(res.status==1){
            getdialog('留言成功！')
                
            }
        }
    })
    });
    $.ajax({
        url: '/api/category/child?id=20&max_depth=1',
        type: 'GET',
        dataType: 'json',
        success:function(res){
                var html='';
            if(res.status==1){
                for(var i in res.data){
                    html+='<li>'+res.data[i].name+'</li>';
                }
               $('.tab').html(html);
               $('.tab li').eq(0).addClass('zhutise');
               $('.tab li').click(function() {
               $('.tab li').removeClass('zhutise');
               $('.main-right>div').addClass('hidden');
               var index = $(this).index();
               $(this).addClass('zhutise');
               $('.main-right>div').eq(index).removeClass('hidden');
               $('.program .title').html($(this).html());
             })
            }
        }
    })
})