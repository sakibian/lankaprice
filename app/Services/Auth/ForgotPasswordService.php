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

use App\Http\Requests\Front\ForgotPasswordRequest;
use App\Services\Auth\Helpers\SendsPasswordResetEmails;
use App\Services\Auth\Helpers\SendsPasswordResetSms;
use App\Services\Auth\Traits\VerificationTrait;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Throwable;

class ForgotPasswordService extends BaseService
{
	use VerificationTrait;
	use SendsPasswordResetEmails, SendsPasswordResetSms;
	
	/**
	 * Forgot password
	 *
	 * @param \App\Http\Requests\Front\ForgotPasswordRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function sendResetLink(ForgotPasswordRequest $request): JsonResponse
	{
		// Get the right auth field
		$authField = getAuthField();
		
		// Send the Token by SMS
		if ($authField == 'phone') {
			return $this->sendResetTokenSms($request);
		}
		
		// Go to the core process
		try {
			$jsonResponse = $this->sendResetLinkEmail($request);
		} catch (Throwable $e) {
			return apiResponse()->error($e->getMessage());
		}
		
		return $jsonResponse;
	}
}
