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

namespace MythicalDash\Services\Calagopus\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use MythicalDash\App;
use MythicalDash\Services\Calagopus\Exceptions\AuthenticationException;

/**
 * Calagopus Authentication Handler
 *
 * Handles user authentication against the Calagopus panel API.
 */
class CalagopusAuth
{
    private Client $httpClient;
    private string $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Authenticate a user with username/email and password.
     *
     * @param string $login Username or email
     * @param string $password Plain text password
     *
     * @return array The authentication response containing user data
     *
     * @throws AuthenticationException
     */
    public function authenticate(string $login, string $password): array
    {
        try {
            $response = $this->httpClient->request('POST', '/api/auth/login', [
                'json' => [
                    'username' => $login,
                    'password' => $password,
                ],
                'timeout' => 10,  // 10 second timeout to prevent hanging
            ]);

            $contents = $response->getBody()->getContents();

            if (empty($contents)) {
                throw new AuthenticationException('Empty response from Calagopus API');
            }

            $decoded = json_decode($contents, true);

            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to decode API response: ' . json_last_error_msg());
            }

            // Validate response structure
            if (!isset($decoded['data']) || !is_array($decoded['data'])) {
                throw new AuthenticationException('Invalid authentication response structure from Calagopus');
            }

            // Validate user data exists within data
            if (!isset($decoded['data']['user']) || !is_array($decoded['data']['user'])) {
                throw new AuthenticationException('Authentication response missing user data');
            }

            $user = $decoded['data']['user'];

            // Validate critical user fields
            if (empty($user['id']) || empty($user['email'])) {
                throw new AuthenticationException('Authentication response missing required user fields (id or email)');
            }

            return $decoded['data'];
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            throw AuthenticationException::apiError('Failed to connect to Calagopus: ' . $e->getMessage());
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();

                if ($statusCode === 401 || $statusCode === 422) {
                    throw AuthenticationException::invalidCredentials();
                }

                if ($statusCode === 429) {
                    throw AuthenticationException::apiError('Calagopus API rate limited - please try again later');
                }

                if ($statusCode >= 500) {
                    throw AuthenticationException::apiError('Calagopus API server error (HTTP ' . $statusCode . ')');
                }
            }

            throw AuthenticationException::apiError('Authentication request failed: ' . $e->getMessage());
        } catch (GuzzleException $e) {
            throw AuthenticationException::apiError('Guzzle HTTP error: ' . $e->getMessage());
        } catch (AuthenticationException $e) {
            // Re-throw authentication exceptions as-is
            throw $e;
        } catch (\Exception $e) {
            throw AuthenticationException::apiError('Unexpected authentication error: ' . $e->getMessage());
        }
    }
}
