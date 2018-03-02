<?php

namespace Vadiktok\TestFrameworkBundle;

use Liip\FunctionalTestBundle\Test\WebTestCase;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Vadiktok\TestFrameworkBundle\Functional\Stub\LogCollector;

abstract class FunctionalTestCase extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client = null;

    /**
     * @var LogCollector
     */
    protected $logger;

    /**
     * @return array
     */
    protected function getFixtures() : array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->client = $this->makeClient();
        $this->loadFixtures($this->getFixtures());

        $this->logger = new LogCollector();
        $this->getContainer()->set('logger', $this->logger);

        parent::setUp();
    }

    protected function assertAccessDeniedResponse()
    {
        $this->assertStatusCode(403, $this->client);
    }

    protected function assertHasFlashMessage(string $type, string $message = null)
    {
        $flashBag = $this->client->getContainer()->get('session')->getFlashBag();
        $this->assertTrue(
            $flashBag->has($type),
            sprintf('Failed asserting that flash message with type "%s" exists.', $type)
        );
        if (null !== $message) {
            $this->assertTrue(
                in_array($message, $flashBag->get($type)),
                sprintf('Failed asserting that flash message "%s" exists.', $message)
            );
        }
    }

    /**
     * @param $url
     * @param array $parameters
     * @param array $files
     * @param array $server
     * @param null $content
     * @return Crawler
     */
    protected function get($url, array $parameters = [], array $files = [], array $server = [], $content = null)
    {
        return $this->client->request('GET', $url, $parameters, $files, $server, $content);
    }

    protected function assertCountRecords(int $expected, string $className)
    {
        $actual = $this->getEntityManager()->getRepository($className)->getCount();
        $this->assertEquals($expected, $actual);
    }

    protected function assertLog($level, $message = null, $context = null, bool $fromDataCollector = false)
    {
        if (!$fromDataCollector) {
            $this->assertTrue($this->logger->has($level, $message, $context));
        } else {
            $profiler = $this->getContainer()->get('profiler')
                ->loadProfileFromResponse($this->client->getResponse());

            $found = false;
            foreach ($profiler->getCollector('logger')->getLogs() as $log) {
                if ($log['priorityName'] === strtoupper($level) && (null === $message || $log['message'] === $message) && (null === $context || $log['context'] === $context)) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found);
        }
    }
}
