<?php
namespace Romm\Formz\Tests\Unit\AssetHandler;

use Romm\Formz\AssetHandler\AssetHandlerFactory;
use Romm\Formz\Configuration\ConfigurationFactory;
use Romm\Formz\Core\Core;
use Romm\Formz\Form\FormObjectFactory;
use Romm\Formz\Service\TypoScriptService;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;

trait AssetHandlerTestTrait
{

    /**
     * @param string $formClassName
     * @return AssetHandlerFactory
     */
    protected function getAssetHandlerFactoryInstance($formClassName)
    {
        $formObjectFactory = new FormObjectFactory;
        $formObjectFactory->injectConfigurationFactory(Core::instantiate(ConfigurationFactory::class));
        $formObjectFactory->injectTypoScriptService(Core::instantiate(TypoScriptService::class));

        $form = $formObjectFactory->getInstanceFromClassName($formClassName, 'foo');
        $controllerContext = new ControllerContext();

        return AssetHandlerFactory::get($form, $controllerContext);
    }

    /**
     * Returns the same string, but without any space/tab/new line.
     *
     * @param string $string
     * @return string
     */
    protected function trimString($string)
    {
        return preg_replace('/\s+/', '', $string);
    }

    /**
     * Returns the string without multi-lines comments.
     *
     * @param string $string
     * @return string
     */
    protected function removeMultiLinesComments($string)
    {
        return preg_replace('#\/\*([^*]|[\r\n]|(\*+([^*\/]|[\r\n])))*\*+\/#m', '', $string);
    }
}
