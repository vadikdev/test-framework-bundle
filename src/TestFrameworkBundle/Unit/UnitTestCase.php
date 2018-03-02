<?php

namespace Vadiktok\TestFrameworkBundle\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

use \PHPUnit_Framework_MockObject_Matcher_Invocation as Matcher;
use ReflectionClass;

abstract class UnitTestCase extends TestCase
{
    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    public function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        parent::setUp();
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     * @param Matcher|null $matcher
     */
    public function assertLog(string $level, string $message, array $context = [], Matcher $matcher = null)
    {
        $this->logger->expects(self::getMatcher($matcher))
            ->method($level)
            ->with($message, $context);
    }

    /**
     * @param Matcher|null $recorder
     * @return Matcher
     */
    protected static function getMatcher(Matcher $recorder = null) : Matcher
    {
        return $recorder === null ? self::once() : $recorder;
    }

    protected function assertMethodExists($object, $method)
    {
        $this->assertTrue(
            method_exists($object, $method),
            sprintf('Failed asserting that class %s has method %s', get_class($object), $method)
        );
    }

    protected function assertMethodNotExists($object, string $method)
    {
        $this->assertFalse(
            method_exists($object, $method),
            sprintf('Failed asserting that class %s hasn\'t method %s', get_class($object), $method)
        );
    }

    protected function assertClassExists(string $className)
    {
        $this->assertTrue(
            class_exists($className),
            sprintf('Failed asserting that class %s exists', $className)
        );
    }

    /**
     * @param $object
     * @param $property
     * @param $value
     */
    protected function injectProperty($object, $property, $value)
    {
        $reflection = new ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);

        $reflection_property->setValue($object, $value);
    }
}
