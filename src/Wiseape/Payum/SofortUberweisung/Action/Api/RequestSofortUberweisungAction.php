<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Payum\SofortUberweisung\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Http\RedirectUrlInteractiveRequest;
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

        $sofortLib = $this->api->doSofortUberweisung((array) $model);
        $model['txn'] = $sofortLib->getTransactionId();

        if($sofortLib->isError()) {
            $exception = new RuntimeException('SofortLib responded with an error. Use RuntimeException::getErrorData() to get detailed SofortLib error messages.');
            $exception->setErrrorData($sofortLib->getErrors());
            throw $exception;
        } else {
            throw new RedirectUrlInteractiveRequest($sofortLib->getPaymentUrl());
        }
    }

    public function supports($request) {
        return $request instanceof RequestSofortUberweisungRequest &&
                $request->getModel() instanceof \ArrayAccess;
    }

}
