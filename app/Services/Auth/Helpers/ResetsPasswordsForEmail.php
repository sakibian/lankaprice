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
use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

trait ResetsPasswordsForEmail
{
	/**
	 * Reset the given user's password
	 *
	 * @param \App\Http\Requests\Front\ResetPasswordRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function resetForEmail(ResetPasswordRequest $request): JsonResponse
	{
		$credentials = $request->only('email', 'password', 'password_confirmation', 'token');
		
		// Here we will attempt to reset the user's password. If it is successful we
		// will update the password on an actual user model and persist it to the
		// database. Otherwise, we will parse the error and return the response.
		$status = Password::reset(
			$credentials,
			function ($user, $password) use ($request) {
				$user->password = Hash::make($password);
				
				$user->setRememberToken(Str::random(60));
				
				$user->email_verified_at = now();
				if ($user->can(Permission::getStaffPermissions())) {
					// Phone auto-verified (for Admin Users)
					$user->phone_verified_at = now();
				}
				
				$user->save();
				
				event(new PasswordReset($user));
			}
		);
		
		if ($status == Password::PASSWORD_RESET) {
			$user = User::where('email', $request->input('email'))->first();
			
			if (!empty($user)) {
				if (Hash::check($request->input('password'), $user->password)) {
					// Auto-Auth the User (API)
					// By creating an API token for the User
					return $this->createUserApiToken($user, $request->input('device_name'), trans($status));
				}
			}
			
			return apiResponse()->success(trans($status));
		} else {
			return apiResponse()->error(trans($status));
		}
	}
}
