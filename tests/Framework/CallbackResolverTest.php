<?php
namespace Clearbooks\LabsApi\Framework;
use Silex\Application;
use stdClass;
use TomVerran\MockContainer;

class CallbackResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockContainer
     */
    private $mockContainer;

    /**
     * @var CallbackResolver
     */
    private $resolver;

    /**
     * @var Application
     */
    private $app;

    /**
     * @param $callback
     * @return array
     */
    private function resolve( $callback )
    {
        return $this->resolver->resolveCallback( $callback );
    }

    /**
     * Set up
     */
    public function setUp()
    {
        $this->app = new Application;
        $this->mockContainer = new MockContainer( [ MiddlewareDummy::class => new MiddlewareDummy ] );
        $this->resolver = new CallbackResolver( $this->mockContainer, $this->app );
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function givenNullName_throwException()
    {
        $this->resolve( null );
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function givenClassWhichIsNotMiddleware_throwException()
    {
        $this->resolve( StdClass::class );
    }

    /**
     * @test
     */
    public function givenClosure_returnClosure()
    {
        $closure = function() {
            echo 'cats';
        };
        $this->assertEquals( $closure, $this->resolve( $closure ) );
    }

    /**
     * @test
     */
    public function givenCallable_returnCallable()
    {
        $callable = [ $this, 'setUp' ];
        $this->assertEquals( $callable, $this->resolve( $callable ) );
    }


    /**
     * @test
     */
    public function givenClassWhichIsMiddleware_returnArrayOfObjectAndMethod()
    {
        $this->assertEquals([new MiddlewareDummy(), 'execute'],  $this->resolve( MiddlewareDummy::class ) );
    }
}