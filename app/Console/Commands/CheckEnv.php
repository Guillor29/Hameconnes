<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckEnv extends Command
{
    protected $signature = 'env:check';
    protected $description = 'Check environment variables';

    public function handle()
    {
        $this->info('Laravel Database Configuration:');
        $this->info('DB_CONNECTION: ' . env('DB_CONNECTION'));
        $this->info('DB_HOST: ' . env('DB_HOST'));
        $this->info('DB_PORT: ' . env('DB_PORT'));
        $this->info('DB_DATABASE: ' . env('DB_DATABASE'));
        $this->info('DB_USERNAME: ' . env('DB_USERNAME'));
        $this->info('DB_PASSWORD: ' . env('DB_PASSWORD'));
        $this->info('DB_CHARSET: ' . env('DB_CHARSET'));

        return Command::SUCCESS;
    }
}
