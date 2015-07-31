<?php

namespace Repository\Src;

use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\ModelInterface as ModelInterface;
use Phalcon\Mvc\User\Component;
use Repository\Abstracted\AbstractGeneric;
use Repository\Interfaces\IGeneric;
use Repository\Interfaces\IRepoCore;
use Repository\Src\Common\RepositoryMessages;

/**
 * Class GenericRepository
 * @package Repository\Src
 */
class GenericRepository extends AbstractGeneric implements IGeneric, IRepoCore
{

    public function __construct(\Phalcon\Config $config, $modelName = null)
    {
        parent::__construct($config);
        if (!empty($modelName) && !$this->boot($modelName)) {
            echo $this->returnAs('json');
            exit;
        }
    }


    public function findAll()
    {

        $loadedModel = $this->model;
        $results = $loadedModel::find($this->criteria);
        $this->results = $results;
        $this->backUpData();
        return $this;
    }


    public function findFirst()
    {
        $loadedModel = $this->model;
        $results = $loadedModel::findFirst($this->criteria);
        if (empty($results)) {
            $this->results = new RepositoryMessages(array('Missing results  at: ' . __FILE__ . ' line ' . __LINE__ . ' !'));
            echo $this->returnAs('json');
            exit;
        }
        $this->results = $results;
        $this->backUpData();
        return $this;
    }

    public function create(array $data)
    {
        $loadedModel = $this->model;
        $this->results = new RepositoryMessages(array('status' => 'Success'));
        if (!$loadedModel->save($data)) {
            $this->results = new RepositoryMessages($loadedModel);
        }
//        $this->results = new RepositoryMessages(array('status' => 'Success'));
        return $this;
    }

    public function update(array $data)
    {
        if (!$this->criteria) {
            $this->results = new RepositoryMessages(array('Missing update criteria!'));
            return $this;
        }
        $this->results = new RepositoryMessages(array('status' => 'Success'));
        $loadedModel = $this->model;
        $findModel = $loadedModel::findFirst($this->criteria);

        if (empty($findModel) || !$findModel->update($data)) {
            $this->results = new RepositoryMessages($loadedModel);
        }
        return $this;
    }

    public function delete()
    {
        if (!$this->criteria) {
            $this->results = new RepositoryMessages(array('Missing delete criteria!'));
            return $this;
        }
        $this->results = new RepositoryMessages(array('status' => 'Success'));
        $loadedModel = $this->model;
        $findModel = $loadedModel::findFirst($this->criteria);
        if (empty($findModel) || !$findModel->delete()) {
            $this->results = new RepositoryMessages($loadedModel);
        }
        return $this;
    }

    /**
     * @deprecated
     * @type Relational
     * @return $this|bool
     */
    public function getMembers()
    {
        if (empty($this->results)) {
            //TODO errors
            return false;
        }
        $this->results = $this->mergeProcess('members');
        return $this;
    }

    //trend
    public function getRelated($alias)
    {
        if (empty($this->results)) {
            $this->results = new RepositoryMessages(array('Missing results  at: ' . __FILE__ . ' line ' . __LINE__ . ' !'));
            echo $this->returnAs('json');
            exit;
        }

        $results = $this->mergeProcess($alias);
        $this->results = $results;
        return $this;
    }
}