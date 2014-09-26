<?php

namespace framework\config\loaders;

use framework\config\Loader;
use framework\config\Reader;
use framework\utility\Tools;
use framework\mvc\Template as TemplateManager;
use framework\utility\Validate;

class Template extends Loader {

    public function load(Reader $reader) {
        $templates = $reader->read();
        foreach ($templates as $name => $datas) {
            if (!Validate::isVariableName($name))
                throw new \Exception('Name of template must be a valid variable name');

            //check required keys
            if (!isset($datas['path']))
                throw new \Exception('Miss path config param for template : "' . $name . '"');
            if (!isset($datas['adaptater']))
                throw new \Exception('Miss adaptater config param for template : "' . $name . '"');


            // Cast global setting
            $params = array();
            foreach ($datas as $key => $value) {
                if ($key == 'comment')
                    continue;

                // Casting
                if (is_string($value))
                    $value = Tools::castValue($value);
                $params[$key] = $value;
            }
            $params['name'] = $name;


            // foreach assets for checking parameters and casting
            if (isset($params['assets']) && is_array($params['assets'])) {
                foreach ($params['assets'] as $assetType => $assetDatas) {
                    //check type
                    if (!TemplateManager::isValidAssetType($assetType))
                        throw new \Exception('Invalid asset : "' . $assetType . '"');


                    if (is_array($assetDatas)) {
                        foreach ($assetDatas as $d => $v) {
                            // Casting
                            if (is_string($v))
                                $params['assets'][$assetType][$d] = Tools::castValue($v);

                            // cache parameters
                            if (isset($assetDatas['cache']) && is_array($assetDatas['cache'])) {
                                if (!isset($assetDatas['cache']['name']))
                                    throw new \Exception('Miss cache name');

                                foreach ($assetDatas['cache'] as $cacheOption => $optionsValue) {
                                    // Casting
                                    if (is_string($optionsValue))
                                        $params['assets'][$assetType]['cache'][$cacheOption] = Tools::castValue($optionsValue);
                                }
                            }
                        }
                    }
                }
            }
            // Add
            TemplateManager::addTemplate($name, TemplateManager::factory($datas['adaptater'], $params, 'framework\mvc\template\adaptaters', 'framework\mvc\template\IAdaptater'), true);
        }
    }

}

?>
