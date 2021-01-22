<?php

namespace Abiside\NewScrap;

use Illuminate\Support\Facades\Http;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;

class HttpClient
{
    /**
     * Execute a GET request to the given URL
     *
     * @param  string  $url
     * @return PHPHtmlParser\Dom
     */
    public function get($url): ?Dom
    {
        return $this->makeRequest($url, 'get');
    }

    /**
     * Execute an HTTP request foor the given url and method
     *
     * @param  string  $url
     * @param  string  $method
     * @return PHPHtmlParser\Dom
     */
    public function makeRequest($url, $method = 'get'): ?Dom
    {
        $response = Http::{$method}($url);

        return $response->successful()
            ? (new Dom)->loadStr($response->body(),
                (new Options())->setRemoveStyles(false)
            ) : null;
    }
}