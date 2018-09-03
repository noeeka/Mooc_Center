


//Regional开始
$(document).ready(function(){
    $(".Regional").click(function(){
        if ($('.grade-eject').hasClass('grade-w-roll')) {
            $('.grade-eject').removeClass('grade-w-roll');
            $('html,body').removeClass('ovfHiden');
            $('.zhezhao').hide();
        } else {
            $('.grade-eject').addClass('grade-w-roll');
            $('html,body').addClass('ovfHiden');
            $('.zhezhao').show();
        }
    });
});

$(document).ready(function(){
    $(".grade-w>li").click(function(){
        $(".grade-t")
            .css("left","33.48%")
    });
});

$(document).ready(function(){
    $(".grade-t>li").click(function(){
        $(".grade-s")
            .css("left","66.96%")
    });
});

//Brand开始

$(document).ready(function(){
    $(".Brand").click(function(){
        if ($('.Category-eject').hasClass('grade-w-roll')) {
            $('.Category-eject').removeClass('grade-w-roll');
            $('html,body').removeClass('ovfHiden');
            $('.zhezhao').hide();
        } else {
            $('.Category-eject').addClass('grade-w-roll');
            $('html,body').addClass('ovfHiden');
            $('.zhezhao').show();
        }
    });
});

$(document).ready(function(){
    $('body').on('click','.Category-w>li',function(){
        $(".Category-t")
            .css("left","40%")
        $('.Category-w').css({width:'40%'});
        $('.Category-t').css({width:'60%'});

    })
});

// $(document).ready(function(){
//     $(".Category-t>li").click(function(){
//         $(".Category-s")
//             .css("left","66.96%")
//     });
// });

//Sort开始

$(document).ready(function(){
    $(".Sort").click(function(){
        if ($('.Sort-eject').hasClass('grade-w-roll')) {
            $('.Sort-eject').removeClass('grade-w-roll');
            $('html,body').removeClass('ovfHiden');
            $('.zhezhao').hide();
        } else {
            $('.Sort-eject').addClass('grade-w-roll');
            $('html,body').addClass('ovfHiden');
            $('.zhezhao').show();
        }
    });
});
$(document).ready(function(){
    $(".paixu").click(function(){
        if ($('.paixu-eject').hasClass('grade-w-roll')) {
            $('.paixu-eject').removeClass('grade-w-roll');
            $('html,body').removeClass('ovfHiden');
            $('.zhezhao').hide();
        } else {
            $('.paixu-eject').addClass('grade-w-roll');
            $('html,body').addClass('ovfHiden');
            $('.zhezhao').show();
        }
    });
});
//判断页面是否有弹出

$(document).ready(function(){
    $(".Regional").click(function(){
        if ($('.Category-eject').hasClass('grade-w-roll')){
            $('.Category-eject').removeClass('grade-w-roll');
        };
    });
});
$(document).ready(function(){
    $(".Regional").click(function(){
        if ($('.Sort-eject').hasClass('grade-w-roll')){
            $('.Sort-eject').removeClass('grade-w-roll');
        };
        if ($('.paixu-eject').hasClass('grade-w-roll')){
            $('.paixu-eject').removeClass('grade-w-roll');
        };
    });
});
$(document).ready(function(){
    $(".Brand").click(function(){
        if ($('.Sort-eject').hasClass('grade-w-roll')){
            $('.Sort-eject').removeClass('grade-w-roll');
        };
    });
});
$(document).ready(function(){
    $(".Brand").click(function(){
        if ($('.grade-eject').hasClass('grade-w-roll')){
            $('.grade-eject').removeClass('grade-w-roll');
        };
        if ($('.paixu-eject').hasClass('grade-w-roll')){
            $('.paixu-eject').removeClass('grade-w-roll');
        };
    });
});
$(document).ready(function(){
    $(".Sort").click(function(){
        if ($('.Category-eject').hasClass('grade-w-roll')){
            $('.Category-eject').removeClass('grade-w-roll');
        };
    });
});
$(document).ready(function(){
    $(".Sort").click(function(){
        if ($('.grade-eject').hasClass('grade-w-roll')){
            $('.grade-eject').removeClass('grade-w-roll');
        };
        if ($('.paixu-eject').hasClass('grade-w-roll')){
            $('.paixu-eject').removeClass('grade-w-roll');
        };

    });
});
$(document).ready(function(){
    $(".paixu").click(function(){
        if ($('.Category-eject').hasClass('grade-w-roll')){
            $('.Category-eject').removeClass('grade-w-roll');
        };
        if ($('.grade-eject').hasClass('grade-w-roll')){
            $('.grade-eject').removeClass('grade-w-roll');
        };
        if ($('.Sort-eject').hasClass('grade-w-roll')){
            $('.Sort-eject').removeClass('grade-w-roll');
        };

    });
});


