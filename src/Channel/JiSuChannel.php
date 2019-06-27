<?php
/**
 * Created by PhpStorm.
 * User: WytheHuang
 * Date: 2019/6/23
 * Time: 14:34
 */

declare(strict_types = 1);
namespace Wythe\Logistics\Channel;

use Wythe\Logistics\Exceptions\HttpException;

/**
 * 极速数据物流查询
 * @package Wythe\Logistics\Channel
 */
class JiSuChannel extends Channel
{
    /**
     * JiSuChannel constructor.
     */
    public function __construct()
    {
        $config = $this->getChannelConfig();
        $this->url = 'https://api.jisuapi.com/express/query?appkey=' . $config['app_key'];
    }

    /**
     * 请求
     *
     * @param string $code
     * @param string $company
     * @return array
     * @throws \Wythe\Logistics\Exceptions\HttpException
     */
    public function request(string $code, string $company = ''): array
    {
        try {
            $params = ['type' => 'auto', 'number' => $code];
            $response = $this->post($this->url, $params);
            $this->toArray($response);
            $this->format();
            return $this->response;
        } catch (HttpException $exception) {
            throw new HttpException($exception->getMessage());
        }
    }

    /**
     * 统一物流信息
     *
     * @return void
     */
    protected function format()
    {
        if (!empty($this->response['data'])) {
            $formatData = [];
            foreach ($this->response['data'] as $datum) {
                $formatData[] = ['time' => $datum['time'], 'description' => $datum['status']];
            }
            $this->response['data'] = $formatData;
        }
    }

    /**
     * 转为数组
     *
     * @param array|string $response
     */
    protected function toArray($response)
    {
        $jsonToArray = \json_decode($response, true);
        if (empty($jsonToArray)) {
            $this->response = [
                'status' => 0,
                'message' => '请求发生不知名错误, 查询不到物流信息',
                'error_code' => 0,
                'data' => [],
                'logistics_company' => ''
            ];
        } else {
            if ($jsonToArray['status'] === 0) {
                $this->response = [
                    'status' => 1,
                    'message' => 'ok',
                    'error_code' => 0,
                    'data' => $jsonToArray['result']['list'],
                    'logistics_company' => ''
                ];
            } else {
                $this->response = [
                    'status' => 0,
                    'message' => $jsonToArray['msg'],
                    'error_code' => $jsonToArray['status'],
                    'data' => [],
                    'logistics_company' => ''
                ];
            }
        }
    }
}