<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\AssetHandler\JavaScript;

use Romm\Formz\Configuration\Form\Field\Field;
use Romm\Formz\Configuration\Form\Field\Validation\Validation;
use Romm\Formz\Core\Core;
use Romm\Formz\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This asset handler generates the JavaScript code which will initialize the
 * validation rules for every field of the form.
 *
 * First, call the function `process()`, then:
 *  - To get the generated JavaScript code: `getJavaScriptCode()`.
 *  - To get the list of files which must be included in order to make the form
 *    run correctly: `getJavaScriptValidationFiles()`
 */
class FieldsValidationJavaScriptAssetHandler extends AbstractJavaScriptAssetHandler
{

    /**
     * @var string
     */
    protected $javaScriptCode;

    /**
     * @var array
     */
    protected $javaScriptValidationFiles = [];

    /**
     * Main function of this asset handler. See class description.
     *
     * @return $this
     */
    public function process()
    {
        $this->javaScriptValidationFiles = [];
        $fieldsJavaScriptCode = [];
        $formConfiguration = $this->getFormObject()->getConfiguration();

        foreach ($formConfiguration->getFields() as $field) {
            $fieldsJavaScriptCode[] = $this->processField($field);
        }

        $formName = GeneralUtility::quoteJSvalue($this->getFormObject()->getName());
        $fieldsJavaScriptCode = implode(CRLF, $fieldsJavaScriptCode);

        $this->javaScriptCode = <<<JS
(function() {
    Formz.Form.get(
        $formName,
        function(form) {
            var field = null;

$fieldsJavaScriptCode
        }
    );
})();
JS;

        return $this;
    }

    /**
     * Will run the process for the given field. You can get back the result
     * with the functions `getJavaScriptValidationFiles()` and
     * `getJavaScriptCode()`.
     *
     * @param Field $field
     * @return string
     */
    protected function processField($field)
    {
        $javaScriptCode = [];
        $fieldName = $field->getFieldName();

        foreach ($field->getValidation() as $validationName => $validationConfiguration) {
            /** @var AbstractValidator $validatorClassName */
            $validatorClassName = $validationConfiguration->getClassName();

            if (in_array(AbstractValidator::class, class_parents($validatorClassName))) {
                // Adding the validator JavaScript validation files to the list.
                $this->javaScriptValidationFiles = array_merge($this->javaScriptValidationFiles, $validatorClassName::getJavaScriptValidationFiles());
                $javaScriptCode[] = (string)$this->getInlineJavaScriptValidationCode($field, $validationName, $validationConfiguration);
            }
        }

        $javaScriptCode = implode(CRLF, $javaScriptCode);
        $javaScriptFieldName = GeneralUtility::quoteJSvalue($fieldName);

        return <<<JS
            /***************************
            * Field: "$fieldName"
            ****************************/
            field = form.getFieldByName($javaScriptFieldName);

            if (null !== field) {
$javaScriptCode
            }

JS;
    }

    /**
     * Generates the JavaScript code to add a validation rule to a field.
     *
     * @param Field      $field
     * @param string     $validationName         The name of the validation rule.
     * @param Validation $validatorConfiguration Contains the current validator configuration.
     * @return string
     */
    protected function getInlineJavaScriptValidationCode(Field $field, $validationName, Validation $validatorConfiguration)
    {
        $javaScriptValidationName = GeneralUtility::quoteJSvalue($validationName);
        $validatorName = addslashes($validatorConfiguration->getClassName());
        $validatorConfigurationFinal = $this->getValidationConfiguration($field, $validationName, $validatorConfiguration);
        $validatorConfigurationFinal = $this->handleValidationConfiguration($validatorConfigurationFinal);

        return <<<JS
                /*
                 * Validation rule "$validationName"
                 */
                field.addValidation($javaScriptValidationName, '$validatorName', $validatorConfigurationFinal);

JS;
    }

    /**
     * This function is here to help unit tests mocking.
     *
     * @param string $jsonValidationConfiguration
     * @return string
     */
    protected function handleValidationConfiguration($jsonValidationConfiguration)
    {
        return $jsonValidationConfiguration;
    }

    /**
     * Returns a JSON array containing the validation configuration needed by
     * JavaScript.
     *
     * @param Field      $field
     * @param string     $validationName
     * @param Validation $validatorConfiguration
     * @return string
     */
    protected function getValidationConfiguration(Field $field, $validationName, Validation $validatorConfiguration)
    {
        $acceptsEmptyValues = $this
            ->getDummyValidator()
            ->cloneValidator($validatorConfiguration->getClassName())
            ->acceptsEmptyValues();

        /** @var FormzLocalizationJavaScriptAssetHandler $formzLocalizationJavaScriptAssetHandler */
        $formzLocalizationJavaScriptAssetHandler = $this->assetHandlerFactory->getAssetHandler(FormzLocalizationJavaScriptAssetHandler::class);

        $messages = $formzLocalizationJavaScriptAssetHandler->getTranslationKeysForFieldValidation($field, $validationName);

        return Core::get()->arrayToJavaScriptJson([
            'options'            => $validatorConfiguration->getOptions(),
            'messages'           => $messages,
            'settings'           => $validatorConfiguration->toArray(),
            'acceptsEmptyValues' => $acceptsEmptyValues
        ]);
    }

    /**
     * @return string
     */
    public function getJavaScriptCode()
    {
        return (string)$this->javaScriptCode;
    }

    /**
     * @return array
     */
    public function getJavaScriptValidationFiles()
    {
        return array_unique($this->javaScriptValidationFiles);
    }
}
