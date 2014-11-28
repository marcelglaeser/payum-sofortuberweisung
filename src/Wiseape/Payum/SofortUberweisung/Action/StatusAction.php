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
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface {

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

        if(!isset($model['txn']) || !strlen($model['txn'])) {
            $request->markNew();
            return;
        }
        
        /**
         * @todo add RuntimeException
         */
        if(!isset($model['status'])) {
            $request->markUnknown();
            return;
        }

        $subcode = isset($model['statusReason']) ? $model['statusReason'] : null;

        switch($model['status']) {
            case Api::STATUS_LOSS:
                $request->markFailed();
                break;
            
            case Api::STATUS_PENDING:
                $request->markPending();
                break;
            
            case Api::STATUS_RECEIVED:
                switch($subcode) {
                    default:
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
                    default:
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
                $request->markUnknown();
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request) {
        return $request instanceof GetStatusInterface
                && $request->getModel() instanceof \ArrayAccess;
    }

}