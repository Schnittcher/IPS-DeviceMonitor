<?php

trait helper
{
    protected function RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
    {
        if (!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 0);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != 0) {
                throw new Exception('Variable profile type does not match for profile ' . $Name);
            }
        }

        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
    }

    protected function RegisterProfileBooleanEx($Name, $Icon, $Prefix, $Suffix, $Associations)
    {
        if (count($Associations) === 0) {
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[count($Associations) - 1][0];
        }

        $this->RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);

        foreach ($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
    }

    protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
    {
        if (!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 1);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != 1) {
                throw new Exception('Variable profile type does not match for profile ' . $Name);
            }
        }
        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
    }

    protected function RegisterProfileIntegerEx($Name, $Icon, $Prefix, $Suffix, $Associations)
    {
        if (count($Associations) === 0) {
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[count($Associations) - 1][0];
        }
        $this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);
        foreach ($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
    }

    protected function WakeOnLan()
    {
        $addr = $this->ReadPropertyString('BroadcastAddress');
        $addr_byte = explode(':', $this->ReadPropertyString('MACAddress'));
        $hw_addr = '';
        for ($a = 0; $a < 6; $a++) {
            $hw_addr .= chr(hexdec($addr_byte[$a]));
        }
        $msg = chr(255) . chr(255) . chr(255) . chr(255) . chr(255) . chr(255);
        for ($a = 1; $a <= 16; $a++) {
            $msg .= $hw_addr;
        }
        // send it to the broadcast address using UDP
        $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($s == false) {
            $this->SendDebug('Error creating socket!', 'Error code is ' . socket_last_error($s) . ' - ' . socket_strerror(socket_last_error($s)), 0);
        } else {
            // setting a broadcast option to socket:
            $opt_ret = socket_set_option($s, 1, 6, true);
            if ($opt_ret < 0) {
                $this->SendDebug('setsockopt() failed, error:', strerror($opt_ret), 0);
            }
            $result = socket_sendto($s, $msg, strlen($msg), 0, $addr, 2050);
            socket_close($s);
            $this->SendDebug('Result', 'Magic Packet sent (' . $result . ') to ' . $addr . ', MAC=' . $this->ReadPropertyString('MACAddress'), 0);
        }
    }

    protected function UnregisterTimer($Name)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id > 0)
        {
            if (!IPS_EventExists($id))
                throw new Exception('Timer not present', E_USER_NOTICE);
            IPS_DeleteEvent($id);
        }
    }
}
