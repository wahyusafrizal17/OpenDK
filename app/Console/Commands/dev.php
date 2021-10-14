<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class dev extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:dev';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat instalasi baru dengan data contoh untuk development';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Artisan::call('key:generate');
        $this->line('Menambahkan key');

        Artisan::call('storage:link');
        $this->line('Menambahkan storage ke public');

        Artisan::call('migrate');
        $this->line('Menambahkan migrasi');

        Artisan::call('db:seed --class="ContohDatabaseSeeder"');
        $this->line('Menambahkan contoh data');
    }
}
