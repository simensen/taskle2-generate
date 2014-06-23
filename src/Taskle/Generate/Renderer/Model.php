<?php
namespace Taskle\Generate\Renderer;

use Exception;
use Symfony\Component\Console\Output\OutputInterface;
use Taskle\Generate\Helper\CaseHelper;
use Twig_Environment;

class Model
{
    protected $twig;
    protected $sourceDir;
    protected $testsDir;
    protected $output;

    public function __construct(Twig_Environment $twig, $sourceDir, $testsDir, OutputInterface $output)
    {
        $this->twig = $twig;
        $this->sourceDir = $sourceDir;
        $this->testsDir = $testsDir;
        $this->output = $output;
    }

    public function build(Array $model)
    {
        $model = $this->validateModel($model);

        $skipExtension = true;

        $templates = array(
            'entity' => ['Model.twig', 'ModelClassExtension.twig', 'ModelTest.twig'],
            'collection' => ['ModelCollection.twig', 'ModelClassExtension.twig', 'ModelCollectionTest.twig'],
            'factory' => ['ModelFactory.twig', 'ModelClassExtension.twig', 'ModelFactoryTest.twig'],
            'filter' => ['ModelFilter.twig', 'ModelClassExtension.twig', false],
            'createRepository' => ['ModelCreateRepository.twig', 'ModelInterfaceExtension.twig', false],
            'retrieveRepository' => ['ModelRetrieveRepository.twig', 'ModelInterfaceExtension.twig', false],
            'updateRepository' => ['ModelUpdateRepository.twig', 'ModelInterfaceExtension.twig', false],
            'deleteRepository' => ['ModelDeleteRepository.twig', 'ModelInterfaceExtension.twig', false],
            'memoryRepository' => ['MemoryModelRepository.twig', 'ModelClassExtension.twig', 'MemoryModelRepositoryTest.twig'],
        );

        foreach ($templates as $name => list($classTemplate, $extensionTemplate, $testTemplate)) {
            $this->buildObject($this->sourceDir, $model[$name]['classname'], $classTemplate, $model);
            $this->buildExtension($this->sourceDir, $model[$name]['classname'], $extensionTemplate, $model);
            if (false !== $testTemplate) {
                $this->buildObject($this->testsDir, $model[$name]['classname'] . 'Test', $testTemplate, $model, $skipExtension);
            }
        }

        foreach ($model['valueobjects'] as $classname) {
            $this->buildObject(
                $this->sourceDir,
                $classname,
                'ValueObject.twig',
                array(
                    'namespace' => $model['namespace'],
                    'extension' => $model['extension'],
                    'classname' => $classname,
                )
            );

            $this->buildExtension(
                $this->sourceDir,
                $classname,
                'ModelClassExtension.twig',
                array(
                    'namespace' => $model['namespace'],
                    'extension' => $model['extension'],
                    'classname' => $classname,
                )
            );

            $this->buildObject(
                $this->testsDir,
                $classname . 'Test',
                'ValueObjectTest.twig',
                array(
                    'namespace' => $model['namespace'],
                    'extension' => $model['extension'],
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

        if (empty($model['extension']) || !is_string($model['extension'])) {
            throw new Exception("Missing extension value for {$singular}");
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
        $model['memoryRepository'] = array(
            'name' => 'memory-' . $singular . '-repository',
            'classname' => 'Memory' . CaseHelper::studlyCase($singular) . 'Repository',
        );
        $model['createRepository'] = array(
            'name' => $singular . '-create-repository',
            'classname' => 'Create' . CaseHelper::studlyCase($singular) . 'Repository',
        );
        $model['retrieveRepository'] = array(
            'name' => $singular . '-retrieve-repository',
            'classname' => 'Retrieve' . CaseHelper::studlyCase($singular) . 'Repository',
        );
        $model['updateRepository'] = array(
            'name' => $singular . '-update-repository',
            'classname' => 'Update' . CaseHelper::studlyCase($singular) . 'Repository',
        );
        $model['deleteRepository'] = array(
            'name' => $singular . '-delete-repository',
            'classname' => 'Delete' . CaseHelper::studlyCase($singular) . 'Repository',
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
                case 'collection':
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
            if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $this->output->writeln('<info>s</info> ' . $filename);
            }
            return true;
        }

        $this->output->writeln('<info>+</info> ' . $filename);

        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, $recursive = true);
        }

        file_put_contents($filename, $this->twig->render($view, $model));
    }

    protected function buildExtension($buildDir, $classname, $view, $model)
    {
        $filename =
            $buildDir . '/' .
            str_replace('\\', '/', $model['extension']) . '/' .
            $classname . '.php';

        if (file_exists($filename)) {
            // Don't overwrite files
            if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $this->output->writeln('<info>s</info> ' . $filename);
            }
            return true;
        }

        $this->output->writeln('<info>+</info> ' . $filename);

        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, $recursive = true);
        }

        $extension = array(
            'namespace' => $model['extension'],
            'use' => $model['namespace'] . '\\' . $classname . ' as Base' . $classname,
            'classname' => $classname,
            'base' => 'Base' . $classname,
        );

        file_put_contents($filename, $this->twig->render($view, $extension));
    }
}
