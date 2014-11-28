<?php

namespace Wiseape\Payum\SofortUberweisung\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Security\GenericTokenFactoryInterface;

/**
 * 
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license EULA
 */
class FillOrderDetailsAction implements ActionInterface {

    /**
     * @var GenericTokenFactoryInterface
     */
    protected $tokenFactory;

    /**
     * @param GenericTokenFactoryInterface $tokenFactory
     */
    public function __construct(GenericTokenFactoryInterface $tokenFactory = null) {
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * {@inheritDoc}
     *
     * @param FillOrderDetails $request
     */
    public function execute($request) {
        RequestNotSupportedException::assertSupports($this, $request);
        $order = $request->getOrder();
        $divisor = pow(10, $order->getCurrencyDigitsAfterDecimalPoint());
        $details = $order->getDetails();
        
        $details['currency'] = $order->getCurrencyCode();
        $details['amount'] = $order->getTotalAmount() / $divisor;
        $details['currency_code'] = $order->getCurrencyCode();
        $details['email_customer'] = $order->getClientEmail();
        $details['reason1'] = $order->getNumber();
        $details['reason2'] = $order->getDescription();
        
        $order->setDetails($details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request) {
        return $request instanceof FillOrderDetails;
    }

}
