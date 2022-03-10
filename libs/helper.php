<?php

declare(strict_types=1);

trait helper
{
    public function WakeOnLan()
    {
        if ($this->ReadPropertyString('BroadcastAddress') != '' && $this->ReadPropertyString('MACAddress') != '') {
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
        } else {
            $this->SendDebug(__FUNCTION__, 'Broadcast or Mac Address is missing', 0);
        }
    }

    protected function UnregisterTimer($Name)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id > 0) {
            if (!IPS_EventExists($id)) {
                throw new Exception('Timer not present', E_USER_NOTICE);
            }
            IPS_DeleteEvent($id);
        }
    }
}
