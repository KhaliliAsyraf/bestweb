<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SampleToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sample-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::first(); // from sample seeding
        $user->tokens()->where('name', 'auth_token')->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->info('|Token|');
        $this->info('-------');
        $this->info($token);
    }
}
