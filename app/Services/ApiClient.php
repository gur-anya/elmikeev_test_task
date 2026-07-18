<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiClient
{
    public function fetch(string $endpoint, \DateTimeImmutable $dateFrom, \DateTimeImmutable $dateTo): \Generator
    {
        $firstPage = Http::get(config('services.web_api.url') . '/' . $endpoint, [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
            'page' => 1,
            'key' => config('services.web_api.key'),
        ])->throw(); //will return 500 at max

        yield $firstPage->json();

        $dataTotal = $firstPage->json()['meta']['total'];

        if ($dataTotal > 500) { //if dataTotal is 500, its all imported in first batch
            $lastPageNumber = $firstPage->json()['meta']['last_page'];
            //will use generator to not have to carry additional arrays in memory
            for ($i = 2; $i <= $lastPageNumber; $i++) {
                $response = Http::get(config('services.web_api.url') . '/' . $endpoint, [
                    'dateFrom' => $dateFrom->format('Y-m-d'),
                    'dateTo' => $dateTo->format('Y-m-d'),
                    'page' => $i,
                    'key' => config('services.web_api.key'),
                ])->throw();

                yield $response->json();
            }
        }
    }
}
