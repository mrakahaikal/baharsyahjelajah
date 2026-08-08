<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreatePostCategoryTool;
use App\Mcp\Tools\DeletePostCategoryTool;
use App\Mcp\Tools\UpdatePostCategoryTool;
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
        CreatePostCategoryTool::class,
        UpdatePostCategoryTool::class,
        DeletePostCategoryTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
