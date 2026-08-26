//商城模板首页方法 By 星河软件工作室
var chenmObj = {
    init: function (isModal, isModalType) {
        this.loadImg();
        this.showBanner1();
        this.showBanner2();
        this.showBanner3();
        this.showModal(isModal, isModalType);
    },
    showBanner1: function () {
        console.log($(".lb1 .swiper-wrapper .swiper-slide").length);
        if ($(".lb1 .swiper-wrapper .swiper-slide").length > 0) {
            new Swiper('.lb1', {
                direction: 'horizontal', // 垂直切换选项
                loop: true, // 循环模式选项
                autoplay: {
                    delay: 4000,
                    stopOnLastSlide: false,
                    disableOnInteraction: false,
                },
                // 如果需要分页器
                pagination: {
                    el: '.lb1 .swiper-pagination',
                    paginationClickable: true
                },
            })
        } else {
            $(".lb1").hide();
        }
    },
    showBanner2: function () {
        console.log($(".lb2 .swiper-wrapper ul").length);
        if ($(".lb2 .swiper-wrapper ul").length > 0) {
            new Swiper('.lb2', {
                direction: 'horizontal', // 垂直切换选项
                loop: true, // 循环模式选项
                autoplay: {
                    delay: 6000,
                    stopOnLastSlide: false,
                    disableOnInteraction: false,
                },
                // 如果需要分页器
                pagination: {
                    el: '.lb2 .swiper-pagination',
                    paginationType: 'fraction',
                    paginationClickable: true
                },
            });
        } else {
            $(".lb2").hide();
        }
    },
    showBanner3: function () {
        if ($(".lb3 .swiper-wrapper .swiper-slide").length > 0) {
            new Swiper('.lb3', {
                direction: 'vertical', // 垂直切换选项
                loop: true, // 循环模式选项
                autoplay: {
                    delay: 3000,
                    stopOnLastSlide: false,
                    disableOnInteraction: false,
                },
                // 如果需要分页器
                pagination: {
                    el: '.lb3 .swiper-pagination'
                },
            })
        } else {
            $(".lb3").hide();
        }
    },
    loadImg: function () {
        if (typeof ($("img.lazy").lazyload) == 'function' && $("img.lazy").length > 0) {
            $("img.lazy").lazyload({
                effect: "fadeIn"
            });
        }
    },
    showModal: function (isModal, isModalType) {
        if ($('#myModal').length > 0 && typeof (isModal) !== 'undefined' && isModal === true) {
            if (isModalType) {
                console.log('弹窗公告：始终弹出');
                $('#myModal').modal('show');
            } else {
                console.log('弹窗公告：一天一次');
                if (!$.cookie('op')) {
                    $('#myModal').modal('show');
                    var cookietime = new Date();
                    cookietime.setTime(cookietime.getTime() + (24 * 60 * 60 * 1000));
                    $.cookie('op', false, {
                        expires: cookietime
                    });
                }
            }
        }
    }
}
if (typeof (isModal) === 'undefined') {
    var isModal = false;
}
if (typeof (isModalType) === 'undefined') {
    var isModalType = 0;
}
chenmObj.init(isModal, isModalType);