//js点击事件监听开始
function grade1(wbj){
    var arr = document.getElementById("gradew").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.borderBottom = "";
    };
    wbj.style.borderBottom = "solid 1px #3F97F0"
}

function gradet(tbj){
    var arr = document.getElementById("gradet").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.background = "";
    };
    tbj.style.background = "#fff"
}

function grades(sbj){
    var arr = document.getElementById("grades").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.borderBottom = "";
    };
    sbj.style.borderBottom = "solid 1px #3F97F0"
}

function Categorytw(wbj){
    var arr = document.getElementById("Categorytw").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.background = "";
    };
    wbj.style.background = "#eee"
}

function Categoryt(tbj){
    var arr = document.getElementById("Categoryt").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.borderBottom = "";
    };
    // tbj.style.background = "#fff"
    tbj.style.borderBottom = "solid 1px #3F97F0"
}

function Categorys(sbj){
    var arr = document.getElementById("Categorys").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.borderBottom = "";
    };
    sbj.style.borderBottom = "solid 1px #3F97F0"
}

function Sorts(sbj){
    var arr = document.getElementById("Sort-Sort").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.borderBottom = "";
    };
    sbj.style.borderBottom = "solid 1px #3F97F0"
}
function paixus(sbj){
    var arr = document.getElementById("paixu-paixu").getElementsByTagName("li");
    for (var i = 0; i < arr.length; i++){
        var a = arr[i];
        a.style.borderBottom = "";
    };
    sbj.style.borderBottom = "solid 1px #3F97F0"
}

// 补充
//点击具体内容相应显示在screenTop
$('.grade-eject').on('click','.grade-w li',function(){
    $('.reservationHot span').text($(this).text());
    if ($('.grade-eject').hasClass('grade-w-roll')){
        $('.grade-eject').removeClass('grade-w-roll');
        $('html,body').removeClass('ovfHiden');
        $('.zhezhao').hide();
    };
})
$('.Category-eject').on('click','.Category-t li',function(){
    $('.region span').text($(this).text());
    if ($('.Category-eject').hasClass('grade-w-roll')){
        $('.Category-eject').removeClass('grade-w-roll');
        $('html,body').removeClass('ovfHiden');
        $('.zhezhao').hide();
    };
})
$('.Sort-eject').on('click','.Sort-Sort li',function(){
    $('.reservationType span').text($(this).text());
    if ($('.Sort-eject').hasClass('grade-w-roll')){
        $('.Sort-eject').removeClass('grade-w-roll');
        $('html,body').removeClass('ovfHiden');
        $('.zhezhao').hide();
    };
})
$('.paixu-eject').on('click','.paixu-paixu li',function(){
    $('.paixu span').text($(this).text());
    if ($('.paixu-eject').hasClass('grade-w-roll')){
        $('.paixu-eject').removeClass('grade-w-roll');
        $('html,body').removeClass('ovfHiden');
        $('.zhezhao').hide();
    };
})

//遮罩
  var zhezhaow = $(window).width();
// console.log($('.Category-eject').height());
  var zhezhaoh = $(window).height() - $('.returnHeader').height() - $('.classification').height();
  $('.zhezhao').css({width:zhezhaow,height:zhezhaoh});
  $('.zhezhao').click(function(){
      $('.grade-eject').removeClass('grade-w-roll');
      $('.Category-eject').removeClass('grade-w-roll');
      $('.Sort-eject').removeClass('grade-w-roll');
      $('.paixu-eject').removeClass('grade-w-roll');
      $('.zhezhao').hide();
      $('html,body').removeClass('ovfHiden');
  })