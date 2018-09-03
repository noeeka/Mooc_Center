// JavaScript Document
$(function(){
	//文本框焦点变色
	$('.main').find('.ipt-border').focus(function(){
		$(this).addClass('border-active');	
	});
	$('.main').find('.ipt-border').blur(function(){
		$(this).removeClass('border-active');	
	});
	//限制文本框
	var cpLock = true;
	$('.textarea').on({
		compositionstart: function () {//中文输入开始
			cpLock = false;
		},
		compositionend: function () {//中文输入结束
			cpLock = true;
		},
		input: function () {//input框中的值发生变化
		$('.fontnum').html('<span>'+this.value.length+'/500</span>');
		}
	})
	//展开
	var introduceh = $('.introduce').text().length;
	if(introduceh>243){
		$('.introduce').append('<span class="tog-introduceh fontcolor" style="background:#EFF3F6; position:absolute; right:0; bottom:-2px; cursor: pointer; white-space:nowrap;">[展开]</span>');
	}
	
	$('.introduce').on('click','.tog-introduceh',function(){
		if($(this).hasClass('active')){
			$(this).removeClass('active').text('[展开]').css({'bottom':-2+'px','right':0,'position':'absolute'}).parent('.introduce').addClass('o3');	
		}else{
			$(this).addClass('active').text('[收起]').css({'bottom':'auto','position':'static','right':'auto'}).parent('.introduce').removeClass('o3');	
		}
	});
	//tab切换
	layui.use('element', function(){
	  var element = layui.element;
	});
	//日期选择
	layui.use('laydate', function(){
		var laydate = layui.laydate;
		laydate.render({
			elem: '#test6',
			range: true,
			done: function(value, date, endDate){
			    console.log(value);
			    //console.log(date);
			    if(value == ''){
					sqtj(usertype,'','')
			    }else{
			    	value.split(' - ');
				    var startDate = value.split(' - ')[0];
				    var endDate = value.split(' - ')[1];
				    sqtj(usertype,startDate,endDate);
			    }
			    

			    //console.log(endDate);
			}
		});
	});
	//slick轮播
	var slickon = false;
	$('.layui-tab-title li').click(function(){
		if($(this).text() == '我的课堂' && slickon == false){
			if(slickon){
				$('.study-slick').slick('unslick');
			};
			$('.study-slick').slick({
			  infinite: false,
			  slidesToShow: 5,
			  slidesToScroll: 1
			});
			slickon = true;	
		}
	});
	//轮播期限
	function selectval(){
		var lbdate = $('.select-date').val();
		$('.show-date').text(lbdate);
	}
	selectval();
	$('.select-date').change(function(){
		selectval();
		//$('.study-slick').slick('unslick');
	});
	//tr位置
	trpos();
	function trpos(){
		$('.all-class-list tr').each(function(index, element) {
			$(this).css('transform','translateY(-'+index*10+'px)')
		});
		$('.all-class-list tr').each(function(index, element) {
			$(this).css('transform','translateY(-'+index*10+'px)')
		});
	}
})


var vm = new Vue({
	el: '#app',
	data:{
		fwldata:[],
		kcfbdata:[],
		dqfbdata:[],
		xxjddata:[],
		studentsexnone:'',
		studentsexboy:'',
		studentsexgirl:''
	},
	created:function(){
		//console.log(this.data)	
		//this.date = this.format(this.date)
	},
	methods:{
		format:function(date){ 
			var testDate = new Date(date);
			return testDate.format("yyyy-MM-dd"); 
		}
	},
	computed:{

	},
	filters: {
	  format: function (date) {
			var testDate = new Date(date); 
			return testDate.format("yyyy年MM月dd日hh小时"); 
	  }
	}
});

