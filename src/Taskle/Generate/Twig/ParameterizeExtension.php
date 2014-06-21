<?php
namespace Taskle\Generate\Twig;

use Taskle\Generate\Helper\CaseHelper;

class ParameterizeExtension extends \Twig_Extension
{
    protected static $int = 0;

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('parameterize', array($this, 'parameterize'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('arguments', array($this, 'arguments'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('parameter', array($this, 'parameter'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('property', array($this, 'property'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('variable', array($this, 'variable'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('samples', array($this, 'samples'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('sample', array($this, 'sample'), array('is_safe' => array('html'))),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('parameterize', array($this, 'parameterize'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('samples', array($this, 'samples'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('replay', array($this, 'replay'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('advance', array($this, 'advance'), array('is_safe' => array('html'))),
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

    public function samples($fields)
    {
        $ignore = func_get_args();
        array_shift($ignore);
        $samples = array();
        foreach ($fields as $field) {
            if (false === array_search($field['name'], $ignore)) {
                $samples[] = $this->sample($field);
            }
        }
        return implode(', ', $samples);
    }

    public function sample($field)
    {
        extract($field);

        $int = self::$int;
        $bool = (0 == $int % 2 ? 'true' : 'false');
        $string = "'{$name}-" . str_pad($int, 3, '0', STR_PAD_LEFT) . "'";
        self::$int++;

        switch ($type) {
            case 'bool':
                return $bool;
                break;
            case 'int':
                return strval(self::$int);
                break;
            case 'string':
                return $string;
                break;
            case 'foreign':
            case 'valueobject':
                return 'new ' . $classname . "({$string})";
                break;
            default:
                return '';
        }
    }

    public function replay()
    {
        self::$int = 0;
        return '';
    }

    public function advance($fields)
    {
        $ignore = func_get_args();
        array_shift($ignore);

        self::$int += count($fields) - count($ignore);
        return '';
    }

    public function getName()
    {
        return 'parameterize_extension';
    }
}
