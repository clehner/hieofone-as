<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

App::singleton('oauth2', function () {
    $storage = new OAuth2\Storage\Pdo(DB::connection()->getPdo());
    // specify your audience (typically, the URI of the oauth server)
    // $issuer = env('URI', false);
    $issuer = URL::to('/');
    $audience = 'https://' . $issuer;
    $config['use_openid_connect'] = true;
    $config['issuer'] = $issuer;
    $config['allow_implicit'] = true;
    $config['use_jwt_access_tokens'] = true;
    $config['refresh_token_lifetime'] = 0;
    // $config['auth_code_lifetime'] = 600;
    $refresh_config['always_issue_new_refresh_token'] = false;
    $refresh_config['unset_refresh_token_after_use'] = false;
    // create server
    $server = new OAuth2\Server($storage, $config);
    if (env('DOCKER') == '1') {
        $publicKey = env('PUBKEY');
        $privateKey = env('PRIVKEY');
    } else {
        $publicKey  = File::get(base_path() . "/.pubkey.pem");
        $privateKey = File::get(base_path() . "/.privkey.pem");
    }
    // create storage for OpenID Connect
    $keyStorage = new OAuth2\Storage\Memory(['keys' => [
        'public_key'  => $publicKey,
        'private_key' => $privateKey
    ]]);
    $server->addStorage($keyStorage, 'public_key');
    // set grant types
    $server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));
    $server->addGrantType(new OAuth2\GrantType\UserCredentials($storage));
    $server->addGrantType(new OAuth2\OpenID\GrantType\AuthorizationCode($storage));
    $server->addGrantType(new OAuth2\GrantType\RefreshToken($storage, $refresh_config));
    $server->addGrantType(new OAuth2\GrantType\JwtBearer($storage, $audience));
    return $server;
});

// Core pages
Route::get('/', ['as' => 'welcome', 'uses' => 'OauthController@welcome']);
Route::any('accept_invitation/{id}', ['as' => 'accept_invitation', 'middleware' => 'csrf', 'uses' => 'OauthController@accept_invitation']);
Route::post('as_push_notification', ['as' => 'as_push_notification', 'uses' => 'OauthController@as_push_notification']);
Route::any('client_register', ['as' => 'client_register', 'uses' => 'OauthController@client_register']);
Route::any('install', ['as' => 'install',  'middleware' => 'csrf', 'uses' => 'OauthController@install']);
Route::any('picture', ['as' => 'picture', 'middleware' => 'csrf', 'uses' => 'OauthController@picture']);
Route::any('picture_cancel', ['as' => 'picture_cancel', 'uses' => 'OauthController@picture_cancel']);

// Login pages
Route::any('login', ['as' => 'login', 'uses' => 'OauthController@login']);
Route::any('logout', ['as' => 'logout', 'uses' => 'OauthController@logout']);
Route::any('login_passwordless', ['as' => 'login_passwordless', 'uses' => 'OauthController@login_passwordless']);
Route::any('login_vp', ['as' => 'login_vp', 'uses' => 'OauthController@login_vp']);
Route::any('login_vp_poll', ['as' => 'login_vp_poll', 'middleware' => 'csrf', 'uses' => 'OauthController@login_vp_poll']);
Route::any('oauth_login', ['as' => 'oauth_login', 'uses' => 'OauthController@oauth_login']);
Route::any('password_email', ['as' => 'password_email', 'middleware' => 'csrf', 'uses' => 'OauthController@password_email']);
Route::any('password_reset/{id}', ['as' => 'password_reset', 'middleware' => 'csrf', 'uses' => 'OauthController@password_reset']);
Route::any('remote_logout', ['as' => 'remote_logout', 'uses' => 'OauthController@remote_logout']);
Route::any('vp_request', ['as' => 'vp_request', 'uses' => 'OauthController@vp_request']);

