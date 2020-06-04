<?php
declare(strict_types = 1);
namespace Useful\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractController implements ControllerInterface
{

    private ResponseInterface $response;

    private LoggerInterface $log;

    public function __construct(ResponseInterface $response, LoggerInterface $log)
    {
        $this->response = $response;
        $this->log = $log;
    }

    final public function getLogger(): LoggerInterface
    {
        return $this->log;
    }

    final public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    final public function writeResponse(string $body, int $status = 200): ResponseInterface
    {
        $this->response->getBody()->write($body);
        return $this->response->withStatus($status);
    }

    abstract public function render(array $body): string;
}
