<?php

namespace Tests\Unit;


use App\Models\Fund;
use App\Services\DuplicateDetectionService;
use App\Services\FundService;
use Mockery;
use PHPUnit\Framework\TestCase;

class FundServiceTest extends TestCase
{
    protected DuplicateDetectionService $mockDuplicateService;
    protected FundService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockDuplicateService = Mockery::mock(DuplicateDetectionService::class);
        $this->service = new FundService($this->mockDuplicateService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_constructor_accepts_duplicate_detection_service(): void
    {
        // Arrange & Act
        $service = new FundService($this->mockDuplicateService);

        // Assert
        $this->assertInstanceOf(FundService::class, $service);
    }

    public function test_delete_fund_calls_delete_and_returns_true(): void
    {
        // Arrange
        $mockFund = Mockery::mock(Fund::class);
        $mockFund->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        // Act
        $result = $this->service->deleteFund($mockFund);

        // Assert
        $this->assertTrue($result);
    }
}
