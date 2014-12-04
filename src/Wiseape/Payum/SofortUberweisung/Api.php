<?php

/**
 * @copyright wiseape GmbH
 * @author Ruben Rögels
 * @license LGPL-3.0+
 */

namespace Wiseape\Payum\SofortUberweisung;

use Payum\Core\Exception\InvalidArgumentException;

class Api {

    const SOFORTLIB_VERSION = '2.1.1';

    /**
     * You'll find detailed explanation of all status (sub-)codes in
     * src/Wiseape/Payum/SofortUberweisung/Resources/doc/SOFORT-Ueberweisung-API-Dokumentation.pdf
     * beginning on page 21
     *
     * The initial state is "pending"
     */
    const STATUS_LOSS = 'loss';
    const SUB_LOSS = 'not_credited';
    const STATUS_PENDING = 'pending';
    const SUB_PENDING = 'not_credited_yet';
    const STATUS_RECEIVED = 'received';
    const SUB_CREDITED = 'credited';
    const SUB_PARTIALLY = 'partially_credited';
    const SUB_OVERPAYMENT = 'overpayment';
    const STATUS_REFUNDED = 'refunded';
    const SUB_COMPENSATION = 'compensation';
    const SUB_REFUNDED = 'refunded';

    /**
     * @todo make configurable:
     * - success (unchecked)
     * - pending
     */
    const STATUS_UNTRACEABLE = 'untraceable';
    const SUB_SOFORT_NEEDED = 'sofort_bank_account_needed';

    /**
     * @var array
     */
    protected $options = array(
        'configkey' => null,
        'timeout' => 1800,
    );

    /**
     * @var \Sofortueberweisung
     */
    protected $sofortLib = null;

    /**
     * @var \SofortLibTransactionData
     */
    protected $sofortLibTxnData = null;

    /**
     * @param array $options
     * @throws InvalidArgumentException
     */
    public function __construct(array $options) {
        $this->options = array_replace($this->options, $options);

        if(!is_int($this->options['timeout']) || $this->options['timeout'] < 300) {
            throw new InvalidArgumentException('The "timeout" option must be an integer >= 300.');
        }

        if(!strlen($this->options['configkey'])) {
            throw new InvalidArgumentException('The "configkey" option must be set.');
        }

        /**
         * @todo check if there is a lib available on packagist or somwehere else
         */
        $sofortLibPath = dirname(__FILE__) . '/Resources/SofortLib-PHP-Payment-' . static::SOFORTLIB_VERSION . '/payment/sofortLibSofortueberweisung.inc.php';
        if(is_file($sofortLibPath)) {
            require_once($sofortLibPath);
        } else {
            throw new InvalidArgumentException('Cannot find SofortLib library in version "' . static::SOFORTLIB_VERSION . '".');
        }

        $this->sofortLib = new \Sofortueberweisung($this->options['configkey']);

        $sofortLibTxnDataPath = dirname(__FILE__) . '/Resources/SofortLib-PHP-Payment-' . static::SOFORTLIB_VERSION . '/core/sofortLibTransactionData.inc.php';
        if(is_file($sofortLibTxnDataPath)) {
            require_once($sofortLibTxnDataPath);
        } else {
            throw new InvalidArgumentException('Cannot find SofortLibTransactionData library in version "' . static::SOFORTLIB_VERSION . '".');
        }
    }

    /**
     * 
     * @param array $fields
     * @return \SofortUeberweisung
     */
    public function doSofortUberweisung(array $fields, &$timeout) {
        $this->sofortLib->setAmount($fields['amount']);
        $this->sofortLib->setCurrencyCode($fields['currency']);

        // setting bank account in this step is optional
        if(isset($fields['bic']) && isset($fields['iban']) && isset($fields['holder'])) {
            $this->sofortLib->setSenderSepaAccount($fields['bic'], $fields['iban'], $fields['holder']);
        }

        $this->sofortLib->setReason(static::prepareString($fields['reason1'], $fields), isset($fields['reason2']) ? static::prepareString($fields['reason2'], $fields) : '');

        // check if success_url is set either in options or in fields
        if(isset($this->options['success_url']) || isset($fields['success_url'])) {
            $this->sofortLib->setSuccessUrl((isset($fields['success_url']) ? $fields['success_url'] : $this->options['success_url']));
        }

        // check if abort_url is set either in options or in fields
        if(isset($this->options['abort_url']) || isset($fields['abort_url'])) {
            $this->sofortLib->setAbortUrl((isset($fields['abort_url']) ? $fields['abort_url'] : $this->options['abort_url']));
        }

        // check if abort_url is set either in options or in fields
        if(isset($this->options['notification_url']) || isset($fields['notification_url'])) {
            $this->sofortLib->setNotificationUrl((isset($fields['notification_url']) ? $fields['notification_url'] : $this->options['notification_url']));
        }

        $timeout = isset($fields['timeout']) ? $fields['timeout'] : $this->options['timeout'];
        $this->sofortLib->setTimeout($timeout);

        /**
         * @todo what's this?
         */
        if(isset($this->options['customer_protection'])) {
            $this->sofortLib->setCustomerProtection(true);
        }

        $this->sofortLib->sendRequest();

        return $this->sofortLib;
    }

    /**
     * @param array|ArrayAccess $fields
     * @return \SofortLibTransactionData
     */
    public function getTxnData($fields) {
        if(!is_array($fields) && !($fields instanceof \ArrayAccess)) {
            throw new \RuntimeException('$fields must be array or implement ArrayAccess.');
        }
        $data = new \SofortLibTransactionData($this->options['configkey']);
        $data->addTransaction($fields['txn']);
        $data->sendRequest();
        return $data;
    }

    /**
     * @return array
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @return \SofortUeberweisung
     */
    public function getSofortLib() {
        return $this->sofortLib;
    }

    /**
     * 
     * @return \SofortLibTransactionData
     */
    public function getSofortLibTxnData() {
        return $this->sofortLibTxnData;
    }

    /**
     * @param string $format
     * @param array $fields
     * @return string
     */
    public static function prepareString($format, array $fields) {
        return vsprintf($format, $fields);
    }

}
