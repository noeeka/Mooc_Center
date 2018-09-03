$(function() {
	layui.use('rate', function() {
		var rate = layui.rate;
		var ins1 = rate.render({
			elem: '#class', //绑定元素
			length: 5,
			value: 2.5,
			half: true,
			readonly: true,
			theme: '#EC6D3A',
			text: true,
			setText: function(value) {
				var arrs = {
					// '2': '极差',
					// '4': '差',
					// '6': '中等',
					// '8': '好',
					// '10': '非常好'
				};
				this.span.text(arrs[value] || (2 * value + "分"));
			}
		});
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
		//$('.share span').addClass('fontcolor');
	})
	/*tab切换*/
	$('.tab li').click(function() {
		var index = $(this).index();
		$('.tab li').removeClass('active1');
		$('.tab-content').hide();
		$(this).addClass('active1');
		$('.tab-content').eq(index).show();
	})
	/*课程目录滑过效果*/
	$('.directory .zj').mouseenter(function() {
		$('.directory dd').removeClass('active2').removeClass('active2-2');
		$('.directory dd').find('button').hide();
		$(this).addClass('active2');
		$(this).find('button').show();
	})
	$('.directory .ks').mouseenter(function() {
		$('.directory dd').removeClass('active2').removeClass('active2-2');
		$('.directory dd').find('button').hide();
		$(this).addClass('active2-2');
		$(this).find('button').show();
	})
	$('.directory dd').mouseleave(function() {
		$('.directory dd').removeClass('active2').removeClass('active2-2');
		$('.directory dd').find('button').hide();
	})
	/*笔记*/
	$('.notes-list li .look').click(function() {
		$(this).parent().siblings().removeClass('o2');
		$(this).hide();
	})
	/*问答*/
	$('.tab2 li').click(function() {
		$('.tab2 li').removeClass('active3');
		$(this).addClass('active3');
	})
	$('ul.qanda-list .list-content').each(function(index, element) {
        var tlength = $(this).text().length;
		if(tlength>90){
			$(this).append('<span class="tog-introduceh fontcolor" style="background:#fff; position:absolute; right:0; bottom:-2px; cursor: pointer; white-space:nowrap;">[展开]</span>');
		}
		
		$('ul.qanda-list .list-content').on('click','.tog-introduceh',function(){
			if($(this).hasClass('active')){
				$(this).removeClass('active').text('[展开]').css({'bottom':-2+'px','right':0,'position':'absolute'}).parent('.list-content').addClass('o2');	
			}else{
				$(this).addClass('active').text('[收起]').css({'bottom':'auto','position':'static','right':'auto'}).parent('.list-content').removeClass('o2');	
			}
		});
    });
	/*评价*/
	layui.use('rate', function() {
		var rate = layui.rate;
		var ins1 = rate.render({
			elem: '#evaluation', //绑定元素
			length: 5,
			value: 2.5,
			half: true,
			readonly: true,
			theme: '#EC6D3A',
			text: true,
			setText: function(value) {
				var arrs = {
					// '2': '极差',
					// '4': '差',
					// '6': '中等',
					// '8': '好',
					// '10': '非常好'
				};
				this.span.text(arrs[value] || (2 * value + "分"));
			}
		});
	});
	//回复弹窗
	$('.qanda-list li .button').click(function() {
		layer.open({
			type: 1,
			content: $('.evaluation-reply'),
			closeBtn: 1,
			area: ['600px', '370px'],
			end: function() {
				layer.closeAll();
				$('.evaluation-reply').hide();
			}
		});

	})
	$('.reply').click(function(){
		layer.open({
			type: 1,
			content: $('.evaluation-reply'),
			closeBtn: 1,
			area: ['600px', '370px'],
			end: function() {
				layer.closeAll();
				$('.evaluation-reply').hide();
			}
		});
	});
	/*main-right*/
	var shoukelen = $('.shouke dd').length;
	if(shoukelen > 3) {
		$('.slidedown').show();
		$('.slidedown').click(function() {
			if($('.slidedown>img').attr('src') == 'images/slide.png') {
				$('.shouke').css('height', 'auto');
				$('.slidedown>img').attr('src', 'images/slide2.png');
				console.log($('.slidedown>img').attr('src'))
			} else {
				$('.shouke').css('height', '336px');
				$('.slidedown>img').attr('src', 'images/slide.png');
			}
		})

	}
	//展开回复
	$('.tab li').click(function(){
		if($(this).index() == 3){
			hf()
		}	
	});
	function hf(){
		$('.bh-list').each(function(index, element) {
            var lih = $(this).find('li').eq(0).height();
			$(this).height(lih+30);
        });
	}
	$('.zksq-btn').click(function(){
		if($(this).prev('.q-list').hasClass('bh-list')){
			$(this).find('span').text('收起');
			$(this).find('img').attr('src','images/slide2.png');
			$(this).prev('.q-list').removeClass('bh-list').css('height','auto');
		}else{
			$(this).find('span').text('展开全部');
			$(this).find('img').attr('src','images/slide.png');
			$(this).prev('.q-list').addClass('bh-list');
			hf();
		}
	});
	//$('.aa').click(function(){
//		vm.sctext = "aa"
//	});
	//评价展开
	$('.tab li').click(function(){
		if($(this).index() == 4){
			pjgd()
		}	
	});
	$('.evaluation-list .slide').click(function(){
		if($(this).hasClass('pjzk')){
			$(this).removeClass('pjzk').prev('p').removeClass('o2');
			$(this).children('span').text('收起');
			$(this).children('img').attr('src','images/slide2.png')
			
		}else{
			$(this).addClass('pjzk').prev('p').addClass('o2');
			$(this).children('span').text('展开全部');
			$(this).children('img').attr('src','images/slide.png')	
		}
	});
	function pjgd(){
		$('.pj-li').each(function(index, element) {
			if($(this).find('p').text().length > 84){
				$(this).find('.slide').show();
			}
		});	
	};
	
})

var vm = new Vue({
	el: '#app',
	data:{
		issc:false,
		sctext:'收藏',
		bm:'报名'
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
		},
		bmfun:function(){
			this.bm = '已报名'	
		}
	},
	filters: {
	  format: function (date) {
			var testDate = new Date(date); 
			return testDate.format("yyyy年MM月dd日hh小时"); 
	  }
	}
});