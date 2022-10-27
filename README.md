[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Version](https://img.shields.io/badge/Symcon%20Version-5.0%20%3E-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Check Style](https://github.com/Schnittcher/IPS-DeviceMonitor/workflows/Check%20Style/badge.svg)](https://github.com/Schnittcher/IPS-DeviceMonitor/actions)

# IPS-DeviceMonitor
   Mit diesem Modul ist es möglich den Online / Offline Status von Geräten im LAN zu überwachen.
 
   ## Inhaltverzeichnis
   1. [Voraussetzungen](#1-voraussetzungen)
   2. [Installation](#2installation)
   3. [Konfiguration in IP-Symcon](#3-konfiguration-in-ip-symcon)
   4. [Spenden](#4-spenden)
   5. [Lizenz](#5-lizenz)
   
## 1. Voraussetzungen

* mindestens IPS Version 5.0

## 2. Installation
IPS-DeviceMonitor
```
https://github.com/Schnittcher/IPS-DeviceMonitor.git
```

## 3. Konfiguration in IP-Symcon

### IPS-DeviceMonitor

Feld | Beschreibung
------------ | -------------
Aktiv| Schaltet die Instanz Aktiv bzw. Inaktiv
Liste von Geräten | Hier kann die Option ausgewählt werden, ob eine Liste von Geräten geprüft werden soll, oder nur ein einzelnes Gerät.
IP-Adresse  | IP-Adresse des Gerätes, welches überwacht werden soll
Host | Diese Liste wird nur angezeigt, wenn die Option Liste von Geräten aktiv ist.
Ping Timeout | Wartezeit in Millisekunden
Update Intervall |Zeit in Sekunden, wie oft das Gerät überprüft werden soll
Fehlversuche aktiv | Hier kann hinterlegt werden, wie oft ein Gerät geprüft werden soll
Versuche |  Hier kann hinterlegt werden, wie oft es versucht werden soll
WOL | Hier können Einstellung für Wake on Lan gesetzt werden, bei einer Liste von Geräten ist WOL nicht möglich und wird ausgeblendet.

## 4. Benutzung
Geräte können über die Variable Wake On Lan oder über die Funktion DM_WakeOnLan($InstanceID) geweckt werden.

## 5. Spenden

Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:    

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EK4JRP87XLSHW" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a> <a href="https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share" target="_blank">Amazon Wunschzettel</a>

## 6. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)