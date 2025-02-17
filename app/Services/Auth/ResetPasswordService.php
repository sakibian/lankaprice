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

namespace App\Services\Auth;

use App\Http\Requests\Front\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\Helpers\ResetsPasswordsForEmail;
use App\Services\Auth\Helpers\ResetsPasswordsForPhone;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Throwable;

class ResetPasswordService extends BaseService
{
	use ResetsPasswordsForEmail, ResetsPasswordsForPhone;
	
	/**
	 * Reset password
	 *
	 * @param \App\Http\Requests\Front\ResetPasswordRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function reset(ResetPasswordRequest $request): JsonResponse
	{
		// Get the right auth field
		$authField = getAuthField();
		
		// Go to the custom process (Phone)
		if ($authField == 'phone') {
			return $this->resetForPhone($request);
		}
		
		// Go to the core process (Email)
		try {
			$jsonResponse = $this->resetForEmail($request);
		} catch (Throwable $e) {
			return apiResponse()->error($e->getMessage());
		}
		
		return $jsonResponse;
	}
	
	/**
	 * Create an API token for the User
	 *
	 * @param $user
	 * @param null $deviceName
	 * @param null $message
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function createUserApiToken($user, $deviceName = null, $message = null): JsonResponse
	{
		$extra = [];
		
		if (isFromApi()) {
			// Revoke previous tokens
			$user->tokens()->delete();
			
			// Create the API access token
			$defaultDeviceName = doesRequestIsFromWebClient() ? 'Website' : 'Other Client';
			$deviceName = $deviceName ?? $defaultDeviceName;
			$token = $user->createToken($deviceName);
			
			// Save extra data
			$extra['authToken'] = $token->plainTextToken;
			$extra['tokenType'] = 'Bearer';
		}
		
		$data = [
			'success' => true,
			'message' => $message,
			'result'  => new UserResource($user),
			'extra'   => $extra,
		];
		
		return apiResponse()->json($data);
	}
}
