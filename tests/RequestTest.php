<?php
declare(strict_types=1);

namespace B2Binpay\Tests;

use B2Binpay\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\TransferException;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var MockHandler
     */
    protected $mockHandler;

    /**
     * @var Request
     */
    protected $request;

    public function setUp()
    {
        $this->mockHandler = new MockHandler();
        $this->client = new Client([
            'handler' => $this->mockHandler,
        ]);

        $this->request = new Request($this->client);
    }

    public function testUpdateAccessToken()
    {
        $responseToken = 'mockToken';

        $this->mockHandler->append(
            new Response(200, [], $this->makeTokenResponse($responseToken))
        );

        $token = $this->request->token($this->getUrl(), $this->getAuthBasic());
        $this->assertSame($responseToken, $token);
    }

    /**
     * @expectedException \B2Binpay\Exception\ConnectionErrorException
     */
    public function testUpdateAccessTokenConnectionError()
    {
        $this->mockHandler->append(
            new TransferException(
                "Error"
            )
        );

        $this->request->token($this->getUrl(), $this->getAuthBasic());
    }

    /**
     * @expectedException \B2Binpay\Exception\EmptyResponseException
     */
    public function testUpdateAccessTokenEmptyResponse()
    {
        $this->mockHandler->append(
            new Response(400, [], null)
        );

        $this->request->token($this->getUrl(), $this->getAuthBasic());
    }

    /**
     * @expectedException \B2Binpay\Exception\ServerApiException
     */
    public function testUpdateAccessTokenServerError()
    {
        $response = json_encode([
            'code' => '-100500',
            'error' => 'SOME_API_ERROR'
        ]);

        $this->mockHandler->append(
            new Response(400, [], $response)
        );

        $this->request->token($this->getUrl(), $this->getAuthBasic());
    }

    public function testSend()
    {
        $data1 = 'OK1';
        $data2 = 'OK2';

        $this->mockHandler->append(
            new Response(200, [], $this->makeDataResponse($data1)),
            new Response(200, [], $this->makeDataResponse($data2))
        );

        $return = $this->request->send($this->getToken(), 'get', '/');
        $this->assertEquals($data1, $return);

        $return = $this->request->send($this->getToken(), 'post', '/');
        $this->assertEquals($data2, $return);
    }

    /**
     * @expectedException \B2Binpay\Exception\ConnectionErrorException
     */
    public function testSendConnectionError()
    {
        $this->mockHandler->append(
            new TransferException(
                "Error"
            )
        );

        $this->request->send($this->getToken(), 'get', 'alarm');
    }

    /**
     * @expectedException \B2Binpay\Exception\EmptyResponseException
     */
    public function testSendEmptyResponse()
    {
        $this->mockHandler->append(
            new Response(400, [], null)
        );

        $this->request->send($this->getToken(), 'get', '/');
    }

    /**
     * @expectedException \B2Binpay\Exception\ServerApiException
     */
    public function testSendServerError()
    {
        $response = json_encode([
            'code' => '-100500',
            'error' => 'SOME_API_ERROR'
        ]);

        $this->mockHandler->append(
            new Response(400, [], $response)
        );

        $this->request->send($this->getToken(), 'get', '/');
    }

    /**
     * @expectedException \B2Binpay\Exception\UpdateTokenException
     */
    public function testUpdateTokenException()
    {
        list($code, $error) = $this->request::ERROR_UPDATE_TOKEN;

        $responseUpdateToken = json_encode([
            'code' => $code,
            'error' => $error
        ]);

        $this->mockHandler->append(
            new Response(400, [], $responseUpdateToken)
        );

        $this->request->send($this->getToken(), 'get', '/');
    }

    private function getToken()
    {
        return 'mockToken';
    }

    /**
     * @return string
     */
    private function getUrl()
    {
        return 'url';
    }

    /**
     * @return string
     */
    private function getAuthBasic()
    {
        return getenv('AUTH_BASIC');
    }

    /**
     * @param string $data
     * @return string
     */
    private function makeDataResponse(string $data): string
    {
        return json_encode([
            'data' => $data
        ]);
    }

    /**
     * @param string $token
     * @return string
     */
    private function makeTokenResponse(string $token): string
    {
        return json_encode([
            'token_type' => 'Bearer',
            'access_token' => $token
        ]);
    }
}
