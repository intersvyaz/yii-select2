<?php

use Intersvyaz\Widgets\BootstrapSelect2;

/**
 * @coversDefaultClass \Intersvyaz\Widgets\BootstrapSelect2
 */
class BootstrapSelect2Test extends \PHPUnit_Framework_TestCase
{
	const TEST_CLASS = '\Intersvyaz\Wigets\BootstrapSelect2';

	/**
	 * @covers ::registerAssets
	 */
	public function testRegisterAssets()
	{
		$cs = \Yii::app()->clientScript;
		$cs->reset();

		$widget = new BootstrapSelect2();
		$widget->name = 'foo';
		$widget->value = 'bar';
		$widget->init();

		ob_start();
		$widget->run();
		ob_end_clean();
		$assetPath = realpath(__DIR__ . '/../../assets');
		$assetUrl = \Yii::app()->assetManager->publish($assetPath);

		$this->assertTrue(
			$cs->isCssFileRegistered($assetUrl . '/select2.css')
		);
		$this->assertTrue(
			$cs->isCssFileRegistered($assetUrl . '/select2-bootstrap.css')
		);
	}
}
