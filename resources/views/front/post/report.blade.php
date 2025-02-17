{{--
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
--}}
@extends('front.layouts.master')

@section('content')
	@include('front.common.spacer')
	<div class="main-container">
		<div class="container">
			<div class="row clearfix">
				
				@include('front.post.inc.notification')
				
				<div class="col-md-12">
					<div class="contact-form">
						
						<h3 class="gray mt-0">
							<strong><a href="{{ urlGen()->post($post) }}">{{ $title }}</a></strong>
						</h3>
						
						<hr class="border-0 bg-secondary mt-1">
						
						<h4>{{ t('There is something wrong with this listing') }}</h4>
		
						<form role="form" method="POST" action="{{ urlGen()->reportPost($post) }}">
							{!! csrf_field() !!}
							@honeypot
							<fieldset>
								<div class="row">
									{{-- report_type_id --}}
									@php
										$reportTypeIdError = (isset($errors) && $errors->has('report_type_id')) ? ' is-invalid' : '';
									@endphp
									<div class="col-md-6 col-12 mb-3 required">
										<label for="report_type_id" class="control-label{{ $reportTypeIdError }}">
											{{ t('Reason') }} <sup>*</sup>
										</label>
										<select id="reportTypeId" name="report_type_id" class="form-control selecter{{ $reportTypeIdError }}">
											<option value="">{{ t('Select a reason') }}</option>
											@foreach($reportTypes as $reportType)
												<option value="{{ data_get($reportType, 'id') }}"
														{{ (old('report_type_id', 0) == data_get($reportType, 'id')) ? ' selected="selected"' : '' }}
												>
													{{ data_get($reportType, 'name') }}
												</option>
											@endforeach
										</select>
									</div>
									
									{{-- email --}}
									@if (auth()->check() && isset(auth()->user()->email))
										<input type="hidden" name="email" value="{{ auth()->user()->email }}">
									@else
										@php
											$emailError = (isset($errors) && $errors->has('email')) ? ' is-invalid' : '';
										@endphp
										<div class="col-md-6 col-12 mb-3 required">
											<label for="email" class="control-label">{{ t('Your Email') }} <sup>*</sup></label>
											<div class="input-group{{ $emailError }}">
												<span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
												<input id="email"
												       name="email"
												       data-valid-type="email"
												       type="text"
												       maxlength="60"
												       class="form-control{{ $emailError }}"
												       value="{{ old('email') }}"
												>
											</div>
										</div>
									@endif
								
									{{-- message --}}
									@php
										$messageError = (isset($errors) && $errors->has('message')) ? ' is-invalid' : '';
									@endphp
									<div class="col-md-12 col-12 mb-3 required">
										<label for="message" class="control-label">
											{{ t('Message') }} <sup>*</sup> <span class="text-count"></span>
										</label>
										<textarea id="message"
												  name="message"
												  class="form-control{{ $messageError }}"
												  rows="10"
												  style="height: 200px;"
										>{{ old('message') }}</textarea>
									</div>
									
									@include('front.layouts.inc.tools.captcha', ['label' => true])
									
									<input type="hidden" name="post_id" value="{{ data_get($post, 'id') }}">
									<input type="hidden" name="abuseForm" value="1">
									
									<div class="mb-3">
										<a href="{{ rawurldecode(url()->previous()) }}" class="btn btn-default btn-lg">{{ t('Back') }}</a>
										<button type="submit" class="btn btn-primary btn-lg">{{ t('Send Report') }}</button>
									</div>
								</div>
							</fieldset>
						</form>
					</div>
				</div>
				
			</div>
		</div>
	</div>
@endsection

@section('after_scripts')
@endsection
