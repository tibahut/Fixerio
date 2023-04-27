<?php

use Mockery as m;
use tibahut\Fixerio\Currency;
use tibahut\Fixerio\Exchange;

class ExchangeTest extends PHPUnit_Framework_TestCase
{
    private $url = 'https://api.apilayer.com/fixer';

    private static $errorResponse = [
        'success' => false,
        'error' => [
            'code' => 999,
            'info' => 'Some error message',
        ],
    ];

    private static $successfulResponse = [
        'success' => true,
        'base' => 'EUR',
        'date' => '2016-01-02',
        'rates' => [
            'GBP' => 1.01,
            'USD' => 1.02,
        ],
    ];

    public function tearDown()
    {
        m::close();
    }

    public function testDefaultBase()
    {
        $url = (new Exchange())->getUrl();
        $expected = $this->url.'/latest?base=EUR';

        $this->assertEquals($url, $expected);
    }

    public function testBase()
    {
        $url = (new Exchange())->base('USD')->getUrl();
        $expected = $this->url.'/latest?base=USD';

        $this->assertEquals($url, $expected);
    }

    public function testSymbols()
    {
        $url = (new Exchange())->symbols('USD', 'GBP')->getUrl();
        $expected = $this->url.'/latest?base=EUR&symbols=USD,GBP';

        $this->assertEquals($url, $expected);
    }

    public function testSymbolsAsArray()
    {
        $url = (new Exchange())->symbols(['USD', 'GBP'])->getUrl();
        $expected = $this->url.'/latest?base=EUR&symbols=USD,GBP';

        $this->assertEquals($url, $expected);
    }

    public function testEmptySymbols()
    {
        $url = (new Exchange())->symbols()->getUrl();
        $expected = $this->url.'/latest?base=EUR';

        $this->assertEquals($url, $expected);
    }

    public function testSecure()
    {
        $url = (new Exchange())->secure()->getUrl();
        $expected = str_replace('http', 'https', $this->url).'/latest?base=EUR';

        $this->assertEquals($url, $expected);
    }

    public function testUnsecure()
    {
        $url = (new Exchange())->unsecure()->getUrl();
        $expected = str_replace('https', 'http', $this->url).'/latest?base=EUR';

        $this->assertEquals($url, $expected);
    }

    public function testHistorical()
    {
        $date = '2012-12-12';
        $url = (new Exchange())->historical($date)->getUrl();
        $expected = $this->url.'/'.$date.'?base=EUR';

        $this->assertEquals($url, $expected);
    }

    public function testFullExample()
    {
        $url = (new Exchange())->secure()->base('USD')->symbols('EUR', 'GBP')->getUrl();
        $expected = str_replace('http', 'https', $this->url).'/latest?base=USD&symbols=EUR,GBP';

        $this->assertEquals($url, $expected);
    }

    public function testResponse()
    {
        $response = m::mock('StdClass');
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(self::$successfulResponse));

        $client = m::mock('GuzzleHttp\Client');
        $client->shouldReceive('request')->once()->andReturn($response);

        $exchange = new Exchange($client);
        $exchange->symbols('GBP', 'USD');

        $rates = $exchange->get();
        $expected = self::$successfulResponse['rates'];

        $this->assertEquals($rates, $expected);
    }

    public function testResponseAsObject()
    {
        $response = m::mock('StdClass');
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(self::$successfulResponse));

        $client = m::mock('GuzzleHttp\Client');
        $client->shouldReceive('request')->once()->andReturn($response);

        $exchange = new Exchange($client);
        $exchange->symbols('GBP', 'USD');

        $rates = $exchange->getAsObject();
        $expected = (object) self::$successfulResponse['rates'];

        $this->assertEquals($rates, $expected);
    }

    public function testResponseAsResult()
    {
        $response = m::mock('StdClass');
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(self::$successfulResponse));

        $client = m::mock('GuzzleHttp\Client');
        $client->shouldReceive('request')->once()->andReturn($response);

        $exchange = new Exchange($client);

        $result = $exchange->getResult();
        $this->assertInstanceOf('\tibahut\Fixerio\Result', $result);

        $this->assertEquals(1.01, $result->getRate(Currency::GBP));
    }

    /**
     * @expectedException \tibahut\Fixerio\Exceptions\ResponseException
     *
     * @expectedExceptionMessage Some error message
     */
    public function testResponseException()
    {
        $response = m::mock('StdClass');
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(self::$errorResponse));

        $client = m::mock('GuzzleHttp\Client');
        $client->shouldReceive('request')->once()->andReturn($response);

        $exchange = new Exchange($client);

        $exchange->get();
    }

    /**
     * @expectedException \tibahut\Fixerio\Exceptions\ResponseException
     *
     * @expectedExceptionMessage Some error message
     */
    public function testResponseResultException()
    {
        $response = m::mock('StdClass');
        $response->shouldReceive('getBody')->once()->andReturn(json_encode(self::$errorResponse));

        $client = m::mock('GuzzleHttp\Client');
        $client->shouldReceive('request')->once()->andReturn($response);

        $exchange = new Exchange($client);

        $exchange->getResult();
    }
}
