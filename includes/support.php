<?php
/**
* Clase para loggear errores
*
* @copyright  2025 - tipi(code)
* @since      1.1.0
*/ 
class ClubBiSupport {
    /**
    * Envia el error al API de Aurora
    * 
    * @author Luis E. Mendoza <lmendoza@codingtipi.com>
    * @since 1.1.0
    */ 
    public static function log_error($line, $file, $error, $exception){
        $body = [
            "Line" => $line,
            "File" => $file,
            "FriendlyMsg" => $error,
            "exception" => $exception,
            "url" => "oxexpeditions.com",
            "version" => CLUB_BI_PLUGIN_VERSION,
        ];

        $ch = curl_init();

        $completeUrl = 'https://aurora.codingtipi.com/support/v1/issues';
        curl_setopt($ch, CURLOPT_URL, $completeUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: application/json',
            'Content-Type: application/json',
            'X-App-Id: ' . CLUB_BI_APP_ID
        ));
        curl_exec($ch);
    }
}