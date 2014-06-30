<?php

/**
 * This file defines all messages for the accounting application. All messages are stored in constants. The constant names depend on the type of the message. It always starts with
 * "MESSAGE_", and is followed by a number. Depending on the number the message is a success, error or info message.
 *
 * 1000 - 1999 = success
 * 2000 - 2999 = error
 * 3000 - 3999 = info
 *
 * Be aware keep the order, to not define a message name twice!
 */

// References
define('MESSAGE_1000', 'Die Referenz wurde gespeichert.');

// General
define('MESSAGE_1900', 'Das Model ":MODELNAME:" wurde gespeichert (:ID:).');

// User
define('MESSAGE_2000', 'Bitte eine E-Mail Adresse angeben.');
define('MESSAGE_2001', 'Bitte ein Passwort angeben.');
define('MESSAGE_2002', 'Die E-Mail Adresse oder das Passwort war falsch.');
define('MESSAGE_2003', 'Der Benutzer wurde deaktiviert. Bitte wenden Sie Sich an den Administrator.');

// Company
define('MESSAGE_2020', 'Es wurder keine Firmen-ID angegeben.');
define('MESSAGE_2021', 'Die angegebene Firmen-ID ist nicht vom richtigen Typ. Es sind nur Nummern erlaubt.');
define('MESSAGE_2022', 'Zur angegebene Firmen-ID wurden keine Daten gefunden.');

// Campaign
define('MESSAGE_2030', 'Es wurde keine Kampagnen-ID angegeben.');
define('MESSAGE_2031', 'Die Kampagne der angegebenen ID gehört nicht Ihnen.');

// Customer
define('MESSAGE_2040', 'Es wurde keine Kunden-ID angegeben.');

//Customer batch edit
define('MESSAGE_2041', 'Du musst schon mind. 1 Kunden auswählen.');
define('MESSAGE_2042', 'Was sollen wir mit diesen Kundendatensätzen machen?');

// Campaign period
define('MESSAGE_1050', 'Die Rechnungsperiode wurde gelöscht.');
define('MESSAGE_1051', 'Die Rechnungsperiode wurde gespeichert.');

define('MESSAGE_2050', 'Es wurde keine Rechnungsperioden-ID angegeben.');
define('MESSAGE_2051', 'Die Rechnung konnte nicht gelöscht werden. Evtl. sind Sie nicht zum Löschen von Rechnungen berechtigt.');
define('MESSAGE_2052', 'Das Start-Datum des Zeitraums überschneidet sich mit einem bereits vorhandenen Zeitraum.');
define('MESSAGE_2053', 'Das End-Datum des Zeitraums überschneidet sich mit einem bereits vorhandenen Zeitraum.');
define('MESSAGE_2054', 'Es wurde entweder nur das Online Marketing Budget oder nur das Klick-Budget angegeben. Bitte beider Felder ausfüllen.');
define('MESSAGE_2055', 'Das Online Marketing Budget ist kleiner als das Klick Budget.');
define('MESSAGE_2056', 'Es wurde eine extra Leistung aber kein Betrag angegeben.');

define('MESSAGE_3050', 'Auf Basis der angegebenen Rechnungsdaten konnte kein eindeutiger Rechnungstype ermittelt werden. Daher konnte nicht geprüft werden, ob sich der angegebene Zeitraum mit einem anderen überlappt. Dies liegt vermutlich daran, dass im Feld "Online Marketing Budget" nichts, oder eine 0 eingetragen wurde.');

// E-Mail
define('MESSAGE_2100', 'Bitte geben Sie einen Betreff an.');
define('MESSAGE_2101', 'Bitte geben Sie einen Text an.');
