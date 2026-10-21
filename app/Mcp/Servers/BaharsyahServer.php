<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Prompts\AuditCategoryTaxonomyPrompt;
use App\Mcp\Prompts\AuditCountryReadinessPrompt;
use App\Mcp\Prompts\AuditDestinationsReadinessPrompt;
use App\Mcp\Prompts\AuditFleetReadinessPrompt;
use App\Mcp\Prompts\AuditVisaServicesReadinessPrompt;
use App\Mcp\Prompts\ConsultFaqAnswersPrompt;
use App\Mcp\Prompts\ConsultTourPackagePrompt;
use App\Mcp\Prompts\ConsultUmrahPackagePrompt;
use App\Mcp\Prompts\ConsultVisaRequirementsPrompt;
use App\Mcp\Prompts\CurateCountryGuidePrompt;
use App\Mcp\Prompts\CurateSocialProofPrompt;
use App\Mcp\Prompts\DesignBannerCampaignPrompt;
use App\Mcp\Prompts\DraftArticlePrompt;
use App\Mcp\Prompts\DraftTourQuotationPrompt;
use App\Mcp\Prompts\DraftUmrahQuotationPrompt;
use App\Mcp\Prompts\DraftWhatsappFollowupPrompt;
use App\Mcp\Prompts\PlanDestinationHighlightPrompt;
use App\Mcp\Prompts\RecommendVehicleForTripPrompt;
use App\Mcp\Prompts\ReviewArticleSeoPrompt;
use App\Mcp\Prompts\SuggestCategoryForTopicPrompt;
use App\Mcp\Resources\ActiveBannersResource;
use App\Mcp\Resources\ArticleCatalogOverviewResource;
use App\Mcp\Resources\CategoryTaxonomyResource;
use App\Mcp\Resources\CountryCatalogResource;
use App\Mcp\Resources\CuratedTestimonialsResource;
use App\Mcp\Resources\CurrencyRatesResource;
use App\Mcp\Resources\DestinationCatalogResource;
use App\Mcp\Resources\FaqKnowledgeBaseResource;
use App\Mcp\Resources\FeaturedCountriesResource;
use App\Mcp\Resources\FeaturedDestinationsResource;
use App\Mcp\Resources\RecentArticlesResource;
use App\Mcp\Resources\TourCatalogResource;
use App\Mcp\Resources\TourCategoriesResource;
use App\Mcp\Resources\UmrahCatalogResource;
use App\Mcp\Resources\UmrahDeparturesScheduleResource;
use App\Mcp\Resources\VehicleFleetCatalogResource;
use App\Mcp\Resources\VehicleRentalAreasResource;
use App\Mcp\Resources\VehicleRentalTermsResource;
use App\Mcp\Resources\VisaCatalogResource;
use App\Mcp\Resources\VisaRequirementsSummaryResource;
use App\Mcp\Resources\WhatsappTemplatesResource;
use App\Mcp\Tools\Articles\CreateArticleTool;
use App\Mcp\Tools\Articles\DeleteArticleTool;
use App\Mcp\Tools\Articles\GetArticleTool;
use App\Mcp\Tools\Articles\ListArticlesTool;
use App\Mcp\Tools\Articles\PublishArticleTool;
use App\Mcp\Tools\Articles\UpdateArticleTool;
use App\Mcp\Tools\Banners\CreateBannerTool;
use App\Mcp\Tools\Banners\DeleteBannerTool;
use App\Mcp\Tools\Banners\GetBannerTool;
use App\Mcp\Tools\Banners\ListBannersTool;
use App\Mcp\Tools\Banners\UpdateBannerTool;
use App\Mcp\Tools\Categories\CreateArticleCategoryTool;
use App\Mcp\Tools\Categories\DeleteArticleCategoryTool;
use App\Mcp\Tools\Categories\GetArticleCategoryTool;
use App\Mcp\Tools\Categories\ListArticleCategoriesTool;
use App\Mcp\Tools\Categories\MergeArticleCategoriesTool;
use App\Mcp\Tools\Categories\UpdateArticleCategoryTool;
use App\Mcp\Tools\Countries\CreateCountryTool;
use App\Mcp\Tools\Countries\DeleteCountryTool;
use App\Mcp\Tools\Countries\GetCountryTool;
use App\Mcp\Tools\Countries\ListCountriesTool;
use App\Mcp\Tools\Countries\UpdateCountryTool;
use App\Mcp\Tools\Destinations\CreateDestinationTool;
use App\Mcp\Tools\Destinations\DeleteDestinationTool;
use App\Mcp\Tools\Destinations\GetDestinationTool;
use App\Mcp\Tools\Destinations\ListDestinationsTool;
use App\Mcp\Tools\Destinations\UpdateDestinationTool;
use App\Mcp\Tools\Faqs\CreateFaqTool;
use App\Mcp\Tools\Faqs\DeleteFaqTool;
use App\Mcp\Tools\Faqs\GetFaqTool;
use App\Mcp\Tools\Faqs\ListFaqsTool;
use App\Mcp\Tools\Faqs\UpdateFaqTool;
use App\Mcp\Tools\Testimonials\CreateTestimonialTool;
use App\Mcp\Tools\Testimonials\DeleteTestimonialTool;
use App\Mcp\Tools\Testimonials\GetTestimonialTool;
use App\Mcp\Tools\Testimonials\ListTestimonialsTool;
use App\Mcp\Tools\Testimonials\UpdateTestimonialTool;
use App\Mcp\Tools\Tours\CalculateTourPriceTool;
use App\Mcp\Tools\Tours\Categories\CreateTourCategoryTool;
use App\Mcp\Tools\Tours\Categories\DeleteTourCategoryTool;
use App\Mcp\Tools\Tours\Categories\GetTourCategoryTool;
use App\Mcp\Tools\Tours\Categories\ListTourCategoriesTool;
use App\Mcp\Tools\Tours\Categories\UpdateTourCategoryTool;
use App\Mcp\Tools\Tours\CreateTourTool;
use App\Mcp\Tools\Tours\DeleteTourTool;
use App\Mcp\Tools\Tours\GetTourTool;
use App\Mcp\Tools\Tours\ListToursTool;
use App\Mcp\Tools\Tours\ManageTourIncludesTool;
use App\Mcp\Tools\Tours\ManageTourItineraryTool;
use App\Mcp\Tools\Tours\ManageTourPricingTierTool;
use App\Mcp\Tools\Tours\Packages\CreateTourPackageTool;
use App\Mcp\Tools\Tours\Packages\DeleteTourPackageTool;
use App\Mcp\Tools\Tours\Packages\GetTourPackageTool;
use App\Mcp\Tools\Tours\Packages\ListTourPackagesTool;
use App\Mcp\Tools\Tours\Packages\UpdateTourPackageTool;
use App\Mcp\Tools\Tours\UpdateTourTool;
use App\Mcp\Tools\Umrah\CalculateUmrahPriceTool;
use App\Mcp\Tools\Umrah\CreateUmrahPackageTool;
use App\Mcp\Tools\Umrah\DeleteUmrahPackageTool;
use App\Mcp\Tools\Umrah\GetUmrahPackageTool;
use App\Mcp\Tools\Umrah\ListUmrahDeparturesTool;
use App\Mcp\Tools\Umrah\ListUmrahPackagesTool;
use App\Mcp\Tools\Umrah\ManageUmrahDepartureTool;
use App\Mcp\Tools\Umrah\ManageUmrahIncludesTool;
use App\Mcp\Tools\Umrah\ManageUmrahItineraryTool;
use App\Mcp\Tools\Umrah\ManageUmrahPricesTool;
use App\Mcp\Tools\Umrah\UpdateUmrahPackageTool;
use App\Mcp\Tools\Vehicles\CreateVehicleRentalAreaTool;
use App\Mcp\Tools\Vehicles\CreateVehicleTool;
use App\Mcp\Tools\Vehicles\DeleteVehicleRentalAreaTool;
use App\Mcp\Tools\Vehicles\DeleteVehicleTool;
use App\Mcp\Tools\Vehicles\GetVehicleRentalAreaTool;
use App\Mcp\Tools\Vehicles\GetVehicleTool;
use App\Mcp\Tools\Vehicles\ListVehicleRentalAreasTool;
use App\Mcp\Tools\Vehicles\ListVehiclesTool;
use App\Mcp\Tools\Vehicles\UpdateVehicleRentalAreaTool;
use App\Mcp\Tools\Vehicles\UpdateVehicleTool;
use App\Mcp\Tools\VisaServices\CreateVisaServiceTool;
use App\Mcp\Tools\VisaServices\DeleteVisaServiceTool;
use App\Mcp\Tools\VisaServices\GetVisaServiceTool;
use App\Mcp\Tools\VisaServices\ListVisaServicesTool;
use App\Mcp\Tools\VisaServices\UpdateVisaServiceTool;
use App\Mcp\Tools\WhatsappTemplates\CreateWhatsappTemplateTool;
use App\Mcp\Tools\WhatsappTemplates\DeleteWhatsappTemplateTool;
use App\Mcp\Tools\WhatsappTemplates\GetWhatsappTemplateTool;
use App\Mcp\Tools\WhatsappTemplates\ListWhatsappTemplatesTool;
use App\Mcp\Tools\WhatsappTemplates\RenderWhatsappMessageTool;
use App\Mcp\Tools\WhatsappTemplates\UpdateWhatsappTemplateTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tool;

