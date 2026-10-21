<?php

declare(strict_types=1);

namespace App\Services\OpenApi;

use App\Mcp\Servers\BaharsyahServer;
use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;

class OpenApiSpecificationGenerator
{
    /**
     * Core recommended tools prioritized when constrained by OpenAPI limits (e.g. OpenAI 30 actions limit).
     *
     * @var array<int, string>
     */
    protected array $priorityToolNames = [
        // Articles & Content
        'list_articles',
        'get_article',
        'create_article',
        'update_article',
        'publish_article',
        'list_article_categories',
        'get_article_category',

        // Destinations & Countries
        'list_destinations',
        'get_destination',
        'list_countries',
        'get_country',

        // Visa & Fleet
        'list_visa_services',
        'get_visa_service',
        'list_vehicles',
        'get_vehicle',
        'list_vehicle_rental_areas',

        // Tours
        'list_tour_categories',
        'list_tours',
        'get_tour',
        'list_tour_packages',
        'get_tour_package',
        'calculate_tour_price',

        // Umrah
        'list_umrah_packages',
        'get_umrah_package',
        'calculate_umrah_price',
        'list_umrah_departures',

        // Customer Engagement
        'list_faqs',
        'list_testimonials',
        'list_whatsapp_templates',
        'render_whatsapp_message',
    ];

