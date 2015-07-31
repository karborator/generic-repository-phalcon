<?php

namespace Repository\Src\Common;

use Phalcon\Mvc\User\Component;
use Repository\Interfaces\IRepoMessage;

class RepositoryMessages extends Component implements IRepoMessage
{
    protected $_result = array();

    public function __construct($objOrArray)
    {
        if (is_array($objOrArray)) {
            $arr = $objOrArray;
        } else {
            $obj = $objOrArray;
            $arr = array('status' => 'Error');
            //Model errors
            if (method_exists($obj, 'getMessages')) {
                foreach ($obj->getMessages() as $msg) {
//                    if (!empty($msg->getMessages())) {
                    $arr['messages'][] = $msg->getMessage();
//                    }
                }
            } else if (method_exists($obj, 'getMessage')) {
                //Try catch erros
                $arr['messages'][] = $obj->getMessage();
            }
        }
        $this->_result = $arr;
    }

    public function toArray()
    {
        return $this->_result;
    }
}