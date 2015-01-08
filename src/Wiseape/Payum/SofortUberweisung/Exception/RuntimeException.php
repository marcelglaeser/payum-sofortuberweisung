<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Payum\SofortUberweisung\Exception;

use Payum\Core\Exception\RuntimeException as BaseRuntimeException;

class RuntimeException extends BaseRuntimeException {

    /**
     * @var array
     */
    protected $errorData = array();

    /**
     * @param string $message
     * @param integer $code
     * @param \Exception $previous
     */
    public function __construct($message, $code = null, $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getErrorData() {
        return $this->errorData;
    }

    /**
     * @param array $data
     */
    public function setErrorData(array $data) {
        $this->errorData = $data;
    }

    /**
     * @param array $errors
     * @return string
     */
    public static function formatErrorMessage(array $errors) {
        $error = $errors[0];
        return (isset($error['field']) ? $error['field'] . ': ' : '') . '(' . $error['code'] . ') ' . $error['message'];
    }

}
