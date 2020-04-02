/**
 * Created by Administrator on 2019/8/22.
 */

    var belong="<?php echo $belong; ?>";
    var mid="<?php echo $mid ?>";


    //获取内容
    function getcontent(id){
        //console.log(id);
        $('.lists').html('').animate({height:'toggle'},50);
        $.post("/index.php?c=manual&a=show", {id: id}, function (res) {
            setCookie('huangyanx'+belong+'Id',id,1,'/main/manual/');
            var title=res.data.name;
            var content=res.data.content;
            // var remark=res.data.remark;
            $('.lists-title a').text(title);
            $('.lists').html(content).animate({height:'toggle'},500);
        },'json');
    }
    $('#manual_left .keyword').keyup(function (e) {
        if(e.keyCode ==13){
            $('.search-btn').trigger("click");
        }
    })
    $('.search-btn').click(function () {
        var keyword=$.trim($('.keyword').val());
        var belong=$('.search-belong').val();
        var len=keyword.length;
        if(keyword===''){
            alert('请输入关键字');
            return false;
        }
      /*  if(len<2){
            alert('关键字至少为2个');
            return false;
        }*/
        $.ajax({
            type:'post',
            url:'/index.php?c=manual&a=search',
            data:{keyword:keyword,belong:belong},
            success:function (rtn) {
                if(rtn.status===undefined){
                    rtn=JSON.parse(rtn);
                }
                if(rtn.data.res[0]!== undefined && rtn.data.res[0]!==''){
                    var li="";
                    // result
                    $.each(rtn.data.res,function (i,val) {
                        var title=val['name'].replace(new RegExp(keyword),"<span class='red'>"+keyword+"</span>");
                        li+="<li class='result-li' onclick='javascript:getcontent("+val['id']+")'>"+title+"</li>";
                    });
                    $('.result ul').html(li);
                    /*var pages=rtn.data.num;
                     if(pages>1){
                     var p="";
                     for (var i=0;i<pages && i<10;i++){
                     p+="<span>"+i+"</span>";
                     }
                     $('.result').append(p);
                     }*/
                }else {
                    $('.result ul').html('无相关搜索');
                }



            },
            error:function (e) {
                alert('出错啦');
            }

        },'json');



    });

    //控制右边菜单高度以及 绝对定位
    var w_h=$(window).height();
    var w_w=$(window).width();
    var header_h=80;
    //树形结构高度 treeDemo
    var tree_h=w_h-63-220;

    $('#treeDemo').css('max-height',tree_h);
    var footer_t=$('footer').offset().top;
    $(window).scroll(function () {
        var cur_h=$(document).scrollTop();
        var aslide_h=$('#manual_left').height();
        //右边栏目
        if(cur_h>(footer_t-1000) && aslide_h>=700){
            $('#manual_left').removeClass('aside_fied');
        }else if(cur_h>header_h&&w_w>1200){
            /*   当前栏目宽度 27%   为除掉 margin宽度所占百分比，即 cur_w=w_w-(w_w-1200)*25%  的百分比
             */
            var mr=(w_w-1200);
            var cur_w=(w_w-mr)*0.25;
            $('#manual_left').addClass('aside_fied').css({'width':cur_w,right:mr/2});
        }else if(cur_h>header_h&&w_w>800){
            $('#manual_left').addClass('aside_fied').css({'width':'25%',right:10});
        } else{
            $('#manual_left').removeClass('aside_fied');
        }
    });


