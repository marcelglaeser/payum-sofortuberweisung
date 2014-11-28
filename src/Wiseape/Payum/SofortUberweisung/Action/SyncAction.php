<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Payum\SofortUberweisung\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Sync;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Wiseape\Payum\SofortUberweisung\Request\Api\GetTransactionDataRequest;

class SyncAction extends PaymentAwareAction {

    /**
     * {@inheritdoc}
     */
    public function execute($request) {
        if(false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->payment->execute(new GetTransactionDataRequest($model));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request) {
        return $request instanceof Sync && $request->getModel() instanceof \ArrayAccess;
    }

}