$().ready(function() {
    //banner轮播

    var swiper = new Swiper('.swiper-container', {
        spaceBetween: 30,
        effect: 'slide',
        speed:300,
        loop: "true",
        slidesPerView:4,
        //centeredSlides:true,
        //centeredSlidesBounds: true,
        //slidesOffsetBefore : 210,
        normalizeSlideIndex:true,
        autoplay: {
            delay: 4000,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    })


    var swiper1 = new Swiper('.swiper-container1', {
        spaceBetween:10,
        effect: 'slide',
        speed: 2000,
        loop: "true",
        // slidesOffsetBefore :60,
        //slidesPerView:1.5,
        autoplay: {
            delay:2000,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    })


    var swiper2 = new Swiper('.swiper-container2', {
        spaceBetween: 30,
        effect: 'slide',
        speed: 1000,
        loop: "true",
        autoplay: {
            delay: 4000,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    })
	
	
	
	
	
	

})






