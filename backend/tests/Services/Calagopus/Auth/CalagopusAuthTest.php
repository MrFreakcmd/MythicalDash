<?php

namespace Tests\Services\Calagopus\Auth;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use MythicalDash\Services\Calagopus\Auth\CalagopusAuth;
use MythicalDash\Services\Calagopus\Exceptions\AuthenticationException;

class CalagopusAuthTest extends TestCase
{
    private $mockHandler;
    private $handlerStack;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $this->handlerStack = HandlerStack::create($this->mockHandler);
    }

    /**
     * Test successful authentication with valid response
     */
    public function testAuthenticateSuccess()
    {
        $validResponse = [
            'data' => [
                'user' => [
                    'id' => 123,
                    'email' => 'user@example.com',
                    'username' => 'testuser',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ]
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($validResponse))
        );

        $auth = $this->createAuthWithMockClient();
        $result = $auth->authenticate('testuser', 'password123');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals(123, $result['user']['id']);
        $this->assertEquals('user@example.com', $result['user']['email']);
    }

    /**
     * Test authentication failure - invalid credentials (401)
     */
    public function testAuthenticateInvalidCredentials401()
    {
        $this->mockHandler->append(
            new Response(401, [], json_encode(['error' => 'Unauthorized']))
        );

        $auth = $this->createAuthWithMockClient();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid API credentials provided');

        $auth->authenticate('testuser', 'wrongpassword');
    }

    /**
     * Test authentication failure - validation error (422)
     */
    public function testAuthenticateValidationError422()
    {
        $this->mockHandler->append(
            new Response(422, [], json_encode(['error' => 'Unprocessable Entity']))
        );

        $auth = $this->createAuthWithMockClient();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid API credentials provided');

        $auth->authenticate('', 'password');
    }

    /**
     * Test API rate limiting (429)
     */
    public function testAuthenticateRateLimited()
    {
        $this->mockHandler->append(
            new Response(429, ['Retry-After' => '60'], json_encode(['error' => 'Too Many Requests']))
        );

        $auth = $this->createAuthWithMockClient();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('rate limited');

        $auth->authenticate('testuser', 'password');
    }

    /**
     * Test API server error (500+)
     */
    public function testAuthenticateServerError()
    {
        $this->mockHandler->append(
            new Response(503, [], json_encode(['error' => 'Service Unavailable']))
        );

        $auth = $this->createAuthWithMockClient();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('server error');

        $auth->authenticate('testuser', 'password');
    }

    /**
     * Test empty response body
     */
    public function testAuthenticateEmptyResponse()
    {
        $this->mockHandler->append(
            new Response(200, [], '')
        );

        $auth = $this->createAuthWithMockClient();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Empty response');

        $auth->authenticate('testuser', 'password');
    }

    /**
     * Test malformed JSON response
     */
    public function testAuthenticateMalformedJson()
    {
        $this->mockHandler->append(
            new Response(200, [], '{invalid json}')
        );

        $auth = $this->createAuthWithMockClient();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to decode');

        $auth->authenticate('testuser', 'password');
    }

    /**
     * Test response missing data array
     */
    public function testAuthenticateMissingDataArray()
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode(['error' => 'no data']))
        );

        $auth = $this->createAuthWithMockClient();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid authentication response structure');

        $auth->authenticate('testuser', 'password');
    }

    /**
     * Test response missing user object
     */
    public function testAuthenticateMissingUserObject()
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode(['data' => ['error' => 'no user']]))
        );

        $auth = $this->createAuthWithMockClient();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('missing user data');

        $auth->authenticate('testuser', 'password');
    }

    /**
     * Test user missing ID
     */
    public function testAuthenticateUserMissingId()
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'user' => [
                        'email' => 'user@example.com',
                        'username' => 'testuser',
                    ]
                ]
            ]))
        );

        $auth = $this->createAuthWithMockClient();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('missing required user fields');

        $auth->authenticate('testuser', 'password');
    }

    /**
     * Test user missing email
     */
    public function testAuthenticateUserMissingEmail()
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'user' => [
                        'id' => 123,
                        'username' => 'testuser',
                    ]
                ]
            ]))
        );

        $auth = $this->createAuthWithMockClient();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('missing required user fields');

        $auth->authenticate('testuser', 'password');
    }

    /**
     * Test response with null user fields
     */
    public function testAuthenticateUserNullFields()
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'user' => [
                        'id' => null,
                        'email' => null,
                        'username' => 'testuser',
                    ]
                ]
            ]))
        );

        $auth = $this->createAuthWithMockClient();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('missing required user fields');

        $auth->authenticate('testuser', 'password');
    }

    /**
     * Test response with zero ID
     */
    public function testAuthenticateUserZeroId()
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'user' => [
                        'id' => 0,
                        'email' => 'user@example.com',
                        'username' => 'testuser',
                    ]
                ]
            ]))
        );

        $auth = $this->createAuthWithMockClient();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('missing required user fields');

        $auth->authenticate('testuser', 'password');
    }

    /**
     * Test response with negative ID
     */
    public function testAuthenticateUserNegativeId()
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'user' => [
                        'id' => -1,
                        'email' => 'user@example.com',
                        'username' => 'testuser',
                    ]
                ]
            ]))
        );

        $auth = $this->createAuthWithMockClient();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('missing required user fields');

        $auth->authenticate('testuser', 'password');
    }

    /**
     * Test connection timeout
     */
    public function testAuthenticateConnectTimeout()
    {
        $this->mockHandler->append(
            new ConnectException('Connection timeout', new Request('POST', 'http://example.com'))
        );

        $auth = $this->createAuthWithMockClient();

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Failed to connect');

        $auth->authenticate('testuser', 'password');
    }

    /**
     * Test response with extra optional fields
     */
    public function testAuthenticateWithOptionalFields()
    {
        $validResponse = [
            'data' => [
                'user' => [
                    'id' => 123,
                    'email' => 'user@example.com',
                    'username' => 'testuser',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'avatar' => 'https://example.com/avatar.jpg',
                    'created_at' => '2026-01-01T00:00:00Z',
                    'extra_field' => 'should be ignored',
                ]
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($validResponse))
        );

        $auth = $this->createAuthWithMockClient();
        $result = $auth->authenticate('testuser', 'password');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals(123, $result['user']['id']);
    }

    /**
     * Helper method to create auth instance with mocked HTTP client
     */
    private function createAuthWithMockClient(): CalagopusAuth
    {
        $client = new Client([
            'handler' => $this->handlerStack,
            'base_uri' => 'http://localhost:8000',
        ]);

        $auth = new CalagopusAuth('http://localhost:8000');

        // Use reflection to inject the mocked client
        $reflection = new \ReflectionClass($auth);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($auth, $client);

        return $auth;
    }
}
