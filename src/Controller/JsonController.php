<?php declare(strict_types=1);


namespace Useful\Controller;


use Psr\Http\Message\ResponseInterface;

class JsonController extends AbstractController
{

    final public function render(array $body): string
    {
        return json_encode($body, JSON_THROW_ON_ERROR);
    }
}




