
var myDate = new Date();
var h_year=myDate.getFullYear(); //获取完整的年份
var h_mongth=myDate.getMonth()+1;
$('.ym').html(h_year+'年'+h_mongth+'月');

var weeks = ['日', '一', '二', '三', '四', '五', '六'];

get_venue();

var today = new Date();

get_book(today.getDate(),myDate.getMonth());
if($(".datelist").children('li').eq(0).hasClass('datelock')){
    $(".yuyuebtn").addClass('lock');
    $(".yuyuebtn").css('background','#999');
    $(".yuyuebtn").html("不可预约");
}

//日期切换
$(".datelist li").click(function () {
    if(!$(this).hasClass('datelock')){
        $(".datelist li").removeClass('active');
        $(this).addClass('active');
        var date=$(this).children('p').eq(0).html();
        var month =$(this).data('month');
        console.log(date)
        get_book(date,month);
    }
})

//获取场馆预约信息
function get_venue() {
    $.ajax({
        url:'/api/room/read/id/'+id,
        dataType:'json',
        type:'post',
        async:false,
        success:function (result) {
            if(result.status ==1){
                var data=result.data;
                //当月日期
                html_date(data.preset_time,data.custom_preset_time);
            }
        }
    })
}

//获取场馆预约信息
function get_book(date,month) {
    request({
        url:'/api/room/wx_read_book',
        dataType:'json',
        type:'post',
        data:{'room_id':id,'date':date},
        async:false,
        success:function (result) {
            if(result.status ==1){
                var start_am=result.data.shijianduan.open_start_time_am;
                var end_am=result.data.shijianduan.open_end_time_am;
                var start_pm=result.data.shijianduan.open_start_time_pm;
                var end_pm=result.data.shijianduan.open_end_time_pm;
                var yiyuyue=result.data.yiyuyue;
                var other_yiyuyue=result.data.other_yiyuyue;
                var amlist_html=html_time(start_am,end_am,date,yiyuyue,other_yiyuyue,month);
                var pmlist_html=html_time(start_pm,end_pm,date,yiyuyue,other_yiyuyue,month);
                $('.am').html(amlist_html);
                $('.pm').html(pmlist_html);

                $(".yuyuebtn").click(function () {
                    if(!$(this).hasClass('lock')){
                        var start_time=$(this).data('time');
                        var _this=$(this);
                        var vunuetime = $(this).parent().text().slice(0,5);
                         vunuetime +=' ~ '+$(this).parent().next().text().slice(0,5);
                         var content = '<p style="font-size: 0.18rem;margin-bottom: 5px;">您确定预约时间</p>'+vunuetime
                        // console.log(vunuetime);
                        // book(start_time,_this);
                        queren(book,start_time,_this,content);
                    }
                })
            }
        }
    },true);
}

//日期表
function html_date(preset_time,customset_time) {
    var start=new Date();
    for (var i = 0; i < 28; i++) {
        var head = '';
        if (i == 0) {
            start.setDate(start.getDate());
        } else {
            start.setDate(start.getDate() + 1);
        }
        var month =start.getMonth();
        var week = start.getDay();
        var day = start.getDate();
        if(preset_time==0){
            //每天
            head +='<li data-month="'+month+'">';
            head += '<p class="day">';
            head += day;
            head += '</p>';
            head += '<p class="week">';
            head += weeks[week];
            head += '</p>';
            head += '<span class="dian">';
            head += '</span>';
            head += '</li>';
        }else if(preset_time==1){
            //周一到周五
            if(week==0||week==6){
                head +='<li class="datelock"  data-month="'+month+'">';
                head += '<p class="day">';
                head += day;
                head += '</p>';
                head += '<p class="week">';
                head +=  weeks[week];
                head += '</p>';
                head += '<span class="dian" style="background:#ccc ">';
                head += '</span>';
                head += '</li>';
            }else{
                head +='<li  data-month="'+month+'">';
                head += '<p class="day">';
                head += day;
                head += '</p>';
                head += '<p class="week">';
                head +=  weeks[week]  ;
                head += '</p>';
                head += '<span class="dian">';
                head += '</span>';
                head += '</li>';
            }
        }else{
            //自定义
            if(customset_time.length>0){
                customset_time_arr=customset_time.split(',');
                if(customset_time_arr.indexOf(week.toString() ) ==-1){
                    head +='<li class="datelock" data-month="'+month+'">';
                    head += '<p class="day">';
                    head += day;
                    head += '</p>';
                    head += '<p class="week">';
                    head += weeks[week];
                    head += '</p>';
                    head += '<span class="dian" style="background:#ccc ">';
                    head += '</span>';
                    head += '</li>';
                }else{
                    head +='<li data-month="'+month+'">';
                    head += '<p class="day">';
                    head += day;
                    head += '</p>';
                    head += '<p class="week">';
                    head += weeks[week];
                    head += '</p>';
                    head += '<span class="dian" >';
                    head += '</span>';
                    head += '</li>';
                }
            }
        }
        $('.datelist').append(head);
    }
    $('.datelist li:first-child').addClass('active');
}

