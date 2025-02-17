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

use App\Http\Requests\Front\ResetPasswordRequest;
use App\Models\PasswordReset;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

trait ResetsPasswordsForPhone
{
	/**
	 * Reset password token
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function sendResetToken(Request $request): JsonResponse
	{
		// Form validation
		$request->validate(['code' => 'required']);
		
		// Check if the token exists
		$passwordReset = PasswordReset::where('token', $request->input('code'))->first();
		if (empty($passwordReset)) {
			$msg = t('The entered code is invalid');
			
			return apiResponse()->error($msg);
		}
		
		return apiResponse()->success();
	}
	
	/**
	 * Reset the given user's password
	 *
	 * @param \App\Http\Requests\Front\ResetPasswordRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function resetForPhone(ResetPasswordRequest $request): JsonResponse
	{
		// Check if Password request exists
		$passwordReset = PasswordReset::query()
			->where('token', $request->input('token'))
			->where('phone', $request->input('phone'))
			->first();
		if (empty($passwordReset)) {
			$msg = t('The code does not match your email or phone number');
			
			return apiResponse()->error($msg);
		}
		
		// Get User
		$user = User::where('phone', $passwordReset->phone)->first();
		if (empty($user)) {
			$msg = t('The entered value is not registered with us');
			
			return apiResponse()->error($msg);
		}
		
		// Update the User
		$user->password = Hash::make($request->input('password'));
		
		$user->phone_verified_at = now();
		if ($user->can(Permission::getStaffPermissions())) {
			// Email address auto-verified (for Admin Users)
			$user->email_verified_at = now();
		}
		
		$user->save();
		
		// Remove password reset data
		$passwordReset->delete();
		
		// Auto-Auth the User (API)
		// By creating an API token for the User
		return $this->createUserApiToken($user, $request->input('device_name'));
	}
}
