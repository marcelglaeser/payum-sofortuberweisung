<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Payum\SofortUberweisung\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Wiseape\Payum\SofortUberweisung\Request\Api\GetTransactionDataRequest;
use Payum\Core\Exception\RequestNotSupportedException;

class GetTransactionDataAction extends BaseApiAwareAction {

    /**
     * {@inheritdoc}
     */
    public function execute($request) {
        if(false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $response = static::handleReponse($this->api->getTxnData($model));

        $model->replace($response);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request) {
        $model = $request->getModel();
        return $request instanceof GetTransactionDataRequest
                && $request->getModel() instanceof \ArrayAccess
                && $model['txn'];
    }

    protected static function handleReponse(\SofortLibTransactionData $txnData, $fields = array()) {
        $methods = array(
            'getAmount',
            'getAmountRefunded',
            'getCount',
            'getPaymentMethod',
            'getConsumerProtection',
            'getStatus',
            'getStatusReason',
            'getStatusModifiedTime',
            'getLanguageCode',
            'getCurrency',
            'getTransaction',
            'getReason',
            'getTime',
            'getProjectId',
            'getRecipientHolder',
            'getRecipientAccountNumber',
            'getRecipientBankCode',
            'getRecipientCountryCode',
            'getRecipientBankName',
            'getRecipientBic',
            'getRecipientIban',
            'getSenderHolder',
            'getSenderAccountNumber',
            'getSenderBankCode',
            'getSenderCountryCode',
            'getSenderBankName',
            'getSenderBic',
            'getSenderIban',
        );

        // apply field list
        if($fields) {
            $methods = array_intersect($methods, $fields);
        }

        $output = array();
        foreach($methods as $method) {
            $key = lcfirst(substr($method, 3));
            switch($method) {
                case 'getReason':
                    $output[$key . '1'] = $txnData->{$method}(0, 0);
                    $output[$key . '2'] = $txnData->{$method}(0, 1);
                    break;
                default:
                    $output[$key] = $txnData->{$method}();
            }
        }
        return $output;
    }

}