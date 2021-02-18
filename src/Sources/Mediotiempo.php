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
        $image = Arr::first($post->find('div.img-container > img'))->getAttribute('src');
        $entry = Arr::first($post->find('div#content-body'));

        // Delete the div with recommended posts
        if ($toDelete = $entry->find('div.nd-rnd-media')[0]) {
            $toDelete->delete();
            unset($toDelete);
        };

        $body = "";

        $child = $entry->firstChild();

        do {
            $tag = $child->getTag()->name();
            switch ($tag) {
                // For images
                case 'img':
                    $body .= $child->outerHtml;
                // For paragraphs
                case 'p':
                    $body .= ! $this->domIsEmpty($child) ? $child->outerHtml : "";
                    break;

                // For divs
                case 'div':
                    $nestedDivs = $child->find('div');
                    $divContent = $child->outerHtml;

                    if ($this->domIsEmpty($child)) break;

                    if (count($nestedDivs)) {
                        foreach ($nestedDivs as $div) {
                            if ($videoId = $div->getAttribute('video-id')) {
                                $divContent = '<div>' . $this->embedYoutube($videoId) . '</div>';

                            } else if ($div->getAttribute('class') == 'see-also') {
                                $divContent = "";
                            }
                        }
                    }

                    $body .= $divContent;
                    break;
            }

            $child = $child->hasNextSibling() ? $child->nextSibling() : false;
        } while ($child);

        $postData = array_merge($postData, [
            'image' => $image,
            'content' => $body,
        ]);

        return $postData;
    }

}
