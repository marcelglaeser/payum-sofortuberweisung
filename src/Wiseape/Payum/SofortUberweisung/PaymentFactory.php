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

class PaymentFactory {

    public static function create(Api $api) {
        $payment = new Payment();

        $payment->addApi($api);

        $payment->addExtension(new EndlessCycleDetectorExtension);

        // Api Actions
        $payment->addAction(new RequestSofortUberweisungAction);
        $payment->addAction(new GetTransactionDataAction);
        
        // generic actions
        $payment->addAction(new \Payum\Core\Action\CaptureOrderAction);
        $payment->addAction(new CaptureAction);
        $payment->addAction(new SyncAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new FillOrderDetailsAction);
        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);

        return $payment;
    }

}
