<?
/**
 * 队列逻辑处理类
 *
 * 实现队列处理方法
 * 
 * @package	Module
 * @author	Xuni
 * @since	2016-10-11
 */
class QueueLibModule extends AppModule
{
    private $cacheType      = 'redisQc';
    private $queueType      = 'redisQ';
    private $queueName      = 'tradeQueue';

    const SECOND = 5;//秒

    /**
     * 创建队列，将需要执行的队列数据丢入队列中
     * @author      Xuni
     * @since       2015-06-19
     * @param       array       队列数据array(
     *                                   'method'    => $method,//方法
     *                                   'data'      => $data,//数据
     *                                   'memo'      => $memo,//备注，可用于测试，方便查找
     *                                   'source'    => $source,//来源于-（1：后台，2：前台web，3：前台seller，4：手机端）
     *                                   );
     * @return      boolean
     */
    public function addQueue($method, $data, $memo='', $source=1)
    {
        $objRq 	= $this->com($this->queueType)->name($this->queueName);
        $objRs  = $this->com($this->cacheType);

        if ( !is_object($objRq) || !is_object($objRs) ) return false;

        //5秒内相同的数据不会被重复加入队列执行
        $uKey   = md5($method.serialize($data).$source.$memo);
        if ( $objRs->get($uKey) == true ) return false;
        //判断是否保存成功---有时redis服务器可用但无法保存成功
        $flag   = $objRs->set($uKey, true, self::SECOND);
        if ( $flag === false ) return false;

        $res = array(
            'method'    => $method,
            'source'	=> $source,
            'data'      => $data,
            'memo'      => $memo,
            );
        return $objRq->push($res);
    }

    /*
     * 特价下架，回归定价
     *
     */
    public function offpriceDown($data)
    {
        $saleId = $data['id'];
        if ( empty($saleId) ) return false;

        $sale = $this->load('internal')->getSaleInfo($saleId);
        if ( empty($sale) ) return false;

        if ( $sale['salePrice'] == 2 || $sale['salePriceDate'] > time() || $sale['salePriceDate'] <= 0 )
            return false;

        $data = array(
            'isOffprice'    => 2,
            'salePriceDate' => 0,
            'price'         => $sale['price'],
            'isSale'        => $sale['isSale'],
            'isLicense'     => $sale['isLicense'],
            'salePrice'     => $sale['salePrice'],
            'priceType'     => $sale['priceType'],
        );
        //非特价还原置项值
        if ( $sale['isTop'] == 3 && empty($sale['tminfo']['embellish']) ) {
            $data['isTop'] = 0;
        } else {
            $data['isTop'] = 2;
        }
        $offList    = explode(',', $sale['offprice']);
        $offList    = array_filter( array_unique($offList) );
        $key        = array_search('1', $offList);
        if ( $key !== false ) unset($offList[$key]);
        sort($offList);

        $data['offprice'] = implode(',', $offList);
        //更新商品信息
        $res = $this->load('internal')->update($data, $saleId);
        if ($res) {
            $this->load('log')->addSaleLog($saleId, 10, '取消商品到期特价(来自系统自动完成)', serialize($data)); //修改价格信息
            $this->load('usercenter')->pushTmPrice($sale['number'], $sale, $data); //推送到用户
            return true;
        }
        return false;
    }

    
}
?>