<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/helper.php';

class IPS_DeviceMonitor extends IPSModule
{
    use Helper;

     public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyString('IPAddress', '');
        $this->RegisterPropertyInteger('PingTimeout', 1000);
        $this->RegisterPropertyInteger('Interval', 20);

        $this->RegisterTimer("DM_UpdateTimer", 0, 'DM_UpdateStatus($_IPS[\'TARGET\']);');
    }

    public function Destroy() {

        $this->UnregisterTimer("DM_UpdateStatus");
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->RegisterVariablenProfiles();
        $this->RegisterVariableBoolean("DeviceStatus", "Status", "DM.Status");
        $this->SetTimerInterval('DM_UpdateTimer', $this->ReadPropertyInteger('Interval') * 1000);
    }

    public function UpdateStatus() {
         if ((($this->ReadPropertyString("IPAddress") != '') AND $this->ReadPropertyString("PingTimeout") != '')) {
             if (@Sys_Ping($this->ReadPropertyString("IPAddress"), $this->ReadPropertyString("PingTimeout"))) {
                 SetValue($this->GetIDForIdent('DeviceStatus'), true);
             } else {
                 SetValue($this->GetIDForIdent('DeviceStatus'), false);
             }
         }
    }

    private function RegisterVariablenProfiles() {
         //Profile for Online / Offline Status
        $this->RegisterProfileBooleanEx("DM.Status", "Network", "", "", Array(
            Array(false, "Offline",  "", 0xFF0000),
            Array(true, "Online",  "", 0x00FF00)
        ));
    }
}
