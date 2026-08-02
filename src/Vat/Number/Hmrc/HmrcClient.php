<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Vat\Number\Hmrc;

use Gewebe\SyliusVATPlugin\Vat\Number\ClientException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * HMRC "Check a UK VAT number" API client
 *
 * The lookup endpoint is open access, no authentication is required.
 */
final class HmrcClient implements HmrcClientInterface
{
    public const PRODUCTION_BASE_URL = 'https://api.service.hmrc.gov.uk';

    public const SANDBOX_BASE_URL = 'https://test-api.service.hmrc.gov.uk';

    private const ACCEPT_HEADER = 'application/vnd.hmrc.2.0+json';

    private const LOOKUP_PATH = '/organisations/vat/check-vat-number/lookup/%s';

    private const TIMEOUT = 10.0;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl = self::PRODUCTION_BASE_URL,
        private readonly ?string $clientId = null,
        private readonly ?string $clientSecret = null,
    ) {
    }

    public function checkVatNumber(string $vatRegistrationNumber): bool
    {
        $token = $this->getAccessToken();

        $url = rtrim($this->baseUrl, '/') . sprintf(self::LOOKUP_PATH, rawurlencode($vatRegistrationNumber));

        try {
            $statusCode = $this->httpClient->request('GET', $url, [
                'headers' => ['Accept' => self::ACCEPT_HEADER, 'Authorization' => 'Bearer ' . $token],
                'timeout' => self::TIMEOUT,
            ])->getStatusCode();
        } catch (HttpClientExceptionInterface $exception) {
            throw new ClientException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return match (true) {
            // The VAT number is registered
            200 === $statusCode => true,
            // 400 INVALID_REQUEST, 404 NOT_FOUND: the VAT number is not registered
            in_array($statusCode, [400, 404], true) => false,
            default => throw new ClientException(
                sprintf('The HMRC VAT number check responded with status code %d.', $statusCode),
                $statusCode,
            ),
        };
    }

    private function getAccessToken(): string
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new ClientException('HMRC API client ID and secret are not set');
        }

        $response = $this->httpClient->request(
            'POST',
            $this->baseUrl . '/oauth/token',
            [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'body' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'client_credentials',
                    'scope' => 'read:vat',
                ],
            ],
        );

        try {
            $data = $response->toArray();
        } catch (HttpClientExceptionInterface $exception) {
            throw new ClientException('HMRC API login failed: ' . $exception->getMessage(), $exception->getCode(), $exception);
        }

        if (isset($data['error']) || !isset($data['access_token'])) {
            throw new ClientException('HMRC API login failed: ' . ($data['error_description'] ?? 'Unknown error'));
        }

        return $data['access_token'];
    }
}
