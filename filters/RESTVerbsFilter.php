<?php
/**
 * RESTVerbsFilter.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('ext.yii-rest.*');

/**
 * Filters invalid HTTP verbs
 *
 * @package     yii-rest
 * @subpackage  filters
 * @version     0.1
 */
class RESTVerbsFilter extends RESTFilter
{
    /**
     * @var array  list of action IDs to which this filter applies
     */
    public $actions;

    /**
     * @var array  list of HTTP methods that are allowed. Defaults to only allow 'POST' requests.
     */
    public $verbs = array('POST');

    /**
     * @var string  exception message
     */
    public $message;

    /**
     * @var integer  exception status code. Defaults to 405 ("Method Not Allowed").
     */
    public $statusCode = 405;

    /**
     * @var integer  exception code
     */
    public $code = 0;

    /**
     * Filters our invalid requests based on the HTTP method
     *
     * @return true  if the request is valid
     * @see badRequest()
     */
    public function preFilter($filterChain)
    {
        $request = Yii::app()->getRequest();
        $action = $filterChain->action;

        $verb = $request->getRequestType();
        if ($this->isActionMatched($action) && !$this->isVerbMatched($verb)) {
            $this->badRequest($verb);
        }
        return true;
    }

    /**
     * Returns whether the given action triggers this filter
     *
     * @param CAction $action  the action
     * @return boolean  whether the action triggers this filter
     */
    public function isActionMatched($action)
    {
        $actions = is_array($this->actions) ? $this->actions : array_map('trim', explode(',', $this->actions));
        return false !== array_search($action->id, $actions);
    }

    /**
     * Returns whether the given verb is allowed
     *
     * @param string $verb  HTTP method name
     * @return boolean  whether the verb is allowed
     */
    public function isVerbMatched($verb)
    {
        $verbs = is_array($this->verbs) ? $this->verbs : array_map('trim', explode(',', $this->verbs));
        return false !== array_search(strtoupper($verb), $verbs);
    }

    /**
     * Handles a bad request
     *
     * @param string $verb  the invalid HTTP method used
     * @throws CHttpException
     */
    public function badRequest($verb)
    {
        $message = str_replace('{verb}', $verb, $this->message ?: Yii::t('yii-rest', 'HTTP method {verb} is invalid. Use: {allowed}', array(
            '{verb}' =>$verb,
            '{allowed}' =>implode(', ', $this->verbs),
        )));
        throw new CHttpException($this->statusCode ?: 400, $message, $this->code);
    }
}