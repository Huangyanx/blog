/**
 * Created by Administrator on 2019/8/9.
 */

jQuery.fn.anchorAnimate = function(settings) {
    settings = jQuery.extend({
        speed : 1100
    }, settings);
    var pre_a='';
    return this.each(function(){
        var caller = this;
        $(caller).click(function (event){
            //当前高亮
            $(pre_a).removeClass("active");
            $(caller).addClass("active");
            pre_a=caller;
            event.preventDefault();
            var locationHref = window.location.href;
            var elementClick = $(caller).attr("href");
            arr=elementClick.split("#");
            var destination = $("#"+arr[1]).offset().top - $('#header').outerHeight();
            $("html,body").stop().animate({ scrollTop: destination}, settings.speed, function(){
                // Detect if pushState is available
                if(history.pushState) {
                    history.pushState(null, null, elementClick);
                }
            });
            return false;
        });
    });
}
/*调用*/
/*左边栏目高度控制  */
    $("#website_left li a").anchorAnimate();
    var w_w=$(window).width();
    var w_h=$(window).height();
    var header_h=80;
    var aslide_h=w_h-header_h;
    $('#website_lefter').css('max-height',aslide_h);

$(window).scroll(function () {
    var cur_h=$(document).scrollTop();
    //左边栏目
    if(cur_h>header_h&&w_w>1200){
        /*   当前栏目宽度 27%   为除掉 margin宽度所占百分比，即 cur_w=w_w-(w_w-1200)*27%  的百分比
         */
        var cur_w=(w_w-(w_w-1200))*0.25;
        $('#website_lefter').addClass('aside_fied').css('width',cur_w);
    }else if(cur_h>header_h&&w_w>800){
        $('#website_lefter').addClass('aside_fied').css('width','25%');
    } else{
        $('#website_lefter').removeClass('aside_fied');
    }
});
