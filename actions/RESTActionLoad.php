<?php
/**
 * RESTActionLoad.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('ext.yii-rest.actions.RESTAction');

/**
 * Loads model attributes
 *
 * @package     yii-rest
 * @subpackage  actions
 * @version     0.1
 */
class RESTActionLoad extends RESTAction
{
    /**
     * Loads attributes into an AR model and saves it
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
        parent::run();
    }
}