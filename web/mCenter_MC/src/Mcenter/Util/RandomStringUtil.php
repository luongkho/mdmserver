<?php

namespace Mcenter\Util;

/**
 * Generate random string
 *
 * @author hoanvd
 */
class RandomStringUtil {

	/**
	 * <p>Creates a random string whose length is the number of characters
	 * specified.</p>
	 *
	 * <p>Characters will be chosen from the set of characters specified.</p>
	 *
	 * @param count  the length of random string to create
	 * @param chars  the character array containing the set of characters to use,
	 *  may be null
	 * @return the random string
	 * @throws IllegalArgumentException if <code>count</code> &lt; 0.
	 */
	public static function random($count, $chars = array()) {
		$randomString = '';
		for ($i = 0; $i < $count; $i++) {
			$randomString .= $chars[rand(0, count($chars) - 1)];
		}
		return $randomString;
	}
}
