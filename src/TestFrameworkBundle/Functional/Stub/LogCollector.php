<?php

namespace Vadiktok\TestFrameworkBundle\Functional\Stub;

use Psr\Log\LoggerInterface;

class LogCollector implements LoggerInterface
{
    /**
     * @var array
     */
    protected $logs = [];

    const LEVEL_EMERGENCY = 'emergency';
    const LEVEL_ALERT = 'alert';
    const LEVEL_CRITICAL = 'critical';
    const LEVEL_ERROR = 'error';
    const LEVEL_WARNING = 'warning';
    const LEVEL_NOTICE = 'notice';
    const LEVEL_INFO = 'info';
    const LEVEL_DEBUG = 'debug';

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = array())
    {
        $this->log(self::LEVEL_EMERGENCY, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = array())
    {
        $this->log(self::LEVEL_ALERT, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = array())
    {
        $this->log(self::LEVEL_CRITICAL, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = array())
    {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = array())
    {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = array())
    {
        $this->log(self::LEVEL_NOTICE, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = array())
    {
        $this->log(self::LEVEL_INFO, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = array())
    {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        $this->logs[$level][] = [
            'message' => $message,
            'context' => $context,
        ];
    }

    /**
     * @param $level
     * @param null $message
     * @param array $context
     * @return bool
     */
    public function has($level, $message = null, $context = []) : bool
    {
        return $this->get($level, $message, $context) !== null;
    }

    /**
     * @param $level
     * @param null $message
     * @param array $context
     * @return array|null
     */
    public function get($level, $message = null, $context = [])
    {
        if (!array_key_exists($level, $this->logs)) {
            return null;
        }

        if (null !== $message || null !== $context) {
            $result = [];
            $test = array_filter([
                'message' => $message,
                'context' => $context,
            ]);
            foreach ($this->logs[$level] as $log) {
                if (array_intersect_key($log, $test) === $test) {
                    $result[] = $log;
                }
            }
            return $result;
        }

        return $this->logs[$level];
    }
}
