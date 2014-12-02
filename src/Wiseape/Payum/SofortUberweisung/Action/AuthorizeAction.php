<?php

namespace Wiseape\Payum\SofortUberweisung\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Authorize;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Request\Sync;

class AuthorizeAction extends PaymentAwareAction  {

    /**
     * {@inheritDoc}
     * 
     * @throws \Payum\Core\Exception\LogicException if the token not set in the instruction.
     * @throws \Payum\Core\Reply\HttpRedirect if authorization required.
     */
    public function execute($request) {
        /** @var $request AuthorizeToken */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        if(!isset($model['payment_url'])) {
            throw new LogicException('The payment URL must be set by RequestSofortUberweisungAction but it was not executed or failed. Review payment details model for more information');
        }

        $this->payment->execute(new Sync($model));
        
        if(isset($model['status'])) {
            throw new LogicException('Authorization already done.');
        }
        
        throw new HttpRedirect($model['payment_url']);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request) {
        return
                $request instanceof Authorize &&
                $request->getModel() instanceof \ArrayAccess
        ;
    }

}
