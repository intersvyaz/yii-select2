<?php

namespace Intersvyaz\Widgets;

/**
 * Select2 widget.
 * @see https://github.com/ivaynberg/select2
 */
class Select2 extends \CInputWidget
{
	// input field types.
	const TYPE_TEXT = 'text';
	const TYPE_SELECT = 'select';

	/**
	 * Input field type.
	 * @var string
	 */
	public $type = self::TYPE_SELECT;

	/**
	 * Widget language.
	 * @var string
	 */
	public $language;

	/**
	 * Data for dropdown field.
	 * @var array
	 */
	public $data = [];

	/**
	 * Select2 options.
	 * @var array
	 */
	public $options = [];

	/**
	 * Event handlers.
	 * @var array
	 */
	public $events = [];

	/**
	 * Whether to use minified assets.
	 * @var bool
	 */
	public $minifiedAssets = true;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if (!isset($this->language)) {
			$this->language = \Yii::app()->getLanguage();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		list($name, $id) = $this->resolveNameID();

		if (isset($this->htmlOptions['id'])) {
			$id = $this->htmlOptions['id'];
		} else {
			$this->htmlOptions['id'] = $id;
		}
		if (isset($this->htmlOptions['name'])) {
			$name = $this->htmlOptions['name'];
		}

		if ($this->type == static::TYPE_TEXT) {
			if ($this->hasModel()) {
				echo \CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
			} else {
				echo \CHtml::textField($name, $this->value, $this->htmlOptions);
			}
		} elseif ($this->type == static::TYPE_SELECT) {
			if ($this->hasModel()) {
				echo \CHtml::activeDropDownList($this->model, $this->attribute, $this->data, $this->htmlOptions);
			} else {
				echo \CHtml::dropDownList($name, $this->value, $this->data, $this->htmlOptions);
			}
		} else {
			throw new \CException("Invalid field type '{$this->type}'");
		}

		$options = !empty($this->options) ? \CJavaScript::encode($this->options) : '';

		$script = "jQuery('#{$id}').select2({$options})";
		foreach ($this->events as $event => $handler) {
			$script .= ".on('{$event}'," . \CJavaScript::encode($handler) . ")";
		}
		$script .= ';';

		\Yii::app()->clientScript
			->registerScript(__CLASS__ . '#' . $this->getId(), $script);
		$this->registerAssets();
	}

	/**
	 * Register widget assets.
	 */
	protected function registerAssets()
	{
		$assetPath = realpath(__DIR__. '/../assets');
		$assetUrl = \Yii::app()->assetManager->publish($assetPath);
		$minifyPrefix = $this->minifiedAssets ? '.min' : '';

		$cs = \Yii::app()->clientScript;
		$cs->registerCssFile($assetUrl . '/select2.css')
			->registerScriptFile($assetUrl . '/select2' . $minifyPrefix . '.js');

		$localeFile = '/select2_locale_' . $this->language . '.js';
		if (file_exists($assetPath . $localeFile)) {
			$cs->registerScriptFile($assetUrl . $localeFile);
		}
	}
}
