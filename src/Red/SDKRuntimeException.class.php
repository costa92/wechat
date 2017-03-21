<?php
/**
 * @author longqiuhong
 */
namespace  Costa92\Wechat\Red;

class  SDKRuntimeException extends \Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}

}

?>