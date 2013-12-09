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
    const XML_PATH_INTEGERNET_PIWIK_SETTIGS_TRACK_ONEPAGE_ACTION = 'integernet_piwik/settigs/track_onepage_action';

    /**
     *
     */
    const SESSION_KEY_MULTISHIPPING_ORDER_IDS = '_integernet_piwik_multishipping_order_ids';

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
     * @return bool
     */
    public function getTrackOnepageSteps()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_INTEGERNET_PIWIK_SETTIGS_TRACK_ONEPAGE_ACTION);
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
     * @return $this
     */
    public function setHasQuoteUpdate()
    {
        Mage::getSingleton('core/session')->setIntegernetPiwikQuote(true);
        return $this;
    }

    /**
     * @param $unset
     * @return null|bool
     */
    public function getHasQuoteUpdate($unset = false)
    {
        if($unset === true) {
            return Mage::getSingleton('core/session')->getIntegernetPiwikQuote(true);
        }

        return Mage::getSingleton('core/session')->getIntegernetPiwikQuote();
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