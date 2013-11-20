<?php
/**
 * RESTAction.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

/**
 * Base class for REST actions
 *
 * REST actions should follow these conventions:
 * - Do not instantiate objects inside the run() method
 * - Record all results in the [results] property
 * - Explicitly pass models and other data to the run() method by configuring your
 *   controller's getActionParams() method.
 *
 *
 * @property string $view    name of the view into which the action result is rendered
 *                           Defaults to the action id.
 * @property array  $params  additional parameters passed to the view
 * @property CMap   $result  the action result. Override this in your child classes to
 *                           customize the data that is passed to the view.
 *
 * @package     yii-rest
 * @subpackage  components
 * @version     0.1
 * @see         yii-rest/actions for example implementations.
 */
abstract class RESTAction extends CAction
{
    /**
     * @var string  name of the view into which the result should be rendered
     */
    public $view;

    /**
     * @var array  additional view parameters. These are merged into [_result].
     */
    public $params = array();

    /**
     * @var CMap  the result of the action. Child classes should write to this
     *            property to record their results.
     * @see getResult()
     */
    private $_result;

    /**
     * Returns the [result] property
     *
     * @return CMap  the action result
     */
    public function getResult($reset=false)
    {
        if (null === $this->_result || $reset) {
            $this->_result = new CMap($this->params);
        }
        return $this->_result;
    }

    /**
     *
     */
    public function getResponse()
    {
        $this->result->mergeWith($this->controller->getActionParams());
        return $this->controller->render($this->view ?: $this->id, $this->result->toArray(), true);
    }

    /**
     * Loads model attributes
     *
     * @param CModel $model       the model
     * @param array  $attributes  attribute values
     * @return boolean  whether any data was loaded
     */
    public function loadModelData($model, $data)
    {
        $oldAttributes = $model->getAttributes();
        $model->setAttributes($data);
        return $oldAttributes !== $model->getAttributes();
    }
}