<?php

namespace App\Services;

use CurlHandle;

class HttpService
{
    private CurlHandle $curlHandle;

    public function __construct()
    {
        $this->curlHandle = curl_init();
    }

    public function get(string $url): string
    {
        curl_setopt($this->curlHandle, CURLOPT_URL, $url);
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);

        return curl_exec($this->curlHandle);
    }
}
