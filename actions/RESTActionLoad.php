<?php
/**
 * RESTActionLoad.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('ext.yii-rest.components.RESTAction');

/**
 * Loads data into a model and renders it to a view
 *
 * This action does not invoke any model methods. By not binding to the `$data` parameter,
 * you can effectively turn this into a simple view action.
 *
 * @package     yii-rest
 * @subpackage  actions
 * @version     0.1
 */
class RESTActionLoad extends RESTAction
{
    /**
     * Loads data into an AR model
     *
     * Two parameters are passed to the view:
     * - boolean       $loaded  whether any data was loaded
     * - CActiveRecord $model   the model
     *
     * @param  CModel $model  the model
     * @param  array  $data   attributes to load into the model
     * @return void  renders the result into a view
     */
    public function run(CModel $model, array $data=null)
    {
        $loaded = $this->loadModelData($model, $data);

        $this->result['loaded'] = $loaded;
        $this->result['model']  = $model;
        echo $this->getResponse();
    }
}