<?php
/**
 * integer_net Magento Module
 *
 * @category IntegerNet
 * @package IntegerNet_Piwik
 * @copyright  Copyright (c) 2012-2013 integer_net GmbH (http://www.integer-net.de/)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author Viktor Franz <vf@integer-net.de>
 */

/**
 * Enter description here ...
 */
class IntegerNet_Piwik_Block_Track extends Mage_Core_Block_Template
{

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        if (!$this->_template) {
            $this->_template = 'integernet_piwik/track.phtml';
        }

        return $this->_template;
    }

    /**
     * @return string
     */
    public function getPiwikJsSrc()
    {
        return Mage::helper('integernet_piwik')->getHost() . 'piwik.js';
    }

    /**
     * @return string
     */
    public function getPiwikPhpSrc()
    {
        return Mage::helper('integernet_piwik')->getHost() . 'piwik.php';
    }

    /**
     * @return int
     */
    public function getSideId()
    {
        return Mage::helper('integernet_piwik')->getSideId();
    }

    /**
     * @return null|string
     */
    public function getCatalogViewArguments()
    {
        $handles = $this->getLayout()->getUpdate()->getHandles();

        if (in_array('catalog_category_view', $handles) && $category = Mage::helper('catalog')->getCategory()) {
            $arguments = array();
            $arguments[] = 'false';
            $arguments[] = 'false';
            $arguments[] = sprintf('"%s"', addslashes(trim($category->getName())));
            $arguments[] = 'false';

            return implode(', ', $arguments);
        }

        return null;
    }

    /**
     * @return null|string
     */
    public function getProductViewArguments()
    {
        $handles = $this->getLayout()->getUpdate()->getHandles();

        if (in_array('catalog_product_view', $handles) && $product = Mage::helper('catalog')->getProduct()) {

            $category = Mage::helper('catalog')->getCategory();

            $arguments = array();
            $arguments[] = sprintf('"%s"', addslashes(trim($product->getSku())));
            $arguments[] = sprintf('"%s"', addslashes(trim($product->getName())));
            $arguments[] = $category ? sprintf('"%s"', addslashes(trim($category->getName()))) : 'false';
            $arguments[] = $product->getFinalPrice();

            return implode(', ', $arguments);
        }

        return null;
    }

    /**
     * @return array|null
     */
    public function getCartItemArguments()
    {
        if ($items = Mage::helper('integernet_piwik')->getCartItems()) {
            $argumentsList = array();

            foreach ($items as $item) {

                $arguments = array();
                $arguments[] = sprintf('"%s"', addslashes($item->getSku()));
                $arguments[] = sprintf('"%s"', addslashes($item->getName()));
                $arguments[] = $item->getCategoryList();
                $arguments[] = $item->getBasePriceInclTax();
                $arguments[] = $item->getQty();

                $argumentsList[] = implode(', ', $arguments);

            }

            return $argumentsList;
        }

        return null;
    }

    /**
     * @return float|null
     */
    public function getCartArguments()
    {
        $cartAmount = Mage::helper('integernet_piwik')->getCartAmount();
        if ($cartAmount !== null) {
            return $cartAmount;
        }

        return null;
    }

    /**
     * @return null|Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _getOrders()
    {
        if (!$this->getData(__METHOD__)) {

            $handles = $this->getLayout()->getUpdate()->getHandles();

            if (in_array('checkout_onepage_success', $handles) || in_array('checkout_multishipping_success', $handles)) {

                $allOrderIds = array();

                if ($orderIds = Mage::helper('integernet_piwik')->getMultishippingOrderIds()) {
                    $allOrderIds = is_array($orderIds) ? $orderIds : array($orderIds);
                } elseif ($orderIds = Mage::getSingleton('checkout/session')->getData('last_order_id')) {
                    $allOrderIds = array($orderIds);
                }

                $orders = Mage::getResourceModel('sales/order_collection');
                $orders->addAttributeToFilter('entity_id', array('in' => $allOrderIds));
                $orders->load();

                if ($orders->count()) {
                    $this->setData(__METHOD__, $orders);
                }
            }
        }

        return $this->getData(__METHOD__);
    }

    /**
     * @return string
     */
    public function getOrderArguments()
    {
        if ($orders = $this->_getOrders()) {

            $incrementId = $orders->getColumnValues('increment_id');
            $baseGrandTotal = $orders->getColumnValues('base_grand_total');
            $baseSubtotalInclTax = $orders->getColumnValues('base_subtotal_incl_tax');
            $baseTaxAmount = $orders->getColumnValues('base_tax_amount');
            $baseShippingInclTax = $orders->getColumnValues('base_shipping_incl_tax');
            $baseDiscountAmount = $orders->getColumnValues('base_discount_amount');

            $arguments = array();
            $arguments[] = sprintf('"%s"', implode(' / ', $incrementId));
            $arguments[] = array_sum($baseGrandTotal);
            $arguments[] = array_sum($baseSubtotalInclTax);
            $arguments[] = array_sum($baseTaxAmount);
            $arguments[] = array_sum($baseShippingInclTax);
            $arguments[] = array_sum($baseDiscountAmount);

            return implode(', ', $arguments);
        }

        return null;
    }

    /**
     * @return array
     */
    public function getOrderItemArguments()
    {
        if ($orders = $this->_getOrders()) {

            $argumentsList = array();

            $single = $orders->count() == 1 ? true : false;;

            foreach ($orders as $order) {

                foreach ($order->getAllVisibleItems() as $item) {

                    $arguments = array();

                    if($single) {
                        $arguments[] = sprintf('"%s"', addslashes($item->getSku()));
                    } else {
                        $arguments[] = sprintf('"%s [%s]"', addslashes($item->getSku()), $order->getIncrementId());
                    }

                    $arguments[] = sprintf('"%s"', addslashes($item->getName()));
                    $arguments[] = Mage::helper('integernet_piwik')->getProductCategoryList($item->getProductId());
                    $arguments[] = $item->getBasePriceInclTax();
                    $arguments[] = $item->getQtyOrdered();

                    $argumentsList[] = implode(', ', $arguments);
                }
            }

            return $argumentsList;
        }

        return null;
    }

    /**
     * @return null|string
     */
    public function _toHtml()
    {
        $helper = Mage::helper('integernet_piwik');

        if ($helper->isActive() && $helper->getSideId() && $helper->getHost()) {
            return trim(parent::_toHtml());
        }

        return null;
    }
}