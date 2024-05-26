<?php

namespace MTI\DealerApi\V2\Repositories;

use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class RepositoryParameters
{
  protected array $select;
  protected array $filter;
  protected array $order = ['ID' => 'ASC'];
  // protected array $cache = ["ttl" => 3600 * 6, "cache_joins" => true];
  protected array $cache = [];
  protected int $limit = 0;
  protected array $runtime = [];


  public function toArray(): array
  {
    $result =  [
      'select' => $this->select,
      'filter' => $this->filter,
      'order' => $this->order,
    ];

    if (count($this->runtime)) {
      $result['runtime'] = $this->runtime;
    }
    if (count($this->cache) && empty($_REQUEST["cache"])) {
      $result['cache'] = $this->cache;
    }
    if ($this->limit) {
      $result['limit'] = $this->limit;
    }
    return $result;
  }

  public function addCache(array $cache)
  {
    $this->cache = $cache;
    return $this;
  }


  public function addOrder(array $order): RepositoryParameters
  {
    $this->order = $order;
    return $this;
  }

  public function addSelect(array $select): RepositoryParameters
  {
    $this->select = $select;
    return $this;
  }

  public function addLimit(int $limit): RepositoryParameters
  {
    $this->limit = $limit;
    return $this;
  }

  public function addFilter(array $filter): RepositoryParameters
  {
    $this->filter = $filter;
    return $this;
  }

  public function addReference($name, $referenceEntity, $referenceFilter, $parameters = array()): RepositoryParameters
  {
    $ref = Join::on($referenceFilter[0], $referenceFilter[1]);
    $this->runtime[] = new Reference($name, $referenceEntity, $ref, $parameters);
    return $this;
  }

  public function addExpression($name, $expression, $buildFrom = null, $parameters = array()): RepositoryParameters
  {
    $this->runtime[] = new ExpressionField($name, $expression, $buildFrom, $parameters);
    return $this;
  }
}
