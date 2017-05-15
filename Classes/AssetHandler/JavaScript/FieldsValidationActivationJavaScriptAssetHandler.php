<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 FormZ project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\AssetHandler\JavaScript;

use Romm\Formz\AssetHandler\AbstractAssetHandler;
use Romm\Formz\Condition\Parser\Tree\ConditionTree;
use Romm\Formz\Form\Definition\Field\Validation\Validator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This asset handler generates the JavaScript code used the activate specific
 * validation rules for a given field. For example, you may want to enable the
 * rule "required" of a given field only under certain circumstances.
 */
class FieldsValidationActivationJavaScriptAssetHandler extends AbstractAssetHandler
{

    /**
     * Main function of this asset handler.
     *
     * @return string
     */
    public function getFieldsValidationActivationJavaScriptCode()
    {
        $javaScriptBlocks = [];
        $formConfiguration = $this->getFormObject()->getDefinition();

        foreach ($formConfiguration->getFields() as $field) {
            foreach ($field->getValidators() as $validator) {
                $fieldConditionExpression = [];
                $javaScriptTree = $this->getConditionTreeForValidator($validator)->getJavaScriptConditions();

                if (false === empty($javaScriptTree)) {
                    foreach ($javaScriptTree as $node) {
                        $fieldConditionExpression[] = 'flag = flag || (' . $node . ');';
                    }

                    $javaScriptBlocks[] = $this->getSingleFieldActivationConditionFunction($validator, $fieldConditionExpression);
                }
            }
        }

        $javaScriptBlocks = implode(CRLF, $javaScriptBlocks);
        $formName = GeneralUtility::quoteJSvalue($this->getFormObject()->getName());

        return <<<JS
(function() {
    Fz.Form.get(
        $formName,
        function(form) {
            var field = null;

$javaScriptBlocks

            form.refreshAllFields();
        }
    );
})();
JS;
    }

    /**
     * This function is just here to make the class more readable.
     *
     * @param Validator $validation
     * @param array     $fieldConditionExpression Array containing the JavaScript condition expression for the field.
     * @return string
     */
    protected function getSingleFieldActivationConditionFunction(Validator $validation, $fieldConditionExpression)
    {
        $fieldName = GeneralUtility::quoteJSvalue($validation->getParentField()->getName());
        $validationName = GeneralUtility::quoteJSvalue($validation->getName());
        $fieldConditionExpression = implode(CRLF . str_repeat(' ', 20), $fieldConditionExpression);

        return <<<JS
            field = form.getFieldByName($fieldName);

            if (null !== field) {
                field.addActivationConditionForValidator(
                    '__auto',
                    $validationName,
                    function (field, continueValidation) {
                        var flag = false;
                        $fieldConditionExpression
                        continueValidation(flag);
                    }
                );
            }
JS;
    }

    /**
     * @param Validator $validator
     * @return ConditionTree
     */
    protected function getConditionTreeForValidator(Validator $validator)
    {
        return $this->conditionProcessor->getActivationConditionTreeForValidator($validator);
    }
}
