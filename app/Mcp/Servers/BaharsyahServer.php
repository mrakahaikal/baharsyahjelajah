<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Prompts\AuditCategoryTaxonomyPrompt;
use App\Mcp\Prompts\AuditCountryReadinessPrompt;
use App\Mcp\Prompts\AuditDestinationsReadinessPrompt;
use App\Mcp\Prompts\AuditVisaServicesReadinessPrompt;
use App\Mcp\Prompts\ConsultVisaRequirementsPrompt;
use App\Mcp\Prompts\CurateCountryGuidePrompt;
use App\Mcp\Prompts\DraftArticlePrompt;
use App\Mcp\Prompts\PlanDestinationHighlightPrompt;
use App\Mcp\Prompts\ReviewArticleSeoPrompt;
use App\Mcp\Prompts\SuggestCategoryForTopicPrompt;
use App\Mcp\Resources\ArticleCatalogOverviewResource;
use App\Mcp\Resources\CategoryTaxonomyResource;
use App\Mcp\Resources\CountryCatalogResource;
use App\Mcp\Resources\CurrencyRatesResource;
use App\Mcp\Resources\DestinationCatalogResource;
use App\Mcp\Resources\FeaturedCountriesResource;
use App\Mcp\Resources\FeaturedDestinationsResource;
use App\Mcp\Resources\RecentArticlesResource;
use App\Mcp\Resources\VisaCatalogResource;
use App\Mcp\Resources\VisaRequirementsSummaryResource;
use App\Mcp\Tools\Articles\CreateArticleTool;
use App\Mcp\Tools\Articles\DeleteArticleTool;
use App\Mcp\Tools\Articles\GetArticleTool;
use App\Mcp\Tools\Articles\ListArticlesTool;
use App\Mcp\Tools\Articles\PublishArticleTool;
use App\Mcp\Tools\Articles\UpdateArticleTool;
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
use App\Mcp\Tools\VisaServices\CreateVisaServiceTool;
use App\Mcp\Tools\VisaServices\DeleteVisaServiceTool;
use App\Mcp\Tools\VisaServices\GetVisaServiceTool;
use App\Mcp\Tools\VisaServices\ListVisaServicesTool;
use App\Mcp\Tools\VisaServices\UpdateVisaServiceTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Baharsyah Jelajah Management & Content Server')]
#[Version('1.0.0')]
#[Instructions('This server provides tools to manage and monitor Baharsyah Jelajah content, including category, article, destination, country, visa services, and real-time currency operations.')]
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
    ];

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
    ];
}
