<?php
/**
 * Licensed under Academic Free License ("AFL") v. 3.0
 * See LICENSE.txt or https://opensource.org/licenses/afl-3.0
 *
 * @category    Siment_HttpHeaderThemeSwitch
 * @copyright   (c) 2017 Simen Thorsrud
 * @author      Simen Thorsrud <simen.thorsrud@gmail.com>
 */

namespace Siment\HttpHeaderThemeSwitch\Plugin\Magento\Framework\View;

use \Magento\Framework\App\Config;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\App\Request\Http as Request;
use \Magento\Framework\Unserialize\Unserialize;
use \Magento\Framework\View\DesignExceptions as ViewDesignExceptions;

/**
 * Class DesignExceptions
 * @package Siment\HttpHeaderThemeSwitch\Plugin\Magento\Framework\View
 */
class DesignExceptions
{
    const XPATH_CONFIG_HTTP_HEADER = 'siment_http_header_theme_switch/general/http_header';

    /**
     * @var ScopeConfigInterface|Config Allows fetching of Magento configuration
     */
    private $scopeConfig;

    /**
     * @var RequestInterface|Request Request object
     */
    private $request;

    /**
     * @var Unserialize Lets you safely unserialize strings
     */
    private $unserialize;

    /**
     * @var string Path to Magento configuration for design exceptions
     */
    private $exceptionConfigPath;

    /**
     * @var string Scope type. Defaults to "store"
     */
    private $scopeType;

    /**
     * DesignExceptions constructor.
     *
     * @param ScopeConfigInterface  $scopeConfig
     * @param RequestInterface      $request
     * @param Unserialize           $unserialize
     * @param                       $exceptionConfigPath
     * @param                       $scopeType
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request,
        Unserialize $unserialize,
        $exceptionConfigPath,
        $scopeType
    ) {
        $this->scopeConfig          = $scopeConfig;
        $this->request              = $request;
        $this->unserialize          = $unserialize;
        $this->exceptionConfigPath  = $exceptionConfigPath;
        $this->scopeType            = $scopeType;
    }

    /**
     * After plugin for ViewDesignExceptions::getThemeByRequest
     *
     * @param ViewDesignExceptions  $subject
     * @param string|bool                  $result
     *
     * @see ViewDesignExceptions::getThemeByRequest
     * @SuppressWarnings("unused")
     *
     * @return string|bool
     */
    public function afterGetThemeByRequest( // @codingStandardsIgnoreLine because of unused $subject
        \Magento\Framework\View\DesignExceptions $subject,
        $result
    ) {
        if ($result === false) {
            /** @var string $httpHeader */
            $httpHeader = $this->getHttpHeader();

            /** @var string $xUaDevice */
            $xUaDevice = $this->request->getServer($httpHeader);
            if (empty($xUaDevice)) {
                return false;
            }

            /** @var null|string $expressions Serialized string */
            $expressions = $this->scopeConfig->getValue(
                $this->exceptionConfigPath,
                $this->scopeType
            );
            if (!$expressions) {
                return false;
            }

            $expressions = $this->unserialize->unserialize($expressions);
            /**
             * @var array   $expressions
             * @var string  $rule
             */
            foreach ($expressions as $rule) {
                if (preg_match($rule['regexp'], $xUaDevice)) {
                    return $rule['value'];
                }
            }
        }
        return $result;
    }

    /**
     * Returns identifier of HTTP header that we want to evaluate
     *
     * @return null|string
     */
    private function getHttpHeader()
    {
        return $this->scopeConfig->getValue(self::XPATH_CONFIG_HTTP_HEADER);
    }
}
