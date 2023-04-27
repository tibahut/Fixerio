<?php

namespace tibahut\Fixerio;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\TransferException;
use tibahut\Fixerio\Exceptions\ConnectionException;
use tibahut\Fixerio\Exceptions\ResponseException;

class Exchange
{
    /**
     * Guzzle client.
     *
     * @var GuzzleClient
     */
    private $guzzle;

    /**
     * URL of fixer.io.
     *
     * @var string
     */
    private $url = 'api.apilayer.com/fixer';

    /**
     * Date when an historical call is made.
     *
     * @var string
     */
    private $date;

    /**
     * Http or Https.
     *
     * @var string
     */
    private $protocol = 'https';

    /**
     * Base currency.
     *
     * @var string
     */
    private $base = 'EUR';

    /**
     * List of currencies to return.
     *
     * @var array
     */
    private $symbols = [];

    /**
     * Holds whether the response should be
     * an object or not.
     *
     * @var array
     */
    private $asObject = false;

    /**
     * Holds the Fixer.io API key.
     *
     * @var string|null
     */
    private $key = null;

    public function __construct(?GuzzleClient $guzzle = null)
    {
        if (isset($guzzle)) {
            $this->guzzle = $guzzle;
        } else {
            $this->guzzle = new GuzzleClient();
        }
    }

    /**
     * Sets the protocol to https if needed.
     */
    public function secure(): Exchange
    {
        $this->protocol = 'https';

        return $this;
    }

    /**
     * Sets the protocol to http if needed.
     */
    public function unsecure(): Exchange
    {
        $this->protocol = 'http';

        return $this;
    }

    /**
     * Sets the base currency.
     */
    public function base(string $currency): Exchange
    {
        $this->base = $currency;

        return $this;
    }

    /**
     * Sets the API key.
     */
    public function key(string $key): Exchange
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Sets the currencies to return.
     * Expects either a list of arguments or
     * a single argument as array.
     *
     * @param array $currencies
     */
    public function symbols($currencies = null): Exchange
    {
        if (func_num_args() and !is_array(func_get_args()[0])) {
            $currencies = func_get_args();
        }

        $this->symbols = $currencies;

        return $this;
    }

    /**
     * Defines that the api call should be
     * historical, meaning it will return rates
     * for any day since the selected date.
     */
    public function historical(string $date): Exchange
    {
        $this->date = date('Y-m-d', strtotime($date));

        return $this;
    }

    /**
     * Returns the correctly formatted url.
     */
    public function getUrl(): string
    {
        return $this->buildUrl($this->url);
    }

    /**
     * Makes the request and returns the response
     * with the rates.
     *
     * @return array
     *
     * @throws ConnectionException if the request is incorrect or times out
     * @throws ResponseException   if the response is malformed
     */
    public function get()
    {
        $url = $this->buildUrl($this->url);

        try {
            $response = $this->makeRequest($url);

            return $this->prepareResponse($response);
        }
        // The client needs to know only one exception, no
        // matter what exception is thrown by Guzzle
        catch (TransferException $e) {
            throw new ConnectionException($e->getMessage());
        }
    }

    /**
     * Makes the request and returns the response
     * with the rates, as a Result object.
     *
     * @return Result
     *
     * @throws ConnectionException if the request is incorrect or times out
     * @throws ResponseException   if the response is malformed
     */
    public function getResult()
    {
        $url = $this->buildUrl($this->url);

        try {
            $response = $this->makeRequest($url);

            return $this->prepareResponseResult($response);
        }
        // The client needs to know only one exception, no
        // matter what exception is thrown by Guzzle
        catch (TransferException $e) {
            throw new ConnectionException($e->getMessage());
        }
    }

    /**
     * Alias of get() but returns an object
     * response.
     *
     * @return object
     *
     * @throws ConnectionException if the request is incorrect or times out
     * @throws ResponseException   if the response is malformed
     */
    public function getAsObject()
    {
        $this->asObject = true;

        return $this->get();
    }

    /**
     * Forms the correct url from the different parts.
     */
    private function buildUrl(string $url): string
    {
        $url = $this->protocol.'://'.$url.'/';

        if ($this->date) {
            $url .= $this->date;
        } else {
            $url .= 'latest';
        }

        $url .= '?base='.$this->base;

        if ($symbols = $this->symbols) {
            $url .= '&symbols='.implode(',', $symbols);
        }

        return $url;
    }

    /**
     * Makes the http request.
     */
    private function makeRequest(string $url): string
    {
        $response = $this->guzzle->request('GET', $url, [
            'headers' => [
                'Content-Type' => 'text/plain',
                'apikey' => $this->key,
            ],
        ]);

        return $response->getBody();
    }

    /**
     * @return array|\stdClass
     *
     * @throws ResponseException if the response is malformed
     */
    private function prepareResponse(string $body)
    {
        $response = json_decode($body, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new ResponseException(json_last_error_msg());
        }

        if (false === $response['success']) {
            throw new ResponseException($response['error']['info'], $response['error']['code']);
        }

        if (!is_array($response['rates'])) {
            throw new ResponseException('Response body is malformed.');
        }

        if ($this->asObject) {
            return (object) $response['rates'];
        }

        return $response['rates'];
    }

    /**
     * @throws ResponseException if the response is malformed
     */
    private function prepareResponseResult(string $body): Result
    {
        $response = json_decode($body, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new ResponseException(json_last_error_msg());
        }

        if (false === $response['success']) {
            throw new ResponseException($response['error']['info'], $response['error']['code']);
        }

        if (
            isset($response['rates'])
            and is_array($response['rates'])
            and isset($response['base'])
            and isset($response['date'])
        ) {
            return new Result(
                $response['base'],
                new \DateTime($response['date']),
                $response['rates']
            );
        }

        throw new ResponseException('Response body is malformed.');
    }
}
