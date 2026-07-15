import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

import.meta.glob([
    '../images/**',
]);

Swiper.use([Navigation, Pagination]);

document.addEventListener('alpine:init', () => {
    window.Alpine.data('contactWorkspace', () => ({
        activeTab: 'trip',
        tripCopy: {},
        b2bCopy: {},

        init() {
            this.activeTab = this.$el.dataset.initialTab === 'b2b' ? 'b2b' : 'trip';
            this.tripCopy = JSON.parse(this.$el.dataset.tripCopy);
            this.b2bCopy = JSON.parse(this.$el.dataset.b2bCopy);
        },

        selectTab(tab, moveFocus = false) {
            this.activeTab = tab;

            const url = new URL(window.location.href);
            url.searchParams.set('type', tab);
            window.history.replaceState({}, '', url);

            if (moveFocus) {
                this.$nextTick(() => this.$refs[`${tab}Tab`]?.focus());
            }
        },

        moveTab(direction) {
            const nextTab = direction === 'first'
                ? 'trip'
                : direction === 'last'
                    ? 'b2b'
                    : this.activeTab === 'trip' ? 'b2b' : 'trip';

            this.selectTab(nextTab, true);
        },

        field(form, name) {
            return form.elements.namedItem(name)?.value?.trim() || '-';
        },

        syncTripMessage(form) {
            const copy = this.tripCopy;

            this.$refs.tripMessage.value = [
                copy.greeting,
                '',
                copy.intent,
                '',
                `${copy.name}: ${this.field(form, 'customer_name')}`,
                `${copy.destination}: ${this.field(form, 'destination_interest')}`,
                `${copy.date}: ${this.field(form, 'estimated_date')}`,
                `${copy.participants}: ${this.field(form, 'participants')}`,
                `${copy.notes}: ${this.field(form, 'travel_notes')}`,
                '',
                copy.closing,
            ].join('\n');
        },

        syncB2bMessage(form) {
            const copy = this.b2bCopy;

            this.$refs.b2bMessage.value = [
                copy.greeting,
                '',
                copy.intent,
                '',
                `${copy.organization}: ${this.field(form, 'organization_name')}`,
                `${copy.pic}: ${this.field(form, 'pic_name')}`,
                `${copy.email}: ${this.field(form, 'business_email')}`,
                `${copy.type}: ${this.field(form, 'partnership_type')}`,
                `${copy.volume}: ${this.field(form, 'estimated_volume')}`,
                `${copy.needs}: ${this.field(form, 'partnership_needs')}`,
                '',
                copy.closing,
            ].join('\n');
        },
    }));
});

document.addEventListener('DOMContentLoaded', () => {
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
