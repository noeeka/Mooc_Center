$(function() {
	/*春晚*/
	var venue = 0; //场馆id
	var area = 0; //区域id
	var page = 1;
	var typeid = 0;
	$.ajax({
		type: "get",
		url: "/api/live_broadcast/index",
		data: {
			type: 1,
			venue: venue,
			page: page,
			address: area,
			typeid: typeid,
			len: 4
		},
		success: function(res) {
			if(res.status == 1) {
				var twolist = '';
				var nowdata=new Date().getTime();
				for(var i in res.data.list) {
					twolist += '<li class="f-left">';
					if(res.data.list[i].end_time<nowdata){
						if(res.data.list[i].playback_link==''){
							console.log(1)
							twolist += '<a href="/portal1/live/huifang" target="_blank">';
						}else{
							console.log(2)
							twolist += '<a href="' + res.data.list[i].playback_link + '" target="_blank">';
						}
					}else{
						console.log(3)
						twolist += '<a href="' + res.data.list[i].live_link + '" target="_blank">';
					}
					twolist += '<img src="' + res.data.list[i].img + '" alt="" />';
					twolist += '<div class="content">';
					twolist += '<div>';
					twolist += '<h3>' + res.data.list[i].live_name + '</h3>';
					twolist += '<p>' + res.data.list[i].name + '</p>';
					twolist += '</div>';
					twolist += '</div>';
					twolist += '</a>';
					twolist += '</li>';
				}
				$('.two-list').html(twolist);
			}

		}
	});
	list(182,4,58);
	list(183,3,59);
	list(184,4,60);
	function list(cid, len, nid) {
		$.ajax({
			type: 'get',
			url: '/api/article/index',
			data: {
				cid: cid,
				page: 1,
				len:len,
				has_child: 0
			},
			success: function(res) {
				if(res.status == 1) {
					var fourlist = '';
					for(var i in res.data.list) {
						fourlist += '<li class="f-left">';
						fourlist += '<a href="/portal1/category/read/?id= ' + res.data.list[i].id + '&nid='+nid+'">';
						fourlist += '<div>';
						fourlist += '<img src="' + res.data.list[i].thumb + '" alt="" />';
						fourlist += '</div>';
						fourlist += '<p>' + res.data.list[i].title + '</p>';
						fourlist += '</a>';
						fourlist += '</li>';
					}
					if(cid==182){
						$('.three-list').html(fourlist);
					}else if(cid==183){
						$('.four-list').html(fourlist);
						
					}else if(cid==184){
						$('.five-list').html(fourlist);
					}
				}
			}
		})
	}
})