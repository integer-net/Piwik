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
 * Class IntegerNet_Piwik_Model_System_Config_Backend_HeadJs
 */
class IntegerNet_Piwik_Model_System_Config_Backend_HeadJs extends Mage_Core_Model_Config_Data
{


    /**
     *
     */
    const PIWIK_JS = 'piwik.js';


    /**
     */
    protected function _beforeSave()
    {
        if ($this->getValue()) {

            $host = $this->getFieldsetDataValue('host');

            $client = new Zend_Http_Client($host . self::PIWIK_JS);
            $reponde = $client->request();

            if ($reponde->getStatus() == 200) {

                try {
                    $dir = Mage::getBaseDir() . DS . 'js' . DS . 'integernet_piwik';

                    $file = new Varien_Io_File();
                    $file->setAllowCreateFolders(true);
                    $file->open(array('path' => $dir));
                    $file->write(self::PIWIK_JS, $reponde->getBody());

                } catch (Exception $e) {
                    $this->setValue(0);
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }

            } else {
                $this->setValue(0);
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('integernet_piwik')->__('Cannot load %s from host "%s"', self::PIWIK_JS, $host));
            }
        }

        return $this;
    }
}
