<?php
/**
 * RESTFacade class file.
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
 * @package     yii-rest
 * @subpackage  components
 */
class RESTFacade extends CComponent
{
    /**
     * @var array  list of RESTFacadeParam configurations. Each configuration is an array with keys:
     * 0 = private attribute name, required
     * 1 = attribute source (@see RESTFacadeSource for values), optional
     * 2 = public attribute name, optional
     */
    public $interface = array();

    /**
     * @var array  parameters for this endpoint
     */
    private $_endpointParams;

    /**
     * @var CMap  the raw data passed in the current request
     */
    private $_rawActionParams;

    /**
     * Returns the endpoint parameters
     *
     * If they have not been loaded, they are instantiated using [loadParams()].
     *
     * @return array  list of RESTFacadeParam objects
     */
    public function getParams($reset=false)
    {
        if (null === $this->_endpointParams || $reset) {
            $this->_endpointParams = $this->loadParams();
        }
        return $this->_endpointParams;
    }

    /**
     * Returns endpoint parameters indexed by private name
     *
     * @return array  list of RESTFacadeParam objects built from [interface]
     */
    public function loadParams()
    {
        $params = array();
        foreach ($this->interface as $config) {
            $name = $config[0];
            unset($config[0]);
            $key = key($config);
            $name = current($config);
            $publicName = is_numeric($key) ? $name : $key;
            $params[$name] = new RESTFacadeParam($source, $name, $publicName);
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
                case RESTFacadeSource::GET:
                    $rawData[$param->name] = $request->getQuery($param->publicName);
                    break;
                case RESTFacadeSource::POST:
                    $rawData[$param->name] = $request->getPost($param->publicName);
                    break;
                case RESTFacadeSource::ANY:
                    $rawData[$param->name] = $request->getParam($param->publicName);
                    break;
                case RESTFacadeSource::DELETE:
                    $rawData[$param->name] = $request->getDelete($param->publicName);
                    break;
                default:
                    throw new CException("Invalid source {$source} for endpoint {$param->name} in ".__CLASS__);
            }
        }
        return $rawData;
    }
}