// Home pages
Route::get('home', ['as' => 'home', 'uses' => 'HomeController@index']);
Route::get('activity_logs', ['as' => 'activity_logs', 'uses' => 'HomeController@activity_logs']);
Route::post('ajax_change_user_policy', ['as' => 'ajax_change_user_policy', 'middleware' => 'csrf', 'uses' => 'HomeController@ajax_change_user_policy']);
Route::get('authorize_client', ['as' => 'authorize_client', 'uses' => 'HomeController@authorize_client']);
Route::get('authorize_client_action/{id}', ['as' => 'authorize_client_action', 'uses' => 'HomeController@authorize_client_action']);
Route::get('authorize_client_disable/{id}', ['as' => 'authorize_client_disable', 'uses' => 'HomeController@authorize_client_disable']);
Route::get('authorize_resource_server', ['as' => 'authorize_resource_server', 'uses' => 'HomeController@authorize_resource_server']);
Route::get('authorize_user', ['as' => 'authorize_user', 'uses' => 'HomeController@authorize_user']);
Route::get('authorize_user_action/{id}', ['as' => 'authorize_user_action', 'uses' => 'HomeController@authorize_user_action']);
Route::get('authorize_user_disable/{id}', ['as' => 'authorize_user_disable', 'uses' => 'HomeController@authorize_user_disable']);
Route::any('certifier_add', ['as' => 'certifier_add', 'uses' => 'HomeController@certifier_add']);
Route::get('certifiers', ['as' => 'certifiers', 'uses' => 'HomeController@certifiers']);
Route::get('change_notify/{id}/{value}/{type}', ['as' => 'change_notify', 'uses' => 'HomeController@change_notify']);
Route::any('change_password', ['as' => 'change_password', 'uses' => 'HomeController@change_password']);
Route::post('change_policy', ['as' => 'change_policy', 'uses' => 'HomeController@change_policy']);
Route::post('change_role', ['as' => 'change_role', 'middleware' => 'csrf', 'uses' => 'HomeController@change_role']);
Route::get('change_user_policy/{name}/{claim_id}/{setting}/{type}', ['as' => 'change_user_policy', 'uses' => 'HomeController@change_user_policy']);
Route::get('clients', ['as' => 'clients', 'uses' => 'HomeController@clients']);
Route::get('consents_resource_server', ['as' => 'consents_resource_server', 'uses' => 'HomeController@consents_resource_server']);
Route::get('custom_policies', ['as' => 'custom_policies', 'uses' => 'HomeController@custom_policies']);
Route::any('custom_policy_edit/{id?}', ['as' => 'custom_policy_edit', 'uses' => 'HomeController@custom_policy_edit']);
Route::get('default_policies', ['as' => 'default_policies', 'uses' => 'HomeController@default_policies']);
Route::get('directories', ['as' => 'directories', 'uses' => 'HomeController@directories']);
Route::any('directory_add/{type?}', ['as' => 'directory_add', 'uses' => 'HomeController@directory_add']);
Route::get('directory_remove/{id}/{consent?}', ['as' => 'directory_remove', 'uses' => 'HomeController@directory_remove']);
Route::post('fhir_edit', ['as' => 'fhir_edit', 'middleware' => 'csrf', 'uses' => 'HomeController@fhir_edit']);
Route::get('invite_cancel/{code}/{redirect?}', ['as' => 'invite_cancel', 'uses' => 'HomeController@invite_cancel']);
Route::get('login_authorize', ['as' => 'login_authorize', 'uses' => 'HomeController@login_authorize']);
Route::get('login_authorize_action/{type}', ['as' => 'login_authorize_action', 'uses' => 'HomeController@login_authorize_action']);
Route::any('make_invitation', ['as' => 'make_invitation', 'uses' => 'HomeController@make_invitation']);
Route::get('my_info', ['as' => 'my_info', 'uses' => 'HomeController@my_info']);
Route::any('my_info_edit', ['as' => 'my_info_edit', 'uses' => 'HomeController@my_info_edit']);
Route::post('policy_user_add/{policy_id}', ['as' => 'policy_user_add', 'uses' => 'HomeController@policy_user_add']);
Route::get('policy_user_remove/{claim_id}/{policy_id}', ['as' => 'policy_user_remove', 'uses' => 'HomeController@policy_user_remove']);
Route::get('proxy_add/{sub}', ['as' => 'proxy_add', 'uses' => 'HomeController@proxy_add']);
Route::get('proxy_remove/{sub}', ['as' => 'proxy_remove', 'uses' => 'HomeController@proxy_remove']);
Route::get('resend_invitation/{id}', ['as' => 'resend_invitation', 'uses' => 'HomeController@resend_invitation']);
Route::get('resources/{id}', ['as' => 'resources', 'uses' => 'HomeController@resources']);
Route::get('resource_servers', ['as' => 'resource_servers', 'uses' => 'HomeController@resource_servers']);
Route::get('resource_view/{id}', ['as' => 'resource_view', 'uses' => 'HomeController@resource_view']);
Route::any('setup_mail', ['as' => 'setup_mail', 'uses' => 'HomeController@setup_mail']);
Route::get('setup_mail_test', ['as' => 'setup_mail_test', 'uses' => 'HomeController@setup_mail_test']);
Route::get('syncthing', ['as' => 'syncthing', 'uses' => 'HomeController@syncthing']);
Route::any('syncthing_add', ['as' => 'syncthing_add', 'uses' => 'HomeController@syncthing_add']);
Route::get('syncthing_remove/{id}', ['as' => 'syncthing_remove', 'uses' => 'HomeController@syncthing_remove']);
Route::get('users', ['as' => 'users', 'uses' => 'HomeController@users']);

