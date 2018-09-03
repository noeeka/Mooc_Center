// JavaScript Document

$(function(){
	//滚动加载
	var curpage = 1;
	$(window).scroll(function(){
	 var scrollTop = $(this).scrollTop();    //滚动条距离顶部的高度
	 var scrollHeight = $(document).height();   //当前页面的总高度
	 var clientHeight = $(this).height();    //当前可视的页面高度
	 if(scrollTop + clientHeight > scrollHeight || scrollTop + clientHeight == scrollHeight){   //距离顶部+当前高度 >=文档总高度 即代表滑动到底部 
	     //滚动条到达底部
	     curpage++;
	     $.ajax({
	        url:'http://mooc.com/v1/user/read/',
	        type:'get',
	        data:{
	                user_type:usertype,
	                page:curpage,
	                len:4
	            },
	        dataType:'json',
	        success: function(res){
	        	res.data.classes.map(function(item) {
	        		item.end_time = vm.format(item.end_time)
	        		vm.classes.push(item);
	        	})
	        	console.log(res);
	        },
	        error: function(){

	        }
	    })
	 }
	});

})

var vm = new Vue({
	el: '#app',
	data:{
		userinfo:{},
		classes:[]
	},
	created:function(){
		$.ajax({
	        url:'http://mooc.com/v1/user/read/',
	        type:'get',
	        data:{
	                user_type:usertype,
	                page:1,
	                len:8
	            },
	        dataType:'json',
	        success: function(res){
	        	vm.userinfo = res.data.user_info;
	        	vm.classes = res.data.classes;
	        	vm.classes.map(function(item) {
	        		item.end_time = vm.format(item.end_time)
	        	})
	        	console.log(vm.classes)
				if(vm.userinfo.sex == 0){
					vm.userinfo.sex = "保密"
				}else if(vm.userinfo.sex == 1){
					vm.userinfo.sex = "男"
				}else if(vm.userinfo.sex == 2){
					vm.userinfo.sex = "女"
				}
	        },
	        error: function(){
	            console.log(this.userinfo.Fans_num)  
	        }
	    })
	    //this.format()
	},
	mounted:function(){
		
	},
	methods:{
		format:function(date){ 
			var testDate = new Date(date);
			return testDate.format("yyyy.MM.dd 到期");  
		},
		gzfun:function(){
			if(this.gz == '已关注'){
				//关注后再次点击取消
				/*$.ajax({
					url:'http://mooc.com/v1/follow/unfollowById/',
			        type:'post',
			        data:{
			                follow_id:vm.userinfo.id
			            },
			        dataType:'json',
			        success:function(res){
			        	console.log(res)
			        },
			        error:function(event, XMLHttpRequest, ajaxOptions, thrownError){
			        	console.log(event);
			        	console.log(XMLHttpRequest);
			        	console.log(ajaxOptions);
			        	console.log(thrownError);
			        }
				})*/
				return false
			}else{
				$.ajax({
					url:'http://mooc.com/v1/follow/followById/',
			        type:'post',
			        data:{
			                follow_id:vm.userinfo.id
			            },
			        dataType:'json',
			        success:function(res){
			        	if(res.msg == '200'){
			        		vm.userinfo.is_follow = 1
			        	}
			        },
			        error:function(){
			        	
			        }
				})
			}
		}
	},
	filters: {

	},
	computed:{
		gz: function () {
	      if(this.userinfo.is_follow == 0){
				return "+ 关注"
			}else{
				return "已关注"
			}
	    }
	}
});