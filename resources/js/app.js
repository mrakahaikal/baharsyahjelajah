import AOS from 'aos';
import 'aos/dist/aos.css';
import Swiper from 'swiper';
import { Autoplay, EffectFade, Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/effect-fade';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

import.meta.glob([
    '../images/**',
]);

Swiper.use([Autoplay, EffectFade, Navigation, Pagination]);

document.addEventListener('DOMContentLoaded', () => {
    AOS.init({
        duration: 650,
        easing: 'ease-out-cubic',
        once: true,
        offset: 80,
        disable: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    });

    document.querySelectorAll('.js-hero-swiper').forEach((element) => {
        new Swiper(element, {
            effect: 'fade',
            loop: true,
            speed: 900,
            autoplay: {
                delay: 5200,
                disableOnInteraction: false,
            },
            pagination: {
                el: element.querySelector('.swiper-pagination'),
                clickable: true,
            },
        });
    });

    document.querySelectorAll('.js-promo-swiper').forEach((element) => {
        new Swiper(element, {
            loop: true,
            speed: 700,
            slidesPerView: 1,
            spaceBetween: 20,
            autoplay: {
                delay: 4500,
                disableOnInteraction: false,
            },
            pagination: {
                el: element.querySelector('.swiper-pagination'),
                clickable: true,
            },
        });
    });

    document.querySelectorAll('.js-testimonial-swiper').forEach((element) => {
        const section = element.closest('section');

        new Swiper(element, {
            speed: 650,
            spaceBetween: 18,
            slidesPerView: 1,
            navigation: {
                nextEl: section?.querySelector('.swiper-button-next'),
                prevEl: section?.querySelector('.swiper-button-prev'),
            },
            pagination: {
                el: element.querySelector('.swiper-pagination'),
                clickable: true,
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                },
            },
        });
    });
});
