$(function(){
	list(195,67);
	list(196,68);
	function list(cid,nid){
		$.ajax({
			type:"get",
			url:"/api/article/index",
			data:{
				cid: cid,
				page: 1,
				len: 1,
				has_child: 0
			},
			success:function(res){
				if(res.status==1){
					var list='';
					for(var i in res.data.list){
						list+='<a href="/portal3/category/read/?id=' + res.data.list[i].id + '&nid=' + nid + '">';
						list+='<img src="'+res.data.list[i].thumb+'" alt="" />';			
						list+='<h3 class="o1">'+res.data.list[i].title+'</h3>';			
						list+='</a>';		
					}
					if(cid==195){
						$('.she').html(list);
					}else if(cid==196){
						$('.wen').html(list);
					}
				}
			}
		});
	}
})
