<?php

namespace App\Services;

use App\Models\Income;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Stock;
use Exception;

class ImportAllData
{
    private const PERIOD_ENTITIES = [
        'incomes' => Income::class,
        'orders' => Order::class,
        'sales' => Sale::class,
    ];

    public function __construct(private Importer $importer)
    {
    }

    /**
     * @throws Exception
     */
    public function execute(string $dateFromString, string $dateToString): void
    {
        $dateFrom = \DateTimeImmutable::createFromFormat('Y-m-d', $dateFromString);
        if ($dateFrom === false) {
            throw new Exception('Invalid FROM date (please provide Y-m-d format)');
        }
        $dateTo = \DateTimeImmutable::createFromFormat('Y-m-d', $dateToString);
        if ($dateTo === false) {
            throw new Exception('Invalid TO date  (please provide Y-m-d format)');
        }

        foreach (self::PERIOD_ENTITIES as $endpoint => $model) {
            $this->importer->import($endpoint, $model, $dateFrom, $dateTo);
        }

        $today = new \DateTimeImmutable('today', new \DateTimeZone(config('services.web_api.timezone')));
        $this->importer->import('stocks', Stock::class, $today, $today);
    }
}
