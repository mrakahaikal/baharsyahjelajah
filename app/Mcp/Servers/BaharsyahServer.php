<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Prompts\AuditCategoryTaxonomyPrompt;
use App\Mcp\Prompts\DraftArticlePrompt;
use App\Mcp\Prompts\ReviewArticleSeoPrompt;
use App\Mcp\Prompts\SuggestCategoryForTopicPrompt;
use App\Mcp\Resources\ArticleCatalogOverviewResource;
use App\Mcp\Resources\CategoryTaxonomyResource;
use App\Mcp\Resources\RecentArticlesResource;
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
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Baharsyah Jelajah Management & Content Server')]
#[Version('1.0.0')]
#[Instructions('This server provides tools to manage and monitor Baharsyah Jelajah content, including article category and content operations.')]
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
    ];
}
