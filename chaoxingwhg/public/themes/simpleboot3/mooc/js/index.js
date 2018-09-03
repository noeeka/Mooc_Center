$(function() {
    var bg1 = $('.baner-content .swiper-container .swiper-slide').eq(0).children('img').attr('src');
    $('.banner-bg').css("background-image", "url(" + bg1 + ")");
    var mySwiper = new Swiper('.baner-content .swiper-container', {
        pagination: '.baner-content .pagination',
        paginationClickable: true,
        moveStartThreshold: 100,
		speed:800,
        autoplay: 3500,
        loop: true,
        onSlideChangeEnd: function(swiper) {
            bg();
        }
    })

    function bg() {
        var bg = $('.baner-content .swiper-slide-active').children('img').attr('src');
        $('.banner-bg').css("background-image", "url(" + bg + ")");
    }
    var height = $('.tab').height();
    var height2 = 75 * $('.tab .more li').length;
    $('.baner-content .tab .list').css('height', height - 41 + 'px');
    $('.tab').mouseover(function(event) {
        $('.baner-content .tab .list').show();
    });
    if ($('.tab li').length > 5) {
        $('.tab .gengduo').show();
        $('.tab>ul li:nth-child(5)').remove();
    }
    $('.tab .more').mouseenter(function(event) {
        if ($('#remove').length === 0) {
            $('.tab .more li:first-child').before('<li id="remove"><span>国学2</span><a href="">摄影</a><a href="">唱歌</a><i class="layui-icon" style="font-size: 16px; color: #fff;">&#xe602;</i> </li>');
        }
        $('.tab .more>ul').show();
        $('.tab .gengduo').hide();
        $('.baner-content .tab .list').css('height', height + height2 - 41 + 'px');
    });
    $('.tab').mouseleave(function(event) {
        $('.baner-content .tab .list').hide();
        $('.tab .gengduo').show();
        $('.tab .more>ul').hide();
        $('.baner-content .tab .list').css('height', height - 41 + 'px');
    });
    var html = '';
    for (var i = 0; i < 8; i++) {
        html += '<li class="f-left">';
        html += ' <a href="">';
        html += '<div class="img">';
        html += '<img src="images/index1.jpg" alt="">';
        html += ' </div>';
        html += '<h3 class="o1">人文视野中的生态学</h3>';
        html += '  <p>观看：3836次</p>';
        html += ' <div id="new' + i + '"></div>';
        html += ' </a>';
        html += '</li>';
        $('.new-list').html(html);
    }
    layui.use('rate', function() {
        var rate = layui.rate;
        for (var i = 0; i < 8; i++) {
            var ins1 = rate.render({
                elem: '#new' + i + '', //绑定元素
                length: 5,
                value: 2.5,
                half: true,
                readonly: true,
                theme: '#EC6D3A',
                text: true,
                setText: function(value) {
                    var arrs = {
                        // '2': '极差',
                        // '4': '差',
                        // '6': '中等',
                        // '8': '好',
                        // '10': '非常好'
                    };
                    this.span.text(arrs[value] || (2 * value + "分"));
                }
            });
        }

    });
    var project = '';
    for (var j = 0; j < 7; j++) {
        project += '<li class="f-left">';
        project += '<a href="">';
        project += '<div class="img">';
		project += '<div class="hj-b">合集</div>';
        project += '<img src="images/index2.jpg" alt="">';
        project += '</div>';
        project += '<div class="info">';
        project += '<h3 class="o1">日本人与日本社会</h3>';
        project += '<p>观看：3784次</p>';
        project += '<p style="margin-top:5px; padding-bottom:8px;">共12个视频</p>';
        project += '</div>';
        project += '</a>';
        project += '</li>';
        $('.project-list').html(project);
    }
    /*layui.use('rate', function() {
        var rate = layui.rate;
        for (var j = 0; j < 7; j++) {
            var ins1 = rate.render({
                elem: '#project' + j + '', //绑定元素
                length: 5,
                value: 2.5,
                half: true,
                readonly: true,
                theme: '#EC6D3A',
                text: true,
                setText: function(value) {
                    var arrs = {
                        // '2': '极差',
                        // '4': '差',
                        // '6': '中等',
                        // '8': '好',
                        // '10': '非常好'
                    };
                    this.span.text(arrs[value] || (2 * value + "分"));
                }
            });
        }

    });*/
    var classic = '';
    for (var k = 0; k < 5; k++) {
        classic += '<li class="f-left">';
        classic += '<a href="">';
        classic += ' <div class="img">';
        classic += '<img src="images/index3.jpg" alt="">';
        classic += ' </div>';
        classic += '<div class="info">';
        classic += '<h3 class="o1">中华诗词之美</h3>';
        classic += ' <div>';
        classic += '<span>观看：3784次</span><span class="score">评分：9.8</span>';
        classic += '</div>';
        classic += '</div>';
        classic += '</a>';
        classic += '</li>';
        $('.classic-list').html(classic);
    }
    var myteacher = new Swiper('.teacher-swiper .swiper-container', {
        paginationClickable: true,
        slidesPerView: 7
    })
    $('.teacher-swiper .arrow-left').on('click', function(e) {
        e.preventDefault()
        myteacher.swipePrev()
    })
    $('.teacher-swiper .arrow-right').on('click', function(e) {
        e.preventDefault()
        myteacher.swipeNext()
    })
    $('.teacher-swiper .swiper-slide img').click(function() {
        $('.teacher-swiper .swiper-slide>div').removeClass('border50');
        var img = $(this).attr('src');
        $('.teacher-info img').attr('src', img);
        $(this).parent().addClass('border50');
    })
	//头部下拉
	$('.nike').mouseenter(function(){
		$('.menu-b').stop().slideDown();
	});
	$('.nike').mouseleave(function(){
		$('.menu-b').stop().slideUp();
	});
	//限制文本框
	//var cpLock = true;
//	$('.textarea').on({
//		compositionstart: function () {//中文输入开始
//			cpLock = false;
//		},
//		compositionend: function () {//中文输入结束
//			cpLock = true;
//		},
//		input: function () {//input框中的值发生变化
//		$('#evaluation-reply em').text(this.value.length+'/200');
//			if (cpLock){
//				if(this.value.length==200){
//					//$('#evaluation-reply em').html('<span style="color:red;">'+this.value.length+'/200</span>');
//					return layer.msg('字数不能超过200字');
//				}
//			}
//				
//		}
//	})
	//联系客服
	$('.right-side-lxkf').click(function(){
		$('.lyb-b').fadeIn();
	});
	$('.close-lyb').click(function(){
		$('.lyb-b').fadeOut();	
	});
	$( "#draggable" ).draggable();
	//关注我们
	$('.right-side-gzwm').mouseenter(function(){
		$('.ewm').fadeIn();
	});
	$('.right-side-gzwm').mouseleave(function(){
		$('.ewm').fadeOut();
	});
})

var vm = new Vue({
    el: '#app',
    data:{
        
    },
    created:function(){
        // $.ajax({
        //     url:'http://demo-mooc.com/api/proxy/user_login',
			// type:'get',
        //     data:{
			// 		center_user_id:'1',
			// 		nick_name:'昵称',
			// 		center_id:'14'
			// 	},
			// dataType:'json',
			// success: function(data){
			// 	console.log(data)
			// },
			// error: function(){
			// 	console.log(1)
			// }
        // })
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