<?php
namespace SM\Promotion\Block\Index;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
class Index extends Template
{
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function getTitle(){
        return "Promotions";
    }

}