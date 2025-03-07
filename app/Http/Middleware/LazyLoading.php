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

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class LazyLoading
{
	/**
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		// Exception for Install & Upgrade Routes
		if (isFromInstallOrUpgradeProcess()) {
			return $next($request);
		}
		
		$response = $next($request);
		
		// Exception for Bots and Other
		$crawler = new CrawlerDetect();
		if (
			$crawler->isCrawler()
			|| isAdminPanel()
			|| empty($response->getContent())
		) {
			return $response;
		}
		
		// Don't minify the HTML if the option is not activated
		if (config('settings.optimization.lazy_loading_activation') == 0) {
			return $response;
		}
		
		// Get HTML
		$buffer = $response->getContent();
		
		// Apply Lazy Loading HTML transformation
		$buffer = $this->applyLazyLoading($buffer);
		
		// Output the minified HTML
		return $response->setContent($buffer);
	}
	
	/**
	 * Apply the Lazy Loading HTML transformation
	 *
	 * @param $buffer
	 * @return mixed|string
	 */
	private function applyLazyLoading($buffer)
	{
		$lazyCssClassName = 'lazyload';
		$lazyDataSrcTagName = 'data-src';
		
		// HTML elements patterns
		$tags = [
			'img'    => [
				'all'      => '/(<img[^>]*>)/ui',
				'filtered' => '/(<img.*?class=".*?' . $lazyCssClassName . '[^"]*"[^>]*>)/ui',
			],
			'iframe' => [
				'all'      => '/(<iframe[^>]*>[^<]*<\/iframe>)/ui',
				'filtered' => '/(<iframe.*class=".*' . $lazyCssClassName . '[^"]*"[^>]*>[^<]*<\/iframe>)/ui',
			],
		];
		
		$lazyBuffer = '';
		$i = 0;
		foreach ($tags as $tag => $pattern) {
			if ($i > 0) {
				$buffer = $lazyBuffer;
			}
			// Get all tag element with the CSS class "lazyload"
			preg_match_all($pattern['all'], $buffer, $matches);
			
			$elements = [];
			if (isset($matches[1]) && !empty($matches[1])) {
				foreach ($matches[1] as $key => $value) {
					if (!preg_match($pattern['filtered'], $value)) {
						continue;
					}
					$elements[] = $value;
				}
				unset($matches);
			}
			// dd($elements); // debug!
			
			if (!empty($elements)) {
				$replace = [];
				$blankSrc = ($tag == 'img') ? url('images/blank.gif') : '';
				
				foreach ($elements as $key => $element) {
					$image = preg_replace('/src="([^"]*)"/ui', $lazyDataSrcTagName . '="\1" src="' . $blankSrc . '"', $element);
					$replace[$key] = $image;
				}
				
				$lazyBuffer = str_replace($elements, $replace, $buffer);
			}
			
			if (empty($lazyBuffer)) {
				$lazyBuffer = $buffer;
			}
			
			$i++;
		}
		
		return $lazyBuffer;
	}
}
