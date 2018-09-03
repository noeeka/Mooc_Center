$(function() {
	$(function() {
		$(".scroll").css({
			"visibility": "visible"
		});
		$(".scroll").stop().animate({
			width: '1456px'
		}, 5000)
	})
	$.ajax({
		type: "get",
		url: "/api/article/index",
		data: {
			zyk: 1
		},
		success: function(res) {
			if(res.status == 1) {
				var slide = '';
				var nid = '';
				for(var i in res.data.list.slice(0, 4)) {
					if(res.data.list[i].cid == 191) {
						nid = 63;
					} else if(res.data.list[i].cid == 192) {
						nid = 64;
					} else if(res.data.list[i].cid == 193) {
						nid = 65;
					} else if(res.data.list[i].cid == 194) {
						nid = 66;
					}
					slide += '<div class="swiper-slide">';
					slide += '<a href="/portal2/category/read/?id=' + res.data.list[i].id + '&nid=' + nid + '">';
					slide += '<img src="' + res.data.list[i].thumb + '" alt="" />';
					slide += '<p>' + res.data.list[i].title + '</p>';
					slide += '</a>';
					slide += '</div>';
				}
				$('.swiper-wrapper').html(slide);
				var list = '';
				for(var j in res.data.list.slice(4, 10)) {
					list += '<li class="clearfix">';
					list += '<a href="/portal2/category/read/?id=' + res.data.list[i].id + '&nid=' + nid + '">';
					list += '<span class="f-left"></span>';
					list += '<h3 class="o1 f-left">' + res.data.list[i].title + '</h3>';
					list += '</a>';
					list += '</li>';
				}
				$('.list').html(list);
				var mySwiper = new Swiper('.swiper-container', {
					pagination: '.pagination',
					paginationClickable: true,
					slidesPerView: 2,
					loop:true,
					autoplay:3000,
					speed:1000
				})
				$('.arrow-left').on('click', function(e) {
					e.preventDefault()
					mySwiper.swipePrev()
				})
				$('.arrow-right').on('click', function(e) {
					e.preventDefault()
					mySwiper.swipeNext()
				})
			}
		}
	});
	list(191, 63);
	list(192, 64);
	list(193, 65);
	list(194, 66);

	function list(cid, nid) {
		$.ajax({
			type: "get",
			url: "/api/article/index",
			data: {
				cid: cid,
				page: 1,
				len: 3,
				has_child: 0
			},
			success: function(res) {
				if(res.status == 1) {
					var titlelist = '';
					for(var k in res.data.list) {
						titlelist += '<li class="o1">';
						titlelist += '<a href="/portal2/category/read/?id=' + res.data.list[k].id + '&nid=' + nid + '">' + res.data.list[k].title + '</a>';
						titlelist += '</li>';
					}
					if(cid == 191) {
						$('.yanchu').html(titlelist);
						$('.yanchu-img img').attr('src', res.data.list[0].thumb);
					} else if(cid == 192) {
						$('.zhanlan').html(titlelist);
						$('.zhanlan-img img').attr('src', res.data.list[0].thumb);
					} else if(cid == 193) {
						$('.jiangzuo').html(titlelist);
						$('.jiangzuo-img img').attr('src', res.data.list[0].thumb);
					} else if(cid == 194) {
						$('.peixun').html(titlelist);
						$('.peixun-img img').attr('src', res.data.list[0].thumb);
					}
				}
			}
		});
	}
})