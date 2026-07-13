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

class UserResource extends CalagopusAdmin
{
    use PaginationTrait;

    /**
     * List all users.
     *
     * @param int $page The page number (1-based, default 1)
     * @param int $perPage Items per page (default 50)
     * @param ?string $search Optional search filter
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function listUsers(int $page = 1, int $perPage = 50, ?string $search = null): array
    {
        try {
            $query = $this->buildPaginationQuery($page, $perPage, $search);
            return $this->request('GET', "/api/admin/users{$query}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_users');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get user details.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getUser(string $userId): array
    {
        try {
            return $this->request('GET', "/api/admin/users/{$userId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_user');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::user($userId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Create a new user.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function createUser(array $data): array
    {
        try {
            return $this->request('POST', '/api/admin/users', ['json' => $data]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('create_user');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Update user.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function updateUser(string $userId, array $data): array
    {
        try {
            return $this->request('PATCH', "/api/admin/users/{$userId}", ['json' => $data]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('update_user');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::user($userId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Delete user.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function deleteUser(string $userId): array
    {
        try {
            return $this->request('DELETE', "/api/admin/users/{$userId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('delete_user');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::user($userId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get user activity.
     *
     * @param string $userId The user ID
     * @param int $page The page number (1-based, default 1)
     * @param int $perPage Items per page (default 50)
     * @param ?string $search Optional search filter
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getUserActivity(string $userId, int $page = 1, int $perPage = 50, ?string $search = null): array
    {
        try {
            $query = $this->buildPaginationQuery($page, $perPage, $search);
            return $this->request('GET', "/api/admin/users/{$userId}/activity{$query}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_user_activity');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::user($userId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get user servers.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getUserServers(string $userId): array
    {
        try {
            return $this->request('GET', "/api/admin/users/{$userId}/servers");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_user_servers');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::user($userId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Send password reset email.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function sendPasswordResetEmail(string $userId): array
    {
        try {
            return $this->request('POST', "/api/admin/users/{$userId}/email/reset-password");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('reset_user_password');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::user($userId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }
}
