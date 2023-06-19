<?php
include_once  '/opt/afterlogic/html/system/autoload.php';

$bAdminPrivileges = true;

\Aurora\System\Api::Init($bAdminPrivileges);
$oCoreDecorator = \Aurora\System\Api::GetModuleDecorator('Core');
$oCoreDecorator->CreateTables();

$oServer = \Aurora\Modules\Mail\Models\Server::where('OwnerType', \Aurora\Modules\Mail\Enums\ServerOwnerType::SuperAdmin)->first();
$oServer->EnableSieve = true;
$oServer->save();

$oSettings = \Aurora\System\Api::GetSettings();
$fgc = get_data("https://afterlogic.com/get-trial-key?productId=afterlogic-mailsuite-pro&format=json");
$oResponse = json_decode($fgc);
if (isset($oResponse->success) && $oResponse->success && isset($oResponse->key) && $oResponse->key !== '')
{
    $oSettings->LicenseKey = $oResponse->key;
    $oSettings->Save();
}

$oCoreDecorator = \Aurora\Modules\Core\Module::Decorator();
$oCoreDecorator->UpdateConfig();

function get_data($url)
{
    $ch = curl_init();
    $timeout = 20;
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
