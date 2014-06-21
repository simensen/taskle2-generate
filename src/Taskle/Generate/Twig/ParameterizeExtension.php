<?php
namespace Taskle\Generate\Twig;

use Taskle\Generate\Helper\CaseHelper;

class ParameterizeExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('parameterize', array($this, 'parameterize'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('arguments', array($this, 'arguments'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('parameter', array($this, 'parameter'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('property', array($this, 'property'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('variable', array($this, 'variable'), array('is_safe' => array('html'))),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('parameterize', array($this, 'parameterize'), array('is_safe' => array('html'))),
        );
    }

    public function parameterize($fields)
    {
        $ignore = func_get_args();
        array_shift($ignore);
        $parameters = array();
        foreach ($fields as $field) {
            if (false === array_search($field['name'], $ignore)) {
                $parameters[] = $this->parameter($field);
            }
        }
        return implode(', ', $parameters);
    }

    public function parameter($field)
    {
        $parameter = '';
        if (isset($field['classname'])) {
            $parameter .= $field['classname'] . ' ';
        }
        $parameter .= $this->variable($field);
        return $parameter;
    }

    public function arguments($fields)
    {
        $ignore = func_get_args();
        array_shift($ignore);
        $arguments = array();
        foreach ($fields as $field) {
            if (false === array_search($field['name'], $ignore)) {
                $arguments[] = $this->variable($field);
            }
        }
        return implode(', ', $arguments);
    }

    public function property($field)
    {
        return '$this->' . CaseHelper::camelCase($field['name']);
    }

    public function variable($field)
    {
        return '$' . CaseHelper::camelCase($field['name']);
    }

    public function getName()
    {
        return 'parameterize_extension';
    }
}
