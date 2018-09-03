$(function(){
	$('.bj-pl-btn').click(function(){
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
})

var vm = new Vue({
	el: '#app',
	data:{
		gz:'+ 关注'
	},
	created:function(){
		//console.log(this.data)	
		//this.date = this.format(this.date)
	},
	methods:{
		format:function(date){ 
			var testDate = new Date(date);
			return testDate.format("yyyy年MM月dd日hh小时"); 
		},
		gzfun:function(){
			this.gz = '已关注'	
		}
	},
	filters: {
	  format: function (date) {
			var testDate = new Date(date); 
			return testDate.format("yyyy年MM月dd日hh小时"); 
	  }
	}
});