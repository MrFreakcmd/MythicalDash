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

namespace MythicalDash\Services\Calagopus\Admin\Resources;

use GuzzleHttp\Exception\ClientException;
use MythicalDash\Services\Calagopus\Admin\CalagopusAdmin;
use MythicalDash\Services\Calagopus\Exceptions\AuthenticationException;
use MythicalDash\Services\Calagopus\Exceptions\PermissionException;
use MythicalDash\Services\Calagopus\Exceptions\RateLimitException;
use MythicalDash\Services\Calagopus\Exceptions\ResourceNotFoundException;
use MythicalDash\Services\Calagopus\Traits\PaginationTrait;

class NestResource extends CalagopusAdmin
{
    use PaginationTrait;

    /**
     * List all nests.
     *
     * @param int $page The page number (1-based, default 1)
     * @param int $perPage Items per page (default 50)
     * @param ?string $search Optional search filter
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function listNests(int $page = 1, int $perPage = 50, ?string $search = null): array
    {
        try {
            $query = $this->buildPaginationQuery($page, $perPage, $search);
            return $this->request('GET', "/api/admin/nests{$query}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_nests');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get nest details.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getNest(string $nestId): array
    {
        try {
            return $this->request('GET', "/api/admin/nests/{$nestId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_nest');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('nest', $nestId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Create a new nest.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function createNest(array $data): array
    {
        try {
            return $this->request('POST', '/api/admin/nests', ['json' => $data]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('create_nest');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Update nest.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function updateNest(string $nestId, array $data): array
    {
        try {
            return $this->request('PATCH', "/api/admin/nests/{$nestId}", ['json' => $data]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('update_nest');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('nest', $nestId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Delete nest.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function deleteNest(string $nestId): array
    {
        try {
            return $this->request('DELETE', "/api/admin/nests/{$nestId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('delete_nest');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('nest', $nestId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get nest eggs.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getNestEggs(string $nestId): array
    {
        try {
            return $this->request('GET', "/api/admin/nests/{$nestId}/eggs");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_eggs');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::forResource('nest', $nestId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }
}
