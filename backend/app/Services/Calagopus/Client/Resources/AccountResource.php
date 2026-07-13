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

namespace MythicalDash\Services\Calagopus\Client\Resources;

use GuzzleHttp\Exception\ClientException;
use MythicalDash\Services\Calagopus\Client\CalagopusClient;
use MythicalDash\Services\Calagopus\Exceptions\AuthenticationException;
use MythicalDash\Services\Calagopus\Exceptions\PermissionException;
use MythicalDash\Services\Calagopus\Exceptions\RateLimitException;
use MythicalDash\Services\Calagopus\Traits\PaginationTrait;

class AccountResource extends CalagopusClient
{
    use PaginationTrait;

    /**
     * Get account details.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function getAccountDetails(): array
    {
        try {
            return $this->request('GET', '/api/client/account');
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_account');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Update account details.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function updateAccountDetails(array $data): array
    {
        try {
            return $this->request('PATCH', '/api/client/account', ['json' => $data]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('update_account');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get account activity.
     *
     * @param int $page The page number (1-based, default 1)
     * @param int $perPage Items per page (default 50)
     * @param ?string $search Optional search filter
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function getAccountActivity(int $page = 1, int $perPage = 50, ?string $search = null): array
    {
        try {
            $query = $this->buildPaginationQuery($page, $perPage, $search);
            return $this->request('GET', "/api/client/account/activity{$query}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_account_activity');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get API keys.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function getApiKeys(): array
    {
        try {
            return $this->request('GET', '/api/client/account/api-keys');
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_api_keys');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Create API key.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function createApiKey(string $description, array $allowedIps = []): array
    {
        try {
            return $this->request('POST', '/api/client/account/api-keys', [
                'json' => [
                    'description' => $description,
                    'allowed_ips' => $allowedIps,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('create_api_key');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Delete API key.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function deleteApiKey(string $identifier): array
    {
        try {
            return $this->request('DELETE', "/api/client/account/api-keys/{$identifier}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('delete_api_key');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Patch API key.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function updateApiKey(string $identifier, array $data): array
    {
        try {
            return $this->request('PATCH', "/api/client/account/api-keys/{$identifier}", ['json' => $data]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('update_api_key');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Recreate API key.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function recreateApiKey(string $identifier): array
    {
        try {
            return $this->request('POST', "/api/client/account/api-keys/{$identifier}/recreate");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('recreate_api_key');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get SSH keys.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function getSshKeys(): array
    {
        try {
            return $this->request('GET', '/api/client/account/ssh-keys');
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_ssh_keys');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Create SSH key.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function createSshKey(string $name, string $publicKey): array
    {
        try {
            return $this->request('POST', '/api/client/account/ssh-keys', [
                'json' => [
                    'name' => $name,
                    'public_key' => $publicKey,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('create_ssh_key');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Delete SSH key.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function deleteSshKey(string $identifier): array
    {
        try {
            return $this->request('DELETE', "/api/client/account/ssh-keys/{$identifier}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('delete_ssh_key');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }
}
