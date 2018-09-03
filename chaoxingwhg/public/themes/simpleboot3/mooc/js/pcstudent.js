// JavaScript Document
$(function(){
	//文本框焦点变色
	$('.main').find('.ipt-border').focus(function(){
		$(this).addClass('border-active');	
	});
	$('.main').find('.ipt-border').blur(function(){
		$(this).removeClass('border-active');	
	});
	//昵称显示提示
	$('.name-ipt').focus(function(){
		$('.tip').fadeIn();	
	});
	$('.name-ipt').blur(function(){
		$('.tip').fadeOut();	
	});
	//tab切换
	layui.use('element', function(){
	  var element = layui.element;
	});
	//slick轮播
	var slickon = false;
	$('.layui-tab-title li').click(function(){
		if($(this).text() == '我的课程' && slickon == false){
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
	$('.study-slick').on('beforeChange', function(event, slick, currentSlide, nextSlide){
	  console.log(nextSlide);
	});
	
	//笔记
	$('.notes-list li .look').click(function(){
		$(this).parent().siblings().removeClass('o2');
		$(this).hide();
	})
	//上传
	layui.use('upload', function(){
	  var $ = layui.jquery
	  ,upload = layui.upload;
	  //普通图片上传
	  var uploadInst = upload.render({
		elem: '#upimg',
		url: 'http://mooc.com/v1/user/edit',
		before: function(obj){
		  //预读本地文件示例，不支持ie8
		  obj.preview(function(index, file, result){
			$('#upload-img').attr('src', result); //图片链接（base64）
		  });
		},
		done: function(res){
		  //如果上传失败
		  if(res.code > 0){
			return layer.msg('上传失败');
		  }
		  //上传成功
		},
		error: function(){
		  //演示失败状态，并实现重传
		  var demoText = $('#demoText');
		  demoText.html('<span style="color: #FF5722;">上传失败</span>');//<a class="layui-btn layui-btn-xs demo-reload">重试</a>
		  demoText.find('.demo-reload').on('click', function(){
			uploadInst.upload();
		  });
		}
	  });
	});	
	//tr位置
	trpos();
	function trpos(){
		$('.qbkc-table tr').each(function(index, element) {
			$(this).css('transform','translateY(-'+index*10+'px)')
		});
		$('.wdsc-table tr').each(function(index, element) {
			$(this).css('transform','translateY(-'+index*10+'px)')
		});
	}
	//性别单选
	$('.radio-sex-b input').change(function(){
		$(this).parents('.radio-sex-b').find('i').removeClass('test-active');
		if($(this).is(':checked')){
			console.log($(this).val())
			$(this).prev('i').addClass('test-active');
		}	
	
	});
	//收藏
	$('.wdsc-btn').click(function() {
		var aurl = 'http://mooc.com/v1/my/myCollect/';
		var sxdata = vm.scdata;
		getajax(aurl,sxdata);
		scrollajax(aurl,sxdata);
	});
})


//滚动加载
function scrollajax(aurl,sxdata){
	var curpage = 1;
	$(window).scroll(function(){
	 var scrollTop = $(this).scrollTop();    //滚动条距离顶部的高度
	 var scrollHeight = $(document).height();   //当前页面的总高度
	 var clientHeight = $(this).height();    //当前可视的页面高度
	 if(scrollTop + clientHeight > scrollHeight || scrollTop + clientHeight == scrollHeight){   //距离顶部+当前高度 >=文档总高度 即代表滑动到底部 
	     //滚动条到达底部
	     curpage++;
	     $.ajax({
	        url:aurl,
	        type:'get',
	        data:{
	                user_type:usertype,
	                page:curpage,
	                len:5
	            },
	        dataType:'json',
	        success: function(res){
	        	for(var i in res.data){
	        		res.data[i].create_time = vm.agodate(res.data[i].create_time);
	        	}
	        	sxdata.push(res.data);
	        	console.log(res);
	        },
	        error: function(){

	        }
	    })
	 }
	});
}
//ajax加载
function getajax(aurl,sxdata){
	$.ajax({
		url:aurl,
        type:'get',
        data:{
                user_type:usertype,
                page:1,
	            len:5
            },
        dataType:'json',
        success: function(res){
        	for(var i in res.data){
        		res.data[i].create_time = vm.agodate(res.data[i].create_time);
        	}
        	sxdata = res.data;
        	//console.log(vm.twdata);
        }
	})
}


var vm = new Vue({
	el: '#app',
	data:{
		userinfo:[],
		twdata:[],
		hddata:[],
		bjdata:[],
		scdata:[]
	},
	created:function(){
		//获取用户信息
		$.ajax({
			url:'http://mooc.com/v1/my/index/',
	        type:'get',
	        data:{
	                user_type:usertype
	            },
	        dataType:'json',
	        success: function(res){
	        	if(res.data.sex == 0){
	        		res.data.sex = '保密'
	        	}else if(res.data.sex == 1){
					res.data.sex = '男'
	        	}else if(res.data.sex == 2){
					res.data.sex = '女'
	        	}
	        	vm.userinfo = res.data;
	        	//console.log(res);
	        }
		})
	},
	methods:{
		agodate:function(ctime){
			var mydate = new Date();
			var nmonth = parseInt(mydate.getMonth())+1;
			var nowdate = mydate.getFullYear() + '-' + nmonth + '-' +mydate.getDate();
			return new Date(nowdate).ago(ctime);
		}
	},
	filters: {
	  
	}
});