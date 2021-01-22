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

        $feedsToGet->each(function ($feed) {
            $posts = $this->getPosts($feed);
        });

        // return $this->getPostData($titleLink->getAttribute('href'), $post);
    }

    protected function getPosts($feed)
    {
        $scrapMap = Arr::get($feed, 'scrap.map');
        $url = $this->getFeedUrl(Arr::get($feed, 'feed'));
        $content = $this->httpClient->get($url);

        $items = collect($content->find(Arr::get($scrapMap, 'items')));
        $valuesScrapMap = Arr::get($scrapMap, 'values');

        $posts = $items->map(function ($item) use ($valuesScrapMap) {
            foreach ($valuesScrapMap as $field => $scrapMap) {
                $aux = explode('|', $scrapMap);
                $scrapMap = Arr::first($aux);
                $prop = Arr::get($aux, 1);

                $valueTag = Arr::first($item->find($scrapMap));

                $post[$field] = $prop
                    ? $valueTag->getAttribute($prop)
                    : $valueTag->text;
            }

            return $post;
        });

        return $posts;
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