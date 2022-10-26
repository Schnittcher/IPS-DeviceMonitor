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
        $this->RegisterPropertyBoolean('ListOfHosts', false);
        $this->RegisterPropertyString('IPAddress', '');
        $this->RegisterPropertyString('HostsList', '{}');
        $this->RegisterPropertyString('BroadcastAddress', '');
        $this->RegisterPropertyString('MACAddress', '');
        $this->RegisterPropertyBoolean('ActiveTries', false);
        $this->RegisterPropertyInteger('Tries', 20);
        $this->RegisterPropertyInteger('PingTimeout', 1000);
        $this->RegisterPropertyInteger('Interval', 20);
        $this->RegisterPropertyBoolean('WakeOnLan', false);

        $this->RegisterTimer('DM_UpdateTimer', 0, 'DM_UpdateStatus($_IPS[\'TARGET\']);');

        $this->RegisterVariablenProfiles();
        $this->RegisterVariableBoolean('DeviceStatus', $this->Translate('State'), 'DM.Status');
        $this->RegisterVariableInteger('LastSeen', $this->Translate('Last seen'), '~UnixTimestamp');
    }

    public function Destroy()
    {
        $this->UnregisterTimer('DM_UpdateStatus');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        $this->RegisterMessage($this->InstanceID, IM_CHANGESTATUS);

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

    public function GetConfigurationForm()
    {
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        if ($this->ReadPropertyBoolean('ListOfHosts')) {
            //Ein Host
            $form['elements'][2]['visible'] = false;
            //Liste von Hosts
            $form['elements'][3]['visible'] = true;
        }
        return json_encode($form);
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {

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
        $ListOfHosts = $this->ReadPropertyBoolean('ListOfHosts');
        $deviceState = false;

        //Liste der Hosts durch gehen und pingen
        if ($ListOfHosts) {
            $hostsList = json_decode($this->ReadPropertyString('HostsList'), true);
            foreach ($hostsList as $key => $host) {
                $deviceState = $this->pingHost($host['IPAddress']);
                if (!$deviceState) {
                    $this->SendDebug('Device offline' . $host['IPAddress'], $host['IPAddress'], 0);
                    $this->SetValue('DeviceStatus', false);
                    return;
                }
            }
            $this->SetValue('DeviceStatus', true);
            $this->SetValue('LastSeen', time());

            return;
        }
        //Nur den einen Host pingen
        $this->SetValue('DeviceStatus', $this->pingHost($this->ReadPropertyString('IPAddress')));
        $this->SetValue('LastSeen', time());
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

    public function listOfHostsVisible($Value)
    {
        if ($Value) {
            $this->UpdateFormField('HostsList', 'visible', true);
            $this->UpdateFormField('IPAddress', 'visible', false);
        } else {
            $this->UpdateFormField('HostsList', 'visible', false);
            $this->UpdateFormField('IPAddress', 'visible', true);
        }
    }

    private function pingHost($IPAddress)
    {
        if ((($IPAddress != '') && $this->ReadPropertyInteger('PingTimeout') != '')) {
            if (@Sys_Ping($IPAddress, $this->ReadPropertyInteger('PingTimeout'))) {
                $this->SetBuffer('Tries', '0');
                return true;
            } else {
                if ((intval($this->GetBuffer('Tries')) < $this->ReadPropertyInteger('Tries')) && ($this->ReadPropertyBoolean('ActiveTries'))) {
                    $tries = intval($this->GetBuffer('Tries'));
                    $tries++;
                    $this->SendDebug('UpdateStatus :: Tries for IP-Address', $IPAddress, 0);
                    $this->SendDebug('UpdateStatus :: Tries', $tries, 0);
                    $this->SetBuffer('Tries', strval($tries));
                    return;
                }
                return false;
            }
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