// Depreciating
Route::get('change_permission/{id}/{action}/{scope}', ['as' => 'change_permission', 'uses' => 'HomeController@change_permission']);
Route::get('change_permission_add_edit/{id}', ['as' => 'change_permission_add_edit', 'uses' => 'HomeController@change_permission_add_edit']);
Route::get('change_permission_remove_edit/{id}', ['as' => 'change_permission_remove_edit', 'uses' => 'HomeController@change_permission_remove_edit']);
Route::get('change_permission_delete/{id}', ['as' => 'change_permission_delete', 'uses' => 'HomeController@change_permission_delete']);
Route::post('rs_authorize_action/{type?}', ['as' => 'rs_authorize_action', 'uses' => 'HomeController@rs_authorize_action']);
Route::get('consent_table', ['as' => 'consent_table', 'uses' => 'HomeController@consent_table']);
Route::get('consent_edit/{id}/{toggle?}/{policy?}/{directory?}', ['as' => 'consent_edit', 'uses' => 'HomeController@consent_edit']);

// Demo pages
Route::post('pnosh_sync', ['as' => 'pnosh_sync', 'uses' => 'OauthController@pnosh_sync']);
Route::any('reset_demo', ['as' => 'reset_demo', 'uses' => 'OauthController@reset_demo']);
Route::any('invite_demo', ['as' => 'invite_demo', 'uses' => 'OauthController@invite_demo']);
Route::get('check_demo', ['as' => 'check_demo', 'uses' => 'OauthController@check_demo']);
Route::get('check_demo_self', ['as' => 'check_demo_self', 'middleware' => 'csrf', 'uses' => 'OauthController@check_demo_self']);

// UMA pages
Route::post('token', ['as' => 'token', 'uses' => 'OauthController@token']);
Route::get('authorize', ['as' => 'authorize', 'uses' => 'OauthController@oauth_authorize']);
Route::get('jwks_uri', ['as' => 'jwks_uri', 'uses' => 'OauthController@jwks_uri']);
Route::get('userinfo', ['as' => 'userinfo', 'uses' => 'OauthController@userinfo']);

// Dynamic client registration
Route::post('register', ['as' => 'register', 'uses' => 'UmaController@register']);

// Requesting party claims endpoint
Route::get('rqp_claims', ['as' => 'rqp_claims', 'uses' => 'UmaController@rqp_claims']);

