-- this script is called by FreeSWITCH Dialplan upon receiving an inbound call on Application extensions, Service Numbers of Rahemma.
-- Script will handover the Incoming-call-session to WebRTC based SIP controller of Application.

-- For Test 4, call-flow and IVR (Interactive Voice Response) functionality controller is Rahemaa_test4.php and it's absolute location is [XAMP Server Root = D:/xampp/htdocs/] Web_Root/Controller/Core/Rahemaa_Test4.php

pathsep = '\\'
uuida = session:get_uuid()
session:setAutoHangup(false)

uuida1 = string.sub(uuida,1)
freeswitch.consoleLog("INFO","UUIDA1:  " .. uuida1 .. "\n")
session:preAnswer()

web_url = "http://127.0.0.1/Rahemaa_test4/Web_Root/Controller/Core/Rahemaa_test4.php?uuid=" ..uuida1
freeswitch.consoleLog("INFO","web_url:  " .. web_url .. "\n")

-- Get a FreeSWITCH API object and call respective controller, mentioned above, in WEB ROOT for call manipulation
api = freeswitch.API()
raw_data = api:execute("curl",web_url)
freeswitch.consoleLog("INFO","Url :\n" .. web_url .. "\n\n")
freeswitch.consoleLog("INFO","Raw data:\n" .. raw_data .. "\n\n")