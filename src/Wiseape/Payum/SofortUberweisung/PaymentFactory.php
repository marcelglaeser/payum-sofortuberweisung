<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Payum\SofortUberweisung;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\Payment;
use Wiseape\Payum\SofortUberweisung\Action\Api\GetTransactionDataAction;
use Wiseape\Payum\SofortUberweisung\Action\Api\RequestSofortUberweisungAction;
use Wiseape\Payum\SofortUberweisung\Action\CaptureAction;
use Wiseape\Payum\SofortUberweisung\Action\FillOrderDetailsAction;
use Wiseape\Payum\SofortUberweisung\Action\StatusAction;
use Wiseape\Payum\SofortUberweisung\Action\SyncAction;
use Wiseape\Payum\SofortUberweisung\Action\AuthorizeAction;

class PaymentFactory {

    public static function create(Api $api) {
        $payment = new Payment();

        $payment->addApi($api);

        $payment->addExtension(new EndlessCycleDetectorExtension);

        // Api Actions
        $payment->addAction(new RequestSofortUberweisungAction);
        $payment->addAction(new GetTransactionDataAction);
        $payment->addAction(new FillOrderDetailsAction);
        
        // generic actions
        $payment->addAction(new \Payum\Core\Action\CaptureOrderAction);
        $payment->addAction(new CaptureAction);
        $payment->addAction(new AuthorizeAction);
        $payment->addAction(new SyncAction);
        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);
        $payment->addAction(new StatusAction);

        return $payment;
    }

}
