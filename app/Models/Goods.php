<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Goods extends Model
{
     //指定表名为users
    protected $table = "goods";

    /**
     * 查询所有大类
     * 
     * @return Object  $list     查询到的数据
     */
    public function getType ()
    {
    	$list = Goods::where("pid","0")->get();
    	return $list;
    }

    /**
     * 查询所有商品
     * 
     * @return Object  $list    查询到的数据
     */
    public function getAll ()
    {
    	$list = Goods::where("pid","!=","0")->get();
    	return $list;
    }
}
