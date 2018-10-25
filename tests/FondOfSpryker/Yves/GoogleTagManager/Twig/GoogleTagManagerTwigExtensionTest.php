<?php

namespace FondOfSpryker\Yves\GoogleTagManager;

use Codeception\Test\Unit;
use FondOfSpryker\Yves\GoogleTagManager\Business\Model\DataLayer\VariableBuilderInterface;
use FondOfSpryker\Yves\GoogleTagManager\Twig\GoogleTagManagerTwigExtension;
use Spryker\Client\Cart\CartClientInterface;
use Spryker\Client\Session\SessionClientInterface;
use Twig_Environment;

class GoogleTagManagerTwigExtensionTest extends Unit
{
    /**
     * @var \Spryker\Client\Cart\CartClientInterface |\PHPUnit\Framework\MockObject\MockObject|null
     */
    protected $cartClientMocK;

    /**
     * @var \FondOFSpryker\Yves\GoogleTagManager\Twig\GoogleTagManagerTwigExtension
     */
    protected $googleTagManagerTwigExtension;

    /**
     * @var \Spryker\Client\Session\SessionClientInterface |\PHPUnit\Framework\MockObject\MockObject|null
     */
    protected $sessionClientMock;

    /**
     * @var \Twig_Environment
     */
    protected $twigEnvironmentMock;

    /**
     * @var \FondOfSpryker\Yves\GoogleTagManager\Business\Model\DataLayer\VariableBuilderInterface |\PHPUnit\Framework\MockObject\MockObject|null
     */
    protected $variableBuilderMock;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $vfsStreamDirectory;

    /**
     * @return void
     */
    public function _before()
    {
        $this->cartClientMocK = $this->getMockBuilder(CartClientInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addItem', 'addItems', 'addValidItems', 'changeItemQuantity', 'clearQuote', 'decreaseItemQuantity', 'getItemCount', 'getQuote', 'increaseItemQuantity', 'removeItem', 'removeItems', 'reloadItems', 'storeQuote'])
            ->getMock();

        $this->sessionClientMock = $this->getMockBuilder(SessionClientInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['all', 'clear', 'get', 'getBag', 'getMetadataBag', 'getId', 'getName', 'has', 'invalidate', 'isStarted', 'migrate', 'registerBag', 'replace', 'remove', 'save', 'set', 'setContainer', 'setId', 'setName', 'start'])
            ->getMock();

        $this->twigEnvironmentMock = $this->getMockBuilder(Twig_Environment::class)
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();

        $this->variableBuilderMock = $this->getMockBuilder(VariableBuilderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultVariables', 'getCategoryVariables', 'getProductVariables', 'getQuoteVariables', 'getOrderVariables'])
            ->getMock();

        $this->googleTagManagerTwigExtension = new GoogleTagManagerTwigExtension(
            'GTM-XXXX',
            true,
            $this->variableBuilderMock,
            $this->cartClientMocK,
            $this->sessionClientMock
        );
    }

    /**
     * @return void
     */
    public function testGetFunction()
    {
        $functions = $this->googleTagManagerTwigExtension->getFunctions();

        $this->assertNotEmpty($functions);
        $this->assertEquals(2, count($functions));
        $this->assertEquals('googleTagManager', $functions[0]->getName());
        $this->assertEquals('dataLayer', $functions[1]->getName());
    }

    /**
     * @return void
     */
    public function testRenderGoogleTagManager()
    {
        $renderedTemplate = '<script></script>';
        $this->twigEnvironmentMock->expects($this->any())
            ->method('render')
            ->willReturn($renderedTemplate);

        $templateName = '@GoogleTagManager/partials/tag.twig';

        $renderer = $this->googleTagManagerTwigExtension->renderGoogleTagManager($this->twigEnvironmentMock, $templateName);

        $this->assertNotEmpty($renderer);
    }

    /**
     * @return void
     */
    public function testRenderGoogleTagManagerWithoutContainerID()
    {
        $renderedTemplate = '<script></script>';
        $this->twigEnvironmentMock->expects($this->any())
            ->method('render')
            ->willReturn($renderedTemplate);

        $templateName = '@GoogleTagManager/partials/tag.twig';

        $googleTagManagerTwigExtension = new GoogleTagManagerTwigExtension(
            '',
            true,
            $this->variableBuilderMock,
            $this->cartClientMocK,
            $this->sessionClientMock
        );

        $renderer = $googleTagManagerTwigExtension->renderGoogleTagManager($this->twigEnvironmentMock, $templateName);

        $this->assertEmpty($renderer);
    }

    /**
     * @return void
     */
    public function testRenderDataLayer()
    {
        $renderedTemplate = '<script>var dataLayer = [()]</script>';

        $this->cartClientMocK->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn(null);

        $this->twigEnvironmentMock->expects($this->atLeastOnce())
            ->method('render')
            ->willReturn($renderedTemplate);

        $this->variableBuilderMock->expects($this->atLeastOnce())
            ->method('getDefaultVariables')
            ->willReturn([]);

        $renderer = $this->googleTagManagerTwigExtension->renderDataLayer($this->twigEnvironmentMock, 'home', []);

        $this->assertNotEmpty($renderer);
    }
}
