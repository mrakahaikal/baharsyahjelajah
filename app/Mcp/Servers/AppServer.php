<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreatePostCategoryTool;
use App\Mcp\Tools\CreatePostTool;
use App\Mcp\Tools\DeletePostCategoryTool;
use App\Mcp\Tools\DeletePostTool;
use App\Mcp\Tools\ListPostCategoriesTool;
use App\Mcp\Tools\ListPostsTool;
use App\Mcp\Tools\UpdatePostCategoryTool;
use App\Mcp\Tools\UpdatePostTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Baharsyah Jelajah MCP Server')]
#[Version('1.0.0')]
#[Instructions('MCP Server for Baharsyah Jelajah application allowing AI Agents to interact with posts, categories, and content.')]
class AppServer extends Server
{
    protected array $tools = [
        ListPostCategoriesTool::class,
        CreatePostCategoryTool::class,
        UpdatePostCategoryTool::class,
        DeletePostCategoryTool::class,

        ListPostsTool::class,
        CreatePostTool::class,
        UpdatePostTool::class,
        DeletePostTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
