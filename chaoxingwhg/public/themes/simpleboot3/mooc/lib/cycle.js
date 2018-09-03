// JavaScript Document

  // JavaScript Document
//created by zcy 20161011
(function($,window,document,undefined){
   //榛樿鍙傛暟var this.PARAMS;
            var plugin=function(ele, opt){
						this.parent=ele;
						this.defaults= {percent:100 ,w:500,oneCircle:"false"};	
						//鍒濆鍖栧弬鏁�
						this.PARAMS=$.extend({},this.defaults,opt) ;
						this.DrawCircle();
				}
		    //瀹氫箟鏂规硶
			plugin.prototype={
				      DrawCircle:function(){
						
					      // if(!canvasSupport()){
							//	return
							//}
							var drawOne=this.PARAMS.oneCircle;
							var r=this.PARAMS.w/2;
							var r1=this.PARAMS.w/2-20;
							var x1=this.PARAMS.w/2;
							var y1=this.PARAMS.w/2;
							var canvas=this.parent[0];
							var tip=this.PARAMS.percent;
							var angle="";
							var init=0;
							var initA=0;
							var preM=0;
							var initM=0;
							var s=2*Math.PI/180;
							var bottomC=Math.PI;
							var allCount=180;
							var allCountP=1.8;
							var poinits=new Array();
							if(drawOne=="ture"){
								angle=tip*2*Math.PI/100;
								canvas.width=this.PARAMS.w;
								canvas.height=this.PARAMS.w;
								 bottomC=2*Math.PI;
								 allCount=0;
								 allCountP=3.6;
							}else{
								angle=tip*Math.PI/100+Math.PI;
								canvas.width=this.PARAMS.w;
								canvas.height=this.PARAMS.w/2;
								init=180;
								preM=Math.PI;
								initM=Math.PI;  
								s=2*Math.PI/180;
							}
							var cxt=canvas.getContext("2d");
							//cxt.lineCap="round";
							cxt.lineWidth=5;
							var speed=1;
							var radius=this.PARAMS.w/2-2;
							var ball={x:0,y:0,speed:2};
							var T1;
							function drawScreen(){
								cxt.fillStyle="rgba(255, 255, 255, 0)";
								cxt.fillRect(0,0,canvas.width,canvas.height);
								//鍒涘缓鍦嗙幆涓庤櫄绾�
								//搴曞渾
								cxt.clearRect(0,0,canvas.width,canvas.height);  
								cxt.beginPath();
								cxt.strokeStyle="#3192E0";
								cxt.arc(x1,y1,r1-6,0,bottomC,true);
								cxt.stroke();  //鍏堟墽琛宻troke  灏变笉浼氬嚭鐜版í绾�
								cxt.closePath();
								//铏氱嚎
								var balls=[];
								var balls=new Array();
								for(var i=initA;i<=360;i+=ball.speed){
									var radians=(i)*(Math.PI/180);
									ball.x=x1+Math.cos(radians)*radius;
									ball.y=y1+Math.sin(radians)*radius;
									balls.push({x:ball.x,y:ball.y});
								}
								for(var i=0;i<balls.length;i++){
									cxt.fillStyle="#a7a7a7";
									cxt.beginPath();
									cxt.arc(balls[i].x,balls[i].y,1,0,Math.PI*2,false);
									//console.log(balls[i].x)
									cxt.closePath();
									cxt.fill(); 
								}
								//鐢诲疄绾�
								if(initM<angle){
									initM+=s;
								}else{
									initM=angle;
									}
								cxt.beginPath();
								cxt.strokeStyle="#FDA027";
								cxt.arc(x1,y1,r1-6,0,initM,false);
								cxt.stroke();  //鍏堟墽琛宻troke  灏变笉浼氬嚭鐜版í绾�
								cxt.closePath();    
								//鐢昏櫄绾� 
								if(init<tip*allCountP+allCount){  //灏忎簬鍒濆瑙掑害
									init+=ball.speed
								}else{
									clearInterval(T1);
								}
								for(var i=initA;i<=init;i+=2){
									var radians2=i*(Math.PI/180);
									var a1=x1+Math.cos(radians2)*radius;
									var a2=y1+Math.sin(radians2)*radius;
									cxt.fillStyle="#ff0000";
									cxt.beginPath();
									cxt.arc(a1,a2,1,0,Math.PI*2,false);
									//console.log(balls[i].x)
									cxt.closePath();
									cxt.fill();
								}
								//鐧惧垎姣旀枃瀛�
								cxt.font="20px sans bold";
								cxt.textBaseline="middle";
								cxt.textAlign="center";
								cxt.fillStyle="#ff0000";
								var messT=tip*initM/angle;
								/*if(drawOne!="ture"){
									 messT=tip*(initM)/angle;
									 console.log(initM-Math.PI)
									}*/
								if(messT>tip){
									  messT=tip
									}
								var mess=messT.toFixed(2)+"%";
								cxt.fillText(mess,canvas.width/2,canvas.height/2);
							}	
						  //
						 
						  T1=setInterval(drawScreen,30) 
					  }
			 }
			function canvasSupport(){
			//鍒ゆ柇鏄惁鏀寔canvas鏍囩
			// return Modernizr.canvas;
		    }	
		//鍦ㄦ彃浠朵腑浣跨敤plugin瀵硅薄
	    $.fn.audios2=function(options){
		//鍒涘缓瀹炰綋
		var plugina=new plugin(this,options);
		}	
})(jQuery,window,document);
