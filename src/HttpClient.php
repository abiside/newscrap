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
        // TODO: Check why HTTP doesn't work
        //$response = Http::{$method}($url);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url
        ));

        $response = curl_exec($curl);

        return $response ? $this->getDomFromString($response) : null;
    }

    /**
     * Return the DOM element for the given string
     *
     * @param  string  $html
     * @return PHPHtmlParser\Dom
     */
    public function getDomFromString(string $html)
    {
        return (new Dom)->loadStr($html,
            (new Options())->setRemoveStyles(false)
        );
    }
}