<?php
/**
 * RESTAdaptorParam class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('ext.yii-rest.*');

/**
 * Model representing a public REST endpoint
 *
 * @package     yii-rest
 * @subpackage  components
 */
class RESTAdaptorParam extends CComponent
{
    /**
     * @var string  internal name for the parameter
     */
    private $_name;

    /**
     * @var string  HTTP request source. E.g. 'GET', 'POST', etc. Defaults to RESTFacadeSource::ANY, meaning any
     *              source can provide this data.
     */
    private $_source;

    /**
     * @var string  public name for the parameter. This is the value API users must specify
     *              when sending data to your controller. Defaults to the name.
     */
    private $_publicName;

    /**
     * Constructs the endpoint parameter
     *
     * @param string $source      source to check for endpoint data
     * @param string $name        endpoint private name
     * @param string $publicName  name of the request variable to map to the private name
     */
    public function __construct($source, $name, $publicName)
    {
        $this->_name       = $name;
        $this->_source     = $source;
        $this->_publicName = $publicName;
    }

    /**
     * Returns the [_name] property
     *
     * @return string  private name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns the [_source] property
     *
     * @return string  source
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Returns the [_publicName] property
     *
     * @return string  public name
     */
    public function getPublicName()
    {
        return $this->_publicName;
    }
}