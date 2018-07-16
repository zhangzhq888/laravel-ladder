<?php
namespace Laravelladder\Core\Collections;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Laravelladder\Core\Endpoints\BaseEndpoint;
use Laravelladder\Core\Models\BaseModel;
use SuperClosure\SerializableClosure;


/**
 * Class BaseCollection
 *
 * 列表基类
 *
 * @package Laravelladder\Core\Collections
 */
class BaseCollection extends Collection{
	/*
	|--------------------------------------------------------------------------
	| 创建Collection相关
	|--------------------------------------------------------------------------
	*/
    /**
     * 创建由items组成的Collection
     *
     * @param BaseModel[] $items
     * @return static
     */
    public static function create($items)
    {
        $array = static::convertInputToArray($items);
        return static::createFromArray($array);
    }
	
	/**
	 * 从字段中创建
	 * @param $array
	 * @return BaseCollection
	 */
    public static function createFromArray($array)
    {
        /**
         * @var Base $collection
         */
        $collection = \App::make('Laravelladder\Core\Collections\BaseCollection', array($array));
        return $collection;
    }
	
	/*
	|--------------------------------------------------------------------------
	| 常用方法
	|--------------------------------------------------------------------------
	*/
	/**
	 * 通过字段名，获取这个字段对应值的数组
	 * @param $field
	 * @return array
	 */
	public function getFieldArray($field){
		return $this->pluck($field)->all();
	}
	
	/*
	|--------------------------------------------------------------------------
	| 打包成返回Api
	|--------------------------------------------------------------------------
	*/
	
	/**
	 * 对于列表中的数据model，调用自定义的toApiArray以便设置自己想要的Api返回值
	 * @param callable $callback
	 * @return $this
	 */
	public function setCustomApiArray(callable $callback)
	{
		$apiArray = [];
		foreach ($this->items as $key => $item) {
			$apiArray[] = $callback($item, $key);
		}
		$this->setApiArray($apiArray);
		return $this;
	}

    protected $apiArray = array();
	
	public function setApiArray($apiArray = null){
		if(
			is_array($apiArray)
			&& !empty($apiArray)
		){
			$this->apiArray = $apiArray;
		} elseif(empty($this->apiArray)){
			$apiArray = array();
			foreach($this->all() as $item){
				/* @var BaseModel $item */
				$apiArray[] = $item->toApiArray();
			}
			$this->apiArray = $apiArray;
		}
		return $this;
	}
	
	/**
	 * 返回一个数组,数组重的每个元素均为item->toApiArray()
	 * @return array
	 */
    public function toApiArray(){
	    return $this
		    ->setApiArray()
		    ->getApiArray();
    }
	/**
	 * 将Collection中的Model替换成另一个Model
	 * @param $modelName
	 * @return $this
	 */
    public function changeItemModel($modelName){
	    foreach ($this->items as &$item){
		    $model = new $modelName();
		    /* @var BaseModel $model */
		    /* @var BaseModel $item */
		    $item = $model->fill(is_array($item)? $item : $item->toArray());
	    }
	    return $this;
    }
    
    public function getApiArray(){
        return $this->apiArray;
    }
	
	/*
	|--------------------------------------------------------------------------
	| 操作Collection相关
	|--------------------------------------------------------------------------
	*/
	/**
	 * 根据传入的数组，按照字段排序
	 * @param callable|null $callback 作为比较的字段
	 * @param array $orderArray 比较顺序
	 * @param bool $reverse
	 * @return static
	 */
	public function sortByFieldArray($callback, array $orderArray, $reverse = false)
	{
		$results = [];
		
		$callback = $this->valueRetriever($callback);
		
		foreach ($this->items as $key => $value) {
			$results[$key] = $callback($value, $key);
		}
		
		if($reverse) $orderArray = array_reverse($orderArray);
		$returnResult = [];
		foreach ($orderArray as $value){
			$key = array_search($value, $results);
			if($this->items[$key] instanceof BaseModel)	$returnResult[] = $this->items[$key];
			if($this->items[$key] instanceof BaseCollection)	$returnResult[] = $this->items[$key];
		}
		
		return new static($returnResult);
	}
	
	
    /**
     * 拷贝collection及其pagination
     *
     * @param BaseCollection $collection
     * @return BaseCollection
     */
    public static function createFromCollection(BaseCollection $collection){
        // 拷贝collection和pagination
        $coll = static::create($collection);
        $coll->setPagination(
            $collection->paginator_total,
            $collection->paginator_perPage,
            $collection->paginator_currentPage,
            $collection->paginator_isPaginated
        );
        return $coll;
    }

    public $paginator_total = 0;
    public $paginator_perPage = 20;
    public $paginator_from = 1;
    public $paginator_isPaginated = false;

