<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/helper.php';
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php';

class DeviceMonitor extends IPSModule
{
    use Helper;
    use VariableProfileHelper;

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyBoolean('Active', false);
        $this->RegisterPropertyString('IPAddress', '');
        $this->RegisterPropertyString('BroadcastAddress', '');
        $this->RegisterPropertyString('MACAddress', '');
        $this->RegisterPropertyBoolean('ActiveTries', false);
        $this->RegisterPropertyInteger('Tries', 20);
        $this->RegisterPropertyInteger('PingTimeout', 1000);
        $this->RegisterPropertyInteger('Interval', 20);
        $this->RegisterPropertyBoolean('WakeOnLan', false);

        $this->RegisterTimer('DM_UpdateTimer', 0, 'DM_UpdateStatus($_IPS[\'TARGET\']);');

        $this->RegisterVariablenProfiles();
        $this->RegisterVariableBoolean('DeviceStatus', 'Status', 'DM.Status');
    }

    public function Destroy()
    {
        $this->UnregisterTimer('DM_UpdateStatus');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        $this->RegisterMessage($this->InstanceID,IM_CHANGESTATUS);

        $WOL = $this->ReadPropertyBoolean('WakeOnLan');
        $this->MaintainVariable('DeviceWOL', $this->Translate('Wake On Lan'), 1, 'DM.WOL', 0, $this->ReadPropertyBoolean('WakeOnLan') == true);
        if ($this->ReadPropertyBoolean('WakeOnLan')) {
            $this->SetValue('DeviceWOL', 1);
            $this->EnableAction('DeviceWOL');
        }
        if ($this->ReadPropertyBoolean('Active')) {
            $this->SetTimerInterval('DM_UpdateTimer', $this->ReadPropertyInteger('Interval') * 1000);
            $this->UpdateStatus();
            $this->SetStatus(102);
        } else {
            $this->SetTimerInterval('DM_UpdateTimer', 0);
            $this->SetStatus(104);
        }
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {

        //Wenn der Status sich der Instanz ändert
        if ($Message == IM_CHANGESTATUS) {
            switch ($Data[0]) {
                case 104: //Inaktiv Variable auf false setzen
                    $this->SetValue('DeviceStatus', false);
                    break;
                default:
                    # code...
                    break;
            }
        }

    }

    public function UpdateStatus()
    {
        if ((($this->ReadPropertyString('IPAddress') != '') && $this->ReadPropertyInteger('PingTimeout') != '')) {
            if (@Sys_Ping($this->ReadPropertyString('IPAddress'), $this->ReadPropertyInteger('PingTimeout'))) {
                $this->SetBuffer('Tries', '0');
                $this->SetValue('DeviceStatus', true);
            } else {
                if ((intval($this->GetBuffer('Tries')) < $this->ReadPropertyInteger('Tries')) && ($this->ReadPropertyBoolean('ActiveTries'))) {
                    $tries = intval($this->GetBuffer('Tries'));
                    $tries++;
                    $this->SendDebug('UpdateStatus :: Tries', $tries, 0);
                    $this->SetBuffer('Tries', strval($tries));
                    return;
                }
                $this->SetValue('DeviceStatus', false);
            }
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

    private function RegisterVariablenProfiles()
    {
        //Profile for Online / Offline Status
        if (!IPS_VariableProfileExists('DM.Status')) {
            $this->RegisterProfileBooleanEx('DM.Status', 'Network', '', '', [
                [false, 'Offline',  '', 0xFF0000],
                [true, 'Online',  '', 0x00FF00],
            ]);
        }

        //Profile for WOL
        if (!IPS_VariableProfileExists('DM.WOL')) {
            $Associations = [];
            $Associations[] = [1, 'Start', '', -1];
            $this->RegisterProfileIntegerEx('DM.WOL', 'Network', '', '', $Associations);
        }
    }
}
