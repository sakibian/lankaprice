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

namespace App\Http\Controllers\Web\Front\Auth;

use App\Http\Controllers\Web\Front\Auth\Traits\ReSendVerificationCode;
use App\Http\Controllers\Web\Front\Auth\Traits\ShowReSendVerificationCodeButton;
use App\Http\Controllers\Web\Front\Auth\Traits\VerifyCode;
use App\Http\Controllers\Web\Front\FrontController;

class VerificationController extends FrontController
{
	use ShowReSendVerificationCodeButton, ReSendVerificationCode, VerifyCode;
}
