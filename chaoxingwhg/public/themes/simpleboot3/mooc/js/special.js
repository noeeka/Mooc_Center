// JavaScript Document

$(function(){
	//展开
	var introduceh = $('.introduce').text().length;
	if(introduceh>203){
		$('.introduce').append('<span class="tog-introduceh fontcolor" style=" position:absolute; right:0; bottom:-2px; cursor: pointer;">[展开]</span>');
	}
	
	$('.introduce').on('click','.tog-introduceh',function(){
		if($(this).hasClass('active')){
			$(this).removeClass('active').text('[展开]').css('bottom',-2+'px').parent('.introduce').addClass('o4');	
		}else{
			$(this).addClass('active').text('[收起]').css('bottom',-30+'px').parent('.introduce').removeClass('o4');	
		}
	});	
	/*分享显示隐藏*/
	$('.share').click(function() {
		if($(this).hasClass('active')){
			$('.bdsharebuttonbox').hide();
			$('.share').removeClass('active');
		}else{
			$('.bdsharebuttonbox').show();
			$('.share').addClass('active')
		}
	})
	/*列表*/
	var list = '';
	for(var j = 0; j < 8; j++) {
		list += '<li class="f-left">';
		list += '<a href="">';
		list += '<div>';
		list += '<img src="images/index1.jpg" alt="" />';
		list += '<div></div>';
		list += '</div>';
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
})

var vm = new Vue({
	el: '#app',
	data:{
		issc:false,
		sctext:'收藏'
	},
	created:function(){
		console.log(this.data)	
		//this.date = this.format(this.date)
	},
	methods:{
		format:function(date){ 
			var testDate = new Date(date);
			return testDate.format("yyyy年MM月dd日hh小时"); 
		},
		sc:function(){
			if(this.issc){
				this.issc = false;
				this.sctext = '收藏'
			}else{
				this.issc = true;
				this.sctext = '已收藏'
			}
		}
	},
	filters: {
	  format: function (date) {
			var testDate = new Date(date); 
			return testDate.format("yyyy年MM月dd日hh小时"); 
	  }
	}
});