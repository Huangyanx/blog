/**
 * Created by Administrator on 2019/3/29.
 */

$(document).ready(function () {
    //pre 的操作
    var tool="<div class='tool'><a href='javascript:;' class='Fullscreen' title='全屏'>全屏</a> <a href='javascript:;' class='copyCode' title='复制'>复制</a> </div>";
    var biao1="<textarea   id='biao1'></textarea>";
    if($(".blogitem article pre").get(0)){
        $(".blogitem article").append(tool,biao1);
        //显示 隐藏
        $(".blogitem article pre").mouseover(function () {
            var index=$(this).index('pre');
            var _top=this.offsetTop+15;
            $(".blogitem article .tool").css({
                top:_top,
                display:'block'
            }).data('index',index);

        }).mouseout(function (e) {
           if(!$(e.relatedTarget).hasClass('tool') && !$(e.relatedTarget).parent().hasClass('tool') ){
               $(".blogitem article .tool").hide();
           }
        });

        /*全屏操作*/
        $(".Fullscreen").click(function () {
            var h=window.innerHeight-60 ;//去掉padding、margin
            if($(this).data("full")){
                $($(".blogitem article pre").eq($(this).parent().data('index'))).css({
                    'width':'auto',
                    'position': 'relative',
                    'z-index':'',
                    'height':'150px',
                    'backgroundColor':'transparent'
                });
                $('body').height('');
                $(this).data("full",0);
            }else{
                $($(".blogitem article pre").eq($(this).parent().data('index'))).css({
                    'width':'98%',
                    'height':h,
                    'position': 'fixed',
                    'left': '0',
                    'top':0,
                    'z-index':'99',
                    'backgroundColor':'#f4ede5'

                });
                $('body').height(h);
                $(this).data("full",1);
            }

        });
        //复制操作
        $('.blogitem article .copyCode').click(function () {
            Url2=$($(".blogitem article pre").eq($(this).parent().data('index'))).html();
            $('#biao1').val(Url2);
            $('#biao1').get(0).select();
            document.execCommand("Copy"); // 执行浏览器复制命令
            alert('复制成功');
        })


    }

    var cur_id=$(".thumbs-up .spot").data("id");
    var head_imgs = localStorage.getItem("head_imgs") || 0;
    var has_submit=false;
    /*是否已经点赞*/
    var zan_name="thumbs-up_zan"+cur_id;
    var cai_name="thumbs-up_cai"+cur_id;
    var zan_num=getCookie(zan_name)|| 0;
    var cai_num=getCookie(cai_name)|| 0;
    if(zan_num==='2'){
        $('.thumbs-up .zan').addClass('active');
        $('.thumbs-up .zan i').data("spot",true);
    }
    if(cai_num==='2'){
        $('.thumbs-up .cai').addClass('active');
        $('.thumbs-up .cai i').data("spot",true);
    }


    /*获取评论列表*/
    get_comment_data();

    /*评论表赋值*/
    var cur_user=localStorage.getItem("cur_user")|| 0;
    if(cur_user){
        var cur_users=JSON.parse(cur_user);
        $('.comment-form .headimg_input').val(cur_users.headimg);
        var head_img='<img src="'+cur_users.headimg+'" alt="头像">';
        $('.comment-form .headimg_img').append(head_img);
        $('.comment-form .nickname').val(cur_users.nickname);
        $('.comment-form .email').val(cur_users.email);
        $('.comment-form .weburl').val(cur_users.weburl);
        $('.comment-form .userid').val(cur_users.userid);
    }


    //点赞 踩
    $('.thumbs-up .zan i').click(function () {
        spot(this,zan_name,'thumbs_up',"article");
    });
    $('.thumbs-up .cai i').click(function () {
        spot(this,cai_name,'step',"article");
    });

    function spot(obj,cookiename,filed,table) {
        var cur_num=$(obj).next("span").text();
        var id=$(obj).parent().next().data('id');
        if($(obj).data("spot")){
            cur_num-=1;
            $(obj).parent().removeClass("active");
            $(obj).data("spot",false);

        }else {
            //点赞
            cur_num++;
            $(obj).parent().addClass("active");
            $(obj).data("spot",true);
        }
        $(obj).next("span").text(cur_num);
        //保存该状态在浏览器
        if(cookiename!==''){
            setCookie(cookiename, 0,90,'/main/articleshow');
        }
        putdata(filed,cur_num,table,id);
    }
    function putdata(filed,cur_num,table,id) {
        if(typeof table==="undefined")  {table="article";}
        if(typeof id==="undefined" ||id==='') { id=cur_id;}
        $.post("/main/spot",{'table':table,"id":id,"field":filed,"value":cur_num,'action':'changespot'},function (rtn) {
            if(rtn.status==1){
                console.log('点赞成功');
            }

        },'json');
    }

    $(".choice_img").click(function (e) {
        /*获取头像*/
        if(!head_imgs){
            if(!$(this).data("get")){
                var data=get_head_img();
                head_img_set(data);
                $(this).data("get",true);
            }
        }else {
            if(!$(this).data("get")){
                head_img_set(JSON.parse(head_imgs));
                !$(this).data("get",true);
            }

        }
        $(".head_img_list").slideToggle();
        e.stopPropagation();

    });
    function head_img_set(data) {
        var imglist="";
        var len=data.length;
        for (var i=0;i<len;i++){
            imglist+="<div class='col-31'> <img src='"+data[i]['url']+"' data-id='"+data[i]['id']+"'/><input type='hidden' name='head_imgs[]' value='"+data[i]['url']+"'></div>";
        }
        imglist+="<div class='clear'></div>";
        $(".head_img_list").append(imglist);
        /*img绑定事件*/
        var btn = $(".head_img_list img");
        if (btn) btn.bind("click",function () {
            checkimg(this);
        });
    }
    /*选中头像*/
    function checkimg(obj) {
        /*当前图片高亮*/
        $(".head_img_list img").removeClass("active");
        $(obj).addClass("active");
        var img_id=$(obj).attr("src");
        $('.headimg_input').val(img_id);
        $('.comment-form .headimg_img img').attr("src",img_id);
    }


    /*列出评论*/
    function comment_put_dom(data,pagelist) {
        var html="";
        var len=data.length;
        if(len===0){
            $('.comment-list ul').append("<p style='text-align: center;'>暂无评论</p>");
        }
        for (var i=0;i<len;i++){
            html+=comment_dom(data[i]);

        }
        $('.comment-list ul').html(html);
        if(pagelist[0]>1){
            var page='<div class="comment_page pages"><a class="nextpage" href="javascript:;" data-pages="'+pagelist[0]+'">下一页</a></div>';
        }else {
            var page='<div class="comment_page pages">共'+pagelist[1]+'条主评论</div>';
        }
        $('.comment-list ').append(page);
        //获取下一页事件     /*---因为无法传递html标签  弄成事件触发*/
        $(".nextpage").bind("click",function () {
            nextPage(2,0,'.comment-list',true);
        });

        //bindEven($('.comment-list ul'));
    }
    /*回复评论的渲染*/
    function second_comment_dom(val) {
        var  html="<ul class='sec_pl'>";
        //console.log(val);
        var len1=val.length;
        for (var j=0; j<len1; j++){
            var img_url1=val[j]['headimg'];
            var weburl=val[j]['weburl']==='' ? "javascript:void(0);":val[j]['weburl'];
            var posttime=val[j]['posttime'];
            var time= php_to_js_time(posttime);
            html+="<li class='comment_one comment_one_"+val[j]['id']+"'><div class='img'><img src='"+img_url1+"'></div>"+
                "<div class='comment_text'><div class='main'><div class='userinfos'><strong><a href='"+weburl+"' target='_blank'> "+
                val[j]['nickname']+"</a></strong><span class='comment_time'>"+time+"</span></div><div class='txt'>"+val[j]['content']+
                "</div><div class='spot'><span class='replay_list' data-id='"+val[j]['id']+"'>回复- <i>"+val[j]['comment_nums']+"</i></span> <i class='iconfont icon-zan-empay zan' title='点赞'></i><span>"+val[j]['thumbs_up']+
                "</span><i class='iconfont icon-cai cai' title='踩'></i><span>"+val[j]['step']+
                "</span></div><div class='comment_btn' data-nickname='"+val[j]['nickname']+"' data-id='"+val[j]['id']+"'>回复</div></div> </div><div class='clear'></div>";
            /* if(val[j]['childid']!==''){
             html+=second_comment_dom(val[j]['child']);
             }*/
            html+="</li>";
        }
        html+="</ul>";
        return html;

    }

    /*提交评论*/
    $('.comment-form .form-submit').click(function () {
        if(!has_submit){
            var content=$('.comment-form .content').val();
            var nickname=$('.comment-form .nickname').val();
            var email=$('.comment-form .email').val();
            var replayid=$(".comment-form input[name='replayid']").val();
            if(content==='' && nickname==='' && email===''){
                alert('请填写完整信息！');
            }else {
                if( ismail(email)){
                    var form_val=$('.comment-form').serialize();
                    form_val+="&action=add&table=1&tid="+cur_id;
                    has_submit=true;
                    $.post("/main/comment",form_val,function (rtn) {
                        has_submit=false;
                        if(rtn.status==2){
                            var data=rtn.data;
                            alert("评论成功");
                            $('.comment-form .content').val('');
                            var cur_user={};
                            cur_user.userid=data.userid;
                            cur_user.headimg=data.headimg;
                            cur_user.nickname=nickname;
                            cur_user.weburl=$('.comment-form .weburl').val();
                            cur_user.email=email;
                            var cur_users=JSON.stringify(cur_user);
                            localStorage.setItem("cur_user", cur_users);
                            $('.headimg_input').val(data.headimg);
                            /*内容加在评论列表上*/
                            if(replayid!==0 && replayid!=='0'){
                                var html='<ul class="sec_pl">';
                                html+=comment_dom(data);
                                html+="</ul>";
                                $(".comment_one_"+replayid).append(html);
                            }else {
                                var html=comment_dom(data);
                                $('.comment-list ul').prepend(html);
                            }
                            //评论成功后进行发送邮件
                            var t=$("h2.title").text();
                            //var random_comment=$('.random_comment').val();
                            var sendmail="action=sendmail&curarticle="+t+"&comment_id="+data.id;
                            $.post("/main/comment",sendmail,function (rtn) {
                                if(rtn.status){

                                    console.log(rtn.msg);
                                }
                            },'json');

                        }else if(rtn.status==3){
                            alert(rtn.msg);
                        }

                    },'json');

                }else {
                    alert('邮箱格式不正确！');

                }
            }

        }


    });

    function get_head_img() {
        $.post("/main/spot",{'action':'getheadimg'},function (rtn) {
            if(rtn.status==2){
                var data=rtn.data;
                var datas=JSON.stringify(data);
                head_imgs=datas;
                localStorage.setItem("head_imgs", datas);
                return data;
            }
        },'json');
    }

    /*是邮箱*/
    function ismail(s) {
        var patrn=/^[A-Za-z0-9\u4e00-\u9fa5]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/;
        if (!patrn.exec(s)) return false
        return true
    }

    function replay(id,nickname) {
        //var comment_form_top=$(".comment_wrap .comment-form").scrollTop();
        var comment_form_top=$(".comment_wrap").offset().top;
        $("body,html").animate({scrollTop:comment_form_top},1000);
        $('.comment-form .replayid').show();
        $('.comment-form .replayid .nickname_p').text(nickname);
        $('.comment-form .replayid input').val(id);

    }

    /*获取评论数据*/
    function get_comment_data(page) {
        var data={};
        data.action="list";
        data.tid=cur_id;
        data.tableid=1;
        if(typeof(page) !=="undefined"||page!==''||page!==undefined ){
            data.page=page;
        }
        $.post("/main/comment",data,function (rtn) {
            if(rtn.status==2){
                console.log(rtn);
                comment_put_dom(rtn.data,rtn.pagelist);
            }

        },'json');
    }

    //php时间格式化
    function php_to_js_time(posttime) {
        var date=new Date(posttime*1000+28800);
        Y = date.getFullYear() + '-';
        M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
        D = date.getDate() + ' ';
        H = date.getHours() + ':';
        I = date.getMinutes() + ':';
        S = date.getSeconds() + ' ';
        return  Y+M+D+H+I+S;
    }


    //$(".choice_img").trigger('click');

    function get_more_comment(rid,obj,page,is_first) {
        if(typeof page==="undefined") page=1;
        var nextpage=page+1;
        var data={};
        data.action="list";
        data.tid=cur_id;
        data.tableid=1;
        data.rid=rid;
        data.page=page;
        $.post("/main/comment",data,function (rtn) {
            if(rtn.status==2){
                var pagelist=rtn.pagelist;
                if(is_first){
                   var  render=""
                    var len=rtn.data.length;
                    for (var i=0;i<len;i++){
                        render+=comment_dom(rtn.data[i]);

                    }
                    $(obj).append(render);
                    var page_wrap=$(obj);

                }else {
                    var render=second_comment_dom(rtn.data);
                    $(obj).data("has_req",true);
                    $(obj).parents("li:eq(0)").append(render);
                    var page_wrap=$(obj).parents("li:eq(0)");
                }
                if(pagelist[0]>page){
                    var pages='<div class="comment_page"><a class="nextpage_'+rid+'" href="javascript:;"  data-pages="'+pagelist[0]+'">下一页</a></div>';
                }else if(pagelist[0]===page){
                    var pages='<div class="comment_page ">最后一页</div>';
                }
                page_wrap.remove(".comment_page");
                page_wrap.append(pages);
                //获取下一页事件     /*---因为无法传递html标签  弄成事件触发*/
                $(".nextpage_"+rid).bind("click",function () {
                    nextPage(nextpage,rid,obj,false);
                });
                // bindEven($(obj).parents("li:eq(0)").find(".sec_pl"));
            }

        },'json');
    }
    /*通过js添加的标签另外绑定事件---出现执行多次--原因是绑定多次-------*/
    $(document).on("click",".comment-list .comment_btn",function () {

        replay($(this).data('id'),$(this).data('nickname'));
    });
    $(document).on("click",".spot .zan",function () {
        spot(this,'','thumbs_up',"comment");
    })
    $(document).on("click",".spot .cai",function () {
        spot(this,'','step',"comment");

    })
    $(document).on("click",".replay_list",function () {
        if($(this).data("has_req") && $(this).find("i").text()>0){
            $(this).parents("li:eq(0)").find(".sec_pl:eq(0)").slideToggle();
        }else if(!$(this).data("has_req") && $(this).find("i").text()>0){
            get_more_comment($(this).data('id'),this);
        }

    });


   function nextPage(page,rid,obj,is_first) {
       if(typeof rid==="undefined") rid=0;
       if(typeof obj==="undefined") obj=".comment-list";
       get_more_comment(rid,obj,page,is_first);
   }

    function comment_dom(val) {
        var html='';
        var img_url=val['headimg'];
        var weburl=val['weburl']==='' ? "javascript:void(0);":val['weburl'];
        var posttime=val['posttime'];  //php与js时间戳相差 time() = Math.round(new Date().getTime()/1000-28800)
        var time= php_to_js_time(posttime);
        var comment_nums= typeof val['comment_nums'] ==="undefined" ? 0:val['comment_nums'];

        //php与js时间戳相差 time() = Math.round(new Date().getTime()/1000-28800)
        html+="<li class='comment_one comment_one_"+val['id']+" '  ><div class='img'><img src='"+img_url+"'></div>"+
            "<div class='comment_text'><div class='main'><div class='userinfos'><strong><a href='"+weburl+"' target='_blank'> "+
            val['nickname']+"</a></strong><span class='comment_time'>"+time+"</span></div><div class='txt'>"+val['content']+
            "</div><div class='spot'><span class='replay_list' data-id='"+val['id']+"'>回复- <i>"+comment_nums+"</i></span> <i class='iconfont icon-zan-empay zan' title='点赞'></i><span>"+val['thumbs_up']+
            "</span><i class='iconfont icon-cai cai' title='点赞'></i><span>"+val['step']+
            "</span></div><div class='comment_btn' data-nickname='"+val['nickname']+"' data-id='"+val['id']+"'> 回复 </div> </div> </div><div class='clear'></div>";

        return html;
    }



});
