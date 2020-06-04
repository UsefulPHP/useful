<?php
declare(strict_types = 1);
namespace Useful\Controller;

use Psr\Http\Message\ResponseInterface;

interface ControllerInterface
{

    public function getResponse(): ResponseInterface;

    public function writeResponse(string $body, int $status = 200): ResponseInterface;

    public function render(array $body): string;
}
