<?php
/**
 * RESTActionDelete.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('ext.yii-rest.components.RESTAction');

/**
 * Deletes an ActiveRecord model
 *
 * @package     yii-rest
 * @subpackage  actions
 * @version     0.1
 */
class RESTActionDelete extends RESTAction
{
    /**
     * Deletes an ActiveRecord model
     *
     * Two parameters are passed to the view:
     * - boolean       $deleted  whether the model was deleted
     * - CActiveRecord $model    the model
     *
     * @param  CActiveRecord $model       the model to populate and save
     * @return void  renders the results into a view
     */
    public function run(CActiveRecord $model)
    {
        $deleted = $model->delete();

        $this->result['deleted'] = $deleted;
        $this->result['model']   = $model;
        echo $this->getResponse();
    }
}