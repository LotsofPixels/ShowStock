<?php

declare(strict_types=1);

namespace LotsofPixels\Showstock\ViewModel;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 *
 */
class Showstock  implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var GetProductSalableQtyInterface
     */
    protected $salebleqty;

    /**
     * @var StockResolverInterface
     */
    protected $stockresolver;

    /**
     * @var StoreManagerInterface
     */
    protected $storemanager;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var ProductFactory
     */
    protected $product;

    /**
     * @var
     */
    protected $registry;



    /**
     * @param ProductFactory $product
     * @param StoreManagerInterface $storemanager
     * @param GetProductSalableQtyInterface $salebleqty
     * @param Http $request
     * @param StockResolverInterface $stockresolver
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ProductFactory                        $product,
        StoreManagerInterface                 $storemanager,
        GetProductSalableQtyInterface         $salebleqty,
        Http                                  $request,
        StockResolverInterface                $stockresolver,
        Context                               $context,
        array                                 $data = []
    )
    {
        $this->product = $product;
        $this->request = $request;
        $this->storemanager = $storemanager;
        $this->salebleqty = $salebleqty;
        $this->stockresolver = $stockresolver;

    }
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentProduct()
    {
        $websiteCode = $this->storemanager->getWebsite()->getCode();
        $stock = $this->stockresolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $stockId = $stock->getStockId();
        $productId = $this->request->getParam('id');
        $loadProduct = $this->product->create()->load($productId);
        $sku = $loadProduct->getSku();
        $type = $loadProduct->getTypeId();
        //$_children = $loadProduct->getTypeInstance()->getUsedProducts($loadProduct);
        //foreach ($_children as $child){
        //    $childs[] = $child->getID();
//}
        $stockQty = $this->salebleqty->execute($sku, $stockId);
        return $stockQty;
    }

    /**
     * @return string
     */
    public function getPdpstock()
    {
        $websiteCode = $this->storemanager->getWebsite()->getCode();
        $stock = $this->stockresolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $stockId = $stock->getStockId();
        $productId = $this->request->getParam('id');
        $loadProduct = $this->product->create()->load($productId);
        $sku = $loadProduct->getSku();
        $type = $loadProduct->getTypeId();
        $stockQty = $this->salebleqty->execute($sku, $stockId);
        return $stockQty;
    }

    public function getProducttype()
    {
        $productId = $this->request->getParam('id');
        $loadProduct = $this->product->create()->load($productId);
        $type = $loadProduct->getTypeId();
        return $type;
    }

    public function getChildstock()
    {

        $websiteCode = $this->storemanager->getWebsite()->getCode();
        $stock = $this->stockresolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $productId = $this->request->getParam('id');
        $loadProduct = $this->product->create()->load($productId);
        $_children = $loadProduct->getTypeInstance()->getUsedProducts($loadProduct);
        foreach ($_children as $child){
            $childProduct = $this->product->create()->load($child->getId());
            $childsku = $childProduct->getSku();
            $childstockId = $stock->getStockId();
            $childstockQty = $this->salebleqty->execute($childsku, $childstockId);
        $childstock []= $childsku . ' Stock: ' . $childstockQty . '<br> ';
        }

        return $childstock;
    }


}
