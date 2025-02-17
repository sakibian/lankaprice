<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Helpers\Common\Cookie;
use App\Http\Controllers\Web\Admin\Controller;
use App\Http\Requests\Front\LoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\Middleware;

class LoginController extends Controller
{
	protected LoginService $loginService;
	
	// If not logged in redirect to
	protected string $loginPath;
	
	// After you've logged in redirect to
	protected string $redirectTo;
	
	// After you've logged out redirect to
	protected string $redirectAfterLogout;
	
	/**
	 * @param \App\Services\Auth\LoginService $loginService
	 */
	public function __construct(LoginService $loginService)
	{
		parent::__construct();
		
		$this->loginService = $loginService;
		
		// Set default URLs
		$this->loginPath = admin_uri('login');
		$this->redirectTo = admin_uri();
		$this->redirectAfterLogout = admin_uri('login');
	}
	
	/**
	 * Get the middleware that should be assigned to the controller.
	 */
	public static function middleware(): array
	{
		$array = [
			new Middleware('guest', except: ['logout']),
		];
		
		return array_merge(parent::middleware(), $array);
	}
	
	// -------------------------------------------------------
	// Laravel overwrites for loading admin views
	// -------------------------------------------------------
	
	/**
	 * Show the application login form.
	 *
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function showLoginForm()
	{
		// Remembering Login
		if (auth()->viaRemember()) {
			return redirect()->intended($this->redirectTo);
		}
		
		return view('admin.auth.login', ['title' => trans('admin.login')]);
	}
	
	/**
	 * @param \App\Http\Requests\Front\LoginRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function login(LoginRequest $request): RedirectResponse
	{
		// Log-in the user
		$data = getServiceData($this->loginService->login($request));
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		if (data_get($data, 'success')) {
			$userId = data_get($data, 'result.id');
			$authToken = data_get($data, 'extra.authToken');
			
			// Log-in the user
			if (!empty($userId)) {
				if (auth()->loginUsingId($userId)) {
					if (!empty($authToken)) {
						session()->put('authToken', $authToken);
					}
					
					// Check & Enable Dark Mode
					if (isDarkModeEnabledForCurrentUser()) {
						Cookie::set('darkTheme', 'dark');
					}
					
					$redirectTo = data_get($data, 'extra.isAdmin') ? admin_uri() : $this->redirectTo;
					
					// Retrieve the previously intended location/URL to redirect user on it after successful log-in
					// If no intended location found, the $redirectTo URL will be used to redirect the user
					return redirect()->intended($redirectTo);
				}
			}
		}
		
		$message = $message ?? trans('auth.failed');
		
		return redirect()->to($this->loginPath)->withErrors(['error' => $message])->withInput();
	}
	
	/**
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function logout(): RedirectResponse
	{
		$userId = auth()->user()?->getAuthIdentifier() ?? '-1';
		
		// Log-out the user
		$data = getServiceData($this->loginService->logout($userId));
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		if (data_get($data, 'success')) {
			// Log out the user on a web client (Browser)
			logoutSession($message);
			
			// Reset Dark Mode
			Cookie::forget('darkTheme');
		} else {
			$message = $message ?? t('unknown_error');
			notification($message, 'error');
		}
		
		$uriPath = property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/';
		
		return redirect()->to($uriPath);
	}
}
