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
 * Class IntegerNet_Piwik_Helper_Config
 */
class IntegerNet_Piwik_Helper_Config extends Mage_Core_Helper_Abstract
{


    /**
     *
     */
    const SESSION_KEY_MULTISHIPPING_ORDER_IDS = '_integernet_piwik_multishipping_order_ids';


    /**
     * @return bool
     */
    public function isActive()
    {
        return Mage::getStoreConfigFlag('integernet_piwik/settings/is_active');
    }


    /**
     * @return null|int
     */
    public function getSideId()
    {
        $sideId = Mage::getStoreConfig('integernet_piwik/settings/side_id');
        $sideId = trim($sideId);

        return preg_match('/^\d*$/', $sideId) ? $sideId : null;
    }


    /**
     * @return null|string
     */
    public function getHost()
    {
        if (Mage::app()->getRequest()->isSecure()) {
            $host = Mage::getStoreConfig('integernet_piwik/settings/host_secure');
        } else {
            $host = Mage::getStoreConfig('integernet_piwik/settings/host');
        }

        $host = trim($host);

        return $host ? $host : null;
    }


    /**
     * @return bool
     */
    public function isHeadJs()
    {
        return Mage::getStoreConfigFlag('integernet_piwik/settings/head_js');
    }


    /**
     * @return bool
     */
    public function getTrackOnepageSteps()
    {
        return Mage::getStoreConfigFlag('integernet_piwik/settings/track_onepage_action');
    }


    /**
     * @return string
     */
    public function getAdvancedSearchResultKey()
    {
        return trim(Mage::getStoreConfig('integernet_piwik/settings/advanced_search_result_key'));
    }


    /**
     * @return string
     */
    public function getAdvancedSearchNoResultKey()
    {
        return trim(Mage::getStoreConfig('integernet_piwik/settings/advanced_search_no_result_key'));
    }
}
