<?php

namespace Tests\Calagopus;

use PHPUnit\Framework\TestCase;
use MythicalDash\Services\Calagopus\Responses\PaginatedResponse;

class PaginatedResponseTest extends TestCase
{
    /**
     * Test parsing normalized response format (with data and pagination keys).
     */
    public function testNormalizedResponseFormat()
    {
        $response = [
            'data' => [
                ['id' => 1, 'name' => 'User 1'],
                ['id' => 2, 'name' => 'User 2'],
            ],
            'pagination' => [
                'total' => 100,
                'page' => 1,
                'per_page' => 50,
                'pages' => 2,
            ],
        ];

        $paginated = new PaginatedResponse($response);

        $this->assertCount(2, $paginated->getData());
        $this->assertEquals(100, $paginated->getPagination()['total']);
        $this->assertEquals(1, $paginated->getPagination()['page']);
        $this->assertEquals(50, $paginated->getPagination()['per_page']);
    }

    /**
     * Test parsing response with data but no explicit pagination wrapper.
     */
    public function testResponseWithoutPaginationWrapper()
    {
        $response = [
            'data' => [
                ['id' => 1, 'name' => 'Server 1'],
            ],
            'total' => 50,
            'page' => 1,
        ];

        $paginated = new PaginatedResponse($response);

        $this->assertCount(1, $paginated->getData());
        $this->assertArrayHasKey('total', $paginated->getPagination());
    }

    /**
     * Test flat response structure (no data wrapper).
     */
    public function testFlatResponseStructure()
    {
        $response = [
            'id' => 1,
            'name' => 'Test',
        ];

        $paginated = new PaginatedResponse($response);

        $this->assertIsArray($paginated->getData());
    }

    /**
     * Test toArray returns normalized format.
     */
    public function testToArrayReturnsNormalizedFormat()
    {
        $response = [
            'data' => [['id' => 1]],
            'pagination' => ['total' => 10, 'page' => 1, 'per_page' => 50, 'pages' => 1],
        ];

        $paginated = new PaginatedResponse($response);
        $result = $paginated->toArray();

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertIsArray($result['data']);
        $this->assertIsArray($result['pagination']);
    }

    /**
     * Test pagination metadata extraction from different field names.
     */
    public function testPaginationFieldNameVariations()
    {
        // Test with 'meta' field
        $response = [
            'data' => [['id' => 1]],
            'meta' => [
                'total' => 100,
                'page' => 1,
            ],
        ];

        $paginated = new PaginatedResponse($response);
        $this->assertArrayHasKey('total', $paginated->getPagination());
    }
}