#[Name('Baharsyah Jelajah Management & Content Server')]
#[Version('1.0.0')]
#[Instructions('This server provides tools to manage and monitor Baharsyah Jelajah content, including category, article, destination, country, visa services, vehicle fleet rentals, tour packages, umrah packages, and real-time currency operations.')]
class BaharsyahServer extends Server
{
    /**
     * The tools registered with the server.
     *
     * @var array<int, class-string>
     */
    protected array $tools = [
        // Category Management
        ListArticleCategoriesTool::class,
        GetArticleCategoryTool::class,
        CreateArticleCategoryTool::class,
        UpdateArticleCategoryTool::class,
        DeleteArticleCategoryTool::class,
        MergeArticleCategoriesTool::class,

        // Article Management
        ListArticlesTool::class,
        GetArticleTool::class,
        CreateArticleTool::class,
        UpdateArticleTool::class,
        PublishArticleTool::class,
        DeleteArticleTool::class,

        // Destination Management
        ListDestinationsTool::class,
        GetDestinationTool::class,
        CreateDestinationTool::class,
        UpdateDestinationTool::class,
        DeleteDestinationTool::class,

        // Country Management
        ListCountriesTool::class,
        GetCountryTool::class,
        CreateCountryTool::class,
        UpdateCountryTool::class,
        DeleteCountryTool::class,

        // Visa Management
        ListVisaServicesTool::class,
        GetVisaServiceTool::class,
        CreateVisaServiceTool::class,
        UpdateVisaServiceTool::class,
        DeleteVisaServiceTool::class,

        // Vehicle & Fleet Management
        ListVehiclesTool::class,
        GetVehicleTool::class,
        CreateVehicleTool::class,
        UpdateVehicleTool::class,
        DeleteVehicleTool::class,

        // Vehicle Rental Areas
        ListVehicleRentalAreasTool::class,
        GetVehicleRentalAreaTool::class,
        CreateVehicleRentalAreaTool::class,
        UpdateVehicleRentalAreaTool::class,
        DeleteVehicleRentalAreaTool::class,

        // FAQ Management
        ListFaqsTool::class,
        GetFaqTool::class,
        CreateFaqTool::class,
        UpdateFaqTool::class,
        DeleteFaqTool::class,

        // Testimonial Management
        ListTestimonialsTool::class,
        GetTestimonialTool::class,
        CreateTestimonialTool::class,
        UpdateTestimonialTool::class,
        DeleteTestimonialTool::class,

        // Banner Management
        ListBannersTool::class,
        GetBannerTool::class,
        CreateBannerTool::class,
        UpdateBannerTool::class,
        DeleteBannerTool::class,

        // WhatsApp Templates
        ListWhatsappTemplatesTool::class,
        GetWhatsappTemplateTool::class,
        RenderWhatsappMessageTool::class,
        CreateWhatsappTemplateTool::class,
        UpdateWhatsappTemplateTool::class,
        DeleteWhatsappTemplateTool::class,

        // Tour Categories
        ListTourCategoriesTool::class,
        GetTourCategoryTool::class,
        CreateTourCategoryTool::class,
        UpdateTourCategoryTool::class,
        DeleteTourCategoryTool::class,

        // Tours Management
        ListToursTool::class,
        GetTourTool::class,
        CreateTourTool::class,
        UpdateTourTool::class,
        DeleteTourTool::class,

        // Tour Packages & Pricing / Itinerary / Includes
        ListTourPackagesTool::class,
        GetTourPackageTool::class,
        CreateTourPackageTool::class,
        UpdateTourPackageTool::class,
        DeleteTourPackageTool::class,
        CalculateTourPriceTool::class,
        ManageTourPricingTierTool::class,
        ManageTourItineraryTool::class,
        ManageTourIncludesTool::class,

        // Umrah Packages, Departures, Pricing, Itinerary, & Includes
        ListUmrahPackagesTool::class,
        GetUmrahPackageTool::class,
        CreateUmrahPackageTool::class,
        UpdateUmrahPackageTool::class,
        DeleteUmrahPackageTool::class,
        ListUmrahDeparturesTool::class,
        ManageUmrahDepartureTool::class,
        CalculateUmrahPriceTool::class,
        ManageUmrahPricesTool::class,
        ManageUmrahItineraryTool::class,
        ManageUmrahIncludesTool::class,
    ];

