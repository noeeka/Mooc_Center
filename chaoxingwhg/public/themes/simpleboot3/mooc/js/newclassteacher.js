// JavaScript Document
$(function(){
	//被选中的节点id对应右侧编辑器内容
	var selectnodeid='';
	//ztree
	var setting = {
		//async: {
		//    enable: true,
		//    checked: true,
		//    type: "get",
		//    url: "aa.xml",
		//    autoParam: ["id"],
		//    dataFilter: Filterlxr
		//},
		data: {
			simpleData: {
				enable: true,
				idKey: "id",
				pIdKey: "pid",
				rootPId: ""
			}
		},
		callback: {
			onClick:ztreeclick,
			//onRemove: onRemove,
			//onRename: zTreeOnRename
			onRename:zTreeOnRename,
			//onNodeCreated: zTreeOnNodeCreated
		},
		view: {
			//showIcon: false,
			//dblClickExpand: false,
			selectedMulti: false,
			showIcon: false,
			showLine: false,
			addDiyDom: addDiyDom
		}
	};
	//ztree点击事件
	function ztreeclick(event, treeId, treeNode){
		var zTree = $.fn.zTree.getZTreeObj("ztree");
		//zTree.expandNode(treeNode);
		//$('.tree-b').find('li').removeClass('active-li').removeClass('active-li-p');
		if(treeNode.isParent){
			zTree.expandNode(treeNode);
		}else{
			$('.tree-b').find('li').removeClass('active-li').removeClass('active-li-p');
			$('.curSelectedNode').parent('li').addClass('active-li');
			var ztreeid = treeNode.id;
			var ptreeid = treeNode.pid;
			var pNode = zTree.getNodeByParam("id",ptreeid );
			$('.active-tit').text(pNode.name + treeNode.name);
			selectnodeid = treeNode.id;
			ue.ready(function() {
				//不可编辑
				ue.setEnabled();
			});
			console.log(selectnodeid);
			ue.ready(function(){
				this.setContent('内容2')
			})
			//$.ajax({})
		};
	};
	//模拟数据
	ajax();
	function ajax(){
		var csztree=[
			{"name":"第一章","id":"01","pid":"0","isParent":true},
			{"name":"第01节","id":"101","pid":"01"},
			{"name":"第02节","id":"102","pid":"01"},
			{"name":"第03节","id":"103","pid":"01"},
			{"name":"第04节","id":"104","pid":"01"},
			{"name":"第05节","id":"105","pid":"01"}
		]
		$.fn.zTree.init($('#ztree'), setting,csztree);
		//默认选中第一个节点
		var treeObj = $.fn.zTree.getZTreeObj("ztree");
		var nodes = treeObj.getNodes();
		if (nodes.length>0) {
			//treeObj.expandNode(nodes[0], true, true, true);
			treeObj.selectNode(nodes[0].children[0]);
			var selectid ='#'+nodes[0].children[0].tId;
			$('.active-tit').text(nodes[0].name + nodes[0].children[0].name);
			selectnodeid = nodes[0].children[0].id;
			console.log(selectnodeid);
			$(selectid).addClass('active-li');
		}
	}
	
	//添加单元
	$('.add-dy').click(function(){
		layer.open({
		  title: '添加单元',
		  //content:$('#nodename'),
		  content:'<input class="add-dy-ipt" type="text">',
		  btn: ['确定', '取消'],
		  yes: function(index, layero){
			var name = $('.add-dy-ipt').val();
			if(name != ''){
				var treeObj = $.fn.zTree.getZTreeObj("ztree");
				var newNode = {"name":name,"id":"03","pid":"0","isParent":true};
				newNode = treeObj.addNodes(null, newNode);
				layer.close(index);
			}else{
				layer.msg('内容不能为空'); 	
			}
			
		  },
  		  btn2: function(index, layero){
			
		  }
		});  
		//var treeObj = $.fn.zTree.getZTreeObj("ztree");
		//treeObj.updateNode(csztree1);	
	})
	//添加课时
	$('.ztree').on('click','.add-ks',function(){
		var treeid = $(this).parents('li').attr('id');
		var zTree = $.fn.zTree.getZTreeObj("ztree");
		var treeNode = zTree.getNodeByParam("tId",treeid );
		layer.open({
		  title: '添加课时',
		  //content:$('#nodename'),
		  content:'<input class="add-ks-ipt" type="text">',
		  btn: ['确定', '取消'],
		  yes: function(index, layero){
			var name = $('.add-ks-ipt').val();
			if(name != ''){
				//$.ajax({});
				var newNode = {"name":name,"id":"013","pid":treeNode.id};
				console.log(newNode);
				newNode = zTree.addNodes(treeNode, newNode);
				layer.close(index);
			}else{
				layer.msg('内容不能为空'); 	
			}
			
		  },
  		  btn2: function(index, layero){
			
		  }
		});  
	});
	//编辑
	$('.ztree').on('click','.edit-ml',function(){
		var treeid = $(this).parents('li').attr('id');
		var zTree = $.fn.zTree.getZTreeObj("ztree");
		var treeNode = zTree.getNodeByParam("tId",treeid );
		zTree.editName(treeNode);
	});
	//编辑ajax
	function zTreeOnRename(event, treeId, treeNode, isCancel) {
		alert(treeNode.id + ", " + treeNode.name);
		console.log(treeNode)
	}
	//删除
	$('.ztree').on('click','.del-ml',function(){
		var treeid = $(this).parents('li').attr('id');
		var zTree = $.fn.zTree.getZTreeObj("ztree");
		var treeNode = zTree.getNodeByParam("tId",treeid);
		var opencontent;
		if(treeNode.isParent){
			opencontent = '是否删除“'+treeNode.name+'”,删除后子目录将同时删除？'
		}else{
			opencontent = '是否删除“'+treeNode.name+'”？'
		}
		layer.open({
		  title: '删除',
		  //content:$('#nodename'),
		  content:opencontent,
		  btn: ['确定', '取消'],
		  yes: function(index, layero){
			//$.ajax({
//				url:'',
//				type:'post',
//				data:{},
//				dataType:'json',
//				success: function(){
//						
//				}	
//			})
			zTree.removeNode(treeNode,true);
			layer.close(index);
			var senode = zTree.getSelectedNodes()[0];
			if(!senode){
			 	ue.ready(function(){
					this.setContent('')
				});
				$('.active-tit').text('请先选择课时再编辑内容');
				selectnodeid = '';
				ue.ready(function() {
					//不可编辑
					ue.setDisabled();
				});
			}
		  },
  		  btn2: function(index, layero){
			
		  }
		});
	})
	//添加编辑删除
	function addDiyDom(treeId, treeNode) {
		var aObj = $("#" + treeNode.tId);
		if(treeNode.isParent){
			aObj.append('<span class="tree-btn-b"><span class="add-ks"><img src="images/addclass.png"></span><span class="edit-ml"><img src="images/editclass.png"></span><span class="del-ml"><img src="images/deleteclass.png"></span></span>');
		}else{
			aObj.append('<span class="tree-btn-b"><span class="edit-ml"><img src="images/editclass.png"></span><span class="del-ml"><img src="images/deleteclass.png"></span></span>');
		}
		
	};
	$('.ztree').on('mouseenter','li',function(){;
		$(this).children('.tree-btn-b').show();
	});
	$('.ztree').on('mouseleave','li',function(){
		$(this).children('.tree-btn-b').hide();
	})
	//实例化编辑器
    var ue = UE.getEditor('editor',{
		toolbars: [
			['undo', 'redo', '|','bold', 'italic', 'underline', '|', 'forecolor', 'insertorderedlist', 'insertunorderedlist', 'cleardoc', '|', '|','fontfamily', 'fontsize', '|','justifyleft', 'justifycenter', 'justifyright', 'justifyjustify','|']
		]
	});
	ue.ready(function(){
		this.setContent('内容')
	})
	//提交
	$('.edit-sub-btn').click(function(){
		if(selectnodeid == '' || ue.getContentTxt() == ''){
			layer.alert('编辑文本不能为空');
		}else{
			console.log(selectnodeid);		
		}
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