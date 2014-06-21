<?php
namespace Taskle\Generate\Twig;

class CaseExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('camelCase', 'Taskle\\Generate\\Helper\\CaseHelper::camelCase'),
            new \Twig_SimpleFilter('StudlyCase', 'Taskle\\Generate\\Helper\\CaseHelper::studlyCase'),
        );
    }

    public function getName()
    {
        return 'case_extension';
    }
}
