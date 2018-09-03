$(function() {
	//数据模板
	var navTpl = $('#nav-template').html();
	var navCmp = Handlebars.compile(navTpl);
	$.ajax({
		type: 'get',
		url: '/api/navigation/index/type/2',
		dataType: 'json',
		async: false,
		success: function(res) {
			if(res.status == 1) {
				len = res.data.length;
				// console.log(len)
				$('.iconFlax').html(navCmp(res.data));
				var urlid = getUrl('navid');
				if(urlid == null) {
					$('.navlist li:first-child').addClass('active');
					//没有参数时滚动到导航首页链接
					// scrollNav('navindex');
				}
				$('.navlist li').each(function(i) {
					if($('.navlist li').eq(i).attr('id') == 'nav34') {
						$('.navlist li').eq(i).find('a').attr('href', '/wx/Readiframe/index?srcid=http://www.baidu.com&title=特色库');
					}
					if($('.navlist li').eq(i).attr('id') == 'nav33') {
						$('.navlist li').eq(i).find('a').attr('href', '/wx/Readiframe/index?srcid=http://www.baidu.com&title=资源库');
					}
					if($('.navlist li').eq(i).attr('id') == urlid) {
						$('.navlist li').eq(i).addClass('active');
						scrollNav(urlid);
					}
				})
//				$('#wrapper li').click(function() {
//					var index = $(this).index();
//					var navurl=res.data[index].url;
//					jsBridge.postNotification('CLIENT_OPEN_URL', {
//						'title': 'xxx',
//						'loadType': 1,
//						'webUrl': navurl,
//						'toolbarType': '2'
//					});
//
//				})
			}
			//设置导航宽度
			var navWidth = $(".iconFlax li").length * 0.7;
			//加上左右内边距
			navWidth += 0.1;
			$(".iconFlax").width(navWidth + "rem")
		}
	});
	title1();
	//当前选择的nav滚动到可视区域
	function scrollNav(navid) {
		var ulwidth = 0;
		$('.navlist li').each(function() {
			var liwidth = $(this).width() + 31;
			// console.log(liwidth)
			ulwidth += liwidth;

		})
		$('#scroller').css({
			width: ulwidth + 30 + 'px'
		});
		// console.log(ulwidth);
		// $('#scroller').css({width:ulwidth.toFixed(2)+'rem'});
		// var winWidth = $(window).width();
		// console.log(winWidth);
		// if(winWidth > 400){
		//     $('#scroller').css({width:ulwidth+40+'px'});
		// }else{
		//     $('#scroller').css({width:ulwidth+30+'px'});
		// }
		var myScroll = new IScroll('#wrapper', {
			eventPassthrough: true,
			scrollX: true,
			scrollY: false,
			preventDefault: false,
			disableMouse: false,
			disablePointer: false
		});
		myScroll.scrollToElement(document.getElementById(navid), 1000, true, true);
	}
})