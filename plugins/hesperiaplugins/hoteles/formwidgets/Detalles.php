<?php namespace HesperiaPlugins\Hoteles\FormWidgets;

use Backend\Classes\FormWidgetBase;

/**
 * detalles Form Widget
 */
class Detalles extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'hesperiaplugins_hoteles_detalles';

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
        $detalles = $this->vars["model"]->detalles; 
        return $this->makePartial('detalles');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->vars['name'] = $this->formField->getName().'[]';
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['model'] = $this->model;
    }

    /**
     * @inheritDoc
     */
    public function loadAssets()
    {
        $this->addCss('css/detalles.css', 'HesperiaPlugins.hoteles');
        $this->addJs('js/detalles.js', 'HesperiaPlugins.hoteles');
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        return $value;
    }
}
