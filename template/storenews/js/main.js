// 初始化轮播图
var swiper = new Swiper('.swiper-container', {
    loop: true,
    autoplay: {
        delay: 4000,
        disableOnInteraction: false,
        pauseOnMouseEnter: true
    },
    effect: 'creative',
    creativeEffect: {
        prev: {
            shadow: true,
            translate: ['-120%', 0, -500],
            rotate: [0, 0, -15],
        },
        next: {
            shadow: true,
            translate: ['120%', 0, -500],
            rotate: [0, 0, 15],
        },
    },
    speed: 1000,
    grabCursor: true,
    watchSlidesProgress: true,
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
        bulletClass: 'swiper-pagination-bullet',
        bulletActiveClass: 'swiper-pagination-bullet-active',
        renderBullet: function (index, className) {
            return '<span class="' + className + '"></span>';
        }
    },
    on: {
        slideChangeTransitionStart: function() {
            var activeSlide = this.slides[this.activeIndex];
            $(activeSlide).find('img').css({
                'transform': 'scale(1.1)',
                'transition': 'all 0.8s ease'
            });
        },
        slideChangeTransitionEnd: function() {
            var slides = this.slides;
            $(slides).find('img').css({
                'transform': 'scale(1)',
                'transition': 'all 0.8s ease'
            });
        },
        touchStart: function() {
            var activeSlide = this.slides[this.activeIndex];
            $(activeSlide).find('img').css('transition', 'all 0.4s ease');
        },
        touchEnd: function() {
            var activeSlide = this.slides[this.activeIndex];
            $(activeSlide).find('img').css('transition', 'all 0.8s ease');
        }
    }
});

// 搜索功能
$(function(){
    function performSearch() {
        var keyword = $('#searchInput').val().trim();
        if(keyword) {
            // 保存搜索历史
            var history = localStorage.getItem('searchHistory') ? JSON.parse(localStorage.getItem('searchHistory')) : [];
            if(!history.includes(keyword)) {
                history.unshift(keyword);
                if(history.length > 10) history.pop();
                localStorage.setItem('searchHistory', JSON.stringify(history));
            }
            window.location.href = '?mod=search&kw=' + encodeURIComponent(keyword);
        }
    }

    $('#searchInput').on('keyup', function(e) {
        if(e.keyCode === 13) {
            performSearch();
        }
    });

    $('#searchBtn').on('click', function() {
        performSearch();
    });

    // 加载搜索历史
    function loadHistory() {
        var history = localStorage.getItem('searchHistory') ? JSON.parse(localStorage.getItem('searchHistory')) : [];
        var html = '';
        history.forEach(function(item) {
            html += '<li class="history-item" onclick="searchKeyword(\'' + item + '\')">';
            html += '<i class="fa fa-history"></i>';
            html += '<span>' + item + '</span>';
            html += '</li>';
        });
        $('#historyList').html(html);
    }

    // 清空搜索历史
    $('#clearHistory').on('click', function() {
        localStorage.removeItem('searchHistory');
        loadHistory();
    });

    loadHistory();

    // 展开/收起功能
    $('.more').click(function(e){
        e.preventDefault();
        var card = $(this).closest('.category-card');
        var content = card.find('.card-content');
        if(content.hasClass('collapsed')) {
            content.removeClass('collapsed').addClass('expanded');
            $(this).text('收起');
            card.find('.sub-category-item.hidden').fadeIn();
        } else {
            content.removeClass('expanded').addClass('collapsed');
            $(this).text('查看更多');
            card.find('.sub-category-item.hidden').fadeOut();
        }
    });
});

// 搜索关键词函数
function searchKeyword(keyword) {
    $('#searchInput').val(keyword);
    $('#searchBtn').click();
} 