EESchema Schematic File Version 4
EELAYER 30 0
EELAYER END
$Descr A4 11693 8268
encoding utf-8
Sheet 6 6
Title ""
Date ""
Rev ""
Comp ""
Comment1 ""
Comment2 ""
Comment3 ""
Comment4 ""
$EndDescr
$Comp
L PSLab:USB_C_Receptacle J3
U 1 1 5ED26D9E
P 6150 2625
F 0 "J3" V 5675 3650 50  0000 L CNN
F 1 "USB_C_Receptacle" V 5675 1225 50  0000 L CNN
F 2 "Connector_USB:USB_C_Receptacle_XKB_U262-16XN-4BVC11" H 6300 2625 50  0001 C CNN
F 3 "https://www.usb.org/sites/default/files/documents/usb_type-c.zip" H 6300 2625 50  0001 C CNN
	1    6150 2625
	0    1    1    0   
$EndComp
$Comp
L Device:R R59
U 1 1 5ED29A8F
P 6850 4150
F 0 "R59" H 6920 4196 50  0000 L CNN
F 1 "5K1" H 6920 4105 50  0000 L CNN
F 2 "Resistor_SMD:R_0603_1608Metric" V 6780 4150 50  0001 C CNN
F 3 "~" H 6850 4150 50  0001 C CNN
	1    6850 4150
	1    0    0    -1  
$EndComp
$Comp
L Device:R R60
U 1 1 5ED29F1E
P 7150 4150
F 0 "R60" H 7220 4196 50  0000 L CNN
F 1 "5K1" H 7220 4105 50  0000 L CNN
F 2 "Resistor_SMD:R_0603_1608Metric" V 7080 4150 50  0001 C CNN
F 3 "~" H 7150 4150 50  0001 C CNN
	1    7150 4150
	1    0    0    -1  
$EndComp
Wire Wire Line
	6850 3225 6850 4000
Wire Wire Line
	7150 4000 7150 3925
Wire Wire Line
	7150 3925 6950 3925
Wire Wire Line
	6950 3925 6950 3225
$Comp
L power:GND #PWR022
U 1 1 5ED3163A
P 6850 4450
F 0 "#PWR022" H 6850 4200 50  0001 C CNN
F 1 "GND" H 6855 4277 50  0000 C CNN
F 2 "" H 6850 4450 50  0001 C CNN
F 3 "" H 6850 4450 50  0001 C CNN
	1    6850 4450
	1    0    0    -1  
$EndComp
$Comp
L power:GND #PWR024
U 1 1 5ED31E94
P 7150 4450
F 0 "#PWR024" H 7150 4200 50  0001 C CNN
F 1 "GND" H 7155 4277 50  0000 C CNN
F 2 "" H 7150 4450 50  0001 C CNN
F 3 "" H 7150 4450 50  0001 C CNN
	1    7150 4450
	1    0    0    -1  
$EndComp
Wire Wire Line
	7150 4450 7150 4300
Wire Wire Line
	6850 4300 6850 4450
Wire Wire Line
	8050 3300 7615 3300
Wire Wire Line
	7150 3300 7150 3225
Wire Wire Line
	6350 3225 6350 3300
Wire Wire Line
	6350 3300 6400 3300
Wire Wire Line
	6450 3300 6450 3225
Wire Wire Line
	6400 3300 6400 3575
Connection ~ 6400 3300
Wire Wire Line
	6400 3300 6450 3300
Wire Wire Line
	6550 3225 6550 3300
Wire Wire Line
	6550 3300 6600 3300
Wire Wire Line
	6650 3300 6650 3225
Wire Wire Line
	6600 3300 6600 3575
Connection ~ 6600 3300
Wire Wire Line
	6600 3300 6650 3300
$Comp
L power:GND #PWR019
U 1 1 5ED35A8D
P 4350 2825
F 0 "#PWR019" H 4350 2575 50  0001 C CNN
F 1 "GND" H 4355 2652 50  0000 C CNN
F 2 "" H 4350 2825 50  0001 C CNN
F 3 "" H 4350 2825 50  0001 C CNN
	1    4350 2825
	1    0    0    -1  
$EndComp
Wire Wire Line
	4350 2825 4350 2625
Wire Wire Line
	4350 2625 4550 2625
Wire Wire Line
	4550 2325 4350 2325
Wire Wire Line
	4350 2325 4350 2625
Connection ~ 4350 2625
NoConn ~ 4850 3225
NoConn ~ 4950 3225
Text HLabel 8050 3300 2    60   Output ~ 0
VBus
Text HLabel 6400 3575 3    60   Output ~ 0
D+
Text HLabel 6600 3575 3    60   Input ~ 0
D-
$Comp
L power:PWR_FLAG #FLG0102
U 1 1 5ED63433
P 7615 3195
F 0 "#FLG0102" H 7615 3270 50  0001 C CNN
F 1 "PWR_FLAG" H 7615 3368 50  0000 C CNN
F 2 "" H 7615 3195 50  0001 C CNN
F 3 "~" H 7615 3195 50  0001 C CNN
	1    7615 3195
	1    0    0    -1  
$EndComp
Wire Wire Line
	7615 3195 7615 3300
Connection ~ 7615 3300
Wire Wire Line
	7615 3300 7150 3300
$EndSCHEMATC
