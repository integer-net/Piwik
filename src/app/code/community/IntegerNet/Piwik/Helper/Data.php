<?php
/**
 * integer_net Magento Module
 *
 * @category IntegerNet
 * @package IntegerNet_Piwik
 * @copyright  Copyright (c) 2012-2013 integer_net GmbH (http://www.integer-net.de/)
 * @author Viktor Franz <vf@integer-net.de>
 */

/**
 * Enter description here ...
 */
class IntegerNet_Piwik_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     *
     */
    const XML_PATH_INTEGERNET_PIWIK_SETTIGS_IS_ACTIVE = 'integernet_piwik/settigs/is_active';
    const XML_PATH_INTEGERNET_PIWIK_SETTIGS_SIDE_ID = 'integernet_piwik/settigs/side_id';
    const XML_PATH_INTEGERNET_PIWIK_SETTIGS_HOST = 'integernet_piwik/settigs/host';
    const XML_PATH_INTEGERNET_PIWIK_SETTIGS_HOST_SECURE = 'integernet_piwik/settigs/host_secure';
    const XML_PATH_INTEGERNET_PIWIK_SETTIGS_HEAD_JS = 'integernet_piwik/settigs/head_js';

    /**
     *
     */
    const SESSION_KEY_MULTISHIPPING_ORDER_IDS = '_integernet_piwik_multishipping_order_ids';
    const SESSION_KEY_QUOTE_ITEMS = '_integernet_piwik_cart_items';

    /**
     * @return bool
     */
    public function isActive()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_INTEGERNET_PIWIK_SETTIGS_IS_ACTIVE);
    }

    /**
     * @return null|int
     */
    public function getSideId()
    {
        $sideId = Mage::getStoreConfig(self::XML_PATH_INTEGERNET_PIWIK_SETTIGS_SIDE_ID);
        $sideId = trim($sideId);

        return preg_match('/^\d*$/', $sideId) ? $sideId : null;
    }

    /**
     * @return null|string
     */
    public function getHost()
    {
        if (Mage::app()->getRequest()->isSecure()) {
            $host = Mage::getStoreConfig(self::XML_PATH_INTEGERNET_PIWIK_SETTIGS_HOST_SECURE);
        } else {
            $host = Mage::getStoreConfig(self::XML_PATH_INTEGERNET_PIWIK_SETTIGS_HOST);
        }

        $host = trim($host);

        return $host ? $host : null;
    }

    /**
     * @return bool
     */
    public function isHeadJs()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_INTEGERNET_PIWIK_SETTIGS_HEAD_JS);
    }

    /**
     * @param $orderIds
     * @return $this
     */
    public function setMultishippingOrderIds($orderIds)
    {
        Mage::getSingleton('core/session')->setData(self::SESSION_KEY_MULTISHIPPING_ORDER_IDS, $orderIds);
        return $this;
    }

    /**
     * @return null|array
     */
    public function getMultishippingOrderIds()
    {
        $orderIds = Mage::getSingleton('core/session')->getData(self::SESSION_KEY_MULTISHIPPING_ORDER_IDS);

        Mage::getSingleton('core/session')->unsetData(self::SESSION_KEY_MULTISHIPPING_ORDER_IDS);

        return $orderIds;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return $this
     */
    public function setCartItems(Mage_Sales_Model_Quote $quote)
    {
        $itemsInfo = Mage::getSingleton('core/session')->getData(self::SESSION_KEY_QUOTE_ITEMS);
        $itemsInfo = ($itemsInfo instanceof Varien_Object) ? $itemsInfo : (new Varien_Object());

        $items = new Varien_Data_Collection();
        $cartAmount = 0;

        foreach ($quote->getAllVisibleItems() as $item) {

            $simpleItem = new Varien_Object(array(
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'category_list' => $this->getProductCategoryList($item->getProductId()),
                'base_price_incl_tax' => $item->getBasePriceInclTax(),
                'qty' => $item->getQty(),
            ));

            $items->addItem($simpleItem);
            $cartAmount += $item->getBaseRowTotalInclTax();
        }

        $hash = md5(serialize($items));

        if ($itemsInfo->getHash() != $hash) {
            $itemsInfo->setHash($hash);
            $itemsInfo->setItems($items);
            $itemsInfo->setCartAmount($cartAmount);

            Mage::getSingleton('core/session')->setData(self::SESSION_KEY_QUOTE_ITEMS, $itemsInfo);
        }

        return $this;
    }

    /**
     * @return null||Varien_Data_Collection
     */
    public function getCartItems()
    {
        $itemsInfo = Mage::getSingleton('core/session')->getData(self::SESSION_KEY_QUOTE_ITEMS);

        if ($itemsInfo instanceof Varien_Object) {

            $items = $itemsInfo->getItems();
            $itemsInfo->unsetData('items');

            Mage::getSingleton('core/session')->setData(self::SESSION_KEY_QUOTE_ITEMS, $itemsInfo);

            return $items;
        }

        return null;
    }

    /**
     * @return null||float
     */
    public function getCartAmount()
    {
        $itemsInfo = Mage::getSingleton('core/session')->getData(self::SESSION_KEY_QUOTE_ITEMS);

        if ($itemsInfo instanceof Varien_Object) {

            $itemsInfo = Mage::getSingleton('core/session')->getData(self::SESSION_KEY_QUOTE_ITEMS);

            $cartAmount = $itemsInfo->getCartAmount();
            $itemsInfo->unsetData('cart_amount');

            Mage::getSingleton('core/session')->setData(self::SESSION_KEY_QUOTE_ITEMS, $itemsInfo);

            return $cartAmount;
        }

        return null;
    }

    /**
     * @param $productId
     * @return string
     */
    public function getProductCategoryList($productId)
    {
        $categoryList = array();

        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')->load($productId);
        if($product->getId()) {
            $categoryIds = $product->getCategoryIds();
            array_splice($categoryIds, 5);

            $categories = Mage::getResourceModel('catalog/category_collection');
            $categories->addAttributeToFilter('entity_id', array('in' => $categoryIds));
            $categories->addAttributeToSelect('name');
            $categories->load();


            foreach($categories as $category) {
                $categoryList[] = sprintf('"%s"', addslashes($category->getName()));
            }
        }

        return sprintf('[%s]', implode(', ', $categoryList));
    }
}