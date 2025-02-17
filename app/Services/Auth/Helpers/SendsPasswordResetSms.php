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

namespace App\Services\Auth\Helpers;

use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

trait SendsPasswordResetSms
{
	/**
	 * Send a reset code to the given user
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function sendResetTokenSms(Request $request): JsonResponse
	{
		// Form validation
		$request->validate(['phone' => 'required']);
		
		// Check if the phone exists
		$user = User::where('phone', $request->input('phone'))->first();
		if (empty($user)) {
			$msg = t('The entered value is not registered with us');
			
			return apiResponse()->error($msg);
		}
		
		// Create the token in database
		$token = mt_rand(100000, 999999);
		$passwordReset = PasswordReset::where('phone', $request->input('phone'))->first();
		if (empty($passwordReset)) {
			$passwordResetInfo = [
				'email'         => null,
				'phone'         => $request->input('phone'),
				'phone_country' => $request->input('phone_country'),
				'token'         => $token,
				'created_at'    => date('Y-m-d H:i:s'),
			];
			$passwordReset = new PasswordReset($passwordResetInfo);
		} else {
			$passwordReset->token = $token;
			$passwordReset->created_at = date('Y-m-d H:i:s');
		}
		$passwordReset->save();
		
		try {
			// Send the token by SMS
			$passwordReset->notify(new ResetPasswordNotification($user, $token, 'phone'));
		} catch (Throwable $e) {
			return apiResponse()->error($e->getMessage());
		}
		
		$message = t('code_sent_by_sms');
		
		$data = [
			'success' => true,
			'message' => $message,
			'result'  => null,
			'extra'   => [
				'codeSentTo' => 'phone',
				'code'       => $token,
			],
		];
		
		return apiResponse()->json($data);
	}
}
