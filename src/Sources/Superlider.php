<?php

namespace Abiside\NewScrap\Sources;

use Abiside\NewScrap\Source;
use Illuminate\Support\Arr;

class Superlider extends Source
{
    /** @var string */
    protected $baseUrl = "https://superlider.mx";

    /** @var string */
    protected $name = "Superlíder";

    /** @var string */
    protected $slug = "superlider";

    /** @var array */
    protected $feedsAvailable = [
        'category' => [
            'map' => [
                'items' => 'article.item-list',
                'values' => [
                    'title' => 'h2 > a',
                    'link' => 'h2 > a|href',
                    'thumbnail' => 'div.post-thumbnail > a > img|src',
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
        $post = $this->httpClient->get($link)->find('article.post-listing');

        // Get Image
        $imagesAttr = Arr::first($post->find('div.single-post-thumb > img'))->getAttribute('srcset');
        $imageOptions = explode(' ', $imagesAttr);
        $image = null;
        foreach ($imageOptions as $key => $string) {
            if ($key%2 == 0) $image = $string;
        }

        $entry = Arr::first($post->find('div.post-inner > div.entry'));
        $body = "";
        $blocks = $entry->find('p');
        foreach ($blocks as $block) {
            $body .= "<p>{$block->text}</p>";
        }

        if(empty($image)) {
            $imgDom = Arr::first($post->find('div.single-post-thumb > img'));
            $image = $this->getDomAttributeValue($imgDom, 'src', 'image');
        }

        $postData = array_merge($postData, [
            'image' => $image,
            'content' => $body,
        ]);

        return $postData;
    }

}