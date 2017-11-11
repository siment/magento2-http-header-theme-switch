<?php
/**
 * Licensed under Academic Free License ("AFL") v. 3.0
 * See LICENSE.txt or https://opensource.org/licenses/afl-3.0
 *
 * @category    Siment_HttpHeaderThemeSwitch
 * @copyright   (c) 2017 Simen Thorsrud
 * @author      Simen Thorsrud <simen.thorsrud@gmail.com>
 */

/**
 * I have added this backwards compatibility because Magento 2.2 has bumped version
 * requirement for PHPUnit - introducing new test classes.
 * @see https://stackoverflow.com/a/42828632/187780
 */
if (!class_exists('\PHPUnit\Framework\TestCase') && class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}
