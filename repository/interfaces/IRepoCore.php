<?php

namespace Repository\Interfaces;


interface IRepoCore
{
    public function setCriteria(array $criteriaData);

    public function mergeResults();

    public function backUpData();

    public function returnAs($returnType);
}