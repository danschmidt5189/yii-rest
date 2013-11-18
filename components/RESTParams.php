<?php
/**
 * RESTParams class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('ext.yii-rest.*');

/**
 * Action parameters model
 *
 * It is not necessary to inherit from this class when creating your own parameter models. This just
 * adds helper methods for loading and validating the underlying model. If you do not need those,
 * don't inherit from this class. If you find yourself adding a $model property to your class, consider
 * inheriting from this class.
 *
 * @property CModel $model  the underlying model. This is configured by [loadModel()] based on the
 *                          other attributes of the class
 *
 * @method CModel loadModel()  loads the model based on the class attributes
 * @method array rules()       adds default rules requiring the model be set, filtering it using loadModel(),
 *                             and declaring it unsafe.
 *
 * @package     yii-rest
 * @subpackage  components
 * @version     0.1
 */
abstract class RESTParams extends CFormModel
{
    /**
     * @var CModel  the underlying model
     */
    public $model;

    /**
     * Returns the model given the current form settings
     *
     * @return CModel  the configured model
     * @see rules()
     */
    abstract public function loadModel();

    /**
     * Returns validation configurations
     *
     * The default implementation loads the model using [loadModel()] as a filter, requires it to
     * not be null, and declares it unsafe. If you override this in a child class and want to preserve
     * these rules, you should call them last.
     *
     * @return array  CValidator configurations
     */
    public function rules()
    {
        return array(
            array('model', 'filter', 'filter' =>array($this, 'loadModel')),
            array('model', 'required'),
            array('model', 'unsafe'),
        );
    }
}