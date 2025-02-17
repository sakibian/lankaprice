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

trait ShowReSendVerificationCodeButton
{
	/**
	 * Show the ReSend Verification Message Link
	 *
	 * @param $entity
	 * @param string $entitySlug
	 * @return bool
	 */
	public function showReSendVerificationEmailLink($entity, string $entitySlug): bool
	{
		if (empty($entity) || empty(data_get($entity, 'id'))) {
			return false;
		}
		
		// Show ReSend Verification Email Link
		if (session()->has('emailVerificationSent')) {
			$url = url('verify/' . $entitySlug . '/' . $entity['id'] . '/resend/email');
			
			$message = t('Resend the verification message to verify your email address');
			$message .= ' <a href="' . $url . '" class="btn btn-sm btn-warning">' . t('Re-send') . '</a>';
			
			flash($message)->warning();
		}
		
		return true;
	}
	
	/**
	 * Show the ReSend Verification SMS Link
	 *
	 * @param $entity
	 * @param string $entitySlug
	 * @return bool
	 */
	public function showReSendVerificationSmsLink($entity, string $entitySlug): bool
	{
		if (empty($entity) || empty(data_get($entity, 'id'))) {
			return false;
		}
		
		// Show ReSend Verification SMS Link
		if (session()->has('phoneVerificationSent')) {
			$url = url('verify/' . $entitySlug . '/' . $entity['id'] . '/resend/sms');
			
			$message = t('Resend the verification message to verify your phone number');
			$message .= ' <a href="' . $url . '" class="btn btn-sm btn-warning">' . t('Re-send') . '</a>';
			
			flash($message)->warning();
		}
		
		return true;
	}
}
