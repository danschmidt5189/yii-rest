<?php
/**
 * RESTSource class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('ext.yii-rest.*');

/**
 * Represents sources for client-provided REST data
 *
 * Source names should map to an HTTP or to a sequence of HTTP methods.
 *
 * @package     yii-rest
 * @subpackage  components
 */
class RESTSource
{
    const ANY    = 'ANY'; // CHttpRequest::getParams()
    const GET    = 'GET'; // CHttpRequest::getQuery()
    const POST   = 'POST'; // CHttpRequest::getPost()
    const DELETE = 'DELETE'; // CHttpRequest::getDelete()
}