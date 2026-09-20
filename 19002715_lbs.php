###[DEF]###
[name		= New Alexa Last Active Echo Device v1.12	]

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

[v#100		= 1.12 ]
[v#101		= 19002715 ]
[v#102		= New-Alexa-Last-Active-Echo-Device ]
[v#103		= 0 ]
[v#104		= 0 ]
[v#105		= 1 ]

###[/DEF]###
###[HELP]###

If you want to use the same voice command at different Echo devices to trigger different actions,
you have to connect the specific output of the Alexa Smarthome Device LBS (A4-A23) to E1 of this LBS.
The value will be sent to one of the outputs A3-A14 depending at which Echo device the voice command was received.
You can specify up to 10 different Echo devices at E3-E12, which will be matched with the outputs A3-A12.

E1: Triggers LBS with the value received by the voice command
(E1 has to be connected to one of the outputs A4-A23 of the Alexa Smarthome Device LBS (LBS19001201)
E2: Enable Logging (0-none|1-emerg|2-alert|3-crit|4-err|5-warning|6-notice|7-info|8-debug)
E3: Name of Echo 1
E4: Name of Echo 2
E5: Name of Echo 3
E6: Name of Echo 4
E7: Name of Echo 5
E8: Name of Echo 6
E9: Name of Echo 7
E10: Name of Echo 8
E11: Name of Echo 9
E12: Name of Echo 10

A1: Name of Echo device which was triggered by the last voice command
A2: Result of last operation
A3: Trigger value at E1 will be sent to A3 if voice command was received by Echo 1
A4: Trigger value at E1 will be sent to A4 if voice command was received by Echo 2
A5: Trigger value at E1 will be sent to A5 if voice command was received by Echo 3
A6: Trigger value at E1 will be sent to A6 if voice command was received by Echo 4
A7: Trigger value at E1 will be sent to A7 if voice command was received by Echo 5
A8: Trigger value at E1 will be sent to A8 if voice command was received by Echo 6
A9: Trigger value at E1 will be sent to A9 if voice command was received by Echo 7
A10: Trigger value at E1 will be sent to A10 if voice command was received by Echo 8
A11: Trigger value at E1 will be sent to A11 if voice command was received by Echo 9
A12: Trigger value at E1 will be sent to A12 if voice command was received by Echo 10
A13: Trigger value at E1 will be sent to A13 if voice command was received by Echo 11
A14: Trigger value at E1 will be sent to A14 if voice command was received by Echo 12
A15: Trigger value at E1 will be sent to A15 if voice command was received by Echo 13
A16: Trigger value at E1 will be sent to A16 if voice command was received by Echo 14
A17: Trigger value at E1 will be sent to A17 if voice command was received by Echo 15
A18: Trigger value at E1 will be sent to A18 if voice command was received by a known Echo device, but not specified at E3-E12
A19: Trigger value at E1 will be sent to A19 if voice command was received by an unknown Echo device

Changelog:
==========
v1.12: Fix repeated HTTP 429 on the csrf-token endpoint: reuse the last good
       anti-csrftoken-a2z (the iOS app reuses one token across many requests)
       instead of fetching every 5 min, fall back to it when a refresh fails
       instead of aborting, and invalidate it when history-records rejects it
       (401/403). Log only a short token/error excerpt, send accept-language
v1.11: Skip ASR_TIMEOUT and FALSE_WAKE_WORD_1P records (device heard nothing or
       woke on a false positive — not a real command); update hardcoded
       User-Agent to the current Alexa iOS build 2.2.761199.0; note
       conversation.deviceInfo is still populated on the live API, so the
       v1.9/v1.10 fallback stays only as a safety net
v1.10: Fallback for empty conversation.deviceInfo: build a timestamp→device map
       from all utterance records with populated deviceInfo, then find the nearest
       match within 5 s; conversation records have no activityKey so the v1.9
       serial-lookup never fired — this replaces it
v1.9: Fallback: extract device serial from activityKey when conversation.deviceInfo
      is empty (Amazon changed API — deviceInfo now always []), look up serial in
      $echos array; reduce history window from 7 days to 1 hour
v1.8: Skip ROUTINES_3P records — triggered automatically on multiple devices,
      not by a person speaking to a specific device
v1.7: Hardcode Alexa iOS app User-Agent for all requests to prevent Amazon
      IP-based blocking (overrides config userAgent from LBS19000809)
v1.6: Add x-amzn-alexa-app header to csrf-token and history-records requests
      to identify as official Alexa iOS app and avoid HTTP 429 rate limiting
v1.5: Log HTTP status code from csrf-token endpoint for better diagnosis
v1.4: Fix anti-csrftoken-a2z fetch — use dedicated /alexa-privacy/apd/csrf-token
      endpoint (plain text response) instead of scraping HTML from activity page
v1.3: Better session error detection — abort early with clear message when csrf cookie
      or anti-csrftoken-a2z is missing; explicit 403 hint to re-login via LBS19000809
v1.2: Improved device detection — routines and conversation records are now recognized;
      DEVICE_ARBITRATION and DISCARDED_NON_DEVICE_DIRECTED_INTENT records are explicitly
      skipped; no transcript required, device name alone is sufficient
v1.1: Updated to new Alexa API endpoint (rah/alexa-history-records-v2), response key
      (alexaHistoryRecords), device field (deviceInfo.deviceName), dynamic timestamps
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

// utteranceTypes to skip — device heard wake word but did NOT handle the request
define('SKIP_UTTERANCE_TYPES', [
    'DEVICE_ARBITRATION',
    'DISCARDED_NON_DEVICE_DIRECTED_INTENT',
    'ROUTINES_3P',       // triggered automatically on multiple devices, not by a person
    'WAKE_WORD_ONLY',    // device heard wake word but no command followed
    'ASR_TIMEOUT',       // device heard wake word but no speech was recognized
    'FALSE_WAKE_WORD_1P' // device woke on a false positive, no command
]);

// Alexa app identifier header — identifies requests as coming from the official Alexa iOS app
define('ALEXA_APP_HEADER', 'eyJhcHBJZCI6ImFtem4xLmFwcGxpY2F0aW9uLjQ1Nzg2ZWUwOWIwMjRhMDhhNjk4ZDMwYjBhZDMxMDM3IiwidmVyc2lvbiI6IjEuMCJ9');

// User-Agent matching the Alexa iOS app — required to avoid Amazon 429 rate limiting on server IPs
define('ALEXA_USER_AGENT', 'AppleWebKit PitanguiBridge/2.2.761199.0-[HARDWARE=iPhone16_1][SOFTWARE=27.0][DEVICE=iPhone]');

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
    // Dedicated endpoint returns the anti-csrftoken-a2z value as plain text
    $url = 'https://www.' . $config['amazon'] . '/alexa-privacy/apd/csrf-token';
    $http_headers = array(
        'Accept: text/plain, text/html, */*',
        'Content-Type: application/json',
        'accept-language: en-US',
        'csrf: ' . $csrf,
        'x-amzn-timezoneid: Europe/Berlin',
        'x-amzn-alexa-app: ' . ALEXA_APP_HEADER,
        'Connection: keep-alive'
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT,      ALEXA_USER_AGENT);
    curl_setopt($ch, CURLOPT_COOKIEFILE,     $config['cookieFile']);
    curl_setopt($ch, CURLOPT_URL,            $url);
    curl_setopt($ch, CURLOPT_HTTP_VERSION,   CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_ENCODING,       "gzip, deflate");
    curl_setopt($ch, CURLOPT_HTTPHEADER,     $http_headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $body     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $token = trim($body);
    // Return [httpCode, token] so caller can log the status
    return [$httpCode, $token];
}

// The anti-csrftoken-a2z token is long-lived: the Alexa iOS app reuses a single one
// across many requests. Hammering the /csrf-token endpoint every few minutes is what
// triggers Amazon's HTTP 429, so we reuse the last good token aggressively and only
// refresh when it is old. A failed refresh no longer discards a still-usable token —
// it falls back to the cached one so a 429/5xx doesn't break the LBS.
//
// Returns [httpCode, token, source] where source is:
//   'cached'    - reused a token that is still within $tokenTTL (no network call)
//   'fresh'     - newly fetched successfully
//   'stale'     - refresh failed, reusing the last good token
//   'throttled' - refresh failed recently and there is no token to fall back to
//   'failed'    - refresh failed and there is no token to fall back to
function get_activity_csrf_cached()
{
    $cacheFile     = '/tmp/.alexa_activity_csrf.json';
    $tokenTTL      = 1800;      // reuse a good token for 30 min without calling Amazon
    $staleMaxAge   = 6 * 3600;  // allow an older token to bridge an outage
    $errorThrottle = 600;       // after a failed refresh, back off this long before retrying

    $data = [];
    if (file_exists($cacheFile)) {
        $decoded = json_decode(file_get_contents($cacheFile), true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }

    $now       = time();
    $token     = $data['token'] ?? '';
    $tokenTs   = $data['token_ts'] ?? 0;
    $lastCode  = $data['last_code'] ?? 0;
    $lastTryTs = $data['last_try_ts'] ?? 0;

    // 1) A still-fresh good token: always prefer it, never call Amazon.
    if ($token !== '' && ($now - $tokenTs) < $tokenTTL) {
        return [200, $token, 'cached'];
    }

    // 2) After a recent failed refresh, don't hammer Amazon again — reuse the old token if
    //    we still have one, otherwise report the failure (throttled).
    if ($lastTryTs > 0 && $lastCode !== 200 && ($now - $lastTryTs) < $errorThrottle) {
        if ($token !== '' && $tokenTs > 0 && ($now - $tokenTs) < $staleMaxAge) {
            return [$lastCode, $token, 'stale'];
        }
        return [$lastCode, '', 'throttled'];
    }

    // 3) Refresh.
    [$httpCode, $newToken] = get_activity_csrf();

    if ($httpCode === 200 && $newToken !== '') {
        file_put_contents($cacheFile, json_encode([
            'token'       => $newToken,
            'token_ts'    => $now,
            'last_code'   => 200,
            'last_try_ts' => $now,
        ]));
        return [200, $newToken, 'fresh'];
    }

    // 4) Refresh failed. Keep the last good token so a 429/5xx doesn't break the LBS.
    //    An HTTP 200 with an empty body also counts as a failure (-1) so it is throttled.
    $failCode = ($httpCode === 200) ? -1 : $httpCode;
    file_put_contents($cacheFile, json_encode([
        'token'       => $token,
        'token_ts'    => $tokenTs,
        'last_code'   => $failCode,
        'last_try_ts' => $now,
    ]));

    if ($token !== '' && $tokenTs > 0 && ($now - $tokenTs) < $staleMaxAge) {
        return [$httpCode, $token, 'stale'];
    }
    return [$httpCode, '', 'failed'];
}

// Drop the cached anti-CSRF token so the next run fetches a fresh one. Called when the
// history endpoint rejects the token (HTTP 401/403).
function invalidate_activity_csrf()
{
    $cacheFile = '/tmp/.alexa_activity_csrf.json';
    if (file_exists($cacheFile)) {
        @unlink($cacheFile);
    }
}

/**
 * Extract the device name from a history record.
 *
 * conversation records  -> deviceInfo is an array of objects. Observed populated on the
 *                          live API (2026-09); the fallback below is only a safety net
 *                          in case Amazon ever omits it again.
 * utterance records     -> deviceInfo is a plain object
 *
 * $utteranceDeviceMap is a pre-built [timestamp => deviceName] map from the utterance
 * records in the same response. Used only when conversation.deviceInfo is empty:
 * find the nearest utterance device within 5 seconds of the conversation timestamp.
 *
 * Returns empty string when the record should be skipped.
 */
function extractDeviceName($activity, $utteranceDeviceMap = [])
{
    $recordType = $activity['recordType'] ?? '';

    if (isset($activity['utteranceType']) && in_array($activity['utteranceType'], SKIP_UTTERANCE_TYPES)) {
        return '';
    }

    if ($recordType === 'conversation') {
        // Primary: use deviceInfo when populated
        if (!empty($activity['deviceInfo']) && is_array($activity['deviceInfo'])) {
            return $activity['deviceInfo'][0]['deviceName'] ?? '';
        }
        // Fallback (safety net): find the nearest utterance device within 5 seconds
        $convTs   = $activity['timestamp'] ?? 0;
        $bestName = '';
        $bestDiff = PHP_INT_MAX;
        foreach ($utteranceDeviceMap as $ts => $name) {
            $diff = abs($ts - $convTs);
            if ($diff < 5000 && $diff < $bestDiff) {
                $bestDiff = $diff;
                $bestName = $name;
            }
        }
        return $bestName;
    } elseif ($recordType === 'utterance') {
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
        logging($id, 'CSRF: ' . ($csrf !== false ? $csrf : 'NOT FOUND — cookie file may be expired'));
        if ($csrf === false) {
            logic_setOutput($id, 1, 'UNKNOWN');
            logic_setOutput($id, 2, 'Session expired: csrf cookie not found. Re-login via LBS19000809 required.');
            logging($id, 'Aborting: csrf cookie missing in ' . $config['cookieFile'], null, 1);
            sql_disconnect();
            exit();
        }

        [$csrfHttpCode, $activity_csrf, $csrfSource] = get_activity_csrf_cached();
        logging($id, 'Activity-CSRF ' . $csrfSource . ' HTTP ' . $csrfHttpCode . ' | token: ' . ($activity_csrf !== '' ? substr($activity_csrf, 0, 48) : '(empty)'));
        if ($activity_csrf === '') {
            $hint = ($csrfHttpCode == 401 || $csrfHttpCode == 403)
                ? ' Session not authenticated — re-login via LBS19000809 required.'
                : ($csrfHttpCode == 429
                    ? ' Amazon rate limit hit — will recover on its own.'
                    : (($csrfHttpCode == 503 || $csrfHttpCode == 502 || $csrfHttpCode == 504)
                        ? ' Amazon server temporarily unavailable — will recover on its own.'
                        : ' Check network or Amazon session.'));
            logic_setOutput($id, 1, 'UNKNOWN');
            logic_setOutput($id, 2, 'csrf-token fetch failed (HTTP ' . $csrfHttpCode . ').' . $hint);
            logging($id, 'Aborting: no usable anti-CSRF token (HTTP ' . $csrfHttpCode . ')', null, 1);
            sql_disconnect();
            exit();
        }
        if ($csrfSource === 'stale') {
            logging($id, 'WARNING: csrf-token refresh failed (HTTP ' . $csrfHttpCode . '), reusing previously cached anti-CSRF token', null, 5);
        }

        $endTime   = round(microtime(true) * 1000);
        $startTime = $endTime - (1 * 60 * 60 * 1000); // 1 hour

        $http_headers = array(
            'DNT: 1',
            'Connection: keep-alive',
            'Content-Type: application/json; charset=UTF-8',
            'Accept: application/json',
            'accept-language: en-US',
            'anti-csrftoken-a2z: ' . $activity_csrf,
            'csrf: ' . $csrf,
            'x-amzn-timezoneid: Europe/Berlin',
            'x-amzn-alexa-app: ' . ALEXA_APP_HEADER
        );
        $url = 'https://www.' . $config['amazon']
            . '/alexa-privacy/apd/rah/alexa-history-records-v2'
            . '?startTime=' . $startTime . '&endTime=' . $endTime;
        logging($id, 'URL: ' . $url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT,      ALEXA_USER_AGENT);
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
                // Build timestamp→device map from utterance records for fallback correlation
                $utteranceDeviceMap = [];
                foreach ($decoded['alexaHistoryRecords'] as $rec) {
                    if ($rec['recordType'] === 'utterance' && isset($rec['deviceInfo']['deviceName'])) {
                        $utteranceDeviceMap[$rec['timestamp']] = $rec['deviceInfo']['deviceName'];
                    }
                }

                foreach ($decoded['alexaHistoryRecords'] as $activity) {
                    $deviceName = extractDeviceName($activity, $echos, $utteranceDeviceMap);
                    if ($deviceName === '')
                        continue;

                    $echoName      = $deviceName;
                    $type          = $activity['utteranceType'] ?? $activity['type'] ?? 'conversation';
                    $title         = $activity['title'] ?? '';
                    $subtitle      = $activity['subTitle'] ?? '';
                    $transcriptText = '';
                    foreach ($activity['voiceHistoryRecordItems'] ?? [] as $item) {
                        if (($item['recordItemType'] ?? '') === 'ASR_REPLACEMENT_TEXT' && !empty($item['transcriptText'])) {
                            $transcriptText = $item['transcriptText'];
                            break;
                        }
                    }
                    logging($id, "Match — Device: '{$echoName}' | Type: '{$type}' | Title: '{$title}' | Command: '{$subtitle}' | Transcript: '{$transcriptText}'");

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
            if ($info['http_code'] == 401 || $info['http_code'] == 403) {
                invalidate_activity_csrf();
                logging($id, 'Cached anti-CSRF token rejected (HTTP ' . $info['http_code'] . '), invalidated for next run', null, 4);
            }
            $hint = ($info['http_code'] == 403)
                ? ' — Session expired or cookies invalid. Re-login via LBS19000809 required.'
                : '';
            logic_setOutput($id, 1, 'UNKNOWN');
            logic_setOutput($id, 2, 'Request failed (' . $info['http_code'] . ')' . $hint);
            logging($id, 'Request failed. HTTP ' . $info['http_code'] . $hint);
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
###[/EXEC]###
