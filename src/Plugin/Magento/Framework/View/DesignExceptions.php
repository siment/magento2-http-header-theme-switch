<?php
/**
 * MIT License. See LICENSE for details.
 * @author Simen Thorsrud <simen.thorsrud@gmail.com>
 */

namespace Siment\HttpHeaderThemeSwitch\Plugin\Magento\Framework\View;

use \Magento\Framework\App\Config;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\App\Request\Http as Request;
use \Magento\Framework\Unserialize\Unserialize;

/**
 * Class DesignExceptions
 * @package Siment\HttpHeaderThemeSwitch\Plugin\Magento\Framework\View
 */
class DesignExceptions
{

    /**
     * @var ScopeConfigInterface|Config
     */
    private $scopeConfig;

    /**
     * @var RequestInterface|Request
     */
    private $request;

    /**
     * @var Unserialize
     */
    private $unserialize;

    /**
     * @var string
     */
    private $exceptionConfigPath;

    /**
     * @var string
     */
    private $scopeType;

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
     * After plugin for \Magento\Framework\View\DesignExceptions::getThemeByRequest
     *
     * @param \Magento\Framework\View\DesignExceptions  $subject
     * @param string|bool                               $result
     * @see \Magento\Framework\View\DesignExceptions::getThemeByRequest
     * @SuppressWarnings("unused")
     * @return string|bool
     */
    // @codingStandardsIgnoreLine because of unused $subject
    public function afterGetThemeByRequest(
        \Magento\Framework\View\DesignExceptions $subject,
        $result
    ) {
        if ($result === false) {
            $xUaDevice = $this->request->getServer('HTTP_X_UA_DEVICE');
            if (empty($xUaDevice)) {
                return false;
            }
            $expressions = $this->scopeConfig->getValue(
                $this->exceptionConfigPath,
                $this->scopeType
            );
            if (!$expressions) {
                return false;
            }
            $expressions = $this->unserialize->unserialize($expressions);
            foreach ($expressions as $rule) {
                if (preg_match($rule['regexp'], $xUaDevice)) {
                    return $rule['value'];
                }
            }
        }
        return $result;
    }
}
