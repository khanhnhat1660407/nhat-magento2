<?php

namespace SM\Promotion\Setup;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * UpgradeData constructor.
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        BlockFactory $blockFactory
    ) {
        $this->blockFactory = $blockFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.3.8', '<')) {

            $cmsBlock = $this->blockFactory->create()->setStoreId(0)->load('promotion_cms_block', 'identifier');

            $cmsBlockData = [
                'title' => 'Promotions',
                'identifier' => 'promotion_cms_block',
                'content' => '<a class="logo-swe-link" href="/" title="">
                            <img class="logo-swe-img" src="https://file.hstatic.net/1000344185/file/uoemj1xih2cluupbe1vw-kvjkaw1umbarw_b4167849b7b34a4687658886e20ddd0e.gif">
                          </a>',
                'is_active' => 1,
                'stores' => [0],
                'sort_order' => 0
            ];

            if (!$cmsBlock->getId()) {
                $this->_blockFactory->create()->setData($cmsBlockData)->save();
            } else {
                $cmsBlock->setContent($cmsBlockData['content'])->save();
            }
        }
        $setup->endSetup();
    }
}

//widget:
// -title:
//number
//slick enable
