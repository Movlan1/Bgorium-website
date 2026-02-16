// Swiper initialization
document.addEventListener('DOMContentLoaded', function () {
    // Main Slider - только для .mainSlider, не влияет на другие слайдеры
    const mainSliderElement = document.querySelector('.mainSlider');
    if (!mainSliderElement) return;

    const mainSlider = new Swiper('.mainSlider', {
        // Optional parameters
        direction: 'horizontal',
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        speed: 800,

        // Pagination - только внутри .mainSlider
        pagination: {
            el: mainSliderElement.querySelector('.swiper-pagination'),
            clickable: true,
            dynamicBullets: true,
        },

        // Navigation arrows - только внутри .mainSlider
        navigation: {
            nextEl: mainSliderElement.querySelector('.swiper-button-next'),
            prevEl: mainSliderElement.querySelector('.swiper-button-prev'),
        },

        // Responsive breakpoints
        breakpoints: {
            320: {
                slidesPerView: 1,
                spaceBetween: 20
            },
            768: {
                slidesPerView: 1,
                spaceBetween: 30
            },
            1024: {
                slidesPerView: 1,
                spaceBetween: 40
            }
        },

        // Effects
        effect: 'slide',

        // Keyboard control
        keyboard: {
            enabled: true,
        },
    });

    // Top Banner Slider - только на мобильных устройствах
    const topBannerSlider = document.querySelector('.u-top-banner__slider .ds-slider-tt__slider');
    const topBannerPagination = document.querySelector('.u-top-banner__slider .ds-slider-tt__pagination');
    const topBannerPrev = document.querySelector('.u-top-banner__slider .ds-slider-tt__button_type_previous');
    const topBannerNext = document.querySelector('.u-top-banner__slider .ds-slider-tt__button_type_next');

    let topBannerSwiper = null;

    function initTopBannerSlider() {
        const isMobile = window.innerWidth <= 719;

        if (isMobile && topBannerSlider && !topBannerSwiper) {
            // Инициализируем слайдер на мобильных
            topBannerSwiper = new Swiper(topBannerSlider, {
                slidesPerView: 1,
                spaceBetween: 12,
                speed: 300,
                loop: true,

                pagination: {
                    el: topBannerPagination,
                    clickable: true,
                    dynamicBullets: true,
                },

                navigation: {
                    nextEl: topBannerNext,
                    prevEl: topBannerPrev,
                },

                breakpoints: {
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 12
                    }
                }
            });
        } else if (!isMobile && topBannerSwiper) {
            // Уничтожаем слайдер на десктопе
            topBannerSwiper.destroy(true, true);
            topBannerSwiper = null;
        }
    }

    // Инициализация при загрузке
    initTopBannerSlider();

    // Переинициализация при изменении размера окна
    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            initTopBannerSlider();
        }, 250);
    });

    // Team Slider - слайдер команды
    const teamSliderElement = document.querySelector('.page-about-team__slider');
    if (teamSliderElement) {
        // Уничтожаем существующий экземпляр, если есть
        if (teamSliderElement.swiper) {
            teamSliderElement.swiper.destroy(true, true);
        }

        const teamSliderPrev = teamSliderElement.querySelector('.swiper-button-prev');
        const teamSliderNext = teamSliderElement.querySelector('.swiper-button-next');
        const teamSliderPagination = teamSliderElement.querySelector('.swiper-pagination');
        const teamSliderScrollbar = teamSliderElement.querySelector('.swiper-scrollbar');

        const teamSlider = new Swiper(teamSliderElement, {
            // Базовые настройки для мобильных (до 320px)
            slidesPerView: 1,
            spaceBetween: 30,
            speed: 600,
            loop: true,
            freeMode: true,

            // Navigation
            navigation: {
                nextEl: teamSliderNext,
                prevEl: teamSliderPrev,
            },

            // Pagination (fraction type)
            pagination: {
                el: teamSliderPagination,
                type: 'fraction',
            },

            // Scrollbar
            scrollbar: {
                el: teamSliderScrollbar,
                draggable: true,
            },

            // Responsive breakpoints
            breakpoints: {
                320: {
                    slidesPerView: 1.2,
                    spaceBetween: 30
                },
                480: {
                    slidesPerView: 1.5,
                    spaceBetween: 30
                },
                768: {
                    slidesPerView: 2.2,
                    spaceBetween: 30
                },
                992: {
                    slidesPerView: 3,
                    spaceBetween: 30
                },
                1200: {
                    slidesPerView: 3.5,
                    spaceBetween: 30
                },
                1400: {
                    slidesPerView: 4,
                    spaceBetween: 30
                }
            },

            // Keyboard control
            keyboard: {
                enabled: true,
            },
        });
    }

    // Clients Slider - слайдер партнеров
    const clientsSliderElement = document.querySelector('#clients-slider');
    if (clientsSliderElement) {
        // Уничтожаем существующий экземпляр, если есть
        if (clientsSliderElement.swiper) {
            clientsSliderElement.swiper.destroy(true, true);
        }

        const clientsSliderPrev = document.querySelector('.u-clients__slider .ds-slider-tt__button_type_previous');
        const clientsSliderNext = document.querySelector('.u-clients__slider .ds-slider-tt__button_type_next');
        const clientsSliderPagination = document.querySelector('.u-clients__slider .ds-slider-tt__pagination');

        const clientsSlider = new Swiper(clientsSliderElement, {
            // Базовые настройки
            slidesPerView: 2,
            spaceBetween: 20,
            speed: 800,
            loop: true,
            autoplay: {
                delay: 2000,
                disableOnInteraction: false,
                pauseOnMouseEnter: false,
            },
            freeMode: false,
            allowTouchMove: true,

            // Navigation
            navigation: {
                nextEl: clientsSliderNext,
                prevEl: clientsSliderPrev,
            },

            // Pagination
            pagination: {
                el: clientsSliderPagination,
                clickable: true,
                dynamicBullets: true,
            },

            // Responsive breakpoints
            breakpoints: {
                320: {
                    slidesPerView: 2,
                    spaceBetween: 20
                },
                480: {
                    slidesPerView: 3,
                    spaceBetween: 30
                },
                768: {
                    slidesPerView: 4,
                    spaceBetween: 40
                },
                992: {
                    slidesPerView: 5,
                    spaceBetween: 50
                },
                1200: {
                    slidesPerView: 6,
                    spaceBetween: 60
                }
            },

            // Keyboard control
            keyboard: {
                enabled: true,
            },
        });
    }
});

