<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Payum\SofortUberweisung\Action;

use Wiseape\Payum\SofortUberweisung\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\StatusRequestInterface;
use Wiseape\Payum\SofortUberweisung\Request\Api\GetTransactionDataRequest;
use Wiseape\Payum\SofortUberweisung\Action\Api\GetTransactionDataAction;

class PaymentDetailsStatusAction implements ActionInterface {

    /**
     * 
     * @param StatusRequestInterface $request
     * @throws type
     */
    public function execute($request) {
        /** @var $request \Payum\Core\Request\StatusRequestInterface */
        if(false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        /**
         * @todo add RuntimeException
         */
        $code = $model['status'];
        $subcode = $model['statusReason'];

        switch($code) {
            case Api::STATUS_LOSS:
                $request->markFailed();
                break;
            case Api::STATUS_PENDING:
                $request->markPending();
                break;
            case Api::STATUS_RECEIVED:
                switch($subcode) {
                    case Api::SUB_OVERPAYMENT:
                    case Api::SUB_PARTIALLY:
                        $request->markUnknown();
                        break;
                    case Api::SUB_CREDITED:
                        $request->markSuccess();
                        break;
                }

                break;
            case Api::STATUS_REFUNDED:
                switch($subcode) {
                    case Api::SUB_COMPENSATION:
                        $request->markUnknown();
                        break;
                    case Api::SUB_REFUNDED:
                        $request->markCanceled();
                        break;
                }

                break;
            case Api::STATUS_UNTRACEABLE:
                // should be pending, but we need it to be successful
                //$request->markPending();
                $request->markSuccess();
                break;
            default:
                /**
                 * @todo how to handle unknown status?
                 */
                $request->markUnknown();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request) {
        $model = $request->getModel();
        return $request instanceof StatusRequestInterface
                && $model instanceof \ArrayAccess
                && $model['txn'];
    }

}