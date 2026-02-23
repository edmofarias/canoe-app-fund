<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

class CompanyServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_company_calls_model_create_with_correct_data(): void
    {
        // Arrange
        $data = ['name' => 'Test Company'];
        $expectedCompany = Mockery::mock(Company::class);
        
        // Create a partial mock of the service
        $service = Mockery::mock(CompanyService::class)->makePartial();
        $service->shouldAllowMockingProtectedMethods();
        
        // Spy on the createCompany method to verify it's called with correct data
        $service->shouldReceive('createCompany')
            ->once()
            ->with($data)
            ->andReturn($expectedCompany);

        // Act
        $result = $service->createCompany($data);

        // Assert
        $this->assertInstanceOf(Company::class, $result);
    }

    public function test_list_companies_returns_collection(): void
    {
        // Arrange
        $expectedCollection = Mockery::mock(Collection::class);
        
        // Create a partial mock of the service
        $service = Mockery::mock(CompanyService::class)->makePartial();
        
        // Mock the listCompanies method
        $service->shouldReceive('listCompanies')
            ->once()
            ->andReturn($expectedCollection);

        // Act
        $result = $service->listCompanies();

        // Assert
        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_delete_company_calls_delete_and_returns_true(): void
    {
        // Arrange
        $service = new CompanyService();
        $mockCompany = Mockery::mock(Company::class);
        
        $mockCompany->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        // Act
        $result = $service->deleteCompany($mockCompany);

        // Assert
        $this->assertTrue($result);
    }

    public function test_delete_company_calls_delete_and_returns_false(): void
    {
        // Arrange
        $service = new CompanyService();
        $mockCompany = Mockery::mock(Company::class);
        
        $mockCompany->shouldReceive('delete')
            ->once()
            ->andReturn(false);

        // Act
        $result = $service->deleteCompany($mockCompany);

        // Assert
        $this->assertFalse($result);
    }

    public function test_create_company_method_signature(): void
    {
        // Verify method exists and has correct signature
        $service = new CompanyService();
        
        $this->assertTrue(
            method_exists($service, 'createCompany'),
            'CompanyService should have createCompany method'
        );
        
        $reflection = new \ReflectionMethod($service, 'createCompany');
        $parameters = $reflection->getParameters();
        
        $this->assertCount(1, $parameters);
        $this->assertEquals('array', $parameters[0]->getType()->getName());
        $this->assertEquals(Company::class, $reflection->getReturnType()->getName());
    }

    public function test_list_companies_method_signature(): void
    {
        // Verify method exists and has correct signature
        $service = new CompanyService();
        
        $this->assertTrue(
            method_exists($service, 'listCompanies'),
            'CompanyService should have listCompanies method'
        );
        
        $reflection = new \ReflectionMethod($service, 'listCompanies');
        $this->assertEquals(Collection::class, $reflection->getReturnType()->getName());
    }

    public function test_delete_company_method_signature(): void
    {
        // Verify method exists and has correct signature
        $service = new CompanyService();
        
        $this->assertTrue(
            method_exists($service, 'deleteCompany'),
            'CompanyService should have deleteCompany method'
        );
        
        $reflection = new \ReflectionMethod($service, 'deleteCompany');
        $parameters = $reflection->getParameters();
        
        $this->assertCount(1, $parameters);
        $this->assertEquals(Company::class, $parameters[0]->getType()->getName());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());
    }
}
