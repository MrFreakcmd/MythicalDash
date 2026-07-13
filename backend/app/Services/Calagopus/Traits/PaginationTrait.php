<?php

/*
 * This file is part of MythicalDash.
 *
 * MIT License
 *
 * Copyright (c) 2020-2025 MythicalSystems
 * Copyright (c) 2020-2025 Cassian Gherman (NaysKutzu)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Services\Calagopus\Traits;

/**
 * Pagination helper trait for standardized list endpoint handling.
 *
 * Provides methods to build query strings and validate pagination parameters
 * across Admin and Client API resources.
 */
trait PaginationTrait
{
    /**
     * Build a standardized pagination query string.
     *
     * @param int $page The page number (1-based)
     * @param int $perPage Items per page
     * @param ?string $search Optional search filter
     *
     * @return string Query string (e.g., "?page=1&per_page=50&search=term")
     */
    protected function buildPaginationQuery(int $page = 1, int $perPage = 50, ?string $search = null): string
    {
        // Validate and sanitize parameters
        $page = max(1, (int) $page);
        $perPage = max(1, min(250, (int) $perPage)); // Cap at 250 items per page

        $queryParts = [
            "page={$page}",
            "per_page={$perPage}",
        ];

        // Add search filter if provided
        if (!empty($search)) {
            $encodedSearch = urlencode(trim($search));
            $queryParts[] = "search={$encodedSearch}";
        }

        return '?' . implode('&', $queryParts);
    }

    /**
     * Build a pagination query string without search parameter.
     *
     * Use this for nested list endpoints that don't support search.
     *
     * @param int $page The page number (1-based)
     * @param int $perPage Items per page
     *
     * @return string Query string (e.g., "?page=1&per_page=50")
     */
    protected function buildSimplePaginationQuery(int $page = 1, int $perPage = 50): string
    {
        return $this->buildPaginationQuery($page, $perPage, null);
    }
}