//时间段html
function html_time(start,end,day,yiyuyue,other_yiyuyue,month) {

    var num = end - start; //小时数
    var list='';
    var date=new Date();
    if(typeof(day)=='undefined'){
        var day=date.getDate();
    }else{
       var day=day;
    }

    for(var i=0;i<=num;i++){
        date.setMonth(month);
        date.setDate(day);
        date.setHours(start + i,0,0);
        var t_timestamp= Date.parse(date)/1000;
        var time= start + i;
        var time=time <10?"0" +time:time;

        if(yiyuyue.indexOf(t_timestamp) !=-1){
            //获取当前时间戳
            var d = new Date();
            var now = d.getTime()/1000;
            console.log(now)

            //获取时间段时间戳
            var t =new Date();
            t.setDate(day);
            t.setHours(start + i,0,0);
            var t_timestamp=t.getTime();

            list += '<li>';
            list += time +':00';
            if(i <=num-1) {
                list += '<span>~</span>';
                list += '<div class="yuyuebtn lock"  data-time='+t_timestamp+' style="background:#999">已预约</div>';
            }
            list += '</li>';

        }else{
            //获取当前时间戳
            var d = new Date();
            var now = d.getTime()/1000;

            //只能预约一小时以后的时间段
            if(now > t_timestamp){
                list += '<li>';
                list += time +':00';
                if(i <=num-1) {
                    list += '<span>~</span>';
                    list += '<div class="yuyuebtn lock"  data-time='+t_timestamp+' style="background:#999">不可预约</div>';
                }
                list += '</li>';
            }else{
                if(other_yiyuyue.indexOf(t_timestamp) !=-1){
                    list += '<li>';
                    list += time +':00';
                    if(i <=num-1) {
                        list += '<span>~</span>';
                        list += '<div class="yuyuebtn lock" data-time='+t_timestamp+' style="background:#999">被预约</div>';
                    }
                    list += '</li>';
                }else{
                    list += '<li>';
                    list += time +':00';
                    if(i <=num-1) {
                        list += '<span>~</span>';
                        list += '<div class="yuyuebtn" data-time='+t_timestamp+' >预约</div>';
                    }
                    list += '</li>';
                }
            }
        }

    }
    return list;
}

//场馆预约
function  book(start_time,_this){
    request({
        url:'/api/room/book',
        dataType:'json',
        type:'post',
        data:{'room_id':id,start_time:start_time},
        async:false,
        success:function (result) {
            if(result.status ==1){
                // alert(result.msg);
                // alert('<p style="font-size: 0.18rem;margin-bottom: 5px;">您确定预约时间</p>'+vunuetime);

                _this.html("已预约");
                _this.css("background","#999");
                _this.addClass('lock');
            }else{
                var regex = /^1000[4-9]$/;
                if(regex.test(result.code)){
                    alert(result.msg,'/wx/login/login');
                }else{
                    alert(result.msg);
                }
            }
        }
    },true);
}

