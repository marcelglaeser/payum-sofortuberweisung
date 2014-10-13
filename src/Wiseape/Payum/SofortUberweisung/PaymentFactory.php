<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Payum\SofortUberweisung;

use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Wiseape\Payum\SofortUberweisung\Action\Api\RequestSofortUberweisungAction;
use Wiseape\Payum\SofortUberweisung\Action\Api\GetTransactionStatusAction;
use Wiseape\Payum\SofortUberweisung\Action\Api\GetTransactionDataAction;
use Wiseape\Payum\SofortUberweisung\Action\CaptureAction;
use Wiseape\Payum\SofortUberweisung\Action\PaymentDetailsSyncAction;
use Wiseape\Payum\SofortUberweisung\Action\PaymentDetailsStatusAction;

class PaymentFactory {

    public static function create(Api $api) {
        $payment = new Payment();

        $payment->addApi($api);

        $payment->addExtension(new EndlessCycleDetectorExtension(5));

        // generic actions
        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);
        $payment->addAction(new CaptureAction);
        $payment->addAction(new PaymentDetailsSyncAction);
        $payment->addAction(new PaymentDetailsStatusAction);

        // Api Actions
        $payment->addAction(new RequestSofortUberweisungAction);
        $payment->addAction(new GetTransactionDataAction);

        return $payment;
    }

}
