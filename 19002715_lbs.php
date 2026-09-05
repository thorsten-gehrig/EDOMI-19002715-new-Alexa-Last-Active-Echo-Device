###[DEF]###
[name		= New Alexa Last Active Echo Device v1.0	]

[e#1 trigger = Trigger ]
[e#2		 = Log level #init=8 ]
[e#3		 = Echo name  1 ]
[e#4		 = Echo name  2 ]
[e#5		 = Echo name  3 ]
[e#6		 = Echo name  4 ]
[e#7		 = Echo name  5 ]
[e#8		 = Echo name  6 ]
[e#9		 = Echo name  7 ]
[e#10		 = Echo name  8 ]
[e#11		 = Echo name  9 ]
[e#12		 = Echo name 10 ]
[e#13		 = Echo name 11 ]
[e#14		 = Echo name 12 ]
[e#15		 = Echo name 13 ]
[e#16		 = Echo name 14 ]
[e#17		 = Echo name 15 ]

[a#1		= Echo Device ]
[a#2		= Result ]
[a#3		= Echo  1 ]
[a#4		= Echo  2 ]
[a#5		= Echo  3 ]
[a#6		= Echo  4 ]
[a#7		= Echo  5 ]
[a#8		= Echo  6 ]
[a#9		= Echo  7 ]
[a#10		= Echo  8 ]
[a#11		= Echo  9 ]
[a#12		= Echo 10 ]
[a#13		= Echo 11 ]
[a#14		= Echo 12 ]
[a#15		= Echo 13 ]
[a#16		= Echo 14 ]
[a#17		= Echo 15 ]
[a#18		= Echo OTHER ]
[a#19		= Echo UNKNOWN ]

[v#100		= 1.0 ]
[v#101		= 19002715 ]
[v#102		= New-Alexa-Last-Active-Echo-Device ]
[v#103		= 0 ]
[v#104		= 0 ]
[v#105		= 1 ]

###[/DEF]###
###[HELP]###

This is almost a drop in replacemennt for Jonofe's "Alexa Last Active Echo Device" (LBS19001202) - with the exception of the last 2 outputs (OTHER, UNKNOWN) are moved down due to extended amount of Echos.

The "last device" detection can be leveraged to use the same command on different rooms - e.g. "open/close blinds" or "lights on/off" can be used in each room and just triggers the specific blinds/lights on the given room where the echo is located.
you have to connect the specific output of Jonofes Alexa Smarthome Device LBS19001201 (A4-A23) to E1 of this LBS.
The value will be sent to one of the outputs A3-A17 depending at which Echo device the voice command was received.
You can specify up to 15 different Echo devices at E3-E17, which will be matched with the outputs A3-A17.

E1: Triggers LBS with the value received by the voice command
(E1 has to be connected to one of the outputs A4-A23 of the Alexa Smarthome Device LBS (LBS19001201)
E2: Enable Logging (0-none|1-emerg|2-alert|3-crit|4-err|5-warning|6-notice|7-info|8-debug)
E3-E17: Name of Echo 1 (till Echo 15)

A1: Name of Echo device which was triggered by the last voice command
A2: Result of last operation
A3: Trigger value at E1 will be sent to A3 if voice command was received by Echo 1
A4: Trigger value at E1 will be sent to A4 if voice command was received by Echo 2
. 
. 
A17: Trigger value at E1 will be sent to A17 if voice command was received by Echo 15
A18: Trigger value at E1 will be sent to A18 if voice command was received by a known Echo device, but not specified at E3-E12
A19: Trigger value at E1 will be sent to A19 if voice command was received by an unknown Echo device

Changelog:
==========
v1.0: Initial version - drop-in replacement for 19001203 for "new Alexa"

###[/HELP]###

###[LBS]###
<?

function LB_LBSID($id)
{
    if ($E = logic_getInputs($id)) {
        logic_setVar($id, 103, $E[2]['value']); // set loglevel to #VAR 103
        if ($E[1]['refresh'])
            logic_callExec(LBSID, $id, false); // start only one instance
    }
}

?>
###[/LBS]###


###[EXEC]###
<?
require (dirname(__FILE__) . "/../../../../main/include/php/incl_lbsexec.php");
set_time_limit(3);
sql_connect();

function logging($id, $msg, $var = NULL, $priority = 8)
{
    $E = getLogicEingangDataAll($id);
    $logLevel = getLogicElementVar($id, 103);
    if (is_int($priority) && $priority <= $logLevel && $priority > 0) {
        $logLevelNames = array(
            'none',
            'emerg',
            'alert',
            'crit',
            'err',
            'warning',
            'notice',
            'info',
            'debug'
        );
        $version = getLogicElementVar($id, 100);
        $lbsNo = getLogicElementVar($id, 101);
        $logName = getLogicElementVar($id, 102) . "-LBS$lbsNo";
        $logName = preg_replace('/ /', '_', $logName);
        if (logic_getVar($id, 104) == 1)
            $logName .= "-$id";
        if (logic_getVar($id, 105) == 1)
            $msg .= " ($id)";
        strpos($_SERVER['SCRIPT_NAME'], $lbsNo) ? $scriptname = 'EXE' . $lbsNo : $scriptname = 'LBS' . $lbsNo;
        writeToCustomLog($logName, str_pad($logLevelNames[$logLevel], 7), $scriptname . " [v$version]:\t" . $msg);
        if (isset($var)) {
            writeToCustomLog($logName, str_pad($logLevelNames[$logLevel], 7), $scriptname . " [v$version]:\t================ ARRAY/OBJECT START ================");
            writeToCustomLog($logName, str_pad($logLevelNames[$logLevel], 7), $scriptname . " [v$version]:\t" . json_encode($var));
            writeToCustomLog($logName, str_pad($logLevelNames[$logLevel], 7), $scriptname . " [v$version]:\t================ ARRAY/OBJECT  END  ================");
        }
    }
}

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    global $id;
    logging($id, "File: $errfile | Error: $errno | Line: $errline | $errstr ");
}

function error_off()
{
    $error_handler = set_error_handler("myErrorHandler");
    error_reporting(0);
}

function error_on()
{
    restore_error_handler();
    error_reporting(E_ALL);
}

function importCSRF()
{
    global $config;
    $csrf = false;
    $fp = fopen($config['cookieFile'], 'r');
    while ($fp && ! feof($fp)) {
        $cookie_line = fgets($fp);
        $cookie_line_array = explode("\t", $cookie_line);
        if ($cookie_line_array[0] == '.' . $config['amazon'] && $cookie_line_array[5] == "csrf") {
            $csrf = trim($cookie_line_array[6]);
            break;
        }
    }
    return $csrf;
}

function get_activity_csrf()
{
    global $config, $csrf;
    $url = 'https://www.' . $config['amazon'] . '/alexa-privacy/apd/activity?ref=activityHistory';
    $http_headers = array(
        'DNT: 1',
        'Connection: keep-alive',
        'Content-Type: application/json; charset=UTF-8',
        'Referer: https://alexa.' . $config['amazon'] . '/spa/index.html',
        'Origin: https://alexa.' . $config['amazon'],
        'csrf: ' . $csrf
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, $config['userAgent']);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $config['cookieFile']);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    curl_close($ch);
    if (preg_match('/"csrf-token" content="(.*?)"/', $body, $match))
        return $match[1];
    else
        return false;
}

$echoName = 'UNKNOWN';
if (file_exists('/tmp/.echos.inc.php')) {
    include_once '/tmp/.echos.inc.php';
    $E = logic_getInputs($id);
    logging($id, "Deriving source echo device...");
    if ($config !== false && $echos !== false && is_array($config) && is_array($echos)) {
        $csrf = importCSRF();
        logging($id, 'CSRF: ' . $csrf);
        $activity_csrf = get_activity_csrf();
        logging($id,'Activity-CSRF:'.$activity_csrf);
        $http_headers = array(
            'DNT: 1',
            'Connection: keep-alive',
            'Content-Type: application/json; charset=UTF-8',
            'anti-csrftoken-a2z: '.$activity_csrf,
            'csrf: ' . $csrf
        );
        $url = 'https://www.' . $config['amazon'] . '/alexa-privacy/apd/rvh/customer-history-records-v2/?startTime=0'
            . '&endTime=2147483647000&pageType=VOICE_HISTORY' ;
        logging($id,'URL: '. $url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $config['userAgent']);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $config['cookieFile']);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"previousRequestToken": null}');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        curl_close($ch);

        $success = $info['http_code'] == 200 ? true : false;
        if ($success) {
         	// 1. Rohdaten loggen
			logging($id, 'lastEcho(): ' . $body);

			// 2. JSON-String in ein PHP-Array umwandeln
			$response = json_decode($body, true);

            $found = false;

            // 3. Array durchsuchen
            if (isset($response['customerHistoryRecords']) && is_array($response['customerHistoryRecords'])) {
                foreach ($response['customerHistoryRecords'] as $activity) {
        
                    if (!empty($activity['voiceHistoryRecordItems']) && is_array($activity['voiceHistoryRecordItems'])) {
                        $transcriptText = '';
            
                        // Suche nach dem ersten gültigen transcriptText
                        foreach ($activity['voiceHistoryRecordItems'] as $item) {
                            if (isset($item['transcriptText']) && is_string($item['transcriptText'])) {
                                $text = trim($item['transcriptText']);
                                // Prüfen, ob nach dem Entfernen von Leerzeichen Text übrig bleibt
                                if ($text !== '') {
                                    $transcriptText = $text;
                                    break;
                                }
                            }
                        }
            
                        // Wenn gültiger Text und Gerätename vorhanden sind
                        if ($transcriptText !== '' && !empty($activity['device']['deviceName'])) {
                            $echoName = $activity['device']['deviceName'];
                
                            // Logging für Treffer
                            logging($id, "Treffer gefunden - Device: '{$echoName}' | Transcript: '{$transcriptText}'");
                
                            // EDOMI Ausgaben setzen (HTTP-Code Variable an dein Skript anpassen, z.B. $info['http_code'] oder 200)
                            $httpCode = isset($info['http_code']) ? $info['http_code'] : '200';
                            logic_setOutput($id, 1, $echoName);
                            logic_setOutput($id, 2, 'OK (' . $httpCode . ')');
                
                            $found = true;
                            break; // Erstes passendes Gerät gefunden (in deinem Beispiel: "Wohnzimmer")
                        }
                    }
                }
            }

            if (!$found) {
                logging($id, 'Kein Gerät mit gültigem Transkript-Inhalt gefunden.');
            }	
            if ($found) {
                $match = false;
                for ($i = 3; $i <= 17; $i ++) {
                    if ($E[$i]['value'] == $echoName) {
                        logic_setOutput($id, $i, $E[1]['value']);
                        $match = true;
                        break;
                    }
                }
                if (! $match)
                    logic_setOutput($id, 18, $E[1]['value']);
            } else {
                logic_setOutput($id, 1, 'UNKNOWN');
                logic_setOutput($id, 2, 'ECHO NOT FOUND (' . $info['http_code'] . ')');
                logic_setOutput($id, 19, $E[1]['value']);
                logging($id, 'Last active ECHO not found. Response: ', $response, 1);
            }
        } else {
            logic_setOutput($id, 1, 'UNKNOWN');
            logic_setOutput($id, 2, 'Request failed (' . $info['http_code'] . ')');
            logging($id, 'Request failed. Response: ', $response, 1);
        }
    } else {
        logic_setOutput($id, 1, 'UNKNOWN');
        logic_setOutput($id, 2, 'Wrong format in /tmp/.echos.inc.php. Make sure Alexa Control LBS is running (LBS19000809).');
    }
} else {
    logic_setOutput($id, 1, 'UNKNOWN');
    logic_setOutput($id, 2, 'File /tmp/.echos.inc.php is missing. Make sure Alexa Control LBS is running (LBS19000809)');
}
logging($id, 'Echo Device indentified as: ' . $echoName);
sql_disconnect();

?>
###[/EXEC]###