    /**
     * Generate OpenAPI 3.1.0 specification array.
     *
     * @param  string|null  $domain  Filter by domain (e.g. 'tour', 'umrah', 'article', 'vehicle', 'all')
     * @param  int|string|null  $limit  Maximum number of actions (default 30 for OpenAI Custom GPT, or 'all')
     * @return array<string, mixed>
     */
    public function generate(?string $domain = null, int|string|null $limit = 30): array
    {
        $toolClasses = BaharsyahServer::registeredToolClasses();
        $toolInstances = [];

        foreach ($toolClasses as $class) {
            /** @var Tool $tool */
            $tool = app($class);
            $toolName = $tool->name();
            $tag = $this->resolveDomainTag($class);

            if ($domain !== null && $domain !== '' && $domain !== 'all') {
                if (! $this->matchesDomain($tag, $toolName, $domain)) {
                    continue;
                }
            }

            $toolInstances[$toolName] = [
                'tool' => $tool,
                'class' => $class,
                'tag' => $tag,
            ];
        }

        // Apply limit / prioritization if requested
        if ($limit !== 'all' && is_numeric($limit) && (int) $limit > 0) {
            $limitInt = (int) $limit;
            if (count($toolInstances) > $limitInt) {
                $filtered = [];

                // 1. Pick priority tools first
                foreach ($this->priorityToolNames as $priorityName) {
                    if (isset($toolInstances[$priorityName])) {
                        $filtered[$priorityName] = $toolInstances[$priorityName];
                        if (count($filtered) >= $limitInt) {
                            break;
                        }
                    }
                }

                // 2. Fill remaining slots if any
                if (count($filtered) < $limitInt) {
                    foreach ($toolInstances as $name => $item) {
                        if (! isset($filtered[$name])) {
                            $filtered[$name] = $item;
                            if (count($filtered) >= $limitInt) {
                                break;
                            }
                        }
                    }
                }

                $toolInstances = $filtered;
            }
        }

        $paths = [];

        foreach ($toolInstances as $toolName => $item) {
            /** @var Tool $tool */
            $tool = $item['tool'];
            $tag = $item['tag'];

            $toolArray = $tool->toArray();
            $inputSchema = $toolArray['inputSchema'] ?? [
                'type' => 'object',
                'properties' => (object) [],
            ];

            $requiredFields = is_array($inputSchema['required'] ?? null) ? $inputSchema['required'] : [];
            $pathKey = "/api/actions/{$toolName}";

            $paths[$pathKey] = [
                'post' => [
                    'operationId' => $toolName,
                    'summary' => $tool->title() ?? Str::headline($toolName),
                    'description' => $tool->description() ?? "Execute the {$toolName} action.",
                    'tags' => [$tag],
                    'requestBody' => [
                        'description' => "Parameters to execute {$toolName}",
                        'required' => count($requiredFields) > 0,
                        'content' => [
                            'application/json' => [
                                'schema' => $inputSchema,
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Action successfully executed',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'description' => 'Execution result payload',
                                    ],
                                ],
                            ],
                        ],
                        '400' => [
                            'description' => 'Validation error or invalid input parameters',
                        ],
                        '401' => [
                            'description' => 'Unauthorized: Invalid or missing Bearer token',
                        ],
                        '403' => [
                            'description' => 'Forbidden: Token lacks required scope permissions',
                        ],
                    ],
                    'security' => [
                        [
                            'OAuth2' => ['mcp:use'],
                        ],
                    ],
                ],
            ];
        }

        $baseUrl = rtrim(url('/'), '/');

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Baharsyah Jelajah AI Actions API',
                'description' => 'OpenAPI 3.1 Actions specification for ChatGPT Custom GPT and external AI agents connecting to Baharsyah Jelajah MCP Tools.',
                'version' => '1.0.0',
            ],
            'servers' => [
                [
                    'url' => $baseUrl,
                    'description' => 'Baharsyah Jelajah Application Server',
                ],
            ],
            'paths' => $paths,
            'components' => [
                'securitySchemes' => [
                    'OAuth2' => [
                        'type' => 'oauth2',
                        'description' => 'OAuth 2.0 Authorization Code Grant',
                        'flows' => [
                            'authorizationCode' => [
                                'authorizationUrl' => route('passport.authorizations.authorize'),
                                'tokenUrl' => route('passport.token'),
                                'scopes' => [
                                    'mcp:use' => 'Full access to execute MCP actions and tools',
                                    'mcp:read' => 'Read access to query content, packages, and pricing',
                                    'mcp:write' => 'Write access to create and update content drafts',
                                    'mcp:publish' => 'Publishing access for articles and tour/umrah updates',
                                    'mcp:admin' => 'Full administrative access',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Resolve domain tag based on tool class namespace.
     *
     * @param  class-string<Tool>  $class
     */
    protected function resolveDomainTag(string $class): string
    {
        if (str_contains($class, '\\Articles\\')) {
            return 'Articles';
        }
        if (str_contains($class, '\\Categories\\')) {
            return 'Article Categories';
        }
        if (str_contains($class, '\\Destinations\\')) {
            return 'Destinations';
        }
        if (str_contains($class, '\\Countries\\')) {
            return 'Countries';
        }
        if (str_contains($class, '\\VisaServices\\')) {
            return 'Visa Services';
        }
        if (str_contains($class, '\\Vehicles\\')) {
            return 'Vehicles & Fleet';
        }
        if (str_contains($class, '\\Tours\\')) {
            return 'Tours & Packages';
        }
        if (str_contains($class, '\\Umrah\\')) {
            return 'Umrah Packages';
        }
        if (str_contains($class, '\\Banners\\')) {
            return 'Banners';
        }
        if (str_contains($class, '\\Faqs\\')) {
            return 'FAQs';
        }
        if (str_contains($class, '\\Testimonials\\')) {
            return 'Testimonials';
        }
        if (str_contains($class, '\\WhatsappTemplates\\')) {
            return 'WhatsApp Templates';
        }

        return 'General Tools';
    }

    /**
     * Determine if a tool matches the requested domain filter.
     */
    protected function matchesDomain(string $tag, string $toolName, string $domain): bool
    {
        $domainLower = strtolower($domain);
        $tagLower = strtolower($tag);
        $nameLower = strtolower($toolName);

        if (str_contains($tagLower, $domainLower) || str_contains($nameLower, $domainLower)) {
            return true;
        }

        if ($domainLower === 'content' && in_array($tag, ['Articles', 'Article Categories'], true)) {
            return true;
        }

        if ($domainLower === 'travel' && in_array($tag, ['Tours & Packages', 'Umrah Packages', 'Destinations'], true)) {
            return true;
        }

        return false;
    }
}