function tjxr(){
	//访问量统计
var fwlChart = echarts.init(document.getElementById('fwl'));
fwl(vm.fwldata);
var kcname = $('.fwl-kc').val();
showclass(kcname);
$('.fwl-kc').change(function(){
	showclass($(this).val())
})
function showclass(xzname){
	if(xzname == '全部'){
		fwl(vm.fwldata);
	}else{
		var kcxzdata = vm.fwldata[1].map(function (item) {
			if(item.name == xzname){
				return item
			} 
        })
		var fwldata_kc = [];
		fwldata_kc.push(vm.fwldata[0]);
		fwldata_kc.push(kcxzdata);
		fwl(fwldata_kc);
	}	
}
$('.fwl-date').change(function(event) {
	console.log($(this).val())
});
function fwl(data) {
    fwlChart.setOption(option = {
		grid:{
			left:'5%',
			right:'5%'	
		},
		tooltip: {
			trigger: 'axis'
		},
        xAxis: {
            data: data[0]
        },
        yAxis: [
            {
				name: '访问次数',
				type: 'value'
			}
        ],
        //dataZoom: [{
            //startValue: '2010-06-01',
            //endValue:'2010-08-01'
        //}],
        series:data[1]
    },
	{notMerge:true});
};
//学习进度
var xxjdChart = echarts.init(document.getElementById('xxjd'));
xxjd(vm.xxjddata);
$('.xxjd-kc').change(function(){
	var xxjdval = $(this).val();
	console.log(xxjdval);
	if(xxjdval == '全部'){
		xxjd(vm.xxjddata);
	}else{
		var xxxzdata = vm.xxjddata[1].map(function (item) {
			if(item.name == xxjdval){
				return item
			} 
        })
		var xxjddata_kc = [];
		xxjddata_kc.push(vm.xxjddata[0]);
		xxjddata_kc.push(xxxzdata);
		xxjd(xxjddata_kc);
	}
})

function xxjd(data) {
    xxjdChart.setOption(option = {
		grid:{
			left:'5%',
			right:'5%'	
		},
		tooltip: {
			trigger: 'axis'
		},
        xAxis: {
            data: data[0]
        },
        yAxis: [
			{
				name: '学生个数',
				type: 'value'
			},
        ],
        //dataZoom: [{
            //startValue: '2010-06-01',
            //endValue:'2010-08-01'
        //}],
        series:data[1]
    },
	{notMerge:true});
};
//学生分布
var xsfbChart = echarts.init(document.getElementById('xsfb'));
xsfb(vm.kcfbdata);
function xsfb(data) {
    xsfbChart.setOption(option = {
		tooltip: {
			trigger: 'axis'
		},
        tooltip : {
			trigger: 'item',
			formatter: "{a} <br/>{b} : {c} ({d}%)"
		},
		legend: {
			type: 'scroll',
			orient: 'vertical',
			right: 100,
			data:data.map(function (item) {
                return item.name;
            })
		},
        //dataZoom: [{
            //startValue: '2010-06-01',
            //endValue:'2010-08-01'
        //}],
        series:[
			{
				type: 'pie',
				radius : '80%',
				center: ['35%', '50%'],
				name:'学生分布',
				data:data
			}
		]
    },
	{notMerge:true});
};
//地区人数
var dqrsChart = echarts.init(document.getElementById('dqrs'));
dqrs(vm.dqfbdata);
function dqrs(data) {
    dqrsChart.setOption(option = {
		 title : {
			subtext: '地区人数',
			x:'left'
		},
		tooltip: {
			trigger: 'axis'
		},
        tooltip : {
			trigger: 'item',
			formatter: "{a} <br/>{b} : {c} ({d}%)"
		},
		legend: {
			type: 'scroll',
			orient: 'vertical',
			right: 'right',
			top:20,
			data:data.map(function (item) {
                return item.name;
            })
		},
        //dataZoom: [{
            //startValue: '2010-06-01',
            //endValue:'2010-08-01'
        //}],
        series:[
			{
				type: 'pie',
				radius: ['60%', '80%'],
				center:['45%','50%'],
				name:'地区人数',
				data:data
			}
		]
    },
	{notMerge:true});
};
//半圆进度条
var w=$(".kchp").width();
var option={
	percent:80 ,
	w:w*0.6
}
$("#kchp-c").audios2(option);

var w=$(".xsbl").width();
var option={
	percent:50 ,
	w:w*0.6
}
$("#xsbl-c").audios2(option);	
}
$('.kctj').click(function(){
	sqtj(usertype,'','');
});
function sqtj(type,startdate,enddate){
	$.ajax({
        url:'http://mooc.com/v1/chen/getStatisticsDataByID',
        type:'get',
        data:{
                user_type:type,
                startDate:startdate,
                endDate:enddate
            },
        dataType:'json',
        success: function(res){
        	//性别
        	vm.studentsexnone = res.data.student_sex_none;
        	vm.studentsexboy = res.data.student_sex_boy;
        	vm.studentsexgirl = res.data.student_sex_girl;
        	//访问量统计
        	var tjdate = res.data.course_visit_num[0];
        	for(var i in tjdate){
        		tjdate[i] = vm.format(tjdate[i])
        	}
        	vm.fwldata.push(tjdate);
        	vm.fwldata.push(res.data.course_visit_num[1]);
        	//学生-课程分布图
        	vm.kcfbdata = res.data.student_course_pie;
        	//学生-课程分布图
        	vm.dqfbdata = res.data.pie_student_area;
        	//渲染统计
        	console.log(res);
            setTimeout(tjxr,100);	
        },
        error: function(){
            console.log(1)  
        }
    })
}
//学习进度统计
$.ajax({
	url: 'http://mooc.com/v1/chen/getLearnSchedule',
	type: 'get',
	dataType: 'json',
	success:function(res){
		for(var i in res.data[0]){
			res.data[0][i] = res.data[0][i]*100 +'%';
		}
		vm.xxjddata.push(res.data[0]);
		vm.xxjddata.push(res.data[1]);
		//setTimeout(tjxr,0);	
	}
})
//个人信息
$.ajax({
	url: 'http://mooc.com/v1/my/index/',
	type: 'get',
	data:{'user_type':usertype},
	dataType: 'json',
	success:function(res){
		console.log(res);

	}
})
$(function(){
	//更新日期
	$('.gxrq-btn').click(function(){
		//alert(1)
		if($(this).hasClass('active')){
			$(this).removeClass('active');
			//$(this).children('img').attr('src','images/rq.png');
			//$.ajax();
		}else{
			$(this).addClass('active');
			//$(this).children('img').attr('src','images/rqon.png');
		}
	});
	//性别单选
	$('.radio-sex-b input').change(function(){
		$(this).parents('.radio-sex-b').find('i').removeClass('test-active');
		if($(this).is(':checked')){
			console.log($(this).val())
			$(this).prev('i').addClass('test-active');
		}	
	
	});
})
