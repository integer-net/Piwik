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
 * Class IntegerNet_Piwik_Helper_Data
 */
class IntegerNet_Piwik_Helper_Data extends Mage_Core_Helper_Abstract
{


    /**
     *
     */
    const SESSION_KEY_MULTISHIPPING_ORDER_IDS = '_integernet_piwik_multishipping_order_ids';


    /**
     * @param $orderIds
     *
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
     * @return $this
     */
    public function setHasQuoteUpdate()
    {
        Mage::getSingleton('core/session')->setIntegernetPiwikQuote(true);

        return $this;
    }


    /**
     * @param $unset
     *
     * @return null|bool
     */
    public function getHasQuoteUpdate($unset = false)
    {
        if ($unset === true) {
            return Mage::getSingleton('core/session')->getIntegernetPiwikQuote(true);
        }

        return Mage::getSingleton('core/session')->getIntegernetPiwikQuote();
    }


    /**
     * @param $productId
     *
     * @return string
     */
    public function getProductCategoryList($productId)
    {
        $categoryList = array();

        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')->load($productId);
        if ($product->getId()) {
            $categoryIds = $product->getCategoryIds();
            array_splice($categoryIds, 5);

            $categories = Mage::getResourceModel('catalog/category_collection');
            $categories->addAttributeToFilter('entity_id', array('in' => $categoryIds));
            $categories->addAttributeToSelect('name');
            $categories->load();

            foreach ($categories as $category) {
                $categoryList[] = sprintf('"%s"', addslashes($category->getName()));
            }
        }

        return sprintf('[%s]', implode(', ', $categoryList));
    }
}
