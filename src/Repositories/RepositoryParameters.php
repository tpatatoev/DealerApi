<?php

namespace MTI\DealerApi\V2\Repositories;

use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\Reference;

class RepositoryParameters
{
  protected array $select;
  protected array $filter;
  protected array $order = ['ID' => 'ASC'];
  protected array $cache = ["ttl" => 3600];
  protected int $limit;
  protected array $runtime;


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
    if (count($this->cache)) {
      $result['cache'] = $this->cache;
    }
    if ($this->limit) {
      $result['limit'] = $this->limit;
    }
    return $result;
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

  public function addRefRuntime(Reference $runtime): RepositoryParameters
  {
    $this->runtime[] = $runtime;
    return $this;
  }

  public function addRuntime($runtime)
  {
    if ($runtime instanceof ExpressionField) {
      $this->addExpRuntime($runtime);
    }
    if ($runtime instanceof Reference) {
      $this->addRefRuntime($runtime);
    }
  }

  public function addExpRuntime(ExpressionField $runtime): RepositoryParameters
  {
    $this->runtime[] = $runtime;
    return $this;
  }
}
