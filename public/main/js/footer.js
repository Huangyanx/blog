/**
 * Created by Administrator on 2019/3/29.
 */
/*手机端导航栏二级菜单按钮*/
var w_w=$(window).width();

if(w_w<=900){
    $('#nav .secondMenu').before('<i class="iconfont icon-xiangyou sencond-btn"></i>');
    /*二级菜单显示隐藏*/
    $('#nav .sencond-btn').click(function (e) {
        if($(this).hasClass('icon-xiangyou')){
            $(this).addClass('icon-xiangzuo1').removeClass('icon-xiangyou');
        }else {
            $(this).addClass('icon-xiangyou').removeClass('icon-xiangzuo1');
        }
        $(this).next('.secondMenu').slideToggle();
        e.stopPropagation();
    });
}


/*手机端导航栏显示隐藏*/
var mobile_show=false;
var wrap_c=false;  //允许点击其他地方 也关闭
$('.mobile_wrap').click(function (e) {
    nav_s_h();
    if(mobile_show){
        /*其他地方点击也关闭*/
        $("body").not("#nav").not('.mobile_wrap').click(function (even) {
            /*出现两次触发的情况*/
            if(wrap_c){
                nav_s_h();
                even.stopPropagation();
            }
        });
    }
    e.stopPropagation();
});

function nav_s_h() {
    if($('.mobile_wrap i').hasClass("icon-iconmore")){
        $('.mobile_wrap i').addClass('icon-weibiao45133');
        $('.mobile_wrap i').removeClass('icon-iconmore');
        mobile_show=true;
        wrap_c=true;
    }else{
        $('.mobile_wrap i').removeClass('icon-weibiao45133');
        $('.mobile_wrap i').addClass('icon-iconmore');
        wrap_c=false;
        mobile_show=false;
    }
    $("#nav").slideToggle();
}

/*滚动事件*/
//窗口高度
var w_h=$(window).height();
var w_h_half=w_h/2;
var header_h=80;
$(window).scroll(function () {
    /*置顶图标显示*/
    var cur_h=$(document).scrollTop();
    if (cur_h>w_h_half){
        $("#goback").fadeIn();
    }else {
        $("#goback").fadeOut();
    }
    /*导航栏*/
    if(cur_h>header_h){
        $('#header').addClass("atop");
    }else {
        $('#header').removeClass("atop");
    }

});
/*置顶*/
$("#goback").click(function () {
    $('html,body').animate({scrollTop: '0px'}, 800);
});
