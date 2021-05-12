<?php
namespace app\common\corelib;

class CacheHelper
{

    protected function _init()
    {
    }

    public static function del($key, $keyPrefix = null)
    {
        if ($keyPrefix != null)
            \yii::$app->cache->keyPrefix = $keyPrefix;
        return \yii::$app->cache->delete($key);
    }

    public static function get($key)
    {
        $value = false;
        try {
            $value = \yii::$app->cache->get($key);
        } catch (\Exception $err) {
//            LogHelper::error('Failed to get cache', LogHelper::CAT_INFRA);
            return false;
        }
        return $value;
    }

    public static function set($key, $value, $duration = 0, $dependency = null)
    {
        try {
            \yii::$app->cache->set($key, $value, $duration, $dependency);
        } catch (\Exception $err) {
//            LogHelper::error('Failed to get cache', LogHelper::CAT_INFRA);
            return false;
        }
    }

    //检查指定key的输入错误次数是否被允许,如:检查一个时间范围内用户输入密码错误次数是否在允许的次数内。fzy.add 2016-03-24
    public function set_error_nums($append_key,$result,$limit_times=1200)
    {
        $nums = 'cache_corelib_check_nums_'.$append_key;
        $times = 'cache_corelib_check_times_'.$append_key;

        if(!$result)
        {
            $error_nums = $this->get($nums);
            //如果输入错误的密码，如果错误密码次数的变量存在
            if($error_nums)
            {
                $last_time = $this->get($times);
                //如果输错时间间隔大于20分钟,重置错误次数为1,否则累加
                if(time()-$last_time>$limit_times)
                {
                    $this->set($nums,1,$limit_times);
                    $this->set($times,time(),$limit_times);
                }
                else
                {
                    $n = $error_nums + 1;
                    $this->set($nums,$n,$limit_times);
                }
            }
            else
            {
                //如果错误密码次数的变量不存在，则设置为1
                $this->set($nums,1,$limit_times);
                $this->set($times,time(),$limit_times);
            }
        }
        else
        {
            //如果输入了正确的密码，重置输入密码错误的次数
            $this->set($nums,0,20);
            $this->set($times,time(),20);
        }

        return true;
    }

    public function get_error_nums($append_key)
    {
        $nums = 'cache_corelib_check_nums_'.$append_key;
       return $this->get($nums);
    }
    public function get_error_datetime($append_key)
    {
        $nums = 'cache_corelib_check_times_'.$append_key;
        return $this->get($nums);
    }

} 