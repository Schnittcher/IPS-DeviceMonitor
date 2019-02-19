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
        $this->RegisterPropertyString('BroadcastAddress', '');
        $this->RegisterPropertyString('MACAddress', '');
        $this->RegisterPropertyInteger('PingTimeout', 1000);
        $this->RegisterPropertyInteger('Interval', 20);

        $this->RegisterTimer('DM_UpdateTimer', 0, 'DM_UpdateStatus($_IPS[\'TARGET\']);');
        $this->RegisterVariablenProfiles();
        $this->RegisterVariableBoolean('DeviceStatus', 'Status', 'DM.Status');
        $this->RegisterVariableInteger('DeviceWOL', 'Wake On Lan', 'DM.WOL');
        SetValue($this->GetIDForIdent('DeviceWOL'), 1);
        $this->EnableAction('DeviceWOL');
    }

    public function Destroy()
    {
        $this->UnregisterTimer('DM_UpdateStatus');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        $this->SetTimerInterval('DM_UpdateTimer', $this->ReadPropertyInteger('Interval') * 1000);
    }

    public function UpdateStatus()
    {
        if ((($this->ReadPropertyString('IPAddress') != '') and $this->ReadPropertyInteger('PingTimeout') != '')) {
            if (@Sys_Ping($this->ReadPropertyString('IPAddress'), $this->ReadPropertyInteger('PingTimeout'))) {
                SetValue($this->GetIDForIdent('DeviceStatus'), true);
            } else {
                SetValue($this->GetIDForIdent('DeviceStatus'), false);
            }
        }
    }

    private function RegisterVariablenProfiles()
    {
        //Profile for Online / Offline Status
        if (!IPS_VariableProfileExists('DM.Status')) {
            $this->RegisterProfileBooleanEx('DM.Status', 'Network', '', '', array(
                array(false, 'Offline',  '', 0xFF0000),
                array(true, 'Online',  '', 0x00FF00),
            ));
        }

        //Profile for WOL
        if (!IPS_VariableProfileExists('DM.WOL')) {
            $Associations = array();
            $Associations[] = array(1, 'Start', '', -1);
            $this->RegisterProfileIntegerEx('DM.WOL', 'Network', '', '', $Associations);
        }
    }

    public function RequestAction($Ident, $Value)
    {
        $this->SendDebug(__FUNCTION__ . ' Ident', $Ident, 0);
        $this->SendDebug(__FUNCTION__ . ' Value', $Value, 0);
        switch ($Ident) {
            case 'DeviceWOL':
                $this->SendDebug(__FUNCTION__, 'Device wakeup', 0);
                $this->WakeOnLan();
                break;
            default:
                $this->SendDebug(__FUNCTION__, 'Undefined Ident', 0);
                break;
        }
    }
}
