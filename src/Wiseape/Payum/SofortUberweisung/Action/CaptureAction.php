<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Payum\SofortUberweisung\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Wiseape\Payum\SofortUberweisung\Request\Api\RequestSofortUberweisungRequest;

class CaptureAction extends PaymentAwareAction {

    public function execute($request) {

        if(!$this->supports($request)) {
            RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if($request->getToken()) {
            if(!isset($model['success_url'])) {
                $model['success_url'] = $request->getToken()->getTargetUrl();
            }

            if(!isset($model['abort_url'])) {
                $model['abort_url'] = $request->getToken()->getTargetUrl();
            }
        }

        $this->payment->execute(new RequestSofortUberweisungRequest($model));

        $this->payment->execute(new Sync($model));

        if(!isset($model['status'])) {
            $this->payment->execute(new Authorize($model));
        }

        $this->payment->execute(new Sync($model));
    }

    public function supports($request) {
        return $request instanceof Capture && $request->getModel() instanceof \ArrayAccess;
    }

}
