<?php
require 'Curl.php';
use \Curl\Curl;
/**
 *
 */
class Biggon{
	/**
	 * Ключ авторизации API
	 * @var string
	 */
	private $key;
	/**
	 * Обьект Curl
	 * @var object
	 */
	private $curl;
	/**
	 * Формат выходных данных xml,json или phps
	 * @var string
	 */
	private $format;

	/**
	 * Создание класса
	 * @param string $key
	 * @param string $format
 	 */
	public function __construct($key,$format="xml") {
		$this->key=$key;
		$this->format=$format;
		$this->curl = new Curl();
		$this->curl->setUserAgent("Biggon_Api_Client_1.0");
		$this->curl->setOpt(CURLOPT_ENCODING , 'gzip');
		$this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, true);
		$this->curl->setOpt(CURLOPT_SSL_VERIFYHOST, 2);
		$this->curl->setOpt(CURLOPT_CAINFO, getcwd() ."/biggon.ru.crt");
	}
	/**
	 * Парсер xml ответа
	 * @param  mixed $response
	 * @return mixed
	 */
	private function parse_xml($response){
		if($response->fail)
    			throw new Exception('Error: ' . $response->code . ': ' . $response->message);
		return $response->result;
	}
	/**
	 * Парсер json ответа
	 * @param  mixed $response
	 * @return mixed
	 */
	private function parse_json($response){
		$response=json_decode($response);
		if($response->fail)
    			throw new Exception('Error: ' . $response->code . ': ' . $response->message);
		return $response->result;
	}
	/**
	 * Парсер phps ответа
	 * @param  mixed $response
	 * @return mixed
	 */
	private function parse_phps($response){
		$response=unserialize($response);
		if($response["fail"])
    			throw new Exception('Error: ' . $response["code"] . ': ' . $response["message"]);
		return $response["result"];
	}
	/**
	 * Данный метод делает запрос к API
	 * @param  string $method
	 * @param  string $url
	 * @param  array $param
	 * @return mixed
	 */
	private function curl_helper($method,$url,$param=""){
    	$this->curl->$method("$url.{$this->format}",$param);
    		if ($this->curl->error)
    		 	throw new Exception('Error: ' . $this->curl->error_code . ': ' . $this->curl->error_message);
    	$this->curl->close();
    	switch ($this->format) {
    		case "xml":
       		 	return $this->parse_xml($this->curl->response);
        	 	break;
    		case "json":
        	 	return $this->parse_json($this->curl->response);
        		break;
    		case "phps":
        		return $this->parse_phps($this->curl->response);
        		break;
		}
	}
	/**
	 * Возвращает список товаров по заданным фильтрам.
	 * @param  int $cat
	 * @param  string $geo
	 * @param  string $type
	 * @param  string $search
	 * @param  int $per
	 * @param  int $page
	 * @return mixed
	 */
	public function items_list($cat=null,$geo=null,$type=null,$search=null,$per=10,$page=1){
		$param=array('cat' => $cat,
					 'geo'=> strtoupper($geo),
					 'type'=> $type,
					 'search'=> $search,
					 'per'=> $per,
					 'page'=> $page,
					 'key'=> $this->key
					 );
		return	$this->curl_helper("get","https://api.biggon.net/v3/items/list",$param);
	}
	/**
	 * Возвращает список категорий (рубрик) товаров.
	 * @return mixed
	 */
	public function cats_list(){
		return	$this->curl_helper("get","https://api.biggon.net/v3/items/cats/list");
	}
	/**
	 * Данный метод позволяет получить подробную информацию о товаре
	 * @param  int $id
	 * @param  int $images
	 * @param  int $reviews
	 * @param  int $offers
	 * @param  string $geo
	 * @return mixed
	 */
	public function items_get($id,$images=0,$reviews=0,$offers=0,$geo=null){
		$param=array('id' => $id,
					 'images'=> $images,
					 'reviews'=> $reviews,
					 'offers'=> $offers,
					 'geo'=> strtoupper($geo),
					 'key'=> $this->key
					 );
		return	$this->curl_helper("get","https://api.biggon.net/v3/items/get",$param);
	}
	/**
	 * Данный метод позволяет сформировать и отправить новый заказ.
	 * @param  string $fio
	 * @param  string $tel
	 * @param  string $addr
	 * @param  int $offer
	 * @param  int $cnt
	 * @param  int $site
	 * @param  string $sub
	 * @param  string $agent
	 * @param  string $timezone
	 * @param  string $ip
	 * @param  string $ref
	 * @param  int $test
	 * @return mixed
	 */
	public function orders_submit($fio,$tel,$addr,$offer,$cnt=1,$site,$sub=null,$agent=null,$timezone,$ip,$ref=null,$test=0){
		$param=array('fio' => $fio,
					 'tel' => $tel,
					 'addr' => $addr,
					 'offer' => $offer,
					 'cnt' => $cnt,
					 'site' => $site,
					 'sub' => $sub,
					 'agent' => $agent,
					 'timezone' => $timezone,
					 'ip' => $ip,
					 'ref' => $ref,
					 'test' => $test,
					 );
		return	$this->curl_helper("post","https://api.biggon.net/v3/orders/submit",$param);

	}
	/**
	 * Данный метод позволяет получить статусы, комментарии и трек-номера посылок у запрошенных заказов.
	 * @param  array $ids
	 * @return mixed
	 */
	public function orders_states($ids){
		$param=array('ids' => $ids,
					 'key'=> $this->key
					 );
		return	$this->curl_helper("get","https://api.biggon.net/v3/orders/states",$param);

	}
	/**
	 * Данный метод позволяет организовать кросс-селлинг, добавляя к только что оформленному заказу дополнительные товары.
	 * @param  int $order
	 * @param  int $offer
	 * @param  int $cnt
	 * @return mixed
	 */
	public function orders_cros($order,$offer,$cnt=1){
		$param=array('order' => $order,
					 'offer' => $offer,
					 'cnt' => $cnt,
					 'key'=> $this->key
					 );
		return	$this->curl_helper("post","https://api.biggon.net/v3/orders/cross",$param);

	}
}