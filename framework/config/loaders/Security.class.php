<?php

namespace framework\config\loaders;

use framework\config\Loader;
use framework\config\Reader;
use framework\utility\Tools;
use framework\Security as SecurityManager;
use framework\utility\Validate;

class Security extends Loader {

    public function load(Reader $reader) {
        $security = $reader->read();
        foreach ($security as $type => $datas) {
            $securityData = array();
            if (isset($datas['autorun']) && is_string($datas['autorun']))
                $securityData['debug'] = Tools::castValue($datas['autorun']);
            elseif (!isset($datas['autorun']))
                $securityData['autorun'] = false;

            //default value
            foreach ($datas as $name => $value) {
                if ($name == 'autorun' || $name == 'comment' || $name == 'form')
                    continue;

                if (is_string($value))
                    $value = Tools::castValue($value);

                $securityData[$name] = $value;
            }

            //formulaires (for Form api)
            if (isset($datas['form'])) {
                $securityData = array();
                foreach ($datas['form'] as $formName => $formDatas) {
                    if (!Validate::isVariableName($formName))
                        throw new \Exception('Security form name must be a valid variable');

                    $form = new \stdClass();
                    $form->name = $formName;
                    if (isset($formDatas['protection'])) {
                        $protections = array();
                        foreach ($formDatas['protection'] as $protectionType => $protectionDatas) {
                            if (is_array($protectionDatas)) {
                                foreach ($protectionDatas as $optionName => $optionValue) {
                                    if ($optionName == 'comment')
                                        continue;
                                    if (is_string($optionValue))
                                        $protectionDatas[$optionName] = Tools::castValue($optionValue);
                                }
                            }
                            if (is_string($value))
                                $value = Tools::castValue($value);
                            $protections[$protectionType] = $protectionDatas;
                        }
                    }

                    $form->protections = $protections;
                    $securityData[] = $form;
                }
            }

            SecurityManager::addSecurity($type, array('autorun' => $datas['autorun'], 'datas' => $securityData), true);
        }
    }

}

?>
