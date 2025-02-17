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
use App\Http\Requests\Front\ForgotPasswordRequest;
use App\Services\Auth\ForgotPasswordService;
use Illuminate\Http\RedirectResponse;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class ForgotPasswordController extends FrontController
{
	use ShowReSendVerificationCodeButton;
	
	protected ForgotPasswordService $forgotPasswordService;
	
	/**
	 * @param \App\Services\Auth\ForgotPasswordService $forgotPasswordService
	 */
	public function __construct(ForgotPasswordService $forgotPasswordService)
	{
		parent::__construct();
		
		$this->forgotPasswordService = $forgotPasswordService;
	}
	
	/**
	 * Get the middleware that should be assigned to the controller.
	 */
	public static function middleware(): array
	{
		$array = ['guest'];
		
		return array_merge(parent::middleware(), $array);
	}
	
	// -------------------------------------------------------
	// Laravel overwrites for loading LaraClassifier views
	// -------------------------------------------------------
	
	/**
	 * Display the form to request a password reset link.
	 *
	 * @return \Illuminate\Contracts\View\View
	 */
	public function showLinkRequestForm()
	{
		// Meta Tags
		[$title, $description, $keywords] = getMetaTag('password');
		MetaTag::set('title', $title);
		MetaTag::set('description', strip_tags($description));
		MetaTag::set('keywords', $keywords);
		
		return view('front.auth.passwords.email');
	}
	
	/**
	 * Send a reset link to the given user.
	 *
	 * @param ForgotPasswordRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
	{
		// Send Reset Password Link
		$data = getServiceData($this->forgotPasswordService->sendResetLink($request));
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		// Error Found
		if (!data_get($data, 'success')) {
			$message = $message ?? t('unknown_error');
			
			return redirect()->back()
				->withInput($request->only('email'))
				->withErrors(['email' => $message]);
		}
		
		// phone
		if (data_get($data, 'extra.codeSentTo') == 'phone') {
			// Save the password reset link (in session)
			$resetPwdUrl = url('password/reset/' . data_get($data, 'extra.code'));
			session()->put('passwordNextUrl', $resetPwdUrl);
			
			// Phone Number verification
			// Get the token|code verification form page URL
			// The user is supposed to have received this token|code by SMS
			$nextUrl = url('verify/password/phone/');
			
			// Go to the verification page
			return redirect()->to($nextUrl);
		}
		
		// email
		return redirect()->back()->with(['status' => $message]);
	}
}
