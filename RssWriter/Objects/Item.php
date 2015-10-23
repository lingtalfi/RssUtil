<?php

namespace RssUtil\RssWriter\Objects;

/*
 * LingTalfi 2015-10-23
 * http://www.rssboard.org/rss-specification
 */
class Item
{

    private $title;
    private $link;
    private $description;
    private $author;
    private $category;
    private $comments;
    private $enclosure;
    private $guid;
    private $pubDate;
    private $source;

    public function __construct()
    {
    }


    public static function create()
    {
        return new static();
    }


    public function author($author)
    {
        $this->author = $author;
        return $this;
    }

    public function category($category)
    {
        $this->category = $category;
        return $this;
    }

    public function comments($comments)
    {
        $this->comments = $comments;
        return $this;
    }

    public function description($description)
    {
        $this->description = $description;
        return $this;
    }

    public function enclosure($enclosure)
    {
        $this->enclosure = $enclosure;
        return $this;
    }

    public function guid($guid)
    {
        $this->guid = $guid;
        return $this;
    }

    public function link($link)
    {
        $this->link = $link;
        return $this;
    }

    public function pubDate($pubDate)
    {
        $this->pubDate = $pubDate;
        return $this;
    }

    public function source($source)
    {
        $this->source = $source;
        return $this;
    }

    public function title($title)
    {
        $this->title = $title;
        return $this;
    }



    //------------------------------------------------------------------------------/
    // 
    //------------------------------------------------------------------------------/


    public function getAuthor()
    {
        return $this->author;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getEnclosure()
    {
        return $this->enclosure;
    }

    public function getGuid()
    {
        return $this->guid;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getPubDate()
    {
        return $this->pubDate;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getTitle()
    {
        return $this->title;
    }


}
