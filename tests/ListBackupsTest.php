<?php
namespace tests;

use AppBundle\Connector\Connector;
use AppBundle\Connector\ConnectorSites;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;

class ListBackupsTest extends TestCase
{

    protected $root;

    protected $successBody;

    protected $failBody;

    public function setUp()
    {
        parent::setUp();
        $this->root = __DIR__.'/../';
        if (!file_exists($this->root.'/sitefacotry.yml')) {
            copy($this->root.'/sitefactory.default.yml', $this->root.'/sitefactory.yml');
        }
        $this->successBody = file_get_contents(__DIR__.'/Mocks/listSuccess.json');
        $this->failBody = file_get_contents(__DIR__.'/Mocks/pingFail.json');
    }

    public function testBackupsSuccess()
    {
        $mock = new MockHandler(
            [
            new Response(200, [], $this->successBody),
            ]
        );
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $connector = new Connector($client);
        $connectorSites = new ConnectorSites($connector);

        $this->assertTrue(is_array($connectorSites->listBackups()));
    }

    public function testBackupsFail()
    {
        $mock = new MockHandler(
            [
            new Response(403, [], $this->failBody),
            ]
        );
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $connector = new Connector($client);
        $connectorSites = new ConnectorSites($connector);

        $this->assertTrue($connectorSites->listBackups() === 'Access denied');
    }

    public function tearDown()
    {
        parent::tearDown();
        unlink($this->root.'/sitefactory.yml');
    }
}
