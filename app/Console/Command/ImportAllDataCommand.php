<?php

namespace App\Console\Command;

use App\Services\ImportAllData;
use Illuminate\Console\Command;

class ImportAllDataCommand extends Command
{
    protected $signature = 'import:all-data
                            {--date-from= : Start of the period in Y-m-d format, e.g. 2026-07-10}
                            {--date-to= : End of the period in Y-m-d format, e.g. 2026-07-17}';

    protected $description = 'Fetches all data for the given Y-m-d period (except stocks: always for today)';

    public function handle(ImportAllData $importAllData): int
    {
        $this->call('migrate', ['--force' => true]);

        $this->info("Importing has started...");
        $this->newLine();
        try {
            $importAllData->execute($this->option('date-from'), $this->option('date-to'));
        } catch (\Throwable $e) {
            $this->error("An error has occurred: ". $e->getMessage());
            $this->newLine();
            return self::FAILURE;
        }
        $this->info("Importing has finished successfully!");
        $this->newLine();
        return self::SUCCESS;
    }
}
