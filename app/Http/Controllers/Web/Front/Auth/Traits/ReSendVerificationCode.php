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

trait ReSendVerificationCode
{
	/**
	 * URL: Re-Send the verification message
	 *
	 * @param string $entitySlug
	 * @param int|string $entityId
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function reSendEmailVerification(string $entitySlug, int|string $entityId): RedirectResponse
	{
		// Resend the email verification link
		$data = getServiceData((new VerificationService())->reSendEmailVerification($entitySlug, $entityId));
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		// Notification Message
		if (data_get($data, 'success')) {
			notification($message, 'success');
		} else {
			$message = $message ?? t('unknown_error');
			notification($message, 'error');
		}
		
		if (!data_get($data, 'extra.emailVerificationSent')) {
			// Remove Notification Trigger
			if (session()->has('emailVerificationSent')) {
				session()->forget('emailVerificationSent');
			}
		}
		
		return redirect()->back();
	}
	
	/**
	 * URL: Re-Send the verification SMS
	 *
	 * @param string $entitySlug
	 * @param int|string $entityId
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function reSendPhoneVerification(string $entitySlug, int|string $entityId): RedirectResponse
	{
		// Resend the verification code
		$data = getServiceData((new VerificationService())->reSendPhoneVerification($entitySlug, $entityId));
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		// Notification Message
		if (data_get($data, 'success')) {
			notification($message, 'success');
		} else {
			$message = $message ?? t('unknown_error');
			notification($message, 'error');
		}
		
		if (!data_get($data, 'extra.phoneVerificationSent')) {
			// Remove Notification Trigger
			if (session()->has('phoneVerificationSent')) {
				session()->forget('phoneVerificationSent');
			}
		}
		
		// Go to user's account after the phone number verification
		if ($entitySlug == 'users') {
			session()->put('userNextUrl', url('account'));
		}
		
		// Go to the code (received by SMS) verification page
		if (!isFromAdminPanel()) {
			return redirect()->to('verify/' . $entitySlug . '/phone/');
		}
		
		return redirect()->back();
	}
}