// Following routes need token authentiation
Route::group(['middleware' => 'token'], function () {
    // Resource set
    Route::resource('resource_set', 'ResourceSetController');

    // Policy
    Route::resource('policy', 'PolicyController');

    // Permission request
    Route::post('permission', ['as' => 'permission', 'uses' => 'UmaController@permission']);

    // Requesting party token request
    Route::post('authz_request', ['as' => 'authz_request', 'uses' => 'UmaController@authz_request']);

    // introspection
    Route::post('introspect', ['as'=> 'introspect', 'uses' => 'OauthController@introspect']);

    // Revocation
    Route::post('revoke', ['as' => 'revoke', 'uses' => 'OauthController@revoke']);

    // get mdNOSH URI
    Route::get('get_mdnosh', ['as' => 'get_mdnosh', 'uses' => 'OauthController@get_mdnosh']);
});

// OpenID Connect relying party routes
Route::get('google', ['as' => 'google', 'uses' => 'OauthController@google']);
Route::any('google_md/{npi?}', ['as' => 'google_md', 'uses' => 'OauthController@google_md']);
Route::any('google_md1', ['as' => 'google_md1', 'uses' => 'OauthController@google_md1']);
Route::get('account/google', ['as' => 'account/google', 'uses' => 'OauthController@google']);
Route::get('installgoogle', ['as' => 'installgoogle', 'uses' => 'OauthController@installgoogle']);

// Configuration endpoints
Route::get('.well-known/openid-configuration', ['as' => 'openid-configuration', function () {
    $scopes = DB::table('oauth_scopes')->get();
    $config = [
        'issuer' => URL::to('/'),
        'grant_types_supported' => [
            'authorization_code',
            'client_credentials',
            'user_credentials',
            'implicit',
            'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'urn:ietf:params:oauth:grant_type:redelegate'
        ],
        'registration_endpoint' => URL::to('register'),
        'token_endpoint' => URL::to('token'),
        'authorization_endpoint' => URL::to('authorize'),
        'introspection_endpoint' => URL::to('introspection'),
        'userinfo_endpoint' => URL::to('userinfo'),
        'scopes_supported' => $scopes,
        'jwks_uri' => URL::to('jwks_uri'),
        'revocation_endpoint' => URL::to('revoke')
    ];
    return $config;
}]);

Route::get('.well-known/uma2-configuration', function () {
    $config = [
        'issuer' => URL::to('/'),
        'pat_profiles_supported' => [
            'bearer'
        ],
        'aat_profiles_supported' => [
            'bearer'
        ],
        'rpt_profiles_supported' => [
            'bearer'
        ],
        'pat_grant_types_supported' => [
            'authorization_code',
            'client_credentials',
            'user_credentials',
            'implicit',
            'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'urn:ietf:params:oauth:grant_type:redelegate'
        ],
        'aat_grant_types_supported' => [
            'authorization_code',
            'client_credentials',
            'user_credentials',
            'implicit',
            'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'urn:ietf:params:oauth:grant_type:redelegate'
        ],
        'grant_types_supported' => [
            'urn:ietf:params:oauth:grant-type:saml2-bearer',
            'urn:ietf:params:oauth:grant-type:uma-ticket',
            'client_credentials',
            'password',
            'authorization_code',
            'urn:ietf:params:oauth:grant-type:device_code',
            'http://oauth.net/grant_type/device/1.0'
        ],
        // 'dynamic_client_endpoint' => URL::to('register'),
        'registration_endpoint' => URL::to('register'),
        'token_endpoint' => URL::to('token'),
        'authorization_endpoint' => URL::to('authorize'),
        'requesting_party_claims_endpoint' => URL::to('rqp_claims'),
        'resource_registration_endpoint' => URL::to('resource_set'),
        'introspection_endpoint' => URL::to('introspect'),
        'permission_endpoint' => URL::to('permission'),
        'rpt_endpoint' => URL::to('authz_request'),
        'userinfo_endpoint' => URL::to('userinfo'),
        'policy_endpoint' => URL::to('policy'),
        'jwks_uri' => URL::to('jwks_uri')
    ];
    return $config;
});

// Webfinger
Route::get('.well-known/webfinger', ['as' => 'webfinger', 'uses' => 'OauthController@webfinger']);

// Update system call
Route::get('update_system/{type?}/{local?}', ['as' => 'update_system', 'uses' => 'OauthController@update_system']);

// test
Route::any('test1', ['as' => 'test1', 'uses' => 'OauthController@test1']);
