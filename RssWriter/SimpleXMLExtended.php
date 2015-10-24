<?php

namespace RssUtil\RssWriter;

/*
 * LingTalfi 2015-10-24
 */
class SimpleXMLExtended extends \SimpleXMLElement
{
    public function myAddCData($cdata_text, $elementName)
    {
        $this->$elementName = null;
        $this->addCData($cdata_text);
    }

    public function addCData($cdata_text)
    {
        $node = dom_import_simplexml($this);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdata_text));
    }
}