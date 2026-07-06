<?php

namespace Tests\Unit;

use App\Services\BillingService;
use CodeIgniter\Test\CIUnitTestCase;

class BillingServiceTest extends CIUnitTestCase
{
    private BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billingService = new BillingService();
    }

    public function testCalculateDirectoryPrice()
    {
        // 9 base + 0.50 per 1000? Wait, the formula is: round(19 + (($totalCount / 1000) * 1.00), 2)
        // Let's test with 0 records
        $this->assertEquals(19.00, $this->billingService->calculateDirectoryPrice(0));

        // 1000 records
        $this->assertEquals(20.00, $this->billingService->calculateDirectoryPrice(1000));

        // 1500 records
        $this->assertEquals(20.50, $this->billingService->calculateDirectoryPrice(1500));

        // 50000 records
        $this->assertEquals(69.00, $this->billingService->calculateDirectoryPrice(50000));
    }

    public function testCalculateBonusPrice()
    {
        // Tiers: 
        // 10000 -> 49
        // 50000 -> 199
        // 100000 -> 349
        // 500000 -> 999
        // 1000000 -> 1499

        $this->assertEquals(49.0, $this->billingService->calculateBonusPrice(10000));
        $this->assertEquals(199.0, $this->billingService->calculateBonusPrice(50000));
        $this->assertEquals(349.0, $this->billingService->calculateBonusPrice(100000));
        $this->assertEquals(999.0, $this->billingService->calculateBonusPrice(500000));
        $this->assertEquals(1499.0, $this->billingService->calculateBonusPrice(1000000));

        // Let's test an intermediate value (e.g. 30000)
        // Range: 10000 to 50000 -> diff = 40000
        // Price Range: 49 to 199 -> diff = 150
        // 30000 is halfway -> price should be 49 + 75 = 124
        $this->assertEquals(124.0, $this->billingService->calculateBonusPrice(30000));
    }

    public function testBuildSinglePaymentLineItem()
    {
        $lineItem = $this->billingService->buildSinglePaymentLineItem('Test Product', 'Desc', 10.50, 'txr_123');

        $this->assertEquals(1, $lineItem['quantity']);
        $this->assertEquals('eur', $lineItem['price_data']['currency']);
        $this->assertEquals(1050, $lineItem['price_data']['unit_amount']); // 10.50 * 100
        $this->assertEquals('Test Product', $lineItem['price_data']['product_data']['name']);
        $this->assertEquals('Desc', $lineItem['price_data']['product_data']['description']);
        $this->assertEquals(['txr_123'], $lineItem['tax_rates']);
    }

    public function testBuildSubscriptionLineItem()
    {
        $lineItem = $this->billingService->buildSubscriptionLineItem('Sub Product', 'Sub Desc', 25.00, 'month', 'txr_456');

        $this->assertEquals(1, $lineItem['quantity']);
        $this->assertEquals('eur', $lineItem['price_data']['currency']);
        $this->assertEquals(2500, $lineItem['price_data']['unit_amount']); // 25.00 * 100
        $this->assertEquals('month', $lineItem['price_data']['recurring']['interval']);
        $this->assertEquals('Sub Product', $lineItem['price_data']['product_data']['name']);
        $this->assertEquals(['txr_456'], $lineItem['tax_rates']);
    }
}
