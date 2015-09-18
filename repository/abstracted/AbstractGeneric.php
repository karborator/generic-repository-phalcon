<?php


namespace Repository\Abstracted;

use Phalcon\Mvc\User\Component;
use Phalcon\Mvc\Model\Resultset\Simple;
use Repository\Interfaces\IGeneric;
use Repository\Interfaces\IRepoCore;
use Repository\Src\Common\MergeManager;
use Repository\Src\Common\RepositoryMessages;

abstract class AbstractGeneric extends AbstractRepoCore implements IGeneric, IRepoCore
{
    protected $modelMap;
    protected $model;

    public function __construct(\Phalcon\Config $config , $entitiesDir = null)
    {
        $this->setModelMap($config->application->modelsDir, $entitiesDir);
    }

    public function setModelMap($dir, $np = null)
    {
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $internalFolder = '\\' . $value;
                    $this->setModelMap($dir . DIRECTORY_SEPARATOR . $value, $np . $internalFolder);
                } else {
                    $position = strpos($value, '.php');
                    $model = ucfirst(mb_strcut($value, 0, $position));
                    if (!empty($model)) {
                        $this->modelMap[$model] = $np . '\\' . $model;
                    }
                }
            }
        }
    }

    public function boot($modelName)
    {
        try {
            $this->model = $this->getDI()->getModelsManager()->load($this->modelMap[ucfirst($modelName)]);
        } catch (\Exception $e) {
            $this->results = new RepositoryMessages(array('Bad model name: ' . ucfirst($modelName)));
            return false;
        }
        MergeManager::$model = $this->model;
        return true;
    }

    public function setModel($modelName)
    {
        if (!$this->boot($modelName)) {
            echo $this->returnAs('json');
            exit;
        }
        return $this;
    }
}