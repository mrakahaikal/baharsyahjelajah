<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Tools\Categories\CreateArticleCategoryTool;
use App\Mcp\Tools\Categories\DeleteArticleCategoryTool;
use App\Mcp\Tools\Categories\GetArticleCategoryTool;
use App\Mcp\Tools\Categories\ListArticleCategoriesTool;
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
    ];

    /**
     * The resources registered with the server.
     *
     * @var array<int, class-string>
     */
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with the server.
     *
     * @var array<int, class-string>
     */
    protected array $prompts = [
        //
    ];
}

