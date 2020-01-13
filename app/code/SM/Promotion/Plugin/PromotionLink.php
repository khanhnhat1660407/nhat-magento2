<?php
namespace SM\Promotion\Plugin;

use Magento\Customer\Model\Session;
use Magento\Theme\Block\Html\Topmenu;

class PromotionLink
{
    private  $_session;
    /**
     * PromotionLink constructor.
     * @param \Magento\Customer\Model\Session $session
     */
    public function __construct(
        Session $session
    ) {
        $this->_session = $session;
    }


    public function afterGetHtml(Topmenu $topMenu, $html)
    {
        $swapPartyUrl = $topMenu->getUrl('promotion');
        $html .= "<li class=\"level0 nav-7 category-item last level-top ui-menu-item\">";
        $html .= "<a href=\"" . $swapPartyUrl . "\" class=\"level-top ui-corner-all\"><span class=\"ui-menu-icon ui-icon ui-icon-carat-1-e\"></span><span>" . __("Promotions") . "</span></a>";
        $html .= "</li>";
        return $html;
    }
}