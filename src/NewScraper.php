<?php

namespace Abiside\NewScrap;

use Abiside\NewScrap\Sources\Superlider;
use Illuminate\Support\Arr;

class NewScraper
{
    /** @var Abiside\NewScrap\Source */
    protected $activeSource;

    /** @var array */
    protected $availableSources = [
        'superlider' => Superlider::class,
    ];

    public function __construct()
    {

    }

    /**
     * Return the latest posts for the given source
     *
     * @param  string  $source
     * @param  string|array  $feed
     */
    public function getLatestPosts(string $source = null, $feed = null)
    {
        $source = $this->getActiveSource($source);

        $posts = $source->getFeedPosts($feed);
    }

    /**
     * Get the active Source class object to make the requests
     *
     * @param  string  $source
     * @return Abiside\NewScrap\Source
     */
    public function getActiveSource(string $source = null)
    {
        return ($this->activeSource && optional($this->activeSource)->slug == $source) || is_null($source)
             ? $this->activeSource
             : $this->setActiveSource($source);
    }

    /**
     * Set the active Source class object to make the requests
     *
     * @param  string  $source
     * @return Abiside\NewScrap\Source
     */
    public function setActiveSource($source)
    {
        $class = Arr::get($this->availableSources, $source);

        return $this->activeSource = $class ? new $class : null;
    }
}