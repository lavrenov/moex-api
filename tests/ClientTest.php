<?php

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();

        $handler = HandlerStack::create($this->mockHandler);
        Client::setExtraOption('handler', $handler);
    }

    protected function tearDown(): void
    {
        $this->mockHandler = null;

        Client::setExtraOption('handler', null);
        Client::destroyInstance();
    }

    public function testGetCounter(): void
    {
        self::assertEquals(0, Client::getInstance()->getCounter());
    }

    /**
     * @throws ReflectionException
     */
    public function testFailedRequestThrowsException(): void
    {
        $mock = $this->getMockBuilder(Client::class)->onlyMethods(['doRequest'])->getMock();
        $mock->method('doRequest')->will($this->throwException(new TransferException));

        $this->expectException(Exception::class);

        $reflection = new ReflectionClass($mock);
        $method = $reflection->getMethod('request');
        $method->setAccessible(true);

        $method->invokeArgs($mock, ['invalid_uri']);
    }

    /**
     * @throws GuzzleException
     */
    public function testSuccessfulRequest(): void
    {
        $response = new Response(200, ['Content-Type' => 'application/json'], '{}');
        $this->mockHandler->append($response);

        $client = Client::getInstance();
        $client->setRequestOption('test', true);
        $this->assertIsArray($client->getEngineList());
    }
}
