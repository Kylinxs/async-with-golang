<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

use GuzzleHttp\Psr7\getallheaders;
use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7\ServerRequest;

class Services_OAuthServer_Utilities
{
    public function tiki2Psr7Request($tikireq)
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $headers = getallheaders();

        $uri = ServerRequest::getUriFromGlobals();
        $body = new LazyOpenStream('php://input', 'r+');
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']) : '1.1';

        $serverRequest = new ServerRequest($method, $uri, $headers, $body, $protocol, $_SERVER);

        return $serverRequest
            ->withCookieParams($_COOKIE)
            ->withQueryParams($tikireq->asArray())
            ->withParsedBody($tikireq->asArray())
            ->withUploadedFiles(ServerRequest::normalizeFiles($_FILES));
    }

    public function processPsr7Response($response)
    {
        $statusLine = sprintf(
            'HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        header($statusLine, true);

        foreach ($response->getHeaders() as $name => $value) {
            $value = $response->getHeaderLine($name);
            $responseHeader = sprintf('%s: %s', $name, $value);
            header($responseHeader, false);
        }

        echo $response->getBody();
        exit();
    }
}
