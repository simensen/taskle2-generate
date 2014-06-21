<?php
namespace Taskle\Generate\Renderer;

use Exception;
use Taskle\Generate\Helper\CaseHelper;
use Twig_Environment;

class Model
{
    protected $twig;
    protected $sourceDir;
    protected $testsDir;

    public function __construct(Twig_Environment $twig, $sourceDir, $testsDir)
    {
        $this->twig = $twig;
        $this->sourceDir = $sourceDir;
        $this->testsDir = $testsDir;
    }

    public function build(Array $model)
    {
        $model = $this->validateModel($model);

        $this->buildObject($this->sourceDir, $model['entity']['classname'], 'Model.twig', $model);
        $this->buildObject($this->sourceDir, $model['collection']['classname'], 'ModelCollection.twig', $model);
        $this->buildObject($this->sourceDir, $model['factory']['classname'], 'ModelFactory.twig', $model);
        $this->buildObject($this->sourceDir, $model['filter']['classname'], 'ModelFilter.twig', $model);
        $this->buildObject($this->sourceDir, $model['repository']['classname'], 'ModelRepository.twig', $model);
        $this->buildObject($this->sourceDir, 'Memory' . $model['repository']['classname'], 'MemoryModelRepository.twig', $model);

        $this->buildObject($this->testsDir, $model['entity']['classname'] . 'Test', 'ModelTest.twig', $model);
        $this->buildObject($this->testsDir, $model['collection']['classname'] . 'Test', 'ModelCollectionTest.twig', $model);
        $this->buildObject($this->testsDir, $model['factory']['classname'] . 'Test', 'ModelFactoryTest.twig', $model);
        $this->buildObject($this->testsDir, 'Memory' . $model['repository']['classname'] . 'Test', 'MemoryModelRepositoryTest.twig', $model);

        foreach ($model['valueobjects'] as $classname) {
            $this->buildObject(
                $this->sourceDir,
                $classname,
                'ValueObject.twig',
                array(
                    'namespace' => $model['namespace'],
                    'classname' => $classname,
                )
            );

            $this->buildObject(
                $this->testsDir,
                $classname . 'Test',
                'ValueObjectTest.twig',
                array(
                    'namespace' => $model['namespace'],
                    'classname' => $classname,
                )
            );
        }
    }

    protected function validateModel(Array $model)
    {
        if (empty($model['singular']) || !is_string($model['singular'])) {
            throw new Exception("Unknown singular value value for model");
        }

        $singular = $model['singular'];

        if (empty($model['namespace']) || !is_string($model['namespace'])) {
            throw new Exception("Missing namespace value for {$singular}");
        }

        if (empty($model['plural']) || !is_string($model['plural'])) {
            throw new Exception("Missing plural value for {$singular}");
        }

        $model['entity'] = array(
            'name' => $singular,
            'classname' => CaseHelper::studlyCase($singular),
        );
        $model['collection'] = array(
            'name' => $singular . '-collection',
            'classname' => CaseHelper::studlyCase($singular) . 'Collection',
        );
        $model['factory'] = array(
            'name' => $singular . '-factory',
            'classname' => CaseHelper::studlyCase($singular) . 'Factory',
        );
        $model['filter'] = array(
            'name' => $singular . '-filter',
            'classname' => CaseHelper::studlyCase($singular) . 'Filter',
        );
        $model['repository'] = array(
            'name' => $singular . '-repository',
            'classname' => CaseHelper::studlyCase($singular) . 'Repository',
        );
        $model['identity'] = array(
            'name' => $singular . '-id',
            'classname' => CaseHelper::studlyCase($singular) . 'Id',
        );

        if (empty($model['fields']) || !is_array($model['fields'])) {
            throw new Exception("Missing fields array for {$singular}");
        }

        if (empty($model['fields']['id']) || !is_array($model['fields']['id'])) {
            throw new Exception("Missing fields:id array for {$singular}");
        }

        $identityCount = 0;

        foreach ($model['fields'] as $key => $value) {
            $model['fields'][$key]['name'] = $key;
            if (empty($value['type']) || !is_string($value['type'])) {
                throw new Exception("Missing fields.{$key}.type value for {$singular}");
            }
            $type = strtolower($value['type']);
            switch ($type) {
                case 'bool':
                case 'int':
                case 'string':
                    // valid type
                    break;
                case 'foreign':
                    if (empty($value['name']) || !is_string($value['name'])) {
                        throw new Exception("Missing fields.{$key}.name value for {$singular}");
                    }
                    if (empty($value['classname']) || !is_string($value['classname'])) {
                        throw new Exception("Missing fields.{$key}.classname value for {$singular}");
                    }
                    if (empty($value['use']) || !is_string($value['use'])) {
                        throw new Exception("Missing fields.{$key}.use value for {$singular}");
                    }
                    break;
                case 'valueobject':
                    if (empty($model['fields'][$key]['classname'])) {
                        $model['fields'][$key]['classname'] =
                            CaseHelper::studlyCase("{$singular}-{$key}");
                    }
                    if (empty($model['valueobjects']) || !is_array($model['valueobjects'])) {
                        $model['valueobjects'] = array();
                    }
                    $model['valueobjects'][] = $model['fields'][$key]['classname'];
                    break;
                default:
                    throw new Exception("Invalid fields.{$key}.type value for {$singular} ({$type})");
            }
        }

        return $model;
    }

    protected function buildObject($buildDir, $classname, $view, $model)
    {
        $filename =
            $buildDir . '/' .
            str_replace('\\', '/', $model['namespace']) . '/' .
            $classname . '.php';

        if (file_exists($filename)) {
            // Don't overwrite files
            return true;
        }

        echo $filename . "\n";

        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, $recursive = true);
        }

        file_put_contents($filename, $this->twig->render($view, $model));
    }
}
