<?php

namespace Abiside\NewScrap;

use Abiside\NewScrap\Post;
use Illuminate\Support\Arr;
use PHPHtmlParser\Dom\Node\HtmlNode as Dom;

class Source
{
    /** @var string */
    protected $baseUrl;

    /** @var string */
    protected $name;

    /** @var string */
    protected $slug;

    /** @var Illuminate\Support\Facades\Http */
    protected $httpClient;

    /** @var array */
    protected $feedsAvailable;

    /** @var Illuminate\Support\Collection */
    protected $feeds;

    public function __construct()
    {
        $this->httpClient = new HttpClient;
    }

    /**
     * Return the posts for the give source feeds
     *
     *
     */
    public function getFeedPosts($feeds)
    {
        $feeds = collect(is_array($feeds) ? $feeds : [$feeds]);
        $feedsAvailable = $this->feedsAvailable;

        $feedsToGet = $feeds->map(function($feed) use ($feedsAvailable) {
            foreach($feedsAvailable as $feedKey => $function) {
                if (str_starts_with($feed, $feedKey)) {
                    return [
                        'feed' => $feed,
                        'scrap' => $function,
                    ];
                }
            }
        });

        return $feedsToGet->flatMap(function ($feed) {
            return $this->getPosts($feed);
        });
    }

    protected function getPosts($feed)
    {
        $scrapMap = Arr::get($feed, 'scrap.map');
        $url = $this->getFeedUrl(Arr::get($feed, 'feed'));
        $posts = [];

        if ($content = $this->httpClient->get($url)) {
            $items = collect($content->find(Arr::get($scrapMap, 'items')));

            $valuesScrapMap = Arr::get($scrapMap, 'values');

            $posts = $items->map(function ($item) use ($valuesScrapMap) {
                foreach ($valuesScrapMap as $field => $scrapMap) {

                    $aux = explode('|', $scrapMap);
                    $scrapMap = Arr::first($aux);
                    $prop = Arr::get($aux, 1);

                    $valueTag = Arr::first($item->find($scrapMap));

                    $post[$field] = $prop
                        ? $this->getDomAttributeValue($valueTag, $prop, $field)
                        : $this->cleanText($valueTag->text);
                }

                return $post;
            });
        }

        return $posts;
    }

    public function getDomAttributeValue($dom, $prop, $field = null)
    {
        $attributeValue = $dom->getAttribute($prop);

        // If we identify the content is not an image wrapper and refers to
        // one of the links attributes
        if (! str_starts_with($attributeValue, 'data:image')) {
            if (in_array($field, ['thumbnail', 'link', 'image'])) {
                $attributeValue = $this->normalizeUrl($attributeValue);
            }

            return $attributeValue;
        }

        // Find for an embed SVG image
        $strAux = explode(',', $attributeValue);
        $str = base64_decode(Arr::get($strAux, 1));
        $dom = $this->httpClient->getDomFromString($str);
        $svg = Arr::first($dom->find('svg'));
        $imageUrl = $svg->getAttribute('data-u');

        return $imageUrl ? urldecode($imageUrl) : null;
    }

    public function normalizeUrl(string $url)
    {
        if (! str_starts_with($url, 'http')) {
            $clean = trim($url, '/');

            $url = "{$this->baseUrl}/$clean";
        }

        return $url;
    }

    /**
     * Return a clear text from the given one
     *
     * @param  string  $text
     * @return string
     */
    public function cleanText(string $text)
    {
        return htmlspecialchars_decode($text, ENT_QUOTES);
    }

    /**
     * Return if a dom element is empty
     *
     * @param  \PHPHtmlParser\Dom\Node\HtmlNode  $dom
     * @return bool
     */
    public function domIsEmpty(Dom $dom): bool
    {
        $content = trim(strip_tags($dom->innerHtml));

        return mb_strlen($content) < 3;
    }

    /**
     * Return the html youtube embed code
     *
     * @param  string  $videoId
     * @return string
     */
    public function embedYoutube(string $videoId)
    {
        return "<iframe width='100%' height='360' src='https://www.youtube.com/embed/{$videoId}'
                frameborder='0' allow='accelerometer; autoplay; clipboard-write;
                encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>";
    }

    /**
     * Return the feed full url to get data from HTTP
     *
     * @param  string  $feed
     * @return string
     */
    protected function getFeedUrl(string $feed)
    {
        $feed = trim($feed, '/');

        return "{$this->baseUrl}/{$feed}";
    }
}
