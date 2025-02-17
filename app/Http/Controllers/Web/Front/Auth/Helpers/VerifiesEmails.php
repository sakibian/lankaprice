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

namespace App\Http\Controllers\Web\Front\Auth\Helpers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

trait VerifiesEmails
{
	use RedirectsUsers;
	
	/**
	 * Show the email verification notice.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function show(Request $request)
	{
		return $request->user()->hasVerifiedEmail()
			? redirect()->to($this->redirectPath())
			: view('front.auth.verify');
	}
	
	/**
	 * Mark the authenticated user's email address as verified.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 * @throws \Illuminate\Auth\Access\AuthorizationException
	 */
	public function verify(Request $request)
	{
		if ($request->route('id') != $request->user()->getKey()) {
			throw new AuthorizationException;
		}
		
		if ($request->user()->hasVerifiedEmail()) {
			return redirect()->to($this->redirectPath());
		}
		
		if ($request->user()->markEmailAsVerified()) {
			event(new Verified($request->user()));
		}
		
		return redirect()->to($this->redirectPath())->with('verified', true);
	}
	
	/**
	 * Resend the email verification notification.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function resend(Request $request)
	{
		if ($request->user()->hasVerifiedEmail()) {
			return redirect()->to($this->redirectPath());
		}
		
		$request->user()->sendEmailVerificationNotification();
		
		return redirect()->back()->with('resent', true);
	}
}
