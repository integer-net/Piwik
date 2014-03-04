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
 * Class IntegerNet_Piwik_Model_Observer
 */
class IntegerNet_Piwik_Model_Observer
{


    /**
     * @param Varien_Event_Observer $observer
     */
    public function checkoutMultishippingControllerSuccessAction(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getOrderIds();

        Mage::helper('integernet_piwik')->setMultishippingOrderIds($orderIds);
    }


    /**
     * @param Varien_Event_Observer $observer
     */
    public function salesQuoteSaveAfter(Varien_Event_Observer $observer)
    {
        Mage::helper('integernet_piwik')->setHasQuoteUpdate();
    }
}
