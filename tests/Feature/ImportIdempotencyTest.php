<?php

namespace Tests\Feature;

use App\Models\Income;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Stock;
use App\Services\ImportAllData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
//tests idempotency
class ImportIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.web_api.url', 'http://api.test');
        Config::set('services.web_api.key', 'test-key');

        Http::fake([
            'http://api.test/incomes*' => Http::response($this->fixture('incomes')),
            'http://api.test/orders*' => Http::response($this->fixture('orders')),
            'http://api.test/sales*' => Http::response($this->fixture('sales')),
            'http://api.test/stocks*' => Http::response($this->fixture('stocks')),
        ]);
    }

    public function test_reimporting_the_same_data_does_not_duplicate(): void
    {
        $import = app(ImportAllData::class);

        $import->execute('2026-07-10', '2026-07-17');
        $afterFirstRun = $this->counts();

        $import->execute('2026-07-10', '2026-07-17');
        $afterSecondRun = $this->counts();

        $this->assertSame(
            ['incomes' => 2, 'orders' => 3, 'sales' => 2, 'stocks' => 4],
            $afterFirstRun,
            'First import must load exactly the fixture rows'
        );
        $this->assertSame(
            $afterFirstRun,
            $afterSecondRun,
            'Re-importing the same data must not create duplicates'
        );
    }

    private function counts(): array
    {
        return [
            'incomes' => Income::count(),
            'orders' => Order::count(),
            'sales' => Sale::count(),
            'stocks' => Stock::count(),
        ];
    }

    private function fixture(string $name): array
    {
        return json_decode(
            file_get_contents(base_path("tests/Fixtures/wb/{$name}.json")),
            true
        );
    }
}
