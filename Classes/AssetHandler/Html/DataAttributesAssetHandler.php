<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Formz project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\Formz\AssetHandler\Html;

use Romm\Formz\AssetHandler\AbstractAssetHandler;
use Romm\Formz\Error\FormResult;
use Romm\Formz\Error\FormzMessageInterface;
use Romm\Formz\Form\FormInterface;
use Romm\Formz\Service\StringService;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * This asset handler generates several data attributes which will be added to
 * the form element in the Fluid template. Most of these data attributes are
 * directly bound to fields and their properties.
 *
 * Example of data attributes:
 *  - Fields values: when a field changes, its new value will be indicated in
 *    the form with the attribute: `formz-value-{field name}="value"`.
 *  - Fields validation: when a field is considered as valid (it passed all its
 *    validation rules), the form gets the attribute: `formz-valid-{field name}`.
 *  - Fields errors: when a field validation fails with an error, the form gets
 *    the attribute: `formz-error-{field name}-{name of the error}`.
 *  - Fields warnings and notices: same as errors.
 */
class DataAttributesAssetHandler extends AbstractAssetHandler
{

    /**
     * Handles the data attributes containing the values of the form fields.
     *
     * Example: `formz-value-color="blue"`
     *
     * @param FormInterface|array $formInstance
     * @param FormResult          $requestResult
     * @return array
     */
    public function getFieldsValuesDataAttributes($formInstance, FormResult $requestResult)
    {
        $result = [];
        $formConfiguration = $this->getFormObject()->getConfiguration();

        foreach ($this->getFormObject()->getProperties() as $fieldName) {
            if (true === $formConfiguration->hasField($fieldName)
                && false === $requestResult->fieldIsDeactivated($formConfiguration->getField($fieldName))
                && $this->isPropertyGettable($formInstance, $fieldName)
            ) {
                $value = ObjectAccess::getProperty($formInstance, $fieldName);
                $value = (is_array($value))
                    ? implode(' ', $value)
                    : $value;

                $result[self::getFieldDataValueKey($fieldName)] = $value;
            }
        }

        return $result;
    }

    /**
     * Checks if the given field name can be accessed within the form instance,
     * whether it is an object or an array.
     *
     * @param FormInterface|array $formInstance
     * @param string              $fieldName
     * @return bool
     */
    protected function isPropertyGettable($formInstance, $fieldName)
    {
        $objectPropertyIsGettable = (
            is_object($formInstance)
            && (
                in_array($fieldName, get_object_vars($formInstance))
                || ObjectAccess::isPropertyGettable($formInstance, $fieldName)
            )
        );

        $arrayPropertyGettable = (
            is_array($formInstance)
            && true === isset($formInstance[$fieldName])
        );

        return $objectPropertyIsGettable || $arrayPropertyGettable;
    }

    /**
     * Handles the data attributes for the fields which got errors, warnings and
     * notices.
     *
     * Examples:
     * - `formz-error-email="1"`
     * - `formz-error-email-rule-default="1"`
     *
     * @param FormResult $requestResult
     * @return array
     */
    public function getFieldsMessagesDataAttributes(FormResult $requestResult)
    {
        $result = [];
        $formConfiguration = $this->getFormObject()->getConfiguration();

        foreach ($requestResult->getSubResults() as $fieldName => $fieldResult) {
            if (true === $formConfiguration->hasField($fieldName)
                && false === $requestResult->fieldIsDeactivated($formConfiguration->getField($fieldName))
            ) {
                if (true === $fieldResult->hasErrors()) {
                    $result += $this->addFieldMessageDataAttribute($fieldName, $fieldResult->getErrors(), 'error');
                }

                if (true === $fieldResult->hasWarnings()) {
                    $result += $this->addFieldMessageDataAttribute($fieldName, $fieldResult->getWarnings(), 'warning');
                }

                if (true === $fieldResult->hasNotices()) {
                    $result += $this->addFieldMessageDataAttribute($fieldName, $fieldResult->getNotices(), 'notice');
                }
            }
        }

        return $result;
    }

    /**
     * @param string                  $fieldName
     * @param FormzMessageInterface[] $messages
     * @param string                  $type
     * @return array
     */
    protected function addFieldMessageDataAttribute($fieldName, array $messages, $type)
    {
        $result = [self::getFieldDataMessageKey($fieldName, $type) => '1'];

        foreach ($messages as $message) {
            $validationName = $message->getValidationName();
            $messageKey = $message->getMessageKey();

            $result[self::getFieldDataValidationMessageKey($fieldName, $type, $validationName, $messageKey)] = '1';
        }

        return $result;
    }

    /**
     * Handles the data attributes for the fields which are valid.
     *
     * Example: `formz-valid-email="1"`
     *
     * @param FormResult $requestResult
     * @return array
     */
    public function getFieldsValidDataAttributes(FormResult $requestResult)
    {
        $result = [];
        $formConfiguration = $this->getFormObject()->getConfiguration();

        foreach ($formConfiguration->getFields() as $field) {
            $fieldName = $field->getFieldName();

            if (false === $requestResult->fieldIsDeactivated($field)
                && false === $requestResult->forProperty($fieldName)->hasErrors()
            ) {
                $result[self::getFieldDataValidKey($fieldName)] = '1';
            }
        }

        return $result;
    }

    /**
     * Formats the data value attribute key for a given field name.
     *
     * @param string $fieldName Name of the field.
     * @return string
     */
    public static function getFieldDataValueKey($fieldName)
    {
        return 'formz-value-' . StringService::get()->sanitizeString($fieldName);
    }

    /**
     * Formats the data valid attribute key for a given field name.
     *
     * @param string $fieldName Name of the field.
     * @return string
     */
    public static function getFieldDataValidKey($fieldName)
    {
        return 'formz-valid-' . StringService::get()->sanitizeString($fieldName);
    }

    /**
     * Formats the data message attribute key for a given field name.
     *
     * @param string $fieldName Name of the field.
     * @param string $type      Type of the message: `error`, `warning` or `notice`.
     * @return string
     */
    public static function getFieldDataMessageKey($fieldName, $type = 'error')
    {
        return 'formz-' . $type . '-' . StringService::get()->sanitizeString($fieldName);
    }

    /**
     * Formats the data message attribute key for a given failed validation for
     * the given field name.
     *
     * @param string $fieldName
     * @param string $type Type of the message: `error`, `warning` or `notice`.
     * @param string $validationName
     * @param string $messageKey
     * @return string
     */
    public static function getFieldDataValidationMessageKey($fieldName, $type, $validationName, $messageKey)
    {
        $stringService = StringService::get();

        return vsprintf(
            'formz-%s-%s-%s-%s',
            [
                $type,
                $stringService->sanitizeString($fieldName),
                $stringService->sanitizeString($validationName),
                $stringService->sanitizeString($messageKey)
            ]
        );
    }
}
