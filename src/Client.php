<?php declare(strict_types=1);

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\ResponseInterface;

class Client
{
    private const API_URL = 'https://iss.moex.com/iss/';
    private static $instance;
    private static $extraOptions = [];
    private $requestOptions = [];
    private $client;
    private $counter = 0;

    /**
     *
     */
    public function __construct()
    {
        $options = [
            'base_uri' => self::API_URL
        ];

        if (isset(self::$extraOptions)) {
            $options = array_merge($options, self::$extraOptions);
        }

        $this->client = new \GuzzleHttp\Client($options);
    }

    /**
     * @return Client
     */
    public static function getInstance(): Client
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     *
     */
    public static function destroyInstance(): void
    {
        if (isset(self::$instance)) {
            self::$instance = null;
        }
    }

    /**
     * @param $option
     * @param $value
     */
    public static function setExtraOption($option, $value): void
    {
        self::$extraOptions[$option] = $value;
    }

    /**
     * @param $option
     * @param $value
     */
    public function setRequestOption($option, $value): void
    {
        $this->requestOptions[$option] = $value;
    }

    /**
     * @return int
     */
    public function getCounter(): int
    {
        return $this->counter;
    }

    /**
     * @param string $relativeUri
     * @param array $params
     * @return string
     * @throws GuzzleException
     * @throws Exception
     */
    protected function request(string $relativeUri, array $params = []): string
    {
        try {
            $uri = $relativeUri . '.json';

            $params['lang'] = 'ru';
            $params['iss.meta'] = 'off';

            $options = [
                'query' => $params
            ];

            if (!empty($this->requestOptions)) {
                $options = array_merge($options, $this->requestOptions);
            }

            $response = $this->doRequest($uri, $options);

            $this->increaseCounter();
        } catch (TransferException $e) {
            $message = 'MoEx ISS API is not available. ' . $e->getMessage();
            throw new Exception($message, $e->getCode(), $e);
        }

        return $response->getBody()->getContents();
    }

    /**
     * @throws GuzzleException
     */
    protected function doRequest(string $uri, array $options): ResponseInterface
    {
        return $this->client->get($uri, $options);
    }

    /**
     * @return array
     * @throws GuzzleException
     */
    public function getEngineList(): array
    {
        $uri = 'engines';
        $this->request($uri);
        return [];
    }

    /**
     *
     */
    private function increaseCounter(): void
    {
        $this->counter++;
    }
}
