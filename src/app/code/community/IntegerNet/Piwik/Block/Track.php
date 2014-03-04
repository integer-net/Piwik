<?php
/**
 * integer_net Magento Module
 *
 * @category IntegerNet
 * @package IntegerNet_Piwik
 * @copyright  Copyright (c) 2013-2014 integer_net GmbH (http://www.integer-net.de/)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software Licence 3.0 (OSL-3.0)
 * @author integer_net GmbH <info@integer-net.de>
 * @author Viktor Franz <vf@integer-net.de>
 */


/**
 * Class IntegerNet_Piwik_Block_Track
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
        return Mage::helper('integernet_piwik/config')->getHost() . 'piwik.js';
    }


    /**
     * @return string
     */
    public function getPiwikPhpSrc()
    {
        return Mage::helper('integernet_piwik/config')->getHost() . 'piwik.php';
    }


    /**
     * @return int
     */
    public function getSideId()
    {
        return Mage::helper('integernet_piwik/config')->getSideId();
    }


    /**
     * @return null|string
     */
    public function getCatalogViewArguments()
    {
        $handles = $this->getLayout()->getUpdate()->getHandles();

        if (in_array('catalog_category_view', $handles) && $category = Mage::helper('catalog')->getCategory()) {
            $arguments = array();
            $arguments['product_sku'] = 'false';
            $arguments['product_name'] = 'false';
            $arguments['category_name'] = sprintf('"%s"', addslashes(trim($category->getName())));
            $arguments['product_price'] = 'false';

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
            $arguments['product_sku'] = sprintf('"%s"', addslashes(trim($product->getSku())));
            $arguments['product_name'] = sprintf('"%s"', addslashes(trim($product->getName())));
            $arguments['category_name'] = $category ? sprintf('"%s"', addslashes(trim($category->getName()))) : 'false';
            $arguments['product_price'] = $product->getFinalPrice();

            return implode(', ', $arguments);
        }

        return null;
    }


    /**
     * @return null|string
     */
    public function searchArguments()
    {
        $arguments = array();
        $handles = $this->getLayout()->getUpdate()->getHandles();

        if (in_array('catalogsearch_result_index', $handles)) {

            $arguments['keyword'] = sprintf('"%s"', addslashes(strtolower(trim(Mage::helper('catalogsearch')->getQuery()->getQueryText()))));
            $arguments['category'] = 'null';
            $arguments['search_count'] = Mage::helper('catalogsearch')->getQuery()->getNumResults();

        } elseif (in_array('catalogsearch_advanced_result', $handles)) {

            $resultKey = Mage::helper('integernet_piwik/config')->getAdvancedSearchResultKey();
            $noResultKey = Mage::helper('integernet_piwik/config')->getAdvancedSearchNoResultKey();

            $count = Mage::getSingleton('catalogsearch/advanced')->getProductCollection()->count();
            $arguments['keyword'] = $count ? sprintf('"%s"', $resultKey) : sprintf('"%s"', $noResultKey);
            $arguments['category'] = 'null';
            $arguments['search_count'] = $count;
        }

        return count($arguments) ? implode(', ', $arguments) : null;
    }


    /**
     * @return bool
     */
    public function getTrackOnepageSteps()
    {
        $handles = $this->getLayout()->getUpdate()->getHandles();

        if (in_array('checkout_onepage_index', $handles) && Mage::helper('integernet_piwik/config')->getTrackOnepageSteps()) {
            return true;
        }

        return false;
    }


    /**
     * @return null|array
     */
    public function getCartItemArguments()
    {
        if (Mage::helper('integernet_piwik')->getHasQuoteUpdate() && $quote = Mage::helper('checkout')->getQuote()) {

            $groupArguments = array();

            foreach ($quote->getAllVisibleItems() as $item) {

                if (array_key_exists($item->getSku(), $groupArguments)) {
                    $groupArguments[$item->getSku()]['price'] += $item->getBaseRowTotalInclTax();
                    $groupArguments[$item->getSku()]['quantity'] += $item->getQty();
                } else {
                    $groupArguments[$item->getSku()]['product_sku'] = sprintf('"%s"', addslashes($item->getSku()));
                    $groupArguments[$item->getSku()]['product_name'] = sprintf('"%s"', addslashes($item->getName()));
                    $groupArguments[$item->getSku()]['product_category'] = Mage::helper('integernet_piwik')->getProductCategoryList($item->getProductId());
                    $groupArguments[$item->getSku()]['price'] = $item->getBaseRowTotalInclTax();
                    $groupArguments[$item->getSku()]['quantity'] = $item->getQty();
                }
            }

            $argumentsList = array();

            foreach ($groupArguments as $arguments) {
                $arguments[3] = $arguments[3] / $arguments[4];
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
        if (Mage::helper('integernet_piwik')->getHasQuoteUpdate() && $quote = Mage::helper('checkout')->getQuote()) {
            return $quote->getBaseGrandTotal();
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
            $arguments['order_id'] = sprintf('"%s"', implode(';', $incrementId));
            $arguments['grand_total'] = array_sum($baseGrandTotal);
            $arguments['sub_total'] = array_sum($baseSubtotalInclTax);
            $arguments['tax'] = array_sum($baseTaxAmount);
            $arguments['shipping'] = array_sum($baseShippingInclTax);
            $arguments['discount'] = array_sum($baseDiscountAmount);

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

            $groupArguments = array();

            /** @var $order Mage_Sales_Model_Order */
            foreach ($orders as $order) {

                /** @var $item Mage_Sales_Model_Order_Item */
                foreach ($order->getAllVisibleItems() as $item) {

                    if (array_key_exists($item->getSku(), $groupArguments)) {
                        $groupArguments[$item->getSku()]['price'] += $item->getBaseRowTotalInclTax();
                        $groupArguments[$item->getSku()]['quantity'] += $item->getQtyOrdered();
                    } else {
                        $groupArguments[$item->getSku()]['product_sku'] = sprintf('"%s"', addslashes($item->getSku()));
                        $groupArguments[$item->getSku()]['product_name'] = sprintf('"%s"', addslashes($item->getName()));
                        $groupArguments[$item->getSku()]['product_category'] = Mage::helper('integernet_piwik')->getProductCategoryList($item->getProductId());
                        $groupArguments[$item->getSku()]['price'] = $item->getBaseRowTotalInclTax();
                        $groupArguments[$item->getSku()]['quantity'] = $item->getQtyOrdered();
                    }
                }
            }

            $argumentsList = array();

            foreach ($groupArguments as $arguments) {
                $arguments['price'] = $arguments['price'] / $arguments['quantity'];
                $argumentsList[] = implode(', ', $arguments);
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
        $configHelper = Mage::helper('integernet_piwik/config');

        if ($configHelper->isActive() && $configHelper->getSideId() && $configHelper->getHost()) {
            return trim(parent::_toHtml());
        }

        return null;
    }


    /**
     * Processing block html after rendering
     *
     * @param   string $html
     *
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        Mage::helper('integernet_piwik')->getHasQuoteUpdate(true);

        return parent::_afterToHtml($html);
    }
}
