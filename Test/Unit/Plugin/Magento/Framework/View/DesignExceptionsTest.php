<?php
/**
 * Licensed under Academic Free License ("AFL") v. 3.0
 * See LICENSE.txt or https://opensource.org/licenses/afl-3.0
 *
 * @category    Siment_HttpHeaderThemeSwitch
 * @copyright   (c) 2017 Simen Thorsrud
 * @author      Simen Thorsrud <simen.thorsrud@gmail.com>
 */

namespace Siment\HttpHeaderThemeSwitch\Test\Plugin\Magento\Framework\View;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\Config;
use \Magento\Framework\App\Request\Http as Request;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Framework\Unserialize\Unserialize;
use \Magento\Framework\View\DesignExceptions;
use \Siment\HttpHeaderThemeSwitch\Plugin\Magento\Framework\View\DesignExceptions as DesignExceptionsPlugin;

/**
 * Tests for Siment\HttpHeaderThemeSwitch\Plugin\Magento\Framework\View\DesignExceptions;
 *
 * @see \Siment\HttpHeaderThemeSwitch\Plugin\Magento\Framework\View\DesignExceptions
 * @package Siment\HttpHeaderThemeSwitch\Test\Plugin\Magento\Framework\View
 */
class DesignExceptionsTest extends \PHPUnit_Framework_TestCase
{
    const EXCEPTION_CONFIG_PATH     = 'design/theme/ua_regexp';
    const SCOPE_TYPE                = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    const XPATH_CONFIG_HTTP_HEADER  = 'siment_http_header_theme_switch/general/http_header';
    const DEVICE_HEADER             = 'HTTP_X_UA_DEVICE';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * Tests for \Siment\HttpHeaderThemeSwitch\Plugin\Magento\Framework\View\DesignExceptions::afterGetThemeByRequest
     *
     * @param string        $scenario
     * @param string|false  $result
     * @param array|false   $expressions
     * @param array|string  $deviceHeader
     * @see \Siment\HttpHeaderThemeSwitch\Plugin\Magento\Framework\View\DesignExceptions::afterGetThemeByRequest
     * @dataProvider testAfterGetThemeByRequestDataProvider
     */
    public function testAfterGetThemeByRequest(
        $scenario,
        $result,
        $expressions,
        $deviceHeader
    ) {
        /** @var array $expected */
        // @codingStandardsIgnoreLine Because I am uncluding file directly - like Magento does in unit tests
        $expected = include '_files/DesignExceptionsTest/testAfterGetThemeByRequest.php';

        /** @var \PHPUnit_Framework_MockObject_MockObject|Config $scopeConfigMock */
        $scopeConfigMock = $this
            ->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|Request $requestMock */
        $requestMock = $this
            ->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getServer'])
            ->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|Unserialize $unserializeMock */
        $unserializeMock = $this
            ->getMockBuilder(Unserialize::class)
            ->disableOriginalConstructor()
            ->getMock();

        if ($result === false) {
            $scopeConfigMock
                ->expects($this->at(0))
                ->method('getValue')
                ->with(self::XPATH_CONFIG_HTTP_HEADER)
                ->willReturn(self::DEVICE_HEADER);

            if (!empty($deviceHeader)) {
                $scopeConfigMock
                    ->expects($this->at(1))
                    ->method('getValue')
                    ->with(self::EXCEPTION_CONFIG_PATH, self::SCOPE_TYPE)
                    ->willReturn($expressions);
            }
            $requestMock
                ->expects($this->once())
                ->method('getServer')
                ->with(self::DEVICE_HEADER)
                ->willReturn($deviceHeader);
        }

        /**
         * @var \Magento\Framework\Unserialize\Unserialize|DesignExceptions $designExceptions
         */
        $designExceptions = $this
            ->getMockBuilder(DesignExceptions::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var DesignExceptionsPlugin $designExPl */
        $designExPl = $this
            ->objectManager
            ->getObject(
                DesignExceptionsPlugin::class,
                [
                    'scopeConfig'           => $scopeConfigMock,
                    'request'               => $requestMock,
                    'unserialize'           => $unserializeMock,
                    'exceptionConfigPath'   => self::EXCEPTION_CONFIG_PATH,
                    'scopeType'             => self::SCOPE_TYPE
                ]
            );

        if (in_array($scenario, ['match', 'no_match'])) {
            $unserializeMock
                ->expects($this->once())
                ->method('unserialize')
                ->willReturn($expressions);
        }

        $actual = $designExPl->afterGetThemeByRequest(
            $designExceptions,
            $result
        );

        $this->assertSame(
            $expected[$scenario],
            $actual
        );
    }

    /**
     * DataProvider for testAfterGetThemeByRequest
     *
     * @see testAfterGetThemeByRequest()
     * @return array
     */
    public function testAfterGetThemeByRequestDataProvider()
    {
        $return = [
            [
                'result_not_false', // scenario
                'result',           // Value of $result
                false,              // Value of $expressions
                'mobile',           // Value of $deviceHeader
            ],
            [
                'device_header_empty',                      // scenario
                false,                                      // Value of $result
                [['regexp' => '/desktop/i', 'value' => 5]], // Value of $expressions
                [],                                         // Value of $deviceHeader
            ],
            [
                'expressions_false',    // scenario
                false,                  // Value of $result
                false,                  // Value of $expressions
                'mobile',               // Value of $deviceHeader
            ],
            [
                'match',                                    // scenario
                false,                                      // Value of $result
                [['regexp' => '/mobile/i', 'value' => 5]],  // Value of $expressions
                'mobile',                                   // Value of $deviceHeader
            ],
            [
                'no_match',                                 // scenario
                false,                                      // Value of $result
                [['regexp' => '/desktop/i', 'value' => 5]], // Value of $expressions
                'mobile',                                   // Value of $deviceHeader
            ]
        ];
        return $return;
    }
}
