<?php

namespace LucasDotDev\Soulbscription\Commands;

use Illuminate\Console\Command;

class SoulbscriptionCommand extends Command
{
    public $signature = 'laravel-soulbscription';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
