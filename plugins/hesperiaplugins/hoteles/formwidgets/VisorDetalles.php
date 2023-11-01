<?php namespace HesperiaPlugins\Hoteles\FormWidgets;

use Backend\Classes\FormWidgetBase;

/**
 * VisorDetalles Form Widget
 */
class VisorDetalles extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'hesperiaplugins_hoteles_visor_detalles';

    /**
     * @inheritDoc
     */
    public function init()
    {
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('visordetalles');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['model'] = $this->model;
    }

    /**
     * @inheritDoc
     */
    public function loadAssets()
    {
        $this->addCss('css/visordetalles.css', 'HesperiaPlugins.hoteles');
        $this->addJs('js/visordetalles.js', 'HesperiaPlugins.hoteles');
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        return $value;
    }
}
