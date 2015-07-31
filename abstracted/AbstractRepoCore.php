<?php


namespace Repository\Abstracted;

use Phalcon\Exception;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Mvc\User\Component;
use Repository\Interfaces\IRepoCore;
use Repository\Interfaces\IRepoMessage;
use Repository\Src\Common\MergeManager;
use Repository\Src\Common\RepositoryMessages;

abstract class AbstractRepoCore extends Component implements IRepoCore
{
    protected $mergeResults = false;
    protected $multiRelated = false;
    protected $backup;
    protected $results;
    protected $criteria;

    public function setCriteria(array $criteriaData)
    {
        $this->criteria = $criteriaData;
        return $this;
    }

    public function mergeResults()
    {
        $this->mergeResults = true;
        MergeManager::mergeResults();
        return $this;
    }

    public function backUpData()
    {
        $this->backup = $this->results;
    }

    public function returnAs($returnType)
    {
//        unset($this->results['related']);
        $arr = array();
        switch ($returnType) {
            case 'array':
                if (is_array($this->results) && $this->mergeResults != true) {
                    foreach ($this->results as $obj) {
                        $arr = array_merge($arr, $obj->toArray());
                    }
                    return $arr;
                } elseif (method_exists($this->results, 'toArray') && !empty($this->results->toArray())) {
                    return $this->results->toArray();
                } else if ($this->mergeResults) {
                    return $this->results;
                }
                return 'empty';
                break;
            case 'collection':
                break;
            case 'resultSet':
                break;
            case 'object':
                return $this->results;
                break;
            case 'json':
                return json_encode(array('results' => $this->returnAs('array')));
                break;
            case 'xml':
                break;
        }
    }

    /**
     * @param $relatedAlias
     * @return array
     */
    protected function mergeProcess($relatedAlias)
    {
        try {
            $mergeManager = new MergeManager($relatedAlias);

            $this->wrapResultsInArray();
            $mergeManager->results = $this->results;

            $res = $mergeManager->loopResults();
            if ($res instanceof IRepoMessage) {
                return $res;
            }
            return MergeManager::$backup;
        } catch (\Exception $e) {
            $repoMsg = new RepositoryMessages($e);
            return $repoMsg->toArray();
        }
    }

    protected function wrapResultsInArray()
    {
        if (!is_array($this->results)) {
            $this->results = array($this->results);
        }
    }
}