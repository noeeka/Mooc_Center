// JavaScript Document

$(function(){
	//弹出左侧目录
	$('.zj-btn').click(function(){
		$('.ml-b').toggleClass('active-ml-b');
		$(this).toggleClass('active');
	});	
	//显示右侧信息
	$('.right-side-btn').click(function(){
		$('.right-side-b').show();	
		$(this).hide();
	});
	$('.close-ritht-side').click(function(){
		$('.right-side-b').hide();	
		$('.right-side-btn').show();
	});
	//展开
	var introduceh = $('.introduce').height();
	if(introduceh>63 || introduceh == 63){
		$('.introduce').append('<span class="tog-introduceh fontcolor" style="background:rgba(0,0,0,0.8); position:absolute; right:0; bottom:-2px; cursor: pointer; white-space:nowrap;">[展开]</span>');
	}
	
	$('.introduce').on('click','.tog-introduceh',function(){
		if($(this).hasClass('active')){
			$(this).removeClass('active').text('[展开]').css({'bottom':-2+'px','right':0,'position':'absolute'}).parent('.introduce').addClass('o3');	
		}else{
			$(this).addClass('active').text('[收起]').css({'bottom':'auto','position':'static','right':'auto'}).parent('.introduce').removeClass('o3');	
		}
	});
	//评论
	$('.pl-btn').click(function(){
		layer.open({
			type: 1,
			content: $('#pl-pop'),
			closeBtn: 1,
			area: ['600px', '370px'],
			end: function() {
				layer.closeAll();
				$('#pl-pop').hide();
			}
		});
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
	$('.reply').click(function(){
		layer.open({
			type: 1,
			content: $('#hf-pop'),
			closeBtn: 1,
			area: ['600px', '370px'],
			end: function() {
				layer.closeAll();
				$('.evaluation-reply').hide();
			}
		});
	});
	$('.button').click(function(){
		layer.open({
			type: 1,
			content: $('#evaluation-reply'),
			closeBtn: 1,
			area: ['600px', '370px'],
			end: function() {
				layer.closeAll();
				$('.evaluation-reply').hide();
			}
		});
	});
	
	/*tab切换*/
	$('.tab li').click(function() {
		var index = $(this).index();
		$('.tab li').removeClass('active1');
		$('.tab-content').hide();
		$(this).addClass('active1');
		$('.tab-content').eq(index).show();
	})
	//笔记问答
	$('.close-twbj').click(function(){
		$('.twbj-b').hide();
	});
	$('.wd-btn').click(function(){
		$('.tw-b').show();
		$('.bj-b').hide();
	});
	$('.bj-btn').click(function(){
		$('.bj-b').show();
		$('.tw-b').hide();
	});
	//实例化编辑器问题
    var uewt = UE.getEditor('editor_wt',{
		toolbars: [
			['undo', 'redo', '|','bold', 'italic', 'underline', '|', 'forecolor', 'insertorderedlist', 'insertunorderedlist', 'cleardoc', '|', '|','fontfamily', 'fontsize', '|','justifyleft', 'justifycenter', 'justifyright', 'justifyjustify']
		]
	});
	//实例化编辑器
    var ue = UE.getEditor('editor_bj',{
		toolbars: [
			['undo', 'redo', '|','bold', 'italic', 'underline', '|', 'forecolor', 'insertorderedlist', 'insertunorderedlist', 'cleardoc', '|', '|','fontfamily', 'fontsize', '|','justifyleft', 'justifycenter', 'justifyright', 'justifyjustify']
		]
	});
	//问题提交
	$('.wt-sub-btn').click(function(){
		if(ue.getContentTxt() == ''){
			layer.alert('编辑文本不能为空');
		}else{
			console.log(selectnodeid);		
		}
	});
	//笔记提交
	$('.bj-sub-btn').click(function(){
		if(ue.getContentTxt() == ''){
			layer.alert('编辑文本不能为空');
		}else{
			console.log(selectnodeid);		
		}
	});
	//视频
	var myPlayer = videojs('my-video');
		videojs("my-video").ready(function(){
		var myPlayer = this;
		myPlayer.play();
	});
	//展开回复
	$('.tab li').click(function(){
		if($(this).index() == 1){
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
	//评价展开
	$('.bottom-tab li').click(function(){
		if($(this).index() == 2){
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
	
	/*评价*/
	layui.use('rate', function() {
		var rate = layui.rate;
		var ins1 = rate.render({
			elem: '#rate-pj', //绑定元素
			length: 5,
			value: 5,
			//half: true,
			//readonly: true,
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
			},
			choose: function(value){
				$('.pj-btn').attr('rate',value);
			}
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