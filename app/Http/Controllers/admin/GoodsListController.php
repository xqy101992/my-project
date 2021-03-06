<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Goods; //Goods表模型
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Events\Image; //图片处理插件

class GoodsListController extends Controller
{
    /**
     * 获取所有货物信息
     *
     * @return 返回到所有商品视图
     */
    public function index (Request $request)
    {
        $search = [];            //存放搜索条件的空数组
        if($request->has('name')){  //搜索条件查询
            //进行了搜索
            $name = $request->input('name');
            $data = Goods::where("pid","!=",0)->where("name","like","%{$name}%")->OrderBy("pid")->paginate(6);
            $search['name'] = $name; // 条件放进数组
        }else{
            //没有进行搜索
            $data = Goods::where("pid","!=",0)->OrderBy("pid")->paginate(6);
        }
        $types = Goods::where("pid","=",0)->get();
        $type = [];
        foreach ($types as $k => $v) {
            $type[$v->id] = $v->name;
        }
        return view('admin.goods_list_all')->with(['data'=>$data])->with(["type"=>$type])->with(["search"=>$search]);
    }

    /**
     * 删除用户数据
     * @param  int $id 接收到的id
     * @return None    跳回上一个页面
     */
    public function ToggleStatus (Request $request)
    {
        $id = $request->id;
        // 执行下架操作
        $good = Goods::find($id);
        if ($good->status == 1) {
            $good->status = '0';
        } else if($good->status == 0) {
            $good->status = '1';
        }
        $good->save();
        return back();
    }

    /**
     * 新增商品信息
     * @param  Request $request 表单数据
     * @return None          直接跳回上一个页面
     */
    public function store (Request $request)
    {
        //判断是否选择分类
        if ($request->pid == "type") { //没有选择，添加大类
            $data = $request->only("name");//获取信息
            $data['pid'] = 0;
        }else{ //选择分类，添加商品
            $data = $request->only("name","price","num","pid","goodsTitle");//获取信息
            //执行上传
            $file = $request->file('img');
            if($file->isValid()){
                $ext = $file->getClientOriginalExtension();//获得后缀 
                $filename = time().rand(1000,9999).".".$ext;//新文件名
                $file->move("./Uploads/Picture/",$filename);
            }
            // 执行缩放
            $img = new Image();
            $img->open("./Uploads/Picture/".$filename)->thumb(160,110)->save("./Uploads/Picture/".$filename);
            $data['img'] = $filename;
        }
        $data['status'] = 1;
        $id = Goods::insert($data);//写入数据库
        return back();
    }

    /**
     * 修改商品信息
     * @param  Request $request 请求数据
     * @param  int  $id         接收到的id
     * @return None             上一页面
     */
    public function update (Request $request,$id)
    {
        $db = Goods::find($id);
        //更新模型数据
        $db->name = $request->name;
        $db->price = $request->price;
        $db->num = $request->num;
        if (!($db->pid)) {
           $db->pid = $request->pid;
        }
        $db->goodsTitle = $request->goodsTitle;
        if ($request->file('img')) {
            $file = $request->file('img');
            if($file->isValid()){
                $ext = $file->getClientOriginalExtension();//获得后缀 
                $filename = time().rand(1000,9999).".".$ext;//新文件名
                $file->move("./Uploads/Picture/",$filename);
            }
            // 执行缩放
            $img = new Image();
            $img->open("./Uploads/Picture/".$filename)->thumb(160,110)->save("./Uploads/Picture/".$filename);
            $db->img = $filename;
        }
        $db->save();
        return back();
    }

    /**
     * 获取下架的货物信息
     *
     * @return 返回到所有商品视图
     */
    public function offIndex (Request $request)
    {
        $search = [];            //存放搜索条件的空数组
        if($request->has('name')){  //搜索条件查询
            //进行了搜索
            $name = $request->input('name');
            $data = Goods::where("pid","!=",0)->where("status","=",0)->where("name","like","%{$name}%")->OrderBy("pid")->paginate(6);
            $search['name'] = $name; // 条件放进数组
        }else{
            //没有进行搜索
            $data = Goods::where("pid","!=",0)->where("status","=",0)->OrderBy("pid")->paginate(6);
        }
        $types = Goods::where("pid","=",0)->get();
        $type = [];
        foreach ($types as $k => $v) {
            $type[$v->id] = $v->name;
        }
        return view('admin.goods_list_all')->with(['data'=>$data])->with(["type"=>$type])->with(["search"=>$search]);
    }
}
