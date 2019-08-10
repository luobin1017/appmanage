/**
 * Created by Administrator on 2019/5/23.
 */
$(document).ready(function(){
    $('.drag_deps-1').hover(function(){
        $(".deps-fo-1").css({"background":"rgba(255,255,255,0.5)","box-shadow": "rgba(255,255,255,0.5) 0px 0px 7px 2px"});
    },function(){
        $(".deps-fo-1").css({"background-color":"#18191e","box-shadow":""});
    });
    $('.drag_deps-2').hover(function(){
        $(".deps-fo-2").css({"background":"rgba(255,255,255,0.5)","box-shadow": "rgba(255,255,255,0.5) 0px 0px 7px 2px"});
    },function(){
        $(".deps-fo-2").css({"background-color":"#18191e","box-shadow":""});
    });
    $('.drag_deps-3').hover(function(){
        $(".deps-fo-3").css({"background":"rgba(255,255,255,0.5)","box-shadow": "rgba(255,255,255,0.5) 0px 0px 7px 2px"});
    },function(){
        $(".deps-fo-3").css({"background-color":"#18191e","box-shadow":""});
    });
});