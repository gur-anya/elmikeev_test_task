<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class Importer
{
    public function __construct(private ApiClient $client)
    {
    }

    /**
     * @throws \Throwable
     */
    public function import(string $endpoint, $model, \DateTimeImmutable $dateFrom, \DateTimeImmutable $dateTo): void
    {
        // decision for remote db: batch size for local db was 500 (exactly one max page)
        $capWorkaround = ((int) env('DB_RECONNECT_EVERY', 0)) > 0;
        $batchSize  = $capWorkaround ? max(1, (int) env('DB_INSERT_BATCH', 200)) : 500;
        $opsPerConn = max(1, (int) env('DB_OPS_PER_CONN', 3));

        $op = 0;
        // fresh connection every few ops so no connection exceeds  cap
        $freshConnEvery = function () use (&$op, $opsPerConn, $capWorkaround) {
            if ($capWorkaround && $op % $opsPerConn === 0) {
                DB::connection()->reconnect();
            }
            $op++;
        };

        $purgedDays = [];

        foreach ($this->client->fetch($endpoint, $dateFrom, $dateTo) as $page) {
            foreach ($page['data'] as $row) {
                $day = substr((string) $row['date'], 0, 10);
                if ($day !== '' && ! isset($purgedDays[$day])) {
                    $freshConnEvery();
                    $this->purgeDay($model, $day);
                    $purgedDays[$day] = true;
                }
            }

            foreach (array_chunk($page['data'], $batchSize) as $batch) {
                $freshConnEvery();
                $model::insert($batch);
            }
        }
    }


    //purging helps us provide idempotency since no unique keys available
    private function purgeDay($model, string $day): void
    {
        $next = (new \DateTimeImmutable($day))->modify('+1 day')->format('Y-m-d');
        $model::query()
            ->where('date', '>=', $day)
            ->where('date', '<', $next)
            ->delete();
    }
}
