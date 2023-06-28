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
$aMatch=array();
preg_match('/href=["\']?([^"\'>]+)["\']?/', $sLinkTrial, $aMatch);
$sUrlTrial = $aMatch[1]."&format=json";
$sKeyTrialJson = get_data($sUrlTrial);
$oResponse = json_decode($sKeyTrialJson);
if (isset($oResponse->success) && $oResponse->success && isset($oResponse->key) && $oResponse->key !== '')
{
    $oSettings->LicenseKey = $oResponse->key;
    $oSettings->Save();
}

function get_data($url)
{
    $ch = curl_init();
    $timeout = 20;
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
