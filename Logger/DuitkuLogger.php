<?php
/**
 * Copyright (c) 2017. All rights reserved Duitku Vabsi Host to Host.
 *
 * This program is free software. You are allowed to use the software but NOT allowed to modify the software.
 * It is also not legal to do any changes to the software and distribute it in your own name / brand.
 *
 * All use of the payment modules happens at your own risk. We offer a free test account that you can use to test the module.
 *
 * @author    Duitku Vabsi
 * @copyright Duitku Vabsi (http://duitku.com)
 * @license   Duitku Vabsi
 *
 */
namespace Duitku\Vabsi\Logger;

use Monolog\Logger;

class DuitkuLogger extends Logger
{
    const REQUEST = 100;
    const ERROR = 400;
    const NOTIFICATION = 200;

     /**
     * Add ePay error to log
     *
     * @param mixed $id
     * @param mixed $reason
     * @return void
     */
    public function addEpayError($id, $reason)
    {
        $errorMessage = 'Duitku ePay Error - ID: ' .$id . ' - ' . $reason;
        $this->addRecord(static::ERROR, $errorMessage);
    }

    /**
     * Add ePay info to log
     *
     * @param mixed $id
     * @param mixed $reason
     * @return void
     */
    public function addEpayInfo($id, $reason)
    {
        $errorMessage = 'Duitku ePay Info - ID: ' .$id . ' - ' . $reason;
        $this->addRecord(static::NOTIFICATION, $errorMessage);
    }
}
