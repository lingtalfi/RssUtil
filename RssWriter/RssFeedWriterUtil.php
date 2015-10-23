<?php

namespace RssUtil\RssWriter;

/*
 * LingTalfi 2015-10-23
 */
use RssUtil\RssWriter\Exception\RssWriterException;
use RssUtil\RssWriter\Objects\Channel;
use RssUtil\RssWriter\Objects\ChannelImage;
use RssUtil\RssWriter\Objects\ChannelSkipDays;
use RssUtil\RssWriter\Objects\ChannelSkipHours;

class RssFeedWriterUtil
{


    /**
     * @var Channel
     */
    private $channel;
    private $autoEscapeMode;

    public function __construct()
    {
        $this->channels = [];
        $this->autoEscapeMode = false;
    }

    public static function create()
    {
        return new static();
    }

    public function autoEscapeMode($bool)
    {
        $this->autoEscapeMode = (bool)$bool;
        return $this;
    }

    public function setChannel(Channel $channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @return string
     * @throws RssWriterException
     */
    public function render()
    {

        if (true === extension_loaded('libxml')) {


            libxml_use_internal_errors(true);
            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><rss version="2.0" />');


            $channelElements = [
                'language' => false,
                'copyright' => false,
                'managingEditor' => false,
                'webMaster' => false,
                'pubDate' => false,
                'lastBuildDate' => false,
                'category' => true,
                'generator' => false,
                'docs' => false,
                'cloud' => true,
                'ttl' => false,
                'image' => 0,
                'rating' => false,
                'textInput' => false,
                'skipHours' => 0,
                'skipDays' => 0,
            ];

            $itemElements = [
                'title' => false,
                'link' => false,
                'description' => false,
                'author' => false,
                'category' => true,
                'comments' => false,
                'enclosure' => true,
                'guid' => false,
                'pubDate' => false,
                'source' => true,
            ];


            $channel = $this->channel;
            $c = $xml->addChild('channel');
            $title = $channel->getTitle();
            $link = $channel->getLink();
            $description = $channel->getDescription();
            if (null !== $title) {
                if (null !== $link) {
                    if (null !== $description) {

                        $c->addChild('title', $this->getValue($title));
                        $c->addChild('link', $this->getValue($link));
                        $c->addChild('description', $this->getValue($description));


                        //------------------------------------------------------------------------------/
                        // Handling channel elements, one by one
                        //------------------------------------------------------------------------------/
                        foreach ($channelElements as $elName => $type) {
                            if (false === $type) {
                                $method = 'get' . ucfirst($elName);
                                if (null !== ($val = $channel->$method())) {
                                    $c->addChild($elName, $this->getValue($val));
                                }
                            }
                            elseif (true === $type) {
                                $uc = ucfirst($elName);
                                $method = 'get' . $uc;
                                $attrMethod = 'getAttr' . $uc;
                                if (null !== ($val = $channel->$method())) {
                                    $child = $c->addChild($elName, $this->getValue($val));
                                    $attrs = $channel->$attrMethod();
                                    if ($attrs) {
                                        foreach ($attrs as $k => $v) {
                                            $child->addAttribute($k, $this->getValue($v));
                                        }
                                    }
                                }
                            }
                            elseif (0 === $type) {
                                $method = 'get' . ucfirst($elName);
                                switch ($elName) {
                                    case 'image':
                                        $image = $channel->$method();
                                        if (null !== $image) {
                                            /**
                                             * @var ChannelImage $image
                                             */
                                            $url = $image->getUrl();
                                            $title = $image->getTitle();
                                            $link = $image->getLink();
                                            if (null !== $url) {
                                                if (null !== $title) {
                                                    if (null !== $link) {

                                                        $im = $c->addChild($elName);
                                                        $im->addChild('url', $this->getValue($url));
                                                        $im->addChild('title', $this->getValue($title));
                                                        $im->addChild('link', $this->getValue($link));

                                                        if (null !== ($val = $image->getWidth())) {
                                                            $im->addChild('width', $this->getValue($image->getWidth()));
                                                        }
                                                        if (null !== ($val = $image->getHeight())) {
                                                            $im->addChild('height', $this->getValue($image->getHeight()));
                                                        }
                                                        if (null !== ($val = $image->getDescription())) {
                                                            $im->addChild('description', $this->getValue($image->getDescription()));
                                                        }
                                                    }
                                                    else {
                                                        $this->error("channelImage requires a link element");
                                                    }
                                                }
                                                else {
                                                    $this->error("channelImage requires a title element");
                                                }
                                            }
                                            else {
                                                $this->error("channelImage requires an url element");
                                            }
                                        }
                                        break;
                                    case 'skipHours':
                                        $sh = $channel->$method();
                                        if (null !== $sh) {

                                            /**
                                             * @var ChannelSkipHours $sh
                                             */
                                            $hours = $sh->getHours();
                                            if ($hours) {
                                                $sk = $c->addChild($elName);
                                                foreach ($hours as $hour) {
                                                    $sk->addChild('hour', $this->getValue($hour));
                                                }
                                            }
                                        }
                                        break;
                                    case 'skipDays':
                                        $sd = $channel->$method();
                                        if (null !== $sd) {
                                            /**
                                             * @var ChannelSkipDays $sd
                                             */
                                            $days = $sd->getDays();
                                            if ($days) {
                                                $sk = $c->addChild($elName);
                                                foreach ($days as $day) {
                                                    $sk->addChild('day', $this->getValue($day));
                                                }
                                            }
                                        }
                                        break;
                                    default:
                                        $this->error("Internal: unknown element: $elName");
                                        break;
                                }
                            }
                            else {
                                // logic error, will likely never happen
                                $this->error("Internal: unknown case of type: $type");
                            }
                        }


                        //------------------------------------------------------------------------------/
                        // Handling Channel items
                        //------------------------------------------------------------------------------/
                        $items = $channel->getItems();
                        foreach ($items as $item) {

                            $xmlItem = $c->addChild('item');

                            $title = $item->getTitle();
                            $description = $item->getDescription();
                            if (null !== $title || null !== $description) {
                                foreach ($itemElements as $elName => $type) {
                                    if (false === $type) {
                                        $method = 'get' . ucfirst($elName);
                                        if (null !== ($val = $item->$method())) {
                                            $xmlItem->addChild($elName, $this->getValue($val));
                                        }
                                    }
                                    elseif (true === $type) {
                                        $uc = ucfirst($elName);
                                        $method = 'get' . $uc;
                                        $attrMethod = 'getAttr' . $uc;
                                        if (null !== ($val = $item->$method())) {
                                            $child = $xmlItem->addChild($elName, $this->getValue($val));
                                            $attrs = $item->$attrMethod();
                                            if ($attrs) {
                                                foreach ($attrs as $k => $v) {
                                                    $child->addAttribute($k, $this->getValue($v));
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            else {
                                $this->error("item requires at least a title or a description element: none given");
                            }
                        }
                    }
                    else {
                        $this->error("channel requires a description element");
                    }
                }
                else {
                    $this->error("channel requires a link element");
                }
            }
            else {
                $this->error("channel requires a title element");
            }


            //------------------------------------------------------------------------------/
            // HANDLING XML ERRORS
            //------------------------------------------------------------------------------/
            $errors = libxml_get_errors();
            if ($errors) {
                $_errs = [];
                foreach ($errors as $error) {
                    $_errs[] = $this->formatXmlError($error, $xml);
                }
                libxml_clear_errors();
                $e = new RssWriterException(implode(', ' . PHP_EOL, $_errs));
                $e->setSimpleXmlErrors($errors);
                throw $e;
            }
            /**
             * Note: I make the assumption, because of the way I handled the errors,
             * that if $xml->asXml() call would return false, then an exception would be thrown.
             * If not, this method needs a bug fix (which means, that's the intention of the method,
             * and should not be changed).
             */
            $dom = new \DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml->asXML());
            return $dom->saveXML();
        }
        else {
            throw new RssWriterException("libxml extension is not loaded");
        }
    }

    //------------------------------------------------------------------------------/
    // 
    //------------------------------------------------------------------------------/
    private function formatXmlError($error, \SimpleXMLElement $xml)
    {
        $return = $xml[$error->line - 1] . ' ::';
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "Warning $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "Fatal Error $error->code: ";
                break;
        }
        $return .= trim($error->message) . " (line: $error->line, column: $error->column";
        if ($error->file) {
            $return .= " file: $error->file";
        }
        $return .= ")";
        return $return;
    }


    private function getValue($m)
    {
        if (true === $this->autoEscapeMode) {
            $m = htmlspecialchars($m);
        }
        return $m;
    }

    private function error($m)
    {
        throw new RssWriterException($m);
    }
}
