<?php

namespace Abiside\NewScrap\Sources;

use Abiside\NewScrap\Source;
use Illuminate\Support\Arr;

class Mediotiempo extends Source
{
    /** @var string */
    protected $baseUrl = "https://www.mediotiempo.com";

    /** @var string */
    protected $name = "MedioTiempo";

    /** @var string */
    protected $slug = "mediotiempo";

    /** @var array */
    protected $feedsAvailable = [
        'temas' => [
            'map' => [
                'items' => 'div.item-news-container',
                'values' => [
                    'title' => 'div.title-container > div.title > a > h2',
                    'link' => 'div.title-container > div.title > a|href',
                    'thumbnail' => 'div.img-container > a > img|data-lazy',
                ],
            ]
        ],
    ];

    /**
     * Return the post detail for a given post link
     *
     * @param  string  $link
     * @param  array  $postData
     * @return Abiside\NewScrap\Post
     */
    public function getPostData(string $link, array $postData)
    {
        $post = $this->httpClient->get($link);

        // Get Image
        //dd($post->find('div.img-container > img'));
        $image = Arr::first($post->find('div.img-container > img'))->getAttribute('src');

        $entry = Arr::first($post->find('div#content-body'));
        $body = "";
        $blocks = $entry->find('p');
        foreach ($blocks as $block) {
            $text = $this->cleanText($block->text);
            $body .= "<p>{$text}</p>";
        }

        $postData = array_merge($postData, [
            'image' => $image,
            'content' => $body,
        ]);

        return $postData;
    }

}
