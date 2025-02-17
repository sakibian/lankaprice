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

use App\Http\Controllers\Web\Front\Auth\Traits\ShowReSendVerificationCodeButton;
use App\Http\Controllers\Web\Front\FrontController;
use App\Http\Requests\Front\UserRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class RegisterController extends FrontController
{
	use ShowReSendVerificationCodeButton;
	
	protected UserService $userService;
	
	// Where to redirect users after login / registration
	protected string $redirectTo = '/account';
	
	/**
	 * @param \App\Services\UserService $userService
	 */
	public function __construct(UserService $userService)
	{
		parent::__construct();
		
		$this->userService = $userService;
		
		$this->commonQueries();
	}
	
	/**
	 * Common Queries
	 */
	public function commonQueries()
	{
		$this->redirectTo = 'account';
	}
	
	/**
	 * Show the form the creation a new user account.
	 *
	 * @return \Illuminate\Contracts\View\View
	 */
	public function showRegistrationForm()
	{
		// Meta Tags
		[$title, $description, $keywords] = getMetaTag('register');
		MetaTag::set('title', $title);
		MetaTag::set('description', strip_tags($description));
		MetaTag::set('keywords', $keywords);
		
		return view('front.auth.register.index');
	}
	
	/**
	 * Register a new user account.
	 *
	 * @param \App\Http\Requests\Front\UserRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function register(UserRequest $request): RedirectResponse
	{
		// Create new user
		$data = getServiceData($this->userService->store($request));
		
		// Parsing the API response
		$message = data_get($data, 'message', t('unknown_error'));
		
		// Notification Message
		if (data_get($data, 'success')) {
			session()->put('message', $message);
		} else {
			flash($message)->error();
			
			return redirect()->back()->withErrors(['error' => $message])->withInput();
		}
		
		// Get User Resource
		$user = data_get($data, 'result');
		
		// Get the next URL
		$nextUrl = url('register/finish');
		
		if (
			data_get($data, 'extra.sendEmailVerification.emailVerificationSent')
			|| data_get($data, 'extra.sendPhoneVerification.phoneVerificationSent')
		) {
			session()->put('userNextUrl', $nextUrl);
			
			if (data_get($data, 'extra.sendEmailVerification.emailVerificationSent')) {
				session()->put('emailVerificationSent', true);
				
				// Show the Re-send link
				$this->showReSendVerificationEmailLink($user, 'users');
			}
			
			if (data_get($data, 'extra.sendPhoneVerification.phoneVerificationSent')) {
				session()->put('phoneVerificationSent', true);
				
				// Show the Re-send link
				$this->showReSendVerificationSmsLink($user, 'users');
				
				// Go to Phone Number verification
				$nextUrl = url('verify/users/phone/');
			}
		} else {
			$userId = data_get($data, 'result.id');
			$authToken = data_get($data, 'extra.authToken');
			
			// Auto log-in the user
			if (!empty($userId)) {
				if (auth()->loginUsingId($userId)) {
					if (!empty($authToken)) {
						session()->put('authToken', $authToken);
					}
					$nextUrl = url('account');
				}
			}
		}
		
		return redirect()->to($nextUrl);
	}
	
	/**
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function finish()
	{
		if (!session()->has('message')) {
			return redirect()->to('/');
		}
		
		// Meta Tags
		MetaTag::set('title', session('message'));
		MetaTag::set('description', session('message'));
		
		return view('front.auth.register.finish');
	}
}
