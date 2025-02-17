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

use App\Events\UserWasLogged;
use App\Http\Requests\Front\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\Permission;
use App\Models\Scopes\VerifiedScope;
use App\Models\User;
use App\Services\Auth\Helpers\AuthenticatesUsers;
use App\Services\Auth\Traits\Verification\CheckIfAuthFieldIsVerified;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class LoginService extends BaseService
{
	use AuthenticatesUsers, CheckIfAuthFieldIsVerified;
	
	// The maximum number of attempts to allow
	protected int $maxAttempts = 5;
	
	// The number of minutes to throttle for
	protected int $decayMinutes = 15;
	
	public function __construct()
	{
		parent::__construct();
		
		// Get values from Config
		$this->maxAttempts = (int)config('settings.security.login_max_attempts', $this->maxAttempts);
		$this->decayMinutes = (int)config('settings.security.login_decay_minutes', $this->decayMinutes);
	}
	
	/**
	 * Log in
	 *
	 * @param \App\Http\Requests\Front\LoginRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function login(LoginRequest $request): JsonResponse
	{
		$errorMessage = trans('auth.failed');
		
		try {
			// If the class is using the ThrottlesLogins trait, we can automatically throttle
			// the login attempts for this application. We'll key this by the username and
			// the IP address of the client making these requests into this application.
			// IMPORTANT: The RateLimiter class in Laravel relies on the cache system.
			// Ensure that caching is enabled and properly configured to utilize this feature.
			if (method_exists($this, 'hasTooManyLoginAttempts') && $this->hasTooManyLoginAttempts($request)) {
				$this->fireLockoutEvent($request);
				$this->sendLockoutResponse($request); // EXIT!
			}
			
			// Get the right auth field (email or phone)
			$authField = getAuthField();
			$fieldValue = $request->input($authField);
			
			// Check username is provided instead of email (in email field)
			if ($authField == 'email') {
				$dbField = getAuthFieldFromItsValue($fieldValue);
			}
			
			// Get credentials values
			$dbField = $dbField ?? $authField;
			$credentials = [
				$dbField   => $fieldValue,
				'password' => $request->input('password'),
				'blocked'  => 0,
			];
			
			// Auth the User
			if (auth()->attempt($credentials, $request->has('remember_me'))) {
				$authUser = auth()->user();
				
				// Get the user as model object
				$user = User::find($authUser->getAuthIdentifier());
				
				// Is user has verified login?
				$tmpData = $this->userHasVerifiedLogin($authUser, $user, $authField);
				$isSuccess = array_key_exists('success', $tmpData) && $tmpData['success'];
				
				// Send the right error message (with possibility to re-send verification code)
				if (!$isSuccess) {
					if (
						array_key_exists('success', $tmpData)
						&& array_key_exists('message', $tmpData)
						&& array_key_exists('extra', $tmpData)
					) {
						return apiResponse()->json($tmpData, Response::HTTP_FORBIDDEN);
					}
					
					return apiResponse()->error($errorMessage);
				}
				
				$extra = [];
				
				// Redirect admin users to the Admin panel
				$isAdmin = $user->hasAllPermissions(Permission::getStaffPermissions());
				$extra['isAdmin'] = $isAdmin;
				
				if (isFromApi()) {
					// Revoke previous tokens
					$user->tokens()->delete();
					
					// Create the API access token
					$defaultDeviceName = doesRequestIsFromWebClient() ? 'Website' : 'Other Client';
					$deviceName = $request->input('device_name', $defaultDeviceName);
					$token = $user->createToken($deviceName);
					
					// Save extra data
					$extra['authToken'] = $token->plainTextToken;
					$extra['tokenType'] = 'Bearer';
				}
				
				$data = [
					'success' => true,
					'result'  => new UserResource($user),
					'extra'   => $extra,
				];
				
				return apiResponse()->json($data);
			}
		} catch (Throwable $e) {
			$errorMessage = $e->getMessage();
		}
		
		// If the login attempt was unsuccessful we will increment the number of attempts
		// to log in and redirect the user back to the login form. Of course, when this
		// user surpasses their maximum number of attempts they will get locked out.
		// IMPORTANT: The RateLimiter class in Laravel relies on the cache system.
		// Ensure that caching is enabled and properly configured to utilize this feature.
		$this->incrementLoginAttempts($request);
		
		return apiResponse()->error($errorMessage);
	}
	
	/**
	 * Log out
	 *
	 * @param $userId
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function logout($userId): JsonResponse
	{
		$authUser = request()->user() ?? auth(getAuthGuard())->user();
		
		if (empty($authUser)) {
			return apiResponse()->error(t('logout_failed'));
		}
		
		if ($authUser->getAuthIdentifier() != $userId) {
			return apiResponse()->unauthorized();
		}
		
		if (isFromApi()) {
			// Get the User Personal Access Token Object
			$personalAccess = $authUser->tokens()->where('id', getApiAuthToken())->first();
			if (!empty($personalAccess)) {
				if ($personalAccess->tokenable_id == $userId) {
					// Revoke the specific token
					$personalAccess->delete();
				}
			}
		}
		
		// Update last user logged date
		$user = User::query()
			->withoutGlobalScopes([VerifiedScope::class])
			->where('id', $userId)
			->first();
		if (!empty($user)) {
			UserWasLogged::dispatch($user);
		}
		
		return apiResponse()->success(t('logout_successful'));
	}
}
