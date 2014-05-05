<?php

use Intersvyaz\Widgets\Select2;

/**
 * @coversDefaultClass \Intersvyaz\Widgets\Select2
 */
class Select2Test extends \PHPUnit_Framework_TestCase
{
	const TEST_CLASS = '\Intersvyaz\Widgets\Select2';

	/**
	 * @param Select2 $widget
	 * @return string
	 */
	public function runAndCapture($widget)
	{
		$widget->init();
		ob_start();
		$widget->run();

		return ob_get_clean();
	}

	/**
	 * @return Select2
	 */
	public function makeWidget()
	{
		return new Select2();
	}

	/**
	 * @covers ::init
	 */
	public function testInit()
	{
		$widget = $this->makeWidget();
		$widget->init();
		$this->assertAttributeNotEmpty('language', $widget);
		$widget->language = 'blah';
		$widget->init();
		$this->assertAttributeEquals('blah', 'language', $widget);
	}

	public function testRun_FieldRendering_Provider()
	{
		$model = new FakeModel();
		$model->login = 'bar';

		return [
			[
				[
					'type' => Select2::TYPE_TEXT,
					'name' => 'foo',
					'value' => 'bar',
					'htmlOptions' => ['class' => 'baz']
				]
			],
			[
				[
					'type' => Select2::TYPE_TEXT,
					'name' => 'foo',
					'value' => 'bar',
					'htmlOptions' => ['id' => 'fake_id', 'name' => 'fake_name', 'class' => 'baz']
				]
			],
			[
				[
					'type' => Select2::TYPE_TEXT,
					'model' => $model,
					'attribute' => 'login',
					'htmlOptions' => ['class' => 'baz']
				]
			],
			[
				[
					'type' => Select2::TYPE_SELECT,
					'name' => 'foo',
					'value' => 'bar',
					'htmlOptions' => ['class' => 'baz'],
					'data' => [1 => 'one', 'bar' => 'blah']
				]
			],
			[
				[
					'type' => Select2::TYPE_SELECT,
					'name' => 'foo',
					'value' => 'bar',
					'htmlOptions' => ['id' => 'fake_id', 'name' => 'fake_name', 'class' => 'baz'],
					'data' => [1 => 'one', 'bar' => 'blah']
				]
			],
			[
				[
					'type' => Select2::TYPE_SELECT,
					'model' => $model,
					'attribute' => 'login',
					'htmlOptions' => ['class' => 'baz'],
					'data' => [1 => 'one', 'bar' => 'blah']
				]
			],
		];
	}

	/**
	 * @param array $widgetOptions Widget options.
	 * @covers ::run
	 * @dataProvider testRun_FieldRendering_Provider
	 */
	public function testRun_FieldRendering($widgetOptions)
	{
		$widget = $this->makeWidget();

		foreach ($widgetOptions as $option => $value) {
			$widget->{$option} = $value;
		}

		$widgetOutput = $this->runAndCapture($widget);

		$value = isset($widgetOptions['model']) ? $widgetOptions['model']->login : $widgetOptions['value'];
		if (isset($widgetOptions['model']))
			$name = CHtml::activeName($widgetOptions['model'], $widgetOptions['attribute']);
		elseif (isset($widgetOptions['htmlOptions']['name'])) {
			$name = $widgetOptions['htmlOptions']['name'];
		} else {
			$name = $widgetOptions['name'];
		}
		if (isset($widgetOptions['model']))
			$id = CHtml::activeId($widgetOptions['model'], $widgetOptions['attribute']);
		elseif (isset($widgetOptions['htmlOptions']['id'])) {
			$id = $widgetOptions['htmlOptions']['id'];
		} else {
			$id = CHtml::getIdByName($widgetOptions['name']);
		}

		if ($widgetOptions['type'] == Select2::TYPE_TEXT) {
			$this->assertTag([
				'tag' => 'input',
				'attributes' => [
					'type' => 'text',
					'name' => $name,
					'id' => $id,
					'value' => $value,
					'class' => 'baz'
				]
			], $widgetOutput);
		} else {
			$this->assertTag([
				'tag' => 'select',
				'attributes' => [
					'name' => $name,
					'id' => $id,
					'class' => 'baz'
				]
			], $widgetOutput);
			$this->assertTag([
				'tag' => 'option',
				'parent' => ['tag' => 'select'],
				'attributes' => [
					'selected' => 'selected',
					'value' => $value
				]
			], $widgetOutput);
		}
	}

	/**
	 * @covers ::run
	 */
	public function testRun_Scripts()
	{
		$widget = $this->makeWidget();
		$widget->name = 'foo';
		$widget->value = 'val';
		$widget->options = ['foo' => 'bar'];
		$widget->events = ['click' => 'return foobar()'];
		$widget->init();
		ob_start();
		$widget->run();
		ob_end_clean();
		$cs = \Yii::app()->clientScript;
		$script = $cs->scripts[$cs->defaultScriptPosition][get_class($widget) . '#' . $widget->getId()];
		$this->assertEquals(
			"jQuery('#foo').select2({'foo':'bar'}).on('click','return foobar()');",
			$script
		);
	}

	/**
	 * @covers ::run
	 */
	public function testRun_RegisterAssets()
	{
		$mock = $this->getMock(self::TEST_CLASS, ['registerAssets']);
		$mock->expects($this->once())
			->method('registerAssets');

		$mock->name = 'foo';
		$mock->value = 'bar';
		$this->runAndCapture($mock);
	}

	/**
	 * @covers ::run
	 * @expectedException CException
	 */
	public function testRun_InvalidType()
	{
		$widget = $this->makeWidget();
		$widget->init();
		$widget->name = 'foo';
		$widget->value = 'bar';
		$widget->type = 'blah';
		$widget->run();
	}

	public function registerAssetsProvider()
	{
		return [
			[true],
			[false],
		];
	}

	/**
	 * @param bool $minify
	 * @dataProvider registerAssetsProvider
	 * @covers ::registerAssets
	 */
	public function testRegisterAssets($minify)
	{
		$cs = \Yii::app()->clientScript;
		$cs->reset();

		$widget = $this->makeWidget();
		$widget->minifiedAssets = $minify;
		$widget->name = 'foo';
		$widget->value = 'bar';
		$widget->language = 'ru';
		$widget->init();

		// non-minifed assets
		ob_start();
		$widget->run();
		ob_end_clean();
		$assetPath = realpath(__DIR__ . '/../../assets');
		$assetUrl = \Yii::app()->assetManager->publish($assetPath);
		$minifyPrefix = $minify ? '.min' : '';

		$this->assertTrue(
			$cs->isCssFileRegistered($assetUrl . '/select2.css')
		);
		$this->assertTrue(
			$cs->isScriptFileRegistered(
				$assetUrl . '/select2' . $minifyPrefix . '.js',
				$cs->defaultScriptFilePosition
			)
		);
		$this->assertTrue(
			$cs->isScriptFileRegistered(
				$assetUrl . '/select2_locale_ru.js',
				$cs->defaultScriptFilePosition
			)
		);
	}
}
