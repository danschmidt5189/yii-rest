<?php
/**
 * RESTParamsException.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('ext.yii-rest.*');

/**
 * Represents HTTP Exceptions arising from invalid action parameters
 *
 * The invalid form model is injected in the contructor and is publicly accessible via
 * [getModel()], allowing you to access detailed custom error information in your
 * exception handler.
 *
 * @property CModel $_model  the model that invalidated th eparameters
 *
 * @package     yii-rest
 * @subpackage  components
 * @version     0.1
 */
class RESTParamsException extends CHttpException
{
    /**
     * @var CModel  invalid model that led to the exception
     */
    private $_model;

    /**
     * Overrides the parent implementation to inject the params dependency.
     *
     * @param CModel $model  the invalid parameters model
     */
    public function __construct(CModel $model, $statusCode=400, $message=null, $code=0, $previous=null)
    {
        $this->_model = $model;
        parent::__construct($statusCode, $message, $code, $previous);
    }

    /**
     * Returns the [_model] property
     *
     * @return CModel  the invalid model that led to the exception
     */
    public function getModel()
    {
        return $this->_model;
    }
}