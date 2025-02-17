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

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Front\ForgotPasswordRequest;
use App\Services\Auth\ForgotPasswordService;
use Illuminate\Http\JsonResponse;

/**
 * @group Authentication
 */
class ForgotPasswordController extends BaseController
{
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
	 * Forgot password
	 *
	 * @bodyParam auth_field string required The user's auth field ('email' or 'phone'). Example: email
	 * @bodyParam email string The user's email address or username (Required when 'auth_field' value is 'email'). Example: user@demosite.com
	 * @bodyParam phone string The user's mobile phone number (Required when 'auth_field' value is 'phone'). Example: null
	 * @bodyParam phone_country string required The user's phone number's country code (Required when the 'phone' field is filled). Example: null
	 * @bodyParam captcha_key string Key generated by the CAPTCHA endpoint calling (Required when the CAPTCHA verification is enabled from the Admin panel).
	 *
	 * @param \App\Http\Requests\Front\ForgotPasswordRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function sendResetLink(ForgotPasswordRequest $request): JsonResponse
	{
		return $this->forgotPasswordService->sendResetLink($request);
	}
}