    /**
     * 设置Collection分页
     * @param $total
     * @param $perPage
     * @param $from
     * @param $isPaginated
     * @return $this
     */
    public function setPagination($total, $perPage, $from, $isPaginated){
	    $this->paginator_total = $total;
	    $this->paginator_perPage = $perPage;
	    $this->paginator_from = $from;
	    $this->paginator_isPaginated = $isPaginated;
	    return $this;
    }

    const PAGINATOR_FIELD_TOTAL = 'total';
    const PAGINATOR_FIELD_PER_PAGE = 'per_page';
    const PAGINATOR_FIELD_FROM = 'from';
    const PAGINATOR_FIELD_IS_PAGINATED = 'isPaginated';
    const PAGINATOR_FIELD_DATA = 'data';

    /**
     * 将Collection cast成有分页逻辑的API
     * @return array
     */
    public function toPaginatedApiArray(){
        if(count($this->getApiArray()) == 0) $this->setApiArray();
        return [
            static::PAGINATOR_FIELD_IS_PAGINATED => $this->paginator_isPaginated,
            static::PAGINATOR_FIELD_TOTAL => $this->paginator_total,
            static::PAGINATOR_FIELD_PER_PAGE => $this->paginator_perPage,
            static::PAGINATOR_FIELD_FROM => $this->paginator_from,
            static::PAGINATOR_FIELD_DATA => $this->getApiArray()
        ];
    }
	
	/**
	 * 传入需要展示的字段，并获取由只包含字段的array组成的Collection
	 * @param array $fields
	 * @return BaseCollection
	 */
	public function toArrayWithFields(array $fields){
		$returnList = [];
		$this->setCustomApiArray(function(BaseModel $model) use ($fields, &$returnList){
			$array = [];
			foreach ($fields as $field){
				$array[$field] = isset($model->{$field}) ? $model->{$field} : null;
			}
			$returnList[] = $array;
		});
		return $returnList;
	}
	
	/**
	 * 获取满足键值对的第一个model
	 * @param $key
	 * @param $value
	 * @param bool $isStrict 是否严格匹配
	 * @return BaseModel | null
	 */
	public function whereFirst($key, $value, $isStrict = false){
		return $this
			->where($key, $value, $isStrict)
			->first();
	}
    
	/**
	 * 将input转换成数组
	 * @param array|Collection $input
	 * @return array
	 */
	public static function convertInputToArray($input)
	{
		if (is_array($input)) {
			return $input;
		} elseif ($input instanceof Collection) {
			return $input->all();
		} else {
			return (array)$input;
		}
	}
	
	/*
	|--------------------------------------------------------------------------
	| Api返回相关
	|--------------------------------------------------------------------------
	*/
	/**
	 * 返回类似Endpoint fire返回值的结果
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 */
	public function toEndpointResponse(){
		return BaseEndpoint::makeResultResponse($this);
	}
	
	/**
	 * 返回可用于CSV的array
	 * @param array $headerFieldMap = [
	 *      '表头显示名称1' => '对应字段名称1',
	 *      '表头显示名称2' => '对应字段名称2',
	 * ]
	 * @param null $callback
	 * @return array
	 */
	public function toCsvArray(array $headerFieldMap = [], $callback = null){
		
		$returnArray = [];
		$headerRow = [];
		$dataRow = [];
		foreach ($headerFieldMap as $key => $value){
			$headerRow[] = $key;
			$dataRow[] = $value;
		}
		
		$returnArray[] = $headerRow;
		$this->each(function(BaseModel $model) use ($dataRow, $callback, &$returnArray){
			if(is_callable($callback)) return $returnArray[] = $callback($model);
			$array = [];
			foreach ($dataRow as $field){
				$array[] = isset($model->{$field}) && !is_null($model->{$field}) ? $model->{$field} : '';
			}
			$returnArray[] = $array;
		});
		
		return $returnArray;
	}
    public function toCsvArraySerialize(array $headerFieldMap = [], SerializableClosure $callback = null){

        $returnArray = [];
        $headerRow = [];
        $dataRow = [];
        foreach ($headerFieldMap as $key => $value){
            $headerRow[] = $key;
            $dataRow[] = $value;
        }
        $callback = $callback->getClosure();
        $returnArray[] = $headerRow;
        $this->each(function(BaseModel $model) use ($dataRow, $callback, &$returnArray){
            if(is_callable($callback)) return $returnArray[] = $callback($model);
            $array = [];
            foreach ($dataRow as $field){
                $array[] = isset($model->{$field}) && !is_null($model->{$field}) ? $model->{$field} : '';
            }
            $returnArray[] = $array;
        });

        return $returnArray;
    }
}