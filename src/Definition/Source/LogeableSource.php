<?php

declare(strict_types=1);

namespace DI\Definition\Source;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

trait LogeableSource
{
    protected ?LoggerInterface $logger = null;

    /** @psalm-var LogLevel::* */
    protected ?string $logLevel = null;

    /**
     * @psalm-param LogLevel::* $logLevel
     * @throws \InvalidArgumentException in case of invalid log level
     */
    public function setLogger(LoggerInterface $logger, string $logLevel = LogLevel::DEBUG): self
    {
        $allowedLogLevels = [
            LogLevel::DEBUG,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
            LogLevel::ERROR,
            LogLevel::CRITICAL,
            LogLevel::ALERT,
            LogLevel::EMERGENCY,
        ];
        if (!in_array($logLevel, $allowedLogLevels, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid log level "%s"', $logLevel));
        }

        $this->logger = $logger;
        $this->logLevel = $logLevel;

        return $this;
    }
}