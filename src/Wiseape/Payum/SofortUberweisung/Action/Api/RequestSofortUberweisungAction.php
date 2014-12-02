<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Payum\SofortUberweisung\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Wiseape\Payum\SofortUberweisung\Request\Api\RequestSofortUberweisungRequest;
use Wiseape\Payum\SofortUberweisung\Exception\RuntimeException;

/**
 * 
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */
class RequestSofortUberweisungAction extends BaseApiAwareAction {

    public function execute($request) {
        if(!$this->supports($request)) {
            RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if(isset($model['txn'])) {
            return;
        }

        $timeout = null;
        $sofortLib = $this->api->doSofortUberweisung((array) $model, $timeout);

        if($sofortLib->isError()) {
            $errors = $sofortLib->getErrors();
            $exception = new RuntimeException(RuntimeException::formatErrorMessage($errors));
            $exception->setErrorData($errors);
            throw $exception;
        }

        $model['txn'] = $sofortLib->getTransactionId();
        $model['expires'] = time() + $timeout;
        $model['payment_url'] = $sofortLib->getPaymentUrl();
    }

    public function supports($request) {
        return $request instanceof RequestSofortUberweisungRequest &&
                $request->getModel() instanceof \ArrayAccess;
    }

}
