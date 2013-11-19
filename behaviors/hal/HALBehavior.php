<?php
/**
 * HALBehavior class file.
 *
 * @copyright 2013 by iWorldServices, Inc.
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('yii-rest.behaviors.hal.*');

/**
 * Adds REST/Hypertext Application Language (HAL)-specific behaviors to a model.
 *
 * @package rest
 * @subpackage hal
 * @link http://stateless.co/hal_specification.html Information about HAL specs
 **/
class HALBehavior extends CActiveRecordBehavior
{
    /**
     * @var array names of
     **/
    private $_attributes;

    /**
     * Returns the model's HAL attributes.
     * If not specified, they are the model's safe attributes.
     **/
    public function getAttributes()
    {
        if (null === $this->_attributes) {
            $this->_attributes = $this->owner->safeAttributeNames;
        }
        $attributes = array();
        foreach ($this->_attributes as $name) {
            $attributes[$name] = CHtml::value($this->owner, $name);
        }
        return $attributes;
    }

    /**
     * Sets [[$_attributes]]
     * @param mixed $attributes either an array of attributes, an instance of CAttributeCollection, or null.
     **/
    public function setAttributes($attributes)
    {
        $this->_attributes = $attributes;
    }

    /**
     * @var HALEmbeddedMap HAL meta data
     **/
    private $_embedded;

    /**
     * Gets [[$_embedded]].
     * If meta is not set, it is instantiated with default values.
     * @return HALEmbeddedMap
     **/
    public function getEmbedded()
    {
        if (!isset($this->_embedded)) {
            $this->setEmbedded(null);
        }
        return $this->_embedded;
    }

    /**
     * Sets [[$_embedded]]
     * @param mixed $meta array of metadata, CAttributeCollection, or null
     **/
    public function setEmbedded($embedded)
    {
        if (!is_array($embedded)) {
            if (is_scalar($embedded)) {
                $embedded = array($embedded);
            } else if (is_object($embedded) && method_exists($embedded, 'toArray')) {
                $embedded = $embedded->toArray();
            }
        }
        $this->_embedded = new HALEmbeddedMap();
        $this->_embedded->mergeWith($embedded);
    }

    /**
     * @var HALMetaCollection HAL meta data
     **/
    private $_meta;

    /**
     * Gets [[$_meta]].
     * If meta is not set, it is instantiated with default values.
     * @return HALMetaCollection
     **/
    public function getMeta()
    {
        if (!isset($this->_meta)) {
            $this->setMeta(array(
                'formName' =>$this->name,
                'isNewRecord' =>$this->owner->isNewRecord,
                'primaryKey' =>$this->owner->primaryKey,
            ));
        }
        return $this->_meta;
    }

    /**
     * Sets [[$_meta]]
     * @param mixed $meta array of metadata, CAttributeCollection, or null
     **/
    public function setMeta($meta)
    {
        if (!is_array($meta)) {
            if (is_scalar($meta)) {
                $meta = array($meta);
            } else if (is_object($meta) && method_exists($meta, 'toArray')) {
                $meta = $meta->toArray();
            }
        }
        $this->_meta = new HALMetaCollection();
        $this->_meta->mergeWith($meta);
    }

    /**
     * @var HALLinkList links indexed
     **/
    private $_links;

    /**
     * Returns [[$_links]].
     **/
    public function getLinks()
    {
        if (!isset($this->_links)) {
            $this->setLinks(null);
        }
        return $this->_links;
    }

    /**
     * Sets [[$_links]]
     * @param mixed $links link list or data array that will be used to instantiate a link list.
     **/
    public function setLinks($links)
    {
        $this->_links = new HALLinkList($links);
    }

    /**
     * Returns the model's unique identifier.
     * @return mixed model's primary key
     **/
    public function getId()
    {
        return $this->owner->primaryKey;
    }

    /**
     * Returns the name of the model.
     * @return string model's form name
     **/
    public function getName()
    {
        return $this->owner->formName();
    }

    /**
     * JSON-encodes the HAL representation
     * @return string HAL JSON object
     **/
    public function toHAL()
    {
        return CJSON::encode($this->toArray());
    }

    /**
     * Returns the HAL array representation
     **/
    public function toArray()
    {
        $hal = $this->getAttributes();
        $hal['_embedded'] = $this->embedded->toArray();
        $hal['_links'] = $this->links->toArray();
        $hal['_meta'] = $this->meta->toArray();
        return $hal;
    }
}