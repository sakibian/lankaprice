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

namespace App\Http\Controllers\Web\Front\Auth\Traits;

use App\Services\VerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait VerifyCode
{
	/**
	 * URL: Verify user's Email Address or Phone Number
	 *
	 * Note: If the token argument is filled, the entity will automatically verify, if not, the token form will be shown
	 *
	 * @param string $entitySlug
	 * @param string $field
	 * @param string|null $token
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function showVerificationForm(string $entitySlug, string $field, ?string $token = null)
	{
		// Show the token/code verification form when the token hasn't filled
		if (empty($token)) {
			return view('front.token', compact('entitySlug', 'field'));
		}
		
		// Verify the entity
		$queryParams = [
			'deviceName' => 'Website',
		];
		$data = getServiceData((new VerificationService())->verifyCode($entitySlug, $field, $token, $queryParams));
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		// Get the Entity Object (User or Post model's entry)
		$entityObject = data_get($data, 'result');
		
		// Check the request status
		if (data_get($data, 'success')) {
			flash($message)->success();
		} else {
			$message = $message ?? t('unknown_error');
			flash($message)->error();
			
			if (empty($entityObject)) {
				return view('front.token', compact('entitySlug', 'field'));
			}
		}
		
		$nextUrl = url('/?from=verification');
		
		// Remove Notification Trigger
		if (session()->has('emailOrPhoneChanged')) {
			session()->forget('emailOrPhoneChanged');
		}
		if (session()->has('emailVerificationSent')) {
			session()->forget('emailVerificationSent');
		}
		if (session()->has('phoneVerificationSent')) {
			session()->forget('phoneVerificationSent');
		}
		
		// users
		if ($entitySlug == 'users') {
			$user = $entityObject;
			
			$userId = data_get($user, 'id');
			$authToken = data_get($data, 'extra.authToken');
			
			if (!empty($userId)) {
				// Auto log-in the user
				if (auth()->loginUsingId($userId)) {
					if (!empty($authToken)) {
						session()->put('authToken', $authToken);
					}
					$nextUrl = url('account');
				} else {
					if (session()->has('userNextUrl')) {
						$nextUrl = session('userNextUrl');
					} else {
						$nextUrl = urlGen()->login();
					}
				}
			}
			
			// Remove Next URL session
			if (session()->has('userNextUrl')) {
				session()->forget('userNextUrl');
			}
		}
		
		// posts
		if ($entitySlug == 'posts') {
			$post = $entityObject;
			
			// Get Listing creation next URL
			if (session()->has('itemNextUrl')) {
				$nextUrl = session('itemNextUrl');
				if (str_contains($nextUrl, 'create') && !session()->has('postId')) {
					$nextUrl = urlGen()->postUri($post);
				}
			} else {
				$nextUrl = urlGen()->postUri($post);
			}
			
			// Remove Next URL session
			if (session()->has('itemNextUrl')) {
				session()->forget('itemNextUrl');
			}
		}
		
		// password (Forgot Password)
		if ($entitySlug == 'password') {
			$nextUrl = url()->previous();
			if (session()->has('passwordNextUrl')) {
				$nextUrl = session('passwordNextUrl');
				
				// Remove Next URL session
				session()->forget('passwordNextUrl');
			}
		}
		
		return redirect()->to($nextUrl);
	}
	
	/**
	 * URL: Verify user's Email Address or Phone Number by submitting a token
	 *
	 * @param string $entitySlug
	 * @param string $field
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	private function postVerificationForm(string $entitySlug, string $field, Request $request): RedirectResponse
	{
		// If the token field is not filled, back to the token form
		$rules = [
			'code' => ['required', 'string'],
		];
		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->withInput();
		}
		
		$token = $request->input('code');
		
		// If the token is submitted,
		// then add it in the URL and redirect users to that URL
		$nextUrl = 'verify/' . $entitySlug . '/' . $field . '/' . $token;
		
		return redirect()->to($nextUrl);
	}
}
