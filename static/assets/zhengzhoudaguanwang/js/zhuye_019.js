



    var mySwiper13 = new Swiper('.swiper-container13', {
        slidesPerView: 1,
        spaceBetween: 500,
        effect: 'slide',
        autoplay: false,
        speed: 300,
        loop: false,
        on: {
            slideChangeTransitionStart: function () {

                let i = this.activeIndex;

                if (i == 0) {

                    $(".part019_top_right_slider").css("left", "0");

                } else if (i == 1) {

                    $(".part019_top_right_slider").css("left", "0.95rem");

                }

                $(".part019_top_right_block ul li").eq(i).addClass("part019_top_right_block_active").siblings().removeClass("part019_top_right_block_active");

            },
        }



    })



    $(function () {

        $(".part019_top_right_block ul li").mousemove(function () {

            var i = $(this).index();

            if (i == 0) {

                $(".part019_top_right_slider").css("left", "0");



            } else if (i == 1) {


                $(".part019_top_right_slider").css("left", "0.95rem");

            }


            $(this).addClass("part019_top_right_block_active").siblings().removeClass("part019_top_right_block_active");


            mySwiper13.slideTo(i);


        })


    })


