// JavaScript Document
$(function(){
	//文本框焦点变色
	$('.class-edit-info').find('.ipt-border').focus(function(){
		$(this).addClass('border-active');	
	});
	$('.class-edit-info').find('.ipt-border').blur(function(){
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
			//if (cpLock){
//				if(this.value.length==500){
//					return layer.msg('字数不能超过500字');
//				}
//			}
				
		}
	})
	//上传
	layui.use('upload', function(){
	  var $ = layui.jquery
	  ,upload = layui.upload;
	  //普通图片上传
	  var uploadInst = upload.render({
		elem: '#upimg',
		url: '/upload/',
		//accept:'video',
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
	//模拟单选
	$('.test-radio').change(function(){
		$(this).parents('.layui-input-block').find('i').removeClass('test-active');
		
		if($(this).is(':checked')){
			$(this).prev('i').addClass('test-active');
		}
	});
	$('.date-radio').change(function(){
		$('.date-b').removeClass('active-date');
		if($(this).val() == '0'){
			$('.date-b').addClass('active-date');
		}
	});
	//时间选择
	layui.use('laydate', function(){
    	var laydate = layui.laydate;
		//日期时间选择器
		laydate.render({
			elem: '#begindate',
			type: 'datetime'
		});
		laydate.render({
			elem: '#overdate',
			type: 'datetime'
		});
	});
})

var vm = new Vue({
	el: '#app',
	data:{
		
	},
	created:function(){
		console.log(this.data)	
		//this.date = this.format(this.date)
	},
	methods:{
		format:function(date){ 
			var testDate = new Date(date);
			return testDate.format("yyyy年MM月dd日hh小时"); 
		}
	},
	filters: {
	  format: function (date) {
			var testDate = new Date(date); 
			return testDate.format("yyyy年MM月dd日hh小时"); 
	  }
	}
});