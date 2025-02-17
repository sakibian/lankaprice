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

namespace App\Http\Controllers\Web\Front\Auth;

use App\Helpers\Common\Cookie;
use App\Http\Controllers\Web\Front\FrontController;
use App\Http\Requests\Front\LoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\Middleware;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class LoginController extends FrontController
{
	protected LoginService $loginService;
	
	// If not logged-in redirect to
	protected string $loginPath = 'login';
	
	// After you've logged-in redirect to
	protected string $redirectTo = 'account';
	
	// After you've logged-out redirect to
	protected string $redirectAfterLogout = '/';
	
	/**
	 * @param \App\Services\Auth\LoginService $loginService
	 */
	public function __construct(LoginService $loginService)
	{
		parent::__construct();
		
		$this->loginService = $loginService;
		
		// Check if the previous URL is from the admin panel area
		$isUrlFromAdminArea = str_contains(url()->previous(), '/' . admin_uri());
		
		// Update the Laravel login redirections URLs
		if ($isUrlFromAdminArea) {
			$this->loginPath = admin_uri('login');
			$this->redirectTo = admin_uri();
			$this->redirectAfterLogout = admin_uri('login');
		} else {
			$this->loginPath = urlGen()->loginPath();
		}
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
	// Laravel overwrites for loading LaraClassifier views
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
		
		// Meta Tags
		[$title, $description, $keywords] = getMetaTag('login');
		MetaTag::set('title', $title);
		MetaTag::set('description', strip_tags($description));
		MetaTag::set('keywords', $keywords);
		
		return view('front.auth.login.index');
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
					
					// Get the intended URL
					$intendedUrl = getAsStringOrNull(session('url.intended'));
					
					// Check if the user is an admin user
					$isAdminUser = data_get($data, 'extra.isAdmin');
					$isUrlFromAdminArea = (!empty($intendedUrl) && str_contains($intendedUrl, '/' . admin_uri()));
					
					// Since non-admin users are automatically log-in from the admin panel URLs,
					// redirect the non-admin users to their account URL at: /account
					if ($isUrlFromAdminArea && !$isAdminUser) {
						return redirect()->to('account');
					}
					
					$redirectTo = $isAdminUser ? admin_uri() : $this->redirectTo;
					
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
			flash($message)->error();
		}
		
		$uriPath = property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/';
		
		return redirect()->to($uriPath);
	}
}
