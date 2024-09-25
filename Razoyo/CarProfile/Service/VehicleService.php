<?php

namespace Razoyo\CarProfile\Service;

use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\Session as CustomerSession;

class VehicleService
{
    /**
     * @var Curl
     */
    private $curlClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerSession
     */
    private $session;

    private $token;

    /**
     * Constructor for VehicleService
     *
     * @param Curl $curlClient
     * @param LoggerInterface $logger
     * @param CustomerSession $session
     */
    public function __construct(
        Curl $curlClient,
        LoggerInterface $logger,
        CustomerSession $session
    ) {
        $this->curlClient = $curlClient;
        $this->logger = $logger;
        $this->session = $session;
    }

    /**
     * Retrieve a list of vehicles from the external API and store any token if provided in response headers.
     *
     * @return array
     * @throws LocalizedException
     */
    public function fetchVehicleList(): array
    {
        $apiEndpoint = 'https://exam.razoyo.com/api/cars';
        try {
            // Set up headers and initiate request
            $this->initializeCurl('application/json');
            $this->logger->info('Requesting vehicle list from API: ' . $apiEndpoint);
            $this->curlClient->get($apiEndpoint);

            // Retrieve response body and headers
            $responseBody = $this->curlClient->getBody();
            $headers = $this->curlClient->getHeaders();

            $this->token = $headers['your-token'];

            // Log response data for debugging
            $this->logger->debug('Vehicle list response: ' . $responseBody);

            // Parse and return JSON response
            return $this->parseApiResponse($responseBody);
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve vehicle list: ' . $e->getMessage());
            throw new LocalizedException(__('Unable to fetch vehicles. Please try again.'));
        }
    }

    /**
     * Fetch the details of a specific vehicle using its ID.
     *
     * @param string $vehicleId
     * @return array
     * @throws LocalizedException
     */
    public function fetchVehicleDetails(string $vehicleId): array
    {
        $apiEndpoint = 'https://exam.razoyo.com/api/cars/' . $vehicleId;
        $token = $this->token;

        try {
            // Initialize curl and set authorization token
            $this->initializeCurl('application/json');
            $this->curlClient->addHeader('Authorization', 'Bearer ' . $token);
            $this->curlClient->get($apiEndpoint);

            // Retrieve response body
            $responseBody = $this->curlClient->getBody();

            // Log response data for debugging
            $this->logger->debug('Vehicle details response for ID ' . $vehicleId . ': ' . $responseBody);

            // Parse and return JSON response
            return $this->parseApiResponse($responseBody);
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve vehicle details for ID ' . $vehicleId . ': ' . $e->getMessage());
            throw new LocalizedException(__('Unable to fetch vehicle details. Please try again.'));
        }
    }

    /**
     * Initialize Curl client with required headers.
     *
     * @param string $contentType
     * @return void
     */
    private function initializeCurl(string $contentType): void
    {
        $this->curlClient->addHeader('Content-Type', $contentType);
    }


    /**
     * Decode API response from JSON.
     *
     * @param string $responseBody
     * @return array
     * @throws LocalizedException
     */
    private function parseApiResponse(string $responseBody): array
    {
        $decodedResponse = json_decode($responseBody, true);


        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Error decoding JSON response: ' . json_last_error_msg());
            throw new LocalizedException(__('Invalid API response format.'));
        }

        return $decodedResponse;
    }
}
