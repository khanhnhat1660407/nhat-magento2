<?php
namespace SM\Promotion\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Block\Product\Context as ProductContext;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Render;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Zend_Db_Expr;

class PromotionProduct extends Template implements BlockInterface
{
    protected $_template = "widget/promotionproduct.phtml";
    protected $_productCollectionFactory;
    protected $_storeManager;
    protected $_productLoader;
    protected $_imageBuilder;
    protected $_cartHelper;

    public function __construct(
        Context $context,
        ProductCollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        ProductFactory $productLoader,
        ProductContext $productContext,
        array $data = []
    )
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_productLoader = $productLoader;
        $this->_imageBuilder = $productContext->getImageBuilder();
        $this->_cartHelper = $productContext->getCartHelper();
        parent::__construct($context, $data);
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @param $limitProduct
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPromotionProduct($limitProduct)
    {
        $storeId = $this->getStoreId();
        $collection = $this->_productCollectionFactory->create();
        //todo improve code for perfromance
        $collection->addAttributeToSelect([
            'entity_id',
            'required_options',
            'special_price',
            'price',
            'is_salable',
            'name',
            'small_image',
            'msrp_display_actual_price_type',
            'url_key',
            'store_id'
        ]);
        $collection->addAttributeToFilter('special_price', [
            ['neq' => 'NULL'],
            ['eq' => 0]
        ]);
        $collection->addAttributeToFilter('price', ['gt' => new Zend_Db_Expr('at_special_price.value')]);
        $collection->addStoreFilter($storeId);
        $collection->setPageSize($limitProduct);
        return $collection;
    }

    /**
     * @param $id
     * @return Product
     */
    public function loadProduct($id)
    {
        return $this->_productLoader->create()->load($id);
    }

    /**
     * @param Product $product
     * @param null $priceType
     * @param $renderZone
     * @param array $arguments
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductPriceHtml(
        Product $product,
        $priceType = null,
        $renderZone = Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        $price = $priceRender->render(
            FinalPrice::PRICE_CODE,
            $product,
            $arguments
        );

        return $price;
    }

    /**
     * @param $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        if (!$product->getTypeInstance()->isPossibleBuyFromList($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    /**
     * @param $product
     * @param array $additional
     * @return string
     */
    public function getProductUrl($product, $additional = [])
    {
        return $product->getUrlModel()->getUrl($product, $additional);
    }

    /**
     * @param $product
     * @param $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->_imageBuilder->create($product, $imageId, $attributes);
    }


}