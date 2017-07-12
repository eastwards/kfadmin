<?
/**
 * 公共console类
 *
 * 公共console继承类
 *
 * @package      Action
 * @author       Xuni
 * @since       2015-04-17
 */
class QueueCommonAction extends ConsoleAction
{
    protected $encoding;

    public function before()
    {
        $this->encoding = $this->com('encoding');
    }

    /**
     * 解密队列数据
     *
     * @param   string      $data   队列加密数据
     * @return  array
     */
    public function decode($data, $encoding=0)
    {
        return $this->encoding->decode($data, $encoding);
    }

    /**
     * 加密队列数据
     *
     * @param   array   $data   队列数据
     * @return  string
     */
    public function encode($data, $encoding=0)
    {
        return $this->encoding->encode($data, $encoding);
    }

}
?>