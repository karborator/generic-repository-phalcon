<?php


namespace Repository\Interfaces;


interface IRepoMessage
{
    public function __construct($objOrArray);

    public function toArray();
}