    /**
     * Get all tool class names registered on this server.
     *
     * @return array<int, class-string<Tool>>
     */
    public static function registeredToolClasses(): array
    {
        /** @var array<int, class-string<Tool>> $tools */
        $tools = (new \ReflectionProperty(static::class, 'tools'))->getDefaultValue();

        return $tools;
    }

    /**
     * The resources registered with the server.
     *
     * @var array<int, class-string>
     */
    protected array $resources = [
        CategoryTaxonomyResource::class,
        RecentArticlesResource::class,
        ArticleCatalogOverviewResource::class,
        DestinationCatalogResource::class,
        FeaturedDestinationsResource::class,
        CountryCatalogResource::class,
        FeaturedCountriesResource::class,
        CurrencyRatesResource::class,
        VisaCatalogResource::class,
        VisaRequirementsSummaryResource::class,
        VehicleFleetCatalogResource::class,
        VehicleRentalTermsResource::class,
        VehicleRentalAreasResource::class,
        FaqKnowledgeBaseResource::class,
        CuratedTestimonialsResource::class,
        ActiveBannersResource::class,
        WhatsappTemplatesResource::class,
        TourCatalogResource::class,
        TourCategoriesResource::class,
        UmrahCatalogResource::class,
        UmrahDeparturesScheduleResource::class,
    ];

    /**
     * The prompts registered with the server.
     *
     * @var array<int, class-string>
     */
    protected array $prompts = [
        AuditCategoryTaxonomyPrompt::class,
        SuggestCategoryForTopicPrompt::class,
        DraftArticlePrompt::class,
        ReviewArticleSeoPrompt::class,
        PlanDestinationHighlightPrompt::class,
        AuditDestinationsReadinessPrompt::class,
        CurateCountryGuidePrompt::class,
        AuditCountryReadinessPrompt::class,
        ConsultVisaRequirementsPrompt::class,
        AuditVisaServicesReadinessPrompt::class,
        RecommendVehicleForTripPrompt::class,
        AuditFleetReadinessPrompt::class,
        ConsultFaqAnswersPrompt::class,
        CurateSocialProofPrompt::class,
        DesignBannerCampaignPrompt::class,
        DraftWhatsappFollowupPrompt::class,
        ConsultTourPackagePrompt::class,
        DraftTourQuotationPrompt::class,
        ConsultUmrahPackagePrompt::class,
        DraftUmrahQuotationPrompt::class,
    ];
}
