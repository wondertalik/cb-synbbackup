<?php
/**
 * Created by PhpStorm.
 * User: won
 * Date: 25/06/2017
 * Time: 21:41
 */

namespace Wn\Modules;

use DateTime;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class CustomLogger
{
    /**
     * @var Logger
     */
    protected $logger;
    protected $identity;

    /**
     * CustomLogger constructor.
     * @param string $directory
     * @throws \Exception
     */
    public function __construct($directory = "")
    {
        $this->identity = (new Identity())->GenIdentity();
        $currentDate = new DateTime();
        $path = ROOT_PATH . "/Logs/{$currentDate->format('Y-m-d')}.log";
        if(!empty($directory)) {
            if(!file_exists(ROOT_PATH . "/Logs/{$directory}"))
                mkdir(ROOT_PATH . "/Logs/{$directory}");

            $path = ROOT_PATH . "/Logs/{$directory}/{$currentDate->format('Y-m-d')}.log";
        }
        // create a log channel
        $this->logger = new Logger('custom');
        $this->logger->pushHandler(new StreamHandler($path));
    }

    public function getIdentity()
    {
        return $this->identity;
    }

    public function setIdentity($identity)
    {
        $this->identity = $identity;
        return $this;
    }

    public function info($message, array $data = [])
    {
        $this->logger->info("[{$this->identity}]: {$message}", $data);
    }

    public function error($message, array $data = [])
    {
        $this->logger->error("[{$this->identity}]: {$message}", $data);
    }

    public function critical($message, array $data = [])
    {
        $this->logger->critical("[{$this->identity}]: {$message}", $data);
    }

    public function warning($message, array $data = [])
    {
        $this->logger->warning("[{$this->identity}]: {$message}", $data);
    }

    public function alert($message, array $data = [])
    {
        $this->logger->alert("[{$this->identity}]: {$message}", $data);
    }

    public function emergency($message, array $data = [])
    {
        $this->logger->emergency("[{$this->identity}]: {$message}", $data);
    }

    public function setHandlers(StreamHandler $handler)
    {
        $this->logger->setHandlers([$handler]);
    }
}