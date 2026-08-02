<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Unit\Vat\Number\Hmrc;

use Gewebe\SyliusVATPlugin\Vat\Number\ClientException;
use Gewebe\SyliusVATPlugin\Vat\Number\Hmrc\HmrcClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class HmrcClientTest extends TestCase
{
    public function testChecksRegisteredVatNumber(): void
    {
        $requestedUrls = [];
        $httpClient = new MockHttpClient([
            function (string $method, string $url, array $options): ResponseInterface {
                self::assertSame('POST', $method);
                self::assertSame(HmrcClient::PRODUCTION_BASE_URL . '/oauth/token', $url);
                self::assertStringContainsString('client_id=client_id', $options['body']);
                self::assertStringContainsString('client_secret=client_secret', $options['body']);
                self::assertStringContainsString('grant_type=client_credentials', $options['body']);

                return new MockResponse('{"access_token":"test_token"}', ['http_code' => 200]);
            },
            function (string $method, string $url, array $options) use (&$requestedUrls): ResponseInterface {
                self::assertSame('GET', $method);
                $authorizationHeaderFound = false;
                foreach ($options['headers'] as $header) {
                    if (stripos($header, 'Authorization: Bearer test_token') === 0) {
                        $authorizationHeaderFound = true;
                        break;
                    }
                }
                self::assertTrue($authorizationHeaderFound, 'Authorization header not found or incorrect');
                $requestedUrls[] = $url;

                return new MockResponse('{"target":{"vatNumber":"123456789"}}', ['http_code' => 200]);
            },
        ]);

        self::assertTrue((new HmrcClient($httpClient, HmrcClient::PRODUCTION_BASE_URL, 'client_id', 'client_secret'))->checkVatNumber('123456789'));
        self::assertCount(1, $requestedUrls);
        self::assertSame(
            HmrcClient::PRODUCTION_BASE_URL . '/organisations/vat/check-vat-number/lookup/123456789',
            $requestedUrls[0],
        );
    }

    public function testChecksUnknownVatNumber(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('{"access_token":"test_token"}', ['http_code' => 200]),
            new MockResponse('{"code":"NOT_FOUND"}', ['http_code' => 404]),
        ]);

        self::assertFalse((new HmrcClient($httpClient, HmrcClient::PRODUCTION_BASE_URL, 'id', 'secret'))->checkVatNumber('987654321'));
    }

    public function testChecksInvalidVatNumber(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('{"access_token":"test_token"}', ['http_code' => 200]),
            new MockResponse('{"code":"INVALID_REQUEST"}', ['http_code' => 400]),
        ]);

        self::assertFalse((new HmrcClient($httpClient, HmrcClient::PRODUCTION_BASE_URL, 'id', 'secret'))->checkVatNumber('12345678'));
    }

    public function testExceptionIfServiceRespondsWithServerError(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('{"access_token":"test_token"}', ['http_code' => 200]),
            new MockResponse('', ['http_code' => 503]),
        ]);

        $this->expectException(ClientException::class);
        (new HmrcClient($httpClient, HmrcClient::PRODUCTION_BASE_URL, 'id', 'secret'))->checkVatNumber('123456789');
    }

    public function testExceptionIfServiceIsUnreachable(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('{"access_token":"test_token"}', ['http_code' => 200]),
            static function (): ResponseInterface {
                throw new TransportException('Connection timed out');
            },
        ]);

        $this->expectException(ClientException::class);
        (new HmrcClient($httpClient, HmrcClient::SANDBOX_BASE_URL, 'id', 'secret'))->checkVatNumber('123456789');
    }

    public function testExceptionIfLoginFails(): void
    {
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options): ResponseInterface {
            self::assertSame('POST', $method);
            return new MockResponse('{"error":"invalid_client","error_description":"client_id is invalid"}', ['http_code' => 401]);
        });

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('HMRC API login failed: HTTP 401 returned for "https://api.service.hmrc.gov.uk/oauth/token".');

        (new HmrcClient($httpClient, HmrcClient::PRODUCTION_BASE_URL, 'wrong_id', 'secret'))->checkVatNumber('123456789');
    }

    public function testExceptionIfCredentialsMissing(): void
    {
        $httpClient = new MockHttpClient();

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('HMRC API client ID and secret are not set');

        (new HmrcClient($httpClient))->checkVatNumber('123456789');
    }
}
