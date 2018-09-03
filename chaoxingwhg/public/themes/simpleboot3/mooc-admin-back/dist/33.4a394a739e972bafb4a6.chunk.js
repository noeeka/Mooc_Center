webpackJsonp([33],{258:function(t,e,i){"use strict";function r(t){l||i(931)}Object.defineProperty(e,"__esModule",{value:!0});var n=i(789),s=i.n(n);for(var a in n)"default"!==a&&function(t){i.d(e,t,function(){return n[t]})}(a);var c=i(933),o=i.n(c),l=!1,d=i(0),u=r,p=d(s.a,o.a,!1,u,null,null);p.options.__file="src/views/course/course.vue",e.default=p.exports},789:function(t,e,i){"use strict";function r(t){return t&&t.__esModule?t:{default:t}}Object.defineProperty(e,"__esModule",{value:!0});var n=i(86),s=r(n),a=i(6),c=r(a);e.default={name:"course_index",data:function(){var t=this,e=this;return{columns:[{type:"selection",align:"center",width:60},{key:"id",title:"ID",width:60},{key:"course_title",title:"标题"},{key:"course_type",title:"类型"},{key:"center_name",title:"来源"},{key:"type",title:"创建者类型",render:function(t,e){return t("span",{},1===e.row.type?"后台":"老师")}},{key:"creator",title:"创建者"},{key:"status",title:"状态",render:function(t,e){return t("span",{},1===e.row.status?"显示":"隐藏")}},{key:"create_time",title:"创建时间",render:function(t,i){return t("span",{},e.dateFormat(i.row.create_time))}},{key:"action",title:"操作",width:250,render:function(i,r){var n=[];return r.row.creator_center_id.toString()===t.cid?n[n.length]=i("Button",{props:{type:"primary",size:"small",icon:"edit"},style:{marginRight:"5px"},on:{click:function(){e.$router.push({name:"course_edit",params:{course_id:r.row.id}})}}},"编辑"):n[n.length]=i("Button",{props:{type:"primary",size:"small",icon:"eye"},style:{marginRight:"5px"},on:{click:function(){e.$router.push({name:"course_view",params:{course_id:r.row.id}})}}},"查看"),r.row.center_id.toString()===t.cid&&(n[n.length]=i("Button",{props:{type:0===r.row.recommend?"primary":"info",size:"small",icon:"flag"},attrs:{id:"recommend-"+r.row.id},style:{marginRight:"5px"},on:{click:function(){e.recommend(r.row.id)}}},0===r.row.recommend?"推荐":"取消推荐"),n[n.length]=i("Poptip",{props:{confirm:!0,title:"确定要删除吗！"},on:{"on-ok":function(){e.delCourse(r.row.id)}}},[i("Button",{props:{type:"error",size:"small",icon:"android-delete"}},"删除")])),i("div",n)}}],data:[],title:"",center_id:0,type_id:[],creator_id:0,creator_type:1,center:{},type:[],creator:{},total:0,current:1,pageSize:10,selectIds:[],change_type_id:[],cid:0}},created:function(){this.cid=c.default.get("center_id"),this.getData(),this.getCenter(),this.getCreator()},methods:{locationToAdd:function(){this.$router.push({name:"course_add"})},batchDel:function(){if(this.selectIds.length>0){var t=this;this.request({api:"/v1/course/delete",user_type:3,ids:this.selectIds},function(e){1===e.status?(t.$Message.success("删除成功"),t.getData()):t.$Message.error(e.msg)})}else this.$Message.error("至少选择一项")},batchType:function(){if(this.selectIds.length>0){var t=this;this.request({api:"/v1/course/change_type",user_type:3,ids:this.selectIds,type_id:this.change_type_id[this.change_type_id.length-1]},function(e){1===e.status?(t.$Message.success("修改分类成功"),t.getData()):t.$Message.error(e.msg)})}else this.$Message.error("至少选择一项")},batchRecommend:function(){if(this.selectIds.length>0){var t=this;this.request({api:"/v1/course/recommend",user_type:3,recommend:1,ids:this.selectIds},function(e){1===e.status?(t.$Message.success("推荐成功"),t.getData()):t.$Message.error(e.msg)})}else this.$Message.error("至少选择一项")},handleSelectionChange:function(t){this.selectIds=[];for(var e in t)this.selectIds[this.selectIds.length]=t[e].id},init:function(){this.title="",this.center_id=0,this.type_id=[],this.type=[],this.creator_id=0,this.current=1,this.total=0},selectData:function(t,e,i,r,n){var s=this;n=n||{},n.api=t,n.user_type=3,this.request(n,function(t){1===t.status?s[e]=s.array_map(t.data,i,r):s[e]={}})},getData:function(){var t=this,e=0===this.type_id.length?0:this.type_id[this.type_id.length-1];this.request({api:"/v1/course/index",page:this.current,len:this.pageSize,center_id:this.center_id,creator_type:this.creator_type,creator_id:this.creator_id,title:this.title,cid:this.cid,other_id:e},function(e){1===e.status?(t.data=e.data.list,t.total=e.data.num):t.init()})},getCenter:function(){this.selectData("/v1/mooc_center/index","center","id","center_name")},getType:function(){var t=this;this.request({api:"/v1/course_type/index",user_type:3,center_id:this.center_id},function(e){1===e.status?t.type=e.data:t.type=[]})},getTeacher:function(){this.selectData("/v1/user/index","creator","id","nick_name",{type:[2],all:1})},getCenterType:function(){this.getType()},getCreator:function(){"2"===this.creator_type?this.getTeacher():this.selectData("/v1/mooc_center/index","creator","id","center_name")},handleSearch:function(){this.current=1,this.getData()},handleCancel:function(){this.init(),this.getData()},handlePageChange:function(t){this.current=t,this.getData()},recommend:function(t){var e=(0,s.default)("#recommend-"+t),i=e.text().trim(),r="推荐"===i?1:0,n=this;this.request({api:"/v1/course/recommend",user_type:3,recommend:r,id:t},function(t){1===t.status?"推荐"===i?(n.$Message.success("推荐成功"),e.html('<i class="ivu-icon ivu-icon-flag"></i><span>取消推荐</span>').removeClass("ivu-btn-primary").addClass("ivu-btn-info")):(n.$Message.success("取消推荐成功"),e.html('<i class="ivu-icon ivu-icon-flag"></i><span>推荐</span>').removeClass("ivu-btn-info").addClass("ivu-btn-primary")):n.$Message.error(t.msg)})},delCourse:function(t){var e=this;this.request({api:"/v1/course/delete",user_type:3,id:t},function(t){1===t.status?(e.$Message.success("删除成功"),e.getData()):e.$Message.error(t.msg)})}}}},931:function(t,e,i){var r=i(932);"string"==typeof r&&(r=[[t.i,r,""]]),r.locals&&(t.exports=r.locals);i(10)("74a6fb84",r,!1,{})},932:function(t,e,i){e=t.exports=i(9)(!1),e.push([t.i,"\n.switch-language-row1 {\n  height: 240px !important;\n}\n.switch-language-tip {\n  font-size: 12px;\n  color: gray;\n  margin-top: 30px;\n}\n",""])},933:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var r=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",[i("Row",{attrs:{gutter:10}},[i("Col",{attrs:{span:"24"}},[i("Card",[i("p",{attrs:{slot:"title"},slot:"title"},[i("Icon",{attrs:{type:"ios-book-outline"}}),t._v("\n                    慕课库管理\n                ")],1),t._v(" "),i("Row",[i("label",{attrs:{for:"center_id"}},[t._v("来源:")]),t._v(" "),i("Select",{staticStyle:{width:"100px"},attrs:{id:"center_id"},on:{"on-change":t.getCenterType},model:{value:t.center_id,callback:function(e){t.center_id=e},expression:"center_id"}},t._l(t.center,function(e,r){return i("Option",{key:r,attrs:{value:r}},[t._v(t._s(e))])})),t._v(" "),i("label",{attrs:{for:"type_id"}},[t._v("分类:")]),t._v(" "),i("Cascader",{staticStyle:{width:"150px",display:"inline-block"},attrs:{data:t.type,id:"type_id"},model:{value:t.type_id,callback:function(e){t.type_id=e},expression:"type_id"}}),t._v(" "),i("label",{attrs:{for:"creator_type"}},[t._v("创建者类型:")]),t._v(" "),i("Select",{staticStyle:{width:"100px"},attrs:{id:"creator_type"},on:{"on-change":t.getCreator},model:{value:t.creator_type,callback:function(e){t.creator_type=e},expression:"creator_type"}},[i("Option",{attrs:{value:"1"}},[t._v("后台")]),t._v(" "),i("Option",{attrs:{value:"2"}},[t._v("老师")])],1),t._v(" "),i("label",{attrs:{for:"creator_id"}},[t._v("创建者:")]),t._v(" "),i("Select",{staticStyle:{width:"100px"},attrs:{id:"creator_id"},model:{value:t.creator_id,callback:function(e){t.creator_id=e},expression:"creator_id"}},t._l(t.creator,function(e,r){return i("Option",{key:r,attrs:{value:r}},[t._v(t._s(e))])})),t._v(" "),i("label",{attrs:{for:"title"}},[t._v("标题:")]),i("Input",{staticStyle:{width:"200px"},attrs:{id:"title",placeholder:"请输入标题搜索..."},model:{value:t.title,callback:function(e){t.title=e},expression:"title"}}),t._v(" "),i("span",{staticStyle:{margin:"0 10px"},on:{click:t.handleSearch}},[i("Button",{attrs:{type:"primary",icon:"search"}},[t._v("搜索")])],1),t._v(" "),i("Button",{attrs:{type:"ghost"},on:{click:t.handleCancel}},[t._v("取消")])],1),t._v(" "),i("Row",{staticStyle:{"margin-top":"10px"}},[i("Cascader",{staticStyle:{width:"150px",display:"inline-block"},attrs:{data:t.type,id:"change_type_id"},model:{value:t.change_type_id,callback:function(e){t.change_type_id=e},expression:"change_type_id"}}),t._v(" "),i("Button",{attrs:{type:"error"},on:{click:t.batchType}},[t._v("批量归类")])],1),t._v(" "),i("Row",{staticStyle:{margin:"10px 0"}},[i("Button",{attrs:{type:"error"},on:{click:t.batchDel}},[t._v("批量删除")]),t._v(" "),i("Button",{attrs:{type:"error"},on:{click:t.batchRecommend}},[t._v("批量设为推荐")]),t._v(" "),i("Button",{attrs:{type:"primary"},on:{click:t.locationToAdd}},[t._v("创建课程")])],1),t._v(" "),i("Row",{staticClass:"searchable-table-con1",staticStyle:{margin:"10px 0"}},[i("Table",{attrs:{columns:t.columns,data:t.data},on:{"on-selection-change":t.handleSelectionChange}})],1),t._v(" "),i("Row",[i("Page",{attrs:{total:t.total,current:t.current,"page-size":t.pageSize,"show-total":"","show-elevator":""},on:{"on-change":t.handlePageChange}})],1)],1)],1)],1)],1)},n=[];r._withStripped=!0;var s={render:r,staticRenderFns:n};e.default=s}});