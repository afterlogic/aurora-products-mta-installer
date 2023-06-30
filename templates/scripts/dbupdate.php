<?php

include_once  '/opt/afterlogic/html/system/autoload.php';

$bAdminPrivileges = true;

\Aurora\System\Api::Init($bAdminPrivileges);
$oCoreDecorator = \Aurora\System\Api::GetModuleDecorator('Core');
$oCoreDecorator->CreateTables();
$oCoreDecorator->UpdateConfig();

$oServer = \Aurora\Modules\Mail\Models\Server::where('OwnerType', \Aurora\Modules\Mail\Enums\ServerOwnerType::SuperAdmin)->first();
$oServer->EnableSieve = true;
$oServer->save();

$oSettings = \Aurora\System\Api::GetSettings();
$oLicensingModule = \Aurora\Modules\LicensingWebClient\Module::getInstance();
$sLinkTrial = $oLicensingModule->oModuleSettings->TrialKeyLink;
if ($sLinkTrial) {
    $aMatch = array();
    preg_match('/href=["\']?([^"\'>]+)["\']?/', $sLinkTrial, $aMatch);
    if (isset($aMatch[1]) && filter_var($aMatch[1], FILTER_VALIDATE_URL) !== false) {
        $aUrlParts = parse_url($aMatch[1]);
        if (isset($aUrlParts['query'])) {
            parse_str($aUrlParts['query'], $aQueryParams);
        } else {
            $aQueryParams = array();
        }
        $aQueryParams["format"] = "json";
        $sUrlTrial = $aUrlParts['scheme'] . '://' . $aUrlParts['host'] . $aUrlParts['path'] . '?' . http_build_query($aQueryParams);
        $sKeyTrialJson = get_data($sUrlTrial);
        $oResponse = json_decode($sKeyTrialJson);
        if (isset($oResponse->success) && $oResponse->success && isset($oResponse->key) && $oResponse->key !== '') {
            $oSettings->LicenseKey = $oResponse->key;
            $oSettings->Save();
        }
    }
}

function get_data($url)
{
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        return false;
    }
    $ch = curl_init();
    $timeout = 20;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
