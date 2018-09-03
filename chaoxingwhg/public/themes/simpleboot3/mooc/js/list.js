$(function() {
	var type1 = '';
	for(var i = 0; i < 50; i++) {
		type1 += '<li class="f-left"><a href="">人文历史</a></li>';
		$('.type1-list-b>ul').html(type1);
	}
	if($('.type1-list-b>ul li').length >= 20) {

	}
	$('.last').click(function() {
		for(var i = 0; i < 20; i++) {
			type1 += '<li class="f-left"><a href="">心理学</a></li>';
			$('.type1-list-b>ul').html(type1);
		}
	})
	/*选择*/
	$('.screen li').click(function() {
		$('.screen li').removeClass('active');
		$('.screen li').removeClass('backgroundcolor');
		$(this).addClass('active');
		$(this).addClass('backgroundcolor');
	})
	/*列表*/
	var list = '';
	for(var j = 0; j < 8; j++) {
		list += '<li class="f-left">';
		list += '<a href="">';
		list += '<img src="images/index1.jpg" alt="" />';
		list += '<div class="info clearfix">';
		list += '<h3 class="o1">人文视野中的生态学</h3>';
		list += '<div class="clearfix">';
		list += '<span class="f-left">观看：3784次</span>';
		list += '<span class="f-right fontcolor">8.0分</span>';
		list += '</div>';
		list += '</div>';
		list += '</a>';
		list += '</li>';
		$('.list').html(list);
	}
	window.onscroll = function() {
		//		var dheight = document.documentElement.scrollHeight;//文档高度
		var dheight = $(document).height();
		//		var sheight = document.body.scrollTop || document.documentElement.scrollTop;//滚动条的距离
		var sheight = $(window).scrollTop() || $(document).scrollTop();
		//		var wheight = document.documentElement.clientHeight;//屏幕高度
		var wheight = $(window).height();
		var cha = dheight - sheight - wheight;
		if(cha < 800) {
			for(var j = 0; j < 2; j++) {
				list += '<li class="f-left">';
				list += '<a href="">';
				list += '<img src="images/index1.jpg" alt="" />';
				list += '<div class="info clearfix">';
				list += '<h3 class="o1">人文视野中的生态学1</h3>';
				list += '<div class="clearfix">';
				list += '<span class="f-left">观看：3784次</span>';
				list += '<span class="f-right fontcolor">8.0分</span>';
				list += '</div>';
				list += '</div>';
				list += '</a>';
				list += '</li>';
			}
			$('.list').html(list);
		}
	}
	//type1展开收起
	$('.type1-zk').click(function(){
		if($(this).hasClass('zk')){
			$(this).removeClass('zk').children('span').text('展开');
			$(this).children('i').removeClass('layui-icon-up').addClass('layui-icon-down');
			$('.type1-list-b ul').removeClass('show-type1');
		}else{
			$(this).addClass('zk').children('span').text('收起');
			$(this).children('i').removeClass('layui-icon-down').addClass('layui-icon-up');
			$('.type1-list-b ul').addClass('show-type1');	
		}
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