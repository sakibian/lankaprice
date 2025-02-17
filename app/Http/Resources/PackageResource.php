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

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PackageResource extends BaseResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray(Request $request): array
	{
		if (!isset($this->id)) return [];
		
		$entity = [
			'id' => $this->id,
		];
		
		$columns = $this->getFillable();
		foreach ($columns as $column) {
			$entity[$column] = $this->{$column} ?? null;
		}
		
		$entity['period_start'] = $this->period_start ?? null;
		$entity['period_end'] = $this->period_end ?? null;
		$entity['description_array'] = $this->description_array ?? [];
		$entity['description_string'] = $this->description_string ?? null;
		$entity['price_formatted'] = $this->price_formatted ?? null;
		
		if (in_array('currency', $this->embed)) {
			$entity['currency'] = new CurrencyResource($this->whenLoaded('currency'), $this->params);
		}
		
		return $entity;
	}
}
