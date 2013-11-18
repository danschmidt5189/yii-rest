<?php
/**
 * RESTActionValidate.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('ext.yii-rest.actions.RESTAction');

/**
 * Loads model attributes and validates the model
 *
 * @property array   $attributes             list of attributes that should be validated
 * @property boolean $performAjaxValidation  whether to validate using CActiveForm::validate() when the request
 *                                           is an Ajax request. Defaults to true.
 *
 * @package     yii-rest
 * @subpackage  actions
 * @version     0.1
 */
class RESTActionValidate extends RESTAction
{
    /**
     * @var array  list of attributes that should be validated. Defaults to null, meaning all
     *             attributes are validated.
     */
    public $attributes;

    /**
     * @var boolean  whether to perform Ajax validation
     */
    public $performAjaxValidation = true;

    /**
     * Loads attributes into an AR model and validates it
     *
     * Three parameters are passed to the view:
     * - boolean $loaded  whether any data was loaded into the model
     * - boolean $valid   whether the model passed validation
     * - CModel  $model   the model
     *
     * If [performAjaxValidation] is true, then CActiveForm::validate() is invoked to render
     * the validation result directly, and no view is rendered.
     *
     * @param  CModel $model  the model
     * @param  array  $data   attributes to load into the model
     * @return void  renders the result into a view
     */
    public function run(CModel $model, array $data=null)
    {
        $request = Yii::app()->getRequest();

        $loaded = $this->loadModelData($model, $data);
        if ($this->performAjaxValidation && $request->getIsAjaxRequest()) {
            echo CActiveForm::validate($model, $this->attributes, false);
            Yii::app()->end();
        }
        $valid = $model->validate($this->attributes);

        $this->result['loaded'] = $loaded;
        $this->result['valid']  = $valid;
        $this->result['model']  = $model;
        parent::run();
    }
}