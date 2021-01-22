<?php

namespace Abiside\NewScrap;

class Post
{
    /** @var  Abiside\NewScrap\Source  */
    protected $source;

    /** @var string  */
    protected $title;

    /** @var string */
    protected $link;

    /** @var string */
    protected $thumbnail;

    /** @var string */
    protected $image;

    /** @var string $content */
    protected $content;

    /** @var date $date */
    protected $date;

    /**
     * Return the complete post object for the given parameters
     *
     * @param  string  $title
     * @param  string  $link
     * @param  string  $thumbnail
     * @return Abiside\NewScrap\Post
     */
    public function __construct(Source $source, string $title, string $link, string $thumbnail)
    {
        $this->title = $title;
        $this->link = $link;
        $this->thumbnail = $thumbnail;
    }
}