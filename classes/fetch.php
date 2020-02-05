<?php

defined("APP_DESC") OR exit('No direct script access allowed');

class Fetch {

    public function process ($url, $optData = array()) {
        if (!is_array($optData)) {
            return array("Invalid data", false);
        }

        $curl = curl_init();
        
        $curOptData = array(
            CURLOPT_URL => API_URL . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
                "x-mailerlite-apikey: " . API_KEY
            ),
        );
        
        foreach ($optData as $optKey => $optValue) {
            $curOptData[$optKey] = $optValue;
        }
        
        curl_setopt_array($curl, $curOptData);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return array($err, $response);
    }

}
