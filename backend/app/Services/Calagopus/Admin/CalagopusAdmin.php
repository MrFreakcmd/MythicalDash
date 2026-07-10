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

namespace MythicalDash\Services\Calagopus\Admin;

use MythicalDash\App;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use MythicalDash\Services\Calagopus\Exceptions\AuthenticationException;

class CalagopusAdmin
{
    private Client $httpClient;
    private string $apiKey;
    private string $baseUrl;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Send a request to the Calagopus Admin API.
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $options Request options
     *
     * @throws GuzzleException
     *
     * @return array Response data
     */
    protected function request(string $method, string $endpoint, array $options = []): ?array
    {
        try {
            $response = $this->httpClient->request($method, $endpoint, $options);
            $statusCode = $response->getStatusCode();
            $contents = $response->getBody()->getContents();

            // Handle specific status codes
            if ($statusCode === 204) {
                App::getInstance(true)->getLogger()->debug('Calagopus Admin API returned 204 No Content');
                return [];
            }

            if ($statusCode === 401) {
                $error = "Calagopus Admin API returned 401 Unauthorized - check API credentials. Base URL: {$this->baseUrl}";
                App::getInstance(true)->getLogger()->error($error);
                throw new AuthenticationException($error);
            }

            if ($statusCode === 403) {
                $error = "Calagopus Admin API returned 403 Forbidden - insufficient permissions";
                App::getInstance(true)->getLogger()->error($error);
                throw new \MythicalDash\Services\Calagopus\Exceptions\PermissionException($error);
            }

            if ($statusCode === 404) {
                $error = "Calagopus Admin API returned 404 Not Found for endpoint: {$endpoint}";
                App::getInstance(true)->getLogger()->warning($error);
                throw new \MythicalDash\Services\Calagopus\Exceptions\ResourceNotFoundException($error);
            }

            // Handle server errors
            if ($statusCode >= 500) {
                $error = "Calagopus Admin API returned {$statusCode} server error. Response: " . substr($contents, 0, 500);
                App::getInstance(true)->getLogger()->error($error);
                throw new \Exception($error);
            }

            // Handle client errors not explicitly handled above
            if ($statusCode >= 400) {
                $error = "Calagopus Admin API returned {$statusCode} client error. Response: " . substr($contents, 0, 500);
                App::getInstance(true)->getLogger()->warning($error);
                throw new \Exception($error);
            }

            // Decode successful response
            $decoded = json_decode($contents, true);

            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                $error = 'Failed to decode Calagopus Admin API response: ' . json_last_error_msg() . '. Raw: ' . substr($contents, 0, 500);
                App::getInstance(true)->getLogger()->error($error);
                throw new \Exception($error);
            }

            return $decoded ?? [];
        } catch (GuzzleException $e) {
            $error = 'Failed to send request to Calagopus Admin API: ' . $e->getMessage() . '. Base URL: ' . $this->baseUrl;
            App::getInstance(true)->getLogger()->error($error);
            throw new \Exception($error, 0, $e);
        }
    }
}
