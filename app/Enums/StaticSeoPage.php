<?php

namespace App\Enums;

enum StaticSeoPage: string
{
    case Home = 'home';
    case ContactIndex = 'contact_index';
    case TourIndex = 'tour_index';
    case UmrohIndex = 'umroh_index';
    case VisaIndex = 'visa_index';
    case TransportIndex = 'transport_index';
    case DestinationIndex = 'destination_index';
    case BlogIndex = 'blog_index';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'Beranda',
            self::ContactIndex => 'Kontak',
            self::TourIndex => 'Daftar Tour',
            self::UmrohIndex => 'Daftar Umrah',
            self::VisaIndex => 'Daftar Visa',
            self::TransportIndex => 'Daftar Transportasi',
            self::DestinationIndex => 'Daftar Destinasi',
            self::BlogIndex => 'Daftar Blog',
        };
    }

    public function routeName(): string
    {
        return match ($this) {
            self::Home => 'home',
            self::ContactIndex => 'contact.index',
            self::TourIndex => 'tour.index',
            self::UmrohIndex => 'umroh.index',
            self::VisaIndex => 'visa.index',
            self::TransportIndex => 'transport.index',
            self::DestinationIndex => 'destination.index',
            self::BlogIndex => 'blog.index',
        };
    }
}
