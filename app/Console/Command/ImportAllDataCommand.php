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

        $bar = null;
        $endpoint = null;
        // one progress bar per endpoint; total page count comes from the API meta
        $onProgress = function (string $ep, int $page, int $last) use (&$bar, &$endpoint) {
            if ($ep !== $endpoint) {
                $bar?->finish();
                $endpoint = $ep;
                $this->newLine();
                $this->line("  <info>$ep</info>");
                $bar = $this->output->createProgressBar(max(1, $last));
            }
            $bar->setProgress($page);
        };

        try {
            $importAllData->execute($this->option('date-from'), $this->option('date-to'), $onProgress);
            $bar?->finish();
        } catch (\Throwable $e) {
            $bar?->finish();
            $this->newLine(2);
            $this->error("An error has occurred: ". $e->getMessage());
            $this->newLine();
            return self::FAILURE;
        }

        $this->newLine(2);
        $this->info("Importing has finished successfully!");
        $this->newLine();
        return self::SUCCESS;
    }
}
