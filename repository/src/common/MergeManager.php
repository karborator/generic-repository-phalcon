<?php
namespace Repository\Src\Common;

use Phalcon\Exception;
use Repository\Interfaces\IRepoMessage;
use Repository\Src\Common\RepositoryMessages;
use Phalcon\Mvc\Model\Resultset\Simple;


class MergeManager extends \Phalcon\Mvc\User\Component
{
    public static $backup;
    public static $model;
    public $results;
    protected $multiRelated = false;
    protected static $mergeResults = false;
    protected static $relatedAlias;

    public function __construct($relatedAlias)
    {
        if (is_array($relatedAlias)) {
            $this->multiRelated = true;
        }
        self::$relatedAlias = $relatedAlias;
    }

    /**
     * Use $this->results
     *
     * Is a resultSetSimple
     * Single Phalcon\Mvc\Model
     * Array of resultSetSimple
     * Just an array
     */
    public function getResultType()
    {
        if ($this->getResultsCount() == 1 && $this->results[0] instanceof \Phalcon\Mvc\Model\Resultset\Simple) {
            return '\Phalcon\Mvc\Model\Resultset\Simple';
        } else if ($this->getResultsCount() == 1 && $this->results[0] instanceof \Phalcon\Mvc\Model) {
            return '\Phalcon\Mvc\Model';
        } elseif ($this->getResultsCount() > 1 && $this->results[0] instanceof \Phalcon\Mvc\Model\Resultset\Simple) {
            if ($this->getResultsCount() == count($this->getRSCountFromArray())) {
                return '\Phalcon\Mvc\Model\Resultset\Simple';
            }
        } else {
            if (is_array($this->results)) {
                return 'array';
            }
        }
        return new RepositoryMessages(array('status' => 'error', 'message' => 'MergeManager error (ResultType) at line:' . __LINE__));
    }

    /**
     * Count elements of type \Phalcon\Mvc\Model\Resultset\Simple
     * @return array
     */
    protected function getRSCountFromArray()
    {
        foreach ($this->results as $key => $obj) {
            if ($obj instanceof \Phalcon\Mvc\Model\Resultset\Simple) {
                $isCollectionOFResultsets [] = true;
            }
        }
        return $isCollectionOFResultsets;
    }

    public static function mergeResults($mergeStatus = null)
    {
        if ($mergeStatus) {
            self::$mergeResults = $mergeStatus;
        } else {
            self::$mergeResults = true;
        }
    }

    /**
     * Count of results
     * @param $results
     * @return int
     */
    public function getResultsCount($results = null)
    {
        if (!empty($results)) {
            return count($results);
        }
        return count($this->results);
    }

    /**
     * Use $this->mergeResults
     */
    public function isMergeResultsActivated()
    {
        return self::$mergeResults;
    }

    /**
     * Related alias must be an array
     * @param $relatedAlias
     */
    public function isMultiRelatedMerge()
    {
        return $this->multiRelated;
    }

    public function loopResults($results = null)
    {
        if ($results) {
            $this->results = $results;
        }

        $resultType = $this->getResultType();
        if ($resultType instanceof IRepoMessage) {
            return $resultType;
        }
        foreach ($this->results as $key => $obj) {
            $this->defaultHandle($obj, $resultType);
        }
    }

    /**
     * @param $collection
     * @return RepositoryMessages
     */
    private function defaultHandle($collection, $resultType)
    {
        try {

            if ($resultType == '\Phalcon\Mvc\Model') {
                $collection = array($collection);
                $resultType = '';
                self::$backup = $this->results;
            }

            if ($resultType == 'array') {
                foreach (self::$backup as $key2 => $obj2) {
                    $related = self::$model->getRelated(self::$relatedAlias);
                    if (!$related) {
                        self::$backup[$key2][self::$relatedAlias] = false;
                    } else {
                        self::$backup[$key2][self::$relatedAlias] = $related->toArray();
                    }
                }
            } else {
                foreach ($collection as $key => $obj) {
                    if (is_object($obj)) {
                        $array = $obj->toArray();
                    }

                    if (!is_array(self::$relatedAlias)) {
                        self::$backup[$key] = $this->merge($array, array(self::$relatedAlias => $obj->getRelated(self::$relatedAlias)->toArray()));
                    } else {
                        $this->loopRelated($obj, $array, $key);
                    }
                }
            }
            unset(self::$backup['related']);
        } catch (\Exception $e) {
            self::$backup = new RepositoryMessages(array('status' => 'error', 'message' => $e->getMessage()));
        }
    }

    /**
     * @param array $array
     * @param $value
     * @return array|null
     */
    public function merge(array $array, array $array2)
    {
        try {
            return array_merge($array, $array2);
        } catch (Exception $e) {
            return new RepositoryMessages(array('status' => 'error', 'message' => $e->getMessage()));
        }
    }

    /**
     * @param $obj
     * @param $array
     * @param $key
     */
    protected function loopRelated($obj, $array, $key)
    {
        foreach (self::$relatedAlias as $keyAlias => $alias) {
            if ($keyAlias == 0) {
                self::$backup['related'] = $obj->getRelated($alias);
                self::$backup[$key] = $this->merge($array, array($alias => self::$backup['related']->toArray()));
            } else {
                foreach (self::$backup['related'] as $keyRelated => $relatedObj) {
                    self::$backup[$key][self::$relatedAlias[$keyAlias - 1]][$key] = $this->merge(
                        self::$backup[$key] [self::$relatedAlias[$keyAlias - 1]][$keyRelated],
                        array($alias => $relatedObj->getRelated($alias)->toArray())
                    );
                }
            }
        }
    }
}