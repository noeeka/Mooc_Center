$(function() {
	list(48,72);
	list(0,73);
	function list(venue,nid) {
		$.ajax({
			type: "get",
			url: "/api/room/index",
			data: {
				venue: venue,
				area: 0,
				typeid: 0,
				page: 1,
				len: 3,
				type: 1,
				sort: 0
			},
			success: function(res) {
				if(res.status == 1) {
					var list = '';
					for(var i in res.data.list) {
						list += '<li class="f-left">';
						list += '<a href="/portal4/room/read/?id=' + res.data.list[i].id + '&nid='+nid+'">';
						list += '<div>';
						list += '<img src="/upload/' + res.data.list[i].thumb + '" alt="" />';
						list += '</div>';
						list += '<h3 class="o1">' + res.data.list[i].name + '</h3>';
						list += '</a>';
						list += '</li>';
					}
					if(venue==0){
						$('.fg .list').html(list);
					}else{
						$('.zg .list').html(list);
					}
				}
			}
		});
	}

})