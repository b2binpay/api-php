<?php
declare(strict_types=1);

namespace B2Binpay\Tests\v1;

use B2Binpay\Request;
use B2Binpay\v1\Api;
use B2Binpay\Exception\UpdateTokenException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ApiTest extends TestCase
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var Request | MockObject
     */
    protected $request;

    public function setUp()
    {
        $this->request = $this->createMock(Request::class);

        $this->api = new Api(
            getenv('AUTH_KEY'),
            getenv('AUTH_SECRET'),
            $this->request,
            true
        );
    }

    public function tearDown()
    {
        $this->api = null;
        $this->request = null;
    }

    public function testGenAuthBasic()
    {
        $this->assertEquals($this->getAuthBasic(), $this->api->genAuthBasic());
    }

    public function testSetAndGetAccessToken()
    {
        $token = 'mockToken';
        $this->api->setAccessToken($token);
        $this->assertSame($token, $this->api->getAccessToken());
    }

    public function testGetNewBillUrl()
    {
        $node = $this->api->getNode(1000);
        $this->assertEquals($this->getNode(), $node);

        $this->api->setTesting(true);
        $url = $this->api->getNewBillUrl(1000);
        $this->assertEquals($this->api::GW_TEST . $this->api::URI_BILLS, $url);

        $this->api->setTesting(false);
        $url = $this->api->getNewBillUrl(1000);
        $this->assertEquals($node . $this->api::URI_BILLS, $url);
    }

    public function testGetBillsUrl()
    {
        $bill1 = 1;
        $bill2 = 2;

        $testUrl = $this->api::GW_TEST . $this->api::URI_BILLS;
        $prodUrl = $this->api::GW_PRODUCTION . $this->api::URI_BILLS;

        $this->api->setTesting(true);
        $url = $this->api->getBillsUrl();
        $this->assertEquals($testUrl, $url);

        $this->api->setTesting(false);
        $url = $this->api->getBillsUrl();
        $this->assertEquals($prodUrl, $url);

        $this->api->setTesting(false);
        $url = $this->api->getBillsUrl($bill1);
        $this->assertEquals($prodUrl . '/' . $bill1, $url);

        $this->api->setTesting(true);
        $url = $this->api->getBillsUrl($bill2);
        $this->assertEquals($testUrl . '/' . $bill2, $url);
    }

    public function testGetWalletsUrl()
    {
        $wallet1 = 1;
        $wallet2 = 2;

        $testUrl = $this->api::GW_TEST . $this->api::URI_WALLETS;
        $prodUrl = $this->api::GW_PRODUCTION . $this->api::URI_WALLETS;

        $this->api->setTesting(true);
        $url = $this->api->getWalletsUrl();
        $this->assertEquals($testUrl, $url);

        $this->api->setTesting(false);
        $url = $this->api->getWalletsUrl();
        $this->assertEquals($prodUrl, $url);

        $this->api->setTesting(false);
        $url = $this->api->getWalletsUrl($wallet1);
        $this->assertEquals($prodUrl . '/' . $wallet1, $url);

        $this->api->setTesting(true);
        $url = $this->api->getWalletsUrl($wallet2);
        $this->assertEquals($testUrl . '/' . $wallet2, $url);
    }

    public function testGetRatesUrl()
    {
        $this->api->setTesting(true);
        $url = $this->api->getRatesUrl();
        $this->assertEquals($this->api::GW_TEST . $this->api::URI_DEPOSIT, $url);

        $this->api->setTesting(false);
        $url = $this->api->getRatesUrl();
        $this->assertEquals($this->api::GW_PRODUCTION . $this->api::URI_DEPOSIT, $url);
    }

    public function testSendRequestUpdateToken()
    {
        $mockToken = 'mockToken';
        $newToken = 'newToken';
        $this->api->setAccessToken($mockToken);

        $this->request
            ->method('send')
            ->will($this->onConsecutiveCalls(
                $this->throwException(new UpdateTokenException),
                []
            ));

        $this->request->expects($this->once())
            ->method('token')
            ->willReturn($newToken);

        $this->api->sendRequest('get', 'url');
        $token = $this->api->getAccessToken();
        $this->assertEquals($newToken, $token);
    }

    /**
     * @expectedException \B2Binpay\Exception\UnknownValueException
     */
    public function testGetNodeUnknownValue()
    {
        $this->api->getNode(9999);
    }
    
    private function getAuthBasic()
    {
        return getenv('AUTH_BASIC');
    }

    private function getNode()
    {
        return getenv('NODE_BTC');
    }
}
