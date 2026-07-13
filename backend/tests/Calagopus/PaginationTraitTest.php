<?php

namespace Tests\Calagopus;

use PHPUnit\Framework\TestCase;
use MythicalDash\Services\Calagopus\Traits\PaginationTrait;

class PaginationTraitTest extends TestCase
{
    use PaginationTrait;

    /**
     * Test basic pagination query building.
     */
    public function testBuildPaginationQuery()
    {
        $query = $this->buildPaginationQuery(1, 50);
        $this->assertEquals('?page=1&per_page=50', $query);
    }

    /**
     * Test pagination query with search.
     */
    public function testBuildPaginationQueryWithSearch()
    {
        $query = $this->buildPaginationQuery(2, 25, 'test user');
        $this->assertEquals('?page=2&per_page=25&search=test+user', $query);
    }

    /**
     * Test pagination validates page number (minimum 1).
     */
    public function testPaginationMinimumPage()
    {
        $query = $this->buildPaginationQuery(0, 50);
        $this->assertStringContainsString('page=1', $query);
    }

    /**
     * Test pagination caps per_page at 250.
     */
    public function testPaginationMaxPerPage()
    {
        $query = $this->buildPaginationQuery(1, 500);
        $this->assertStringContainsString('per_page=250', $query);
    }

    /**
     * Test pagination with null search doesn't include search param.
     */
    public function testPaginationNullSearch()
    {
        $query = $this->buildPaginationQuery(1, 50, null);
        $this->assertStringNotContainsString('search', $query);
    }

    /**
     * Test simple pagination query (without search).
     */
    public function testSimplePaginationQuery()
    {
        $query = $this->buildSimplePaginationQuery(1, 50);
        $this->assertEquals('?page=1&per_page=50', $query);
        $this->assertStringNotContainsString('search', $query);
    }

    /**
     * Test URL encoding of search term.
     */
    public function testSearchTermEncoding()
    {
        $query = $this->buildPaginationQuery(1, 50, 'server & database');
        $this->assertStringContainsString('search=server%20%26%20database', $query);
    }
}
