<?php
namespace Clearbooks\LabsApi;
use Clearbooks\LabsApi\Framework\Endpoint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class EndpointTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Endpoint
     */
    protected $endpoint;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @param $query
     */
    protected function executeWithQuery( $query )
    {
        $this->response = $this->endpoint->execute( new Request( $query ) );
    }

    /**
     * Assert a JSON response
     * @param $expected
     */
    protected function assertJsonResponse( $expected )
    {
        $this->assertEquals( $expected, json_decode( $this->response->getContent(), true ) );
    }
}