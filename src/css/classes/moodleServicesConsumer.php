<?php

require_once __DIR__ . '/../config.inc.php';
require_once __DIR__ . '/../classes/constants.php';

// How to enable local tandem external function in Moodle [admin required]:
// 1. Enable web services
// 2. Enable protocols                  At least one protocol should be enabled. For security reasons, only protocols that are to be used should be enabled.
// 3. Create a specific user            A web services user is required to represent the system controlling Moodle.
// 4. Check user capability             The user should have appropriate capabilities according to the protocols used, for example webservice/rest:use, webservice/soap:use.
//                                      To achieve this, create a web services role with protocol capabilities allowed and assign it to the web services user as a system role.
//                                      Assign system role to the new user in Users > Permissions > Assign system roles
// 5. Select a service                  A service is a set of web service functions. You will allow the user to access to a new service.
//                                      On the Add service page check 'Enable' and 'Authorised users' options. Select 'No required capability'.
// 6. Add functions                     Select required functions for the newly created service.
// 7. Select a specific user            Add the web services user as an authorised user.
// 8. Create a token for a user         Create a token for the web services user.
// 9. Disable developer documentation   Detailed web services documentation is available for enabled protocols.
// 10. Test the service                 Simulate external access to the service using any test client.
//                                      Use an enabled protocol with token authentication.
//                                      WARNING: The functions that you test WILL BE EXECUTED, so be careful what you choose to test!

class MoodleServicesConsumer {
    /**
     * Dashboard > Site administration > Plugins > Web services > External services > Functions > Add functions to the service "Tandem Services"
     * @param $courseId
     * @return array|bool
     */
    public static function last_access_moodle($courseId) {
        /** @var SimpleXMLElement $xml */
        list($xml, $err) = self::post_moodle_request('local_tandem_get_last_access_info', [
            'courseid' => $courseId
        ]);

        if ($err) {
            return false;
        }

        $usersaccessinfo = array();
        if (!empty($xml->SINGLE->KEY[0]->MULTIPLE->SINGLE)) {
            foreach($xml->SINGLE->KEY[0]->MULTIPLE->SINGLE as $xml2) {
                $usersaccessinfo[] = array(
                    'last_access' => (string) $xml2->KEY[0]->VALUE,
                    'firstname' => (string) $xml2->KEY[1]->VALUE,
                    'lastname' => (string) $xml2->KEY[2]->VALUE,
                    'email' => (string) $xml2->KEY[3]->VALUE,
                );
            }
        }

        return $usersaccessinfo;
    }

    /**
     * @param string $useremail
     * @param int $courseid
     * @return bool[]|false
     */
    public static function get_user_survey_status($useremail, $courseid) {
        list($xml, $err) = self::post_moodle_request('local_tandem_get_user_survey_status', [
            'useremail' => $useremail,
            'courseid' => $courseid
        ]);

        if ($err) {
            return false;
        }

        $presurveycompleted = ('1' === (string) $xml->SINGLE->KEY[0]->SINGLE->KEY[0]->VALUE);
        $postsurveycompleted = ('1' === (string) $xml->SINGLE->KEY[0]->SINGLE->KEY[1]->VALUE);

        return [
            'presurveycompleted' => $presurveycompleted,
            'postsurveycompleted' => $postsurveycompleted,
        ];
    }

    /**
     * @param string $functionname
     * @param array $data
     * @return SimpleXMLElement[]|false[]
     */
    private static function post_moodle_request($functionname, $data) {
        // Moodle external service settings
        $token = MOODLE_EXTERNAL_SERVICES_TOKEN;
        $domainname = MOODLE_EXTERNAL_SERVICES_DOMAIN;
        $protocol = MOODLE_EXTERNAL_SERVICES_PROTOCOL;

        // Request settings
        $moodleurl = "$domainname/webservice/$protocol/server.php?wstoken=$token&wsfunction=$functionname";
        $postfields = ['data' => $data];

        // Do request
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $moodleurl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($postfields),
            CURLOPT_HTTPHEADER => ['Cache-Control: no-cache', 'Content-Type: application/x-www-form-urlencoded'],
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        $xml = false;
        if (!$err) {
            $xml = simplexml_load_string(trim($response), 'SimpleXMLElement', LIBXML_NOCDATA);
        }

        return array($xml, $err);
    }
}
