<?php

namespace Intersvyaz\Widgets;

/**
 * Select2 widget with bootstrap styles.
 * @see http://ivaynberg.github.io/select2/
 */
class BootstrapSelect2 extends Select2
{
	protected function registerAssets()
	{
		parent::registerAssets();

		$assetPath = realpath(__DIR__. '/../assets');
		$assetUrl = \Yii::app()->assetManager->publish($assetPath);
		\Yii::app()->clientScript
			->registerCssFile($assetUrl . '/select2-bootstrap.css');
	}
}