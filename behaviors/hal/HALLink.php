<?php
/**
 * HALLink class file.
 *
 * @copyright 2013 by iWorldServices, Inc.
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('yii-rest.behaviors.hal.*');

/**
 * Represents a HAL link.
 *
 * @package rest
 * @subpackage hal
 */
class HALLink extends CMap
{
    /**
     * @var string link uri value. This can be relative or absolute.
     **/
    private $_href;

    /**
     * Gets [[$_href]]
     **/
    public function getHref()
    {
        return $this->_href;
    }

    /**
     * Sets [[$_href]]
     **/
    public function setHref($href)
    {
        $this->_href = $href;
    }

    /**
     * Overrides the parent implementation to require the href attribute to be set
     **/
    public function __construct($data=null)
    {
        if (isset($data['href'])) {
            $this->setHref($data['href']);
            unset($data['href']);
        } else if (is_string($data)) {
            $this->setHref($data);
            $data = null;
        } else {
            throw new CException('Must specify '.__CLASS__.'::$href');
        }
        parent::__construct($data);
    }

    /**
     * Returns the array representation of the link
     * This will always have an 'href' attribute.
     * @return array
     **/
    public function toArray()
    {
        $array = parent::toArray();
        $array['href'] = $this->href;
        return $array;
    }
}