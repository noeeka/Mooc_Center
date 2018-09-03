// JavaScript Document

Vue.component('header-all', {
  props: ['results'],
  data:function(){
	  return {
		list:[
			{name:1},
			{name:2},
			{name:3},
			{name:4}
		]
	  } 
  },
  template:'<div class="header"><div class="header-safe"><div class="header-content clearfix"><div class="logo f-left"><img src="images/logo2.png"></div><div class="search f-left clearfix"><div class="select f-left"><select name="" id="type"><option value="1">全部字段</option><option value="2">国学</option><option value="3">作品</option><option value="4">慕课</option></select></div><div class="sousuo f-left"><input type="text"><div class="search-btn" @click="searchhead"> 搜索</div></div></div><div class="f-right login-reg" style="display: none;"><a href="" class="login">登录</a><span>|</span><a href="" class="reg">注册</a></div><div class="f-right nickname clearfix"><img src="images/index1.jpg" alt="" class="f-left"><div class="f-left nike"><p class="o1">昵称昵称</p><ul class="menu-b"><li><a href="">个人中心</a></li></ul></div></div></div></div></div>',
  created:function(){
	
  },
  methods:{
	searchhead:function(){
		//alert($('.aa').text());	
	}  
  }
});

Vue.component('footer-all', {
  props: ['results'],
  data:function(){
	  return {
		list:[
			{name:1},
			{name:2},
			{name:3},
			{name:4}
		]
	  } 
  },
  template:'<div class="footer"><div class="footer-safe"><div class="footer-content"><ul class="clearfix"><li class="f-left"><a href="">技术支持:超星泛雅Copyright © 1993-2018 超星网 </a></li><li class="f-left"><a href="">单位编号:1101081827 </a></li><li class="f-left"><a href="">京ICP证:060172号 网络 </a></li><li class="f-left"><a href="">视听许可证:0110438号 </a></li></ul></div></div></div>',
  created:function(){
	
  },
  methods:{
	searchhead:function(){
		//alert($('.aa').text());	
	}  
  }
});
