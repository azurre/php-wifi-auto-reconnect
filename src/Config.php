<?php
/**
 * @author Aleksandr Milenin
 * @email  admin@azrr.info
 * @copyright Copyright (c)Aleksandr Milenin (https://azrr.info/)
 */

namespace App;

use App\Component\SmartObject;

/**
 * @property Config $logFile
 * @property Config $logLevel
 * @property Config $logger
 * @property Config $essidList
 * @property Config $interface
 * @property Config $pingTimeout
 * @property Config $pingCount
 * @property Config $pingLossThreshold
 * @property Config $pingBin
 * @property Config $pingWlan
 * @property Config $pingAddress
 * @property Config $timezone
 * @property Config $gatewayMode
 */
class Config extends SmartObject
{
}
