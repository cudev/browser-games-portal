<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Cudev Ltd.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Ludos\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CudevAccessOnly
{
    const COMPANY_IP = '82.117.232.9';
    const USER = 'cudevgames';
    const PASS = 'cudevgames';

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        if ($this->isAuthorized($request)) {
            return $next($request, $response);
        }
        return $response->withStatus(401)->withHeader('WWW-Authenticate', 'Basic realm="Protected Area"');
    }

    private function isAuthorized(ServerRequestInterface $request)
    {
        $serverParams = $request->getServerParams();
        $remoteAddress = $serverParams['REMOTE_ADDRESS'] ?? null;
        $user = $serverParams['PHP_AUTH_USER'] ?? null;
        $pass = $serverParams['PHP_AUTH_PW'] ?? null;
        return !($remoteAddress !== self::COMPANY_IP && ($user !== self::USER || $pass !== self::PASS));
    }
}
