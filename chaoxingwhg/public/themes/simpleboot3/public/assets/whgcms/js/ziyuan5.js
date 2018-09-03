$(function() {
	/*banner*/
	$.ajax({
		type: "get",
		url: "/api/getbanner/index",
		data: {
			page: 1,
			len: 4
		},
		success: function(res) {
			if(res.status == 1) {
				var banner = '';
				for(var i in res.data) {
					var pcurl = res.data[i].url;
					banner += '<div class="swiper-slide">';
					banner += '<a href="' + pcurl + '">';
					banner += '<img src="' + res.data[i].thumb + '" alt="" />';
					banner += '<div>';
					banner += '<h3 class="o1">' + res.data[i].title + '</h3>';
					banner += '</div>';
					banner += '</a>';
					banner += '</div>';
				}
				$('.swiper-wrapper').html(banner);
				var mySwiper = new Swiper('.banner .swiper-container', {
					pagination: '.banner .pagination',
					paginationClickable: true,
					autoplay: 4000,
					speed: 1000,
					loop: true
				})
			}
		}
	});
	/*热门推荐*/
	/*广场舞活动*/
	/*健康养生*/
	list(199, 74);
	list(210, 85);

	function list(cid, nid) {
		$.ajax({
			type: "get",
			url: "/api/article/index",
			data: {
				cid: cid,
				page: 1,
				len: 4,
				has_child: 0
			},
			success: function(res) {
				if(res.status == 1) {
					var list1 = '';
					for(var i in res.data.list) {
						list1 += '<li class="f-left">';
						list1 += '<a href="/portal5/category/read/?id=' + res.data.list[i].id + '&nid=' + nid + '">';
						list1 += '<div class="content">';
						list1 += '<img src="' + res.data.list[i].thumb + '" alt="" />';
						list1 += '<div class="list-content">';
						list1 += '<div>';
						list1 += '<h3 class="o1">' + res.data.list[i].title + '</h3>';
						list1 += '<p class="o1">' + res.data.list[i].abstract + '</p>';
						list1 += '</div>';
						list1 += '</div>';
						list1 += '</div>';
						list1 += '</a>';
						list1 += '</li>';
					}
					if(cid == 199) {
						$('.activity .list').html(list1);
					} else if(cid == 210) {
						$('.movie .list').html(list1);
					}
				}
			}
		});
	}
	/*文化广角*/
	$.ajax({
		type: "get",
		url: "/api/article/index",
		data: {
			cid: 200,
			page: 1,
			len: 4,
			has_child: 0
		},
		success: function(res) {
			if(res.status == 1) {
				var list2 = '';
				for(var i in res.data.list) {
					list2 += '<li class="f-left">';
					list2 += '<a href="/portal5/category/read/?id=' + res.data.list[i].id + '&nid=75">';
					list2 += '<div class="list-content">';
					list2 += '<img src="' + res.data.list[i].thumb + '" alt="" />';
					list2 += '</div>';
					list2 += '<h3>' + res.data.list[i].title + '</h3>';
					list2 += '</a>';
					list2 += '</li>';
				}
				$('.conner .list').html(list2);
			}
		}
	});
	/*少儿乐园*/
	$.ajax({
		type: "get",
		url: "/api/article/index",
		data: {
			cid: 201,
			page: 1,
			len: 1,
			has_child: 0
		},
		success: function(res) {
			if(res.status == 1) {
				var list3 = '';
				for(var i in res.data.list) {
					list3 += '<a href="/portal5/category/read/?id=' + res.data.list[i].id + '&nid=76">';
					list3 += '<div>';
					list3 += '<img src="' + res.data.list[i].thumb + '" alt="" />';
					list3 += '</div>';
					list3 += '<h3 class="o1">' + res.data.list[i].title + '</h3>';
					list3 += '<p class="o5">' + res.data.list[i].abstract + '</p>';
					list3 += '</a>'
				}
				$('.paradise .list').html(list3);
			}
		}
	});
	/*艺术视界*/
	$.ajax({
		type: "get",
		url: "/api/article/index",
		data: {
			cid: 202,
			page: 1,
			len: 6,
			has_child: 0
		},
		success: function(res) {
			if(res.status == 1) {
				var list4 = '';
				for(var i in res.data.list) {
					list4 += '<li class="f-left">';
					list4 += '<a href="/portal5/category/read/?id=' + res.data.list[i].id + '&nid=77">';
					list4 += '<div>';
					list4 += '<img src="' + res.data.list[i].thumb + '" alt="" />';
					list4 += '</div>';
					list4 += '<h3 class="o1">' + res.data.list[i].title + '</h3>';
					list4 += '</a>';
					list4 += '</li>';
				}
				$('.art .list').html(list4);
			}
		}
	});
})