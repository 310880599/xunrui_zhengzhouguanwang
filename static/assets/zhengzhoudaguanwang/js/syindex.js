$(function(){
     // 首页banner
	var swiper = new Swiper('.sy_banner', {
		spaceBetween: 30,
		centeredSlides: true,
		loop: true,
		autoplay: {
			delay: 3000,
			disableOnInteraction: false,
		},
		pagination: {
			el: '.sy_pag',
			clickable: true,
		},
		navigation: {
			nextEl: '.sy_next',
			prevEl: '.sy_prev',
		},
	});




	$(".main_visual").hover(function(){
		$("#btn_prev,#btn_next").fadeIn()
	},function(){
		$("#btn_prev,#btn_next").fadeOut()
	});
	$dragBln = false;
	$(".main_image").touchSlider({
		flexible : true,
		speed : 200,
		btn_prev : $("#btn_prev"),
		btn_next : $("#btn_next"),
		paging : $(".flicking_con a"),
		counter : function (e){
			$(".flicking_con a").removeClass("on").eq(e.current-1).addClass("on");
		}
	});
	$(".main_image").bind("mousedown", function() {
		$dragBln = false;
	});
	$(".main_image").bind("dragstart", function() {
		$dragBln = true;
	});
//	$(".main_image a").click(function(){
//		console.log($dragBln);
//		if($dragBln) {
//			return false;
//		}
//	});
	timer = setInterval(function(){
		$("#btn_next").click();
	}, 5000);
	$(".main_visual").hover(function(){
		clearInterval(timer);
	},function(){
		timer = setInterval(function(){
			$("#btn_next").click();
		},5000);
	});
	$(".main_image").bind("touchstart",function(){
		clearInterval(timer);
	}).bind("touchend", function(){
		timer = setInterval(function(){
			$("#btn_next").click();
		}, 5000);
	});
	$("#dnav li").click(function(){
			$(this).addClass("currents").siblings().removeClass("currents");
			var n=$(this).index();
			$(".textsbox1").eq(n).addClass("display").siblings().removeClass("display");
	});
	$("#dnav2 dd").click(function(){
			$(this).addClass("currents").siblings().removeClass("currents");
			var n=$(this).index();
			$(".textsbox2").eq(n).addClass("display").siblings().removeClass("display");
	});
	$(".img").width($(".img .pros").length*192);
	$(".btn1").click(function(){
	$(".img").animate({marginLeft:-192},1000,function(){
		$(".img").css({marginLeft:0});
		$(".img .pros:first").insertAfter(".img .pros:last");
	});	
	});

	$(".btn2").click(function(){
	$(".img").animate({marginLeft:192},1000,function(){
		$(".img").css({marginLeft:0});
		$(".img .pros:last").insertBefore(".img .pros:first");
	});	
	});
	
/*$('.navbox li').eq(0).addClass('navliadd');
navsonlist(2,'.navsonul1','.nvasonbg1');
navsonlist(7,'.navsonul2','.nvasonbg2'); 
navsonlist(9,'.navsonul3','.nvasonbg3');   
function navsonlist(n,obj,box){
	$('.navbox li').eq(n).hover(function(){
		$(box).stop(true).animate({'height':'118px'},300);
		$(obj).show();
	},function(){
		$(box).stop(true).animate({'height':0},300,function(){
			$(obj).hide();
		});
	});

	$(box).hover(function(){
		$(box).stop(true).animate({'height':'118px'},300);
		$(obj).show();
	},function(){
		$(box).stop(true).animate({'height':0},300,function(){
			$(obj).hide();
		});
	});
};*/


function _confirm1() {
	mizhu.confirm('', '是否要取消关注？', function(flag) {
		if(flag) {
			mizhu.alert('', '取消成功');
		};
	});
};
function _confirm2() {
	mizhu.confirm('温馨提醒', '是否要取消关注？', function(flag) {
		if(flag) {
			mizhu.alert('', '取消成功');
		};
	});
};  

// $('.xxknav dd').hover(function(){
// 	var _this=$(this).index();
// 	$('.xxtubox li').eq(_this).show().siblings().hide();
// 	$(this).find('.sanjiaoimg').css({'visibility':'visible'}).parent().siblings().find('.sanjiaoimg').css({'visibility':'hidden'});
// 	$(this).find('p').addClass('sanjiao').parent().siblings().find('p').removeClass('sanjiao');
// })


// $('.taocan li').hover(function(){
// 	var this_=this;
// 	var tcliw=$(this).find('.divbor').width()/2;
// 	var tclih=$(this).find('.divbor').height()/2;
// 	$(this).find('.tclibg').css({'visibility':'visible'});
// 	$(this).find('.divbor').css({'marginTop':-tclih,'marginLeft':-tcliw});
// 	$(this).find('.xiantop').stop(true).animate({'width':'100%'},200,function(){
// 		$(this_).find('.xianright').stop(true).animate({'height':'100%'},200);
// 	});
// 	$(this).find('.xianleft').stop(true).animate({'height':'100%'},200,function(){
// 		$(this_).find('.xianbottom').stop(true).animate({'width':'100%'},200);
// 	});
// 	$(this).find('.tclibtext').stop(true).animate({'bottom':'-30px'},10);
// },function(){
// 	var this_=this;
// 	$(this).find('.xiantop').stop(true).animate({'width':0},200,function(){
// 		$(this_).find('.xianright').stop(true).animate({'height':0},200);
// 	});
// 	$(this).find('.xianleft').stop(true).animate({'height':0},200,function(){
// 		$(this_).find('.xianbottom').stop(true).animate({'width':0},200);
// 	});
// 	$(this).find('.tclibg').css({'visibility':'hidden'});
// 	$(this).find('.tclibtext').stop(true).animate({'bottom':0},300);
// });


var obj=$('.mincfzs');
var t=obj.offset().top-300;
$(window).scroll(function(){
	if($(window).scrollTop()>=t){
		shu('#shuzi1',5000,10000,100);
		shu('#shuzi2',1,26,1);
		shu('#shuzi3',1,10,1);
		shu('#shuzi4',50,300,15);
		shu('#shuzi5',1,30,2);
		t=999999999999;
	};
});

function shu(obj,y,x,n){
	var n=n;
var y=y;
	var x=x;
	var strtext='';
	var time=setInterval(function(){
		if(y<x){
			y+=n;
		};
		if(y<10){
				$(obj).html('0'+y);
			}else{
				$(obj).html(y);
			};

	},100);
	
};

setInterval(function(){
	clf=Math.round(Math.random()*(120000-30000)+60000);
	rgf=Math.round(Math.random()*(35000-30000)+100);
	sjf=Math.round(Math.random()*(3500-2000)+2000);
	glf=Math.round(Math.random()*(10000-5000)+300);
	ysf=Math.round(Math.random()*(120000-30000)+75000);
	$('#clf').html(clf);
	$('#rgf').html(rgf);
	$('#sjf').html(sjf);
	$('#glf').html(glf);
	$('#ysf').html(ysf);
},280);

$('#liucul li').hover(function(){
	$(this).find('img').stop(true).animate({'width':'46px'},300).find('p').stop(true).animate({'fontSize':'20px'},150);	
},function(){
	$(this).find('img').stop(true).animate({'width':'42px'},300).find('p').stop(true).animate({'fontSize':'18px'},150)
});
$('.minnavbox li .mintopnav').addClass('bounceInDown wow animated');
$('.biaoti1,.biaoti2').addClass('wow fadeInUp animated');
$('.xxktextico').addClass('wow animated tada');
$('.taocan_ckxq').addClass('wow animated tada');
$('.liucul li,.liucxiao').addClass('wow fadeInUp animated');
$('.playgd li').addClass('wow bounceInRight animated');

submitclick('#sytopsubmit','#xingming','#tell','#bjformbox1');
	function submitclick(obj,name,tel,objr){
		$(obj).click(function(){
			 if($(this).parent().siblings().find(name).val()==""){
				$(this).parent().siblings().find(name).focus();
				alert("用户名不能为空！");
				return false;
			}else if($(this).parent().siblings().find(tel).val()=="") {
				$(this).parent().siblings().find(tel).focus();
				alert("手机号码不能为空！");
				return false;
			}else if(!(/^1[3578]{1}[0-9]{9}$/.test($(this).parent().siblings().find(tel).val()))){
				$(this).parent().siblings().find(tel).focus();
				alert("手机号码输入有误！");
				return false;
			}else{
				add_ajaxmessage3(objr);	
				setTimeout(function(){
					$('#yuyuebmbox').fadeOut(300);
				},300);
			};
		});
};

function add_ajaxmessage3(objr){
	$.ajax({
		type: "POST",
		url: "/baoming",
		data: $(objr).serialize(),
		success: function(data) {
		    console.log(data.error)
		    if(data.error==-1){
		         swal({title: '',text: '您已提交过！',type:'warning',timer: 2000});
		    }else{
    			window._agl && window._agl.push(['track', ['success', {t: 3}]]);
    			swal({title:"Good!", text:"成功提交，稍后客服会联系你，请保持电话畅通！", type:"success"},function(){
    $(objr)[0].reset();	})
		    }
		}
	});
	return false;
};





if (!(/msie [6|7|8|9]/i.test(navigator.userAgent))){
	new WOW().init();
};


})