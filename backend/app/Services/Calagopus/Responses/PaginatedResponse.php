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

namespace MythicalDash\Services\Calagopus\Responses;

/**
 * Standardized paginated response wrapper.
 *
 * Handles API responses with pagination metadata and normalizes
 * the response format for consistent frontend consumption.
 */
class PaginatedResponse
{
    private array $data;
    private array $pagination;

    /**
     * Create a new PaginatedResponse from API response data.
     *
     * @param array $response The API response (may contain nested data/pagination)
     */
    public function __construct(array $response)
    {
        // Extract pagination metadata and data from response
        if (isset($response['data']) && isset($response['pagination'])) {
            // Already normalized format
            $this->data = $response['data'];
            $this->pagination = $response['pagination'];
        } elseif (isset($response['data']) && is_array($response['data'])) {
            // Data exists but no explicit pagination wrapper
            $this->data = $response['data'];
            $this->pagination = $this->extractPaginationMetadata($response);
        } else {
            // Flat response structure
            $this->data = $response;
            $this->pagination = [];
        }
    }

    /**
     * Get the data items.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get pagination metadata.
     */
    public function getPagination(): array
    {
        return $this->pagination;
    }

    /**
     * Get the full normalized response.
     */
    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'pagination' => $this->pagination,
        ];
    }

    /**
     * Extract pagination metadata from response.
     */
    private function extractPaginationMetadata(array $response): array
    {
        $pagination = [];

        // Check for common pagination field names
        $paginationFields = ['pagination', 'meta', '_meta', 'paging'];
        foreach ($paginationFields as $field) {
            if (isset($response[$field]) && is_array($response[$field])) {
                $pagination = $response[$field];
                break;
            }
        }

        // Map common pagination field names to standard format
        return [
            'total' => $pagination['total'] ?? $pagination['count'] ?? 0,
            'page' => $pagination['page'] ?? $pagination['current_page'] ?? 1,
            'per_page' => $pagination['per_page'] ?? $pagination['per_page'] ?? 50,
            'pages' => $pagination['pages'] ?? $pagination['last_page'] ?? 1,
        ];
    }
}
