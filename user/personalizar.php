<?php

require_once(__DIR__ . '/../config.php');
require_once($CFG->libdir . '/navigationlib.php');
use auth_oidc\jwt;

require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}

$userid = optional_param('userid', $USER->id, PARAM_INT);
$currentuser = $userid == $USER->id;
global $CFG, $DB;

$tokenrec = $DB->get_record('auth_oidc_token', ['userid' => $USER->id]);

$idtoken = jwt::instance_from_encoded($tokenrec->idtoken);
var_dump($idtoken);
        
$redirecturi = (!empty($CFG->loginhttps)) ? str_replace('http://', 'https://', $CFG->wwwroot) : $CFG->wwwroot;
$redirecturi .= '/auth/oidc/';
        
$params = [
    'response_type' => 'code',
    'client_id' => $idtoken->claim('aud'),
    'scope' => $tokenrec->scope,
    'response_mode' => 'form_post',
    'redirect_uri' => $redirecturi,
    'acr_values' => 'action:manage',
    'login_hint' => $idtoken->claim('name'),
];

$redirecturl = new \moodle_url($idtoken->claim('iss').'/connect/authorize', $params);
redirect($redirecturl);             

        
