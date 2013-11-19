<?php
/**
 * RESTActionSave.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('ext.yii-rest.components.RESTAction');

/**
 * Saves an ActiveRecord model
 *
 * Your controller must pass an instance of CActiveRecord as the `model` parameter. This can be
 * done by overriding `getActionParams()`. Any attributes to set to the model should be passed
 * using `attributes`.
 *
 * @property boolean $requireLoadedToSave  whether to require that data was loaded to save the model
 *
 * @package     yii-rest
 * @subpackage  actions
 * @version     0.1
 */
class RESTActionSave extends RESTAction
{
    /**
     * @var boolean  whether to require the model be modified to save it.
     */
    public $requireLoadedToSave = true;

    /**
     * Loads attributes into an AR model and saves it
     *
     * Three parameters are passed to the view:
     * - boolean       $loaded  whether any data was loaded into the model
     * - boolean       $saved   whether the model saved. This is null if the save method is not called.
     * - CActiveRecord $model   the model
     *
     * @param  CActiveRecord $model       the model to populate and save
     * @param  array         $attributes  attribute values indexed by name. Defaults to null.
     * @return void                       renders `saved`, `loaded`, and `model` into the view
     */
    public function run(CActiveRecord $model, array $data=null)
    {
        $loaded = $this->loadModelData($model, $data);
        $saved = ($loaded || !$this->requireLoadedToSave) ? $model->save() : null;

        $this->result['loaded'] = $loaded;
        $this->result['saved']  = $saved;
        $this->result['model']  = $model;
        echo $this->getResponse();
    }
}