<?php

return [
    //'interface' => 'wlan0', // Set interface manually
    'gatewayMode' => 'default', // wlan / balanced / eth-priority / wlan-priority
    'logLevel' => 'info',
    'logFile' => './wlan.log',
    //'pingTimeout' => 0.2, // 200 ms
    'pingAddress' => '8.8.8.8',
    'pingCount' => 5,
    'pingLossThreshold' => 1, // 20% of loss is acceptable
    'pingWlan' => true, // Ping using wlan iface - ignore wired connection
    'essidList' => [
        'My_WiFi' => '/etc/wpa_supplicant.conf'
    ]
];
