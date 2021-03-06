<?php
/**
 * RESTAdaptor class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('ext.yii-rest.*');

/**
 * Maps public API parameters to a controller's internal parameter model
 *
 * Use this class when your public API users send data using key names that do not match the attributes
 * of your controller's action parameter model.
 *
 * @property array $interface  parameters configuration
 *
 * @property array $_restAdaptorParams  array of RESTAdaptorParam objects loaded using [interface]
 * @property CMap  $rawActionParams     the raw action parameters pulled from the current request
 *
 * @method array getClientInterface()    returns a description of the client interface for this adaptor
 * @method array getInternalInterface()  returns a description of the internal interface for this adaptor
 * @method array getRawActionParams()    returns the raw action parameters parsed from the current request
 *
 * @package     yii-rest
 * @subpackage  components
 */
class RESTAdaptor extends CComponent
{
    /**
     * @var array  list of RESTAdaptorParam configurations. The first element in the configuration
     *             is the `RESTSource` constant indicating the source of the value. The second is
     *             either a string or a key =>value pair. If a string, the string is both the internal
     *             and public name for the parameter. If a key =>value pair, the key is the public name
     *             and the value is the internal name of the parameter.
     */
    public $interface = array();

    /**
     * @var array  parameters for this endpoint
     */
    private $_restAdaptorParams;

    /**
     * @var CMap  the raw data passed in the current request
     */
    private $_rawActionParams;

    /**
     * Returns the parameter sources (GET, POST, ...) indexed by public parameter name
     *
     * This indicates to clients what they can send to the controller that will be understood.
     *
     * @return array  HTTP method names indexed by parameter name
     */
    public function getClientInterface()
    {
        $interface = array();
        foreach ($this->params as $param) {
            $interface[$param->publicName] = $param->source;
        }
        return $interface;
    }

    /**
     * Returns the names of attributes recognized internally by the controller
     *
     * @return array  list of attribute names
     */
    public function getInternalInterface()
    {
        return array_keys($this->params);
    }

    /**
     * Returns the endpoint parameters
     *
     * If they have not been loaded, they are instantiated using [loadParams()].
     *
     * @return array  list of RESTAdaptorParam objects
     */
    public function getParams($reset=false)
    {
        if (null === $this->_restAdaptorParams || $reset) {
            $this->_restAdaptorParams = $this->loadParams();
        }
        return $this->_restAdaptorParams;
    }

    /**
     * Returns endpoint parameters indexed by private name
     *
     * @return array  list of RESTAdaptorParam objects built from [interface]
     */
    public function loadParams()
    {
        $params = array();
        foreach ($this->interface as $config) {
            $source = $config[0];
            unset($config[0]);
            $key        = key($config);
            $name       = current($config);
            $publicName = is_numeric($key) ? $name : $key;
            $params[]   = new RESTAdaptorParam($source, $name, $publicName);
        }
        return $params;
    }

    /**
     * Returns the raw action parameter values
     *
     * If [_rawActionParams] is not loaded, it will be using [loadRawActionParams()].
     *
     * @return CMap  the [_rawActionParams] property
     */
    public function getRawActionParams($reset=false)
    {
        if (null === $this->_rawActionParams || $reset) {
            $this->_rawActionParams = new CMap($this->loadRawActionParams(), true);
        }
        return $this->_rawActionParams;
    }

    /**
     * Returns the raw action parameters indexed by their private name
     *
     * @return array  raw action parameter values
     */
    public function loadRawActionParams()
    {
        $request = Yii::app()->getRequest();

        $rawData = array();
        foreach ($this->params as $param) {
            switch (strtoupper($param->source)) {
                case RESTSource::GET:
                    $value = $request->getQuery($param->publicName);
                    break;
                case RESTSource::POST:
                    $value = $request->getPost($param->publicName);
                    break;
                case RESTSource::ANY:
                    $value = $request->getParam($param->publicName);
                    break;
                case RESTSource::DELETE:
                    $value = $request->getDelete($param->publicName);
                    break;
                default:
                    throw new CException("Invalid source `$source` for internal param {$param->name}, client param {$param->publicName} in ".__CLASS__);
            }
            // Prevent overriding previously set data with nulls
            if (!empty($value) || !isset($rawData[$param->name])) {
                $rawData[$param->name] = $value;
            }
        }
        return $rawData;
    }
}