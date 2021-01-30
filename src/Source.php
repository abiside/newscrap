<?php

namespace Abiside\NewScrap;

use Abiside\NewScrap\Post;
use Illuminate\Support\Arr;
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
                        ? $this->getDomAttributeValue($valueTag, $prop)
                        : $valueTag->text;
                }

                return $post;
            });
        }

        return $posts;
    }

    public function getDomAttributeValue($dom, $prop)
    {
        $attributeValue = $dom->getAttribute($prop);

        if (! str_starts_with($attributeValue, 'data:image')) return $attributeValue;

        // Find for an embed SVG image
        $strAux = explode(',', $attributeValue);
        $str = base64_decode(Arr::get($strAux, 1));
        $dom = $this->httpClient->getDomFromString($str);
        $svg = Arr::first($dom->find('svg'));
        $imageUrl = $svg->getAttribute('data-u');

        return $imageUrl ? urldecode($imageUrl) : null;
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