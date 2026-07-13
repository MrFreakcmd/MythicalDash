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

class NodeResource extends CalagopusAdmin
{
    use PaginationTrait;

    /**
     * List all nodes.
     *
     * @param int $page The page number (1-based, default 1)
     * @param int $perPage Items per page (default 50)
     * @param ?string $search Optional search filter
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function listNodes(int $page = 1, int $perPage = 50, ?string $search = null): array
    {
        try {
            $query = $this->buildPaginationQuery($page, $perPage, $search);
            return $this->request('GET', "/api/admin/nodes{$query}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_nodes');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get node details.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getNode(string $nodeId): array
    {
        try {
            return $this->request('GET', "/api/admin/nodes/{$nodeId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_node');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::node($nodeId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Create a new node.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws RateLimitException
     */
    public function createNode(array $data): array
    {
        try {
            return $this->request('POST', '/api/admin/nodes', ['json' => $data]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('create_node');
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Update node.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function updateNode(string $nodeId, array $data): array
    {
        try {
            return $this->request('PATCH', "/api/admin/nodes/{$nodeId}", ['json' => $data]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('update_node');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::node($nodeId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Delete node.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function deleteNode(string $nodeId): array
    {
        try {
            return $this->request('DELETE', "/api/admin/nodes/{$nodeId}");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('delete_node');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::node($nodeId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get node system stats.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getNodeStats(string $nodeId): array
    {
        try {
            return $this->request('GET', "/api/admin/nodes/{$nodeId}/system/stats");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_node_stats');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::node($nodeId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get node configuration.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getNodeConfig(string $nodeId): array
    {
        try {
            return $this->request('GET', "/api/admin/nodes/{$nodeId}/config");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_node_config');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::node($nodeId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get node servers.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getNodeServers(string $nodeId): array
    {
        try {
            return $this->request('GET', "/api/admin/nodes/{$nodeId}/servers");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_node_servers');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::node($nodeId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }

    /**
     * Get node allocations.
     *
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ResourceNotFoundException
     * @throws RateLimitException
     */
    public function getNodeAllocations(string $nodeId): array
    {
        try {
            return $this->request('GET', "/api/admin/nodes/{$nodeId}/allocations");
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            if ($statusCode === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            if ($statusCode === 403) {
                throw PermissionException::missingPermission('view_allocations');
            }

            if ($statusCode === 404) {
                throw ResourceNotFoundException::node($nodeId);
            }

            if ($statusCode === 429) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                throw RateLimitException::withRetryAfter($retryAfter);
            }

            throw $e;
        }
    }
}
