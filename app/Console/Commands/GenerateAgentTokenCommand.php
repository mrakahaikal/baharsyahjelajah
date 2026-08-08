<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('mcp:token {email? : Email address of the user for whom to issue the token} {--name=ai-agent : Name of the API token}')]
#[Description('Generate a Sanctum API token for an AI Agent to access the MCP server.')]
class GenerateAgentTokenCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $tokenName = (string) $this->option('name');

        if ($email) {
            $user = User::where('email', $email)->first();

            if (! $user) {
                $this->error("User with email [{$email}] not found.");

                return self::FAILURE;
            }
        } else {
            $user = User::first();

            if (! $user) {
                $this->error('No users found in database. Please run seeders or create a user first.');

                return self::FAILURE;
            }
        }

        $token = $user->createToken($tokenName)->plainTextToken;

        $this->components->info("Sanctum API Token successfully generated for User [{$user->email}]!");
        $this->line('');
        $this->line("Token Name: <comment>{$tokenName}</comment>");
        $this->line("Bearer Token: <info>{$token}</info>");
        $this->line('');
        $this->components->warn('Keep this token secure. Pass it in your HTTPS request header as:');
        $this->line("  <comment>Authorization: Bearer {$token}</comment>");

        return self::SUCCESS;
    }
}
