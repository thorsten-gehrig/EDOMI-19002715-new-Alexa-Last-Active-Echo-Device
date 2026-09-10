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
LBS_EXEC_START
<?
require (dirname(__FILE__) . "/../../../../main/include/php/incl_lbsexec.php");
set_time_limit(3);
sql_connect();

// utteranceTypes to skip — device heard wake word but did NOT handle the request
define('SKIP_UTTERANCE_TYPES', [
    'DEVICE_ARBITRATION',
    'DISCARDED_NON_DEVICE_DIRECTED_INTENT',
]);

function logging($id, $msg, $var = NULL, $priority = 8)
{
    $E = getLogicEingangDataAll($id);
    $logLevel = getLogicElementVar($id, 103);
    if (is_int($priority) && $priority <= $logLevel && $priority > 0) {
        $logLevelNames = array('none','emerg','alert','crit','err','warning','notice','info','debug');
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
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    curl_close($ch);
    if (preg_match('/"csrf-token" content="(.*?)"/', $body, $match))
        return $match[1];
    else
        return false;
}

/**
 * Extract the device name from a history record.
 *
 * conversation records  → deviceInfo is an array of objects
 * utterance records     → deviceInfo is a plain object
 *
 * Returns empty string when the record should be skipped.
 */
function extractDeviceName($activity)
{
    // Skip records where the device heard the wake word but did not handle the request
    if (isset($activity['utteranceType']) && in_array($activity['utteranceType'], SKIP_UTTERANCE_TYPES)) {
        return '';
    }

    if ($activity['recordType'] === 'conversation') {
        // deviceInfo is an array; first entry is the responding device
        if (!empty($activity['deviceInfo']) && is_array($activity['deviceInfo'])) {
            return $activity['deviceInfo'][0]['deviceName'] ?? '';
        }
    } elseif ($activity['recordType'] === 'utterance') {
        // deviceInfo is a plain object
        return $activity['deviceInfo']['deviceName'] ?? '';
    }

    return '';
}

// ─── main ────────────────────────────────────────────────────────────────────

$echoName = 'UNKNOWN';

if (file_exists('/tmp/.echos.inc.php')) {
    include_once '/tmp/.echos.inc.php';
    $E = logic_getInputs($id);
    logging($id, "Deriving source echo device...");

    if ($config !== false && $echos !== false && is_array($config) && is_array($echos)) {

        $csrf = importCSRF();
        logging($id, 'CSRF: ' . $csrf);
        $activity_csrf = get_activity_csrf();
        logging($id, 'Activity-CSRF: ' . $activity_csrf);

        $endTime   = round(microtime(true) * 1000);
        $startTime = $endTime - (7 * 24 * 60 * 60 * 1000);

        $http_headers = array(
            'DNT: 1',
            'Connection: keep-alive',
            'Content-Type: application/json; charset=UTF-8',
            'Accept: application/json',
            'anti-csrftoken-a2z: ' . $activity_csrf,
            'csrf: ' . $csrf,
            'x-amzn-timezoneid: Europe/Berlin'
        );
        $url = 'https://www.' . $config['amazon']
            . '/alexa-privacy/apd/rah/alexa-history-records-v2'
            . '?startTime=' . $startTime . '&endTime=' . $endTime;
        logging($id, 'URL: ' . $url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT,      $config['userAgent']);
        curl_setopt($ch, CURLOPT_COOKIEFILE,     $config['cookieFile']);
        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION,   CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_ENCODING,       "gzip, deflate");
        curl_setopt($ch, CURLOPT_HEADER,         1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     $http_headers);
        curl_setopt($ch, CURLOPT_POST,           1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     '{}');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $response    = curl_exec($ch);
        $info        = curl_getinfo($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header      = substr($response, 0, $header_size);
        $body        = substr($response, $header_size);
        curl_close($ch);

        if ($info['http_code'] == 200) {
            logging($id, 'Response body: ' . $body);

            $decoded = json_decode($body, true);
            $found   = false;

            if (isset($decoded['alexaHistoryRecords']) && is_array($decoded['alexaHistoryRecords'])) {
                foreach ($decoded['alexaHistoryRecords'] as $activity) {
                    $deviceName = extractDeviceName($activity);
                    if ($deviceName === '')
                        continue;

                    $echoName = $deviceName;
                    $type     = $activity['utteranceType'] ?? $activity['type'] ?? 'conversation';
                    logging($id, "Match — Device: '{$echoName}' | Type: '{$type}'");

                    logic_setOutput($id, 1, $echoName);
                    logic_setOutput($id, 2, 'OK (' . $info['http_code'] . ')');
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $match = false;
                for ($i = 3; $i <= 17; $i++) {
                    if ($E[$i]['value'] == $echoName) {
                        logic_setOutput($id, $i, $E[1]['value']);
                        $match = true;
                        break;
                    }
                }
                if (!$match)
                    logic_setOutput($id, 18, $E[1]['value']);
            } else {
                logic_setOutput($id, 1, 'UNKNOWN');
                logic_setOutput($id, 2, 'ECHO NOT FOUND (' . $info['http_code'] . ')');
                logic_setOutput($id, 19, $E[1]['value']);
                logging($id, 'No matching device found in response.', $decoded, 1);
            }

        } else {
            logic_setOutput($id, 1, 'UNKNOWN');
            logic_setOutput($id, 2, 'Request failed (' . $info['http_code'] . ')');
            logging($id, 'Request failed. HTTP ' . $info['http_code']);
        }

    } else {
        logic_setOutput($id, 1, 'UNKNOWN');
        logic_setOutput($id, 2, 'Wrong format in /tmp/.echos.inc.php. Make sure Alexa Control LBS is running (LBS19000809).');
    }
} else {
    logic_setOutput($id, 1, 'UNKNOWN');
    logic_setOutput($id, 2, 'File /tmp/.echos.inc.php is missing. Make sure Alexa Control LBS is running (LBS19000809)');
}

logging($id, 'Echo Device identified as: ' . $echoName);
sql_disconnect();

?>
