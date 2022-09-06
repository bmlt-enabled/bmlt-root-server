<?php

namespace App\Http\Controllers\Legacy;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class LegacyAuthController extends Controller
{
    private int $REQUEST_TYPE_ADMIN_XML = 1;
    private int $REQUEST_TYPE_ADMIN_JSON = 2;
    private int $REQUEST_TYPE_WEB = 3;

    public function handle(Request $request)
    {
        $adminAction = $request->input('admin_action');
        if ($adminAction == 'login') {
            Auth::logout();
            $request->session()->invalidate();
            $username = $request->input('c_comdef_admin_login');
            $password = $request->input('c_comdef_admin_password');
            $credentials = ['login_string' => $username, 'password' => $password];
            if (Auth::attempt($credentials)) {
                return $this->loggedInResponse($request);
            }

            return $this->badCredentialsResponse($request);
        } elseif ($adminAction == 'logout') {
            Auth::logout();
            $request->session()->invalidate();
            return $this->logoutResponse($request);
        }

        return LegacyController::handle($request);
    }

    private function loggedInResponse($request)
    {
        $apiType = $this->getApiType($request);
        if ($apiType == $this->REQUEST_TYPE_ADMIN_XML) {
            return response('OK');
        } elseif ($apiType == $this->REQUEST_TYPE_ADMIN_JSON) {
            return response();
        } else {
            $redirectUrl = $request->input('attemptedurl', '/');
            if (str_contains($redirectUrl, 'bad_login_form')) {
                $redirectUrl = '/';
            }
            $response = redirect($redirectUrl);
            $languagePref = $request->input('lang_enum');
            if ($languagePref) {
                $response = $response->withCookie(cookie('bmlt_admin_lang_pref', $languagePref, 60 * 24 * 365));
            }
            return $response;
        }
    }

    private function badCredentialsResponse($request)
    {
        $apiType = $this->getApiType($request);
        if ($apiType == $this->REQUEST_TYPE_ADMIN_XML) {
            return response('<h1>NOT AUTHORIZED</h1>');
        } elseif ($apiType == $this->REQUEST_TYPE_ADMIN_JSON) {
            return response('NOT AUTHORIZED');
        } else {
            $response = redirect('/?bad_login_form=1');
            $languagePref = $request->input('lang_enum');
            if ($languagePref) {
                $response = $response->withCookie(cookie('bmlt_admin_lang_pref', $languagePref, 60 * 24 * 365));
            }
            return $response;
        }
    }

    private function logoutResponse($request)
    {
        $apiType = $this->getApiType($request);
        if ($apiType == $this->REQUEST_TYPE_ADMIN_XML) {
            return response('BYE');
        } elseif ($apiType == $this->REQUEST_TYPE_ADMIN_JSON) {
            return response('BYE');
        } else {
            return redirect('/');
        }
    }

    private function getApiType(Request $request): int
    {
        $pathInfo = LegacyPathInfo::parse($request);
        if (str_ends_with($pathInfo->path, '/server_admin/xml.php')) {
            return $this->REQUEST_TYPE_ADMIN_XML;
        } elseif (str_ends_with($pathInfo->path, '/server_admin/json.php')) {
            return $this->REQUEST_TYPE_ADMIN_JSON;
        } else {
            return $this->REQUEST_TYPE_WEB;
        }
    }
}
