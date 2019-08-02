<?php
/**
 * 文章插件
 */
use Plugins\Post\Models\PluPost;
use Plugins\Post\Models\PluRouteMap;
use Plugins\Post\Models\PluTag;
use Plugins\Post\Models\PluTagMap;

class postPlugin extends Core\Library\Plugins
{
    const LIMIT = 20;
    /**
     * 用来初始化插件，一般用于安装时使用
     *
     * @return void
     */
    public function init()
    {
        echo '文章插件';
    }

    /**
     * 文章目录
     *
     * @return void
     */
    public function index()
    {
        $this->view->pagetitle = '管理文章';
        $currpage = $this->request->getQuery("currpage");
        if (empty($currpage) || $currpage < 1) {
            $currpage = 1;
        }
        $rows = PluPost::find([
            "offset" => ($currpage - 1) * self::LIMIT,
            "limit" => self::LIMIT,
            "order" => "id DESC",
        ]);
        $rows = $rows->toArray();
        $this->view->rows = $rows;
    }

    public function create()
    {
        $title = date("Y-m-d H:i:s",time());;
        $posts = new PluPost();
        $posts->uid = $this->plugins->auth['id'];
        $posts->text = ' ';
        $posts->created_at = time();
        $posts->status = 0;
        $posts->title = $title;
        $posts->draft = '';
        $posts->tag = '';
        $posts->description = '';
        if ($posts->save() === false) {
            echo 1;die;
            //$this->logger->error('文章更新失败 ' . __LINE__); //将错误记录日志，便于今后分析。
            //$json = ['code' => 500010101, 'msg' => 'fail']; //错误码：500代表内部错误，01代码文章模块，01保存操作，01代表保存失败
        } else {
            $this->view->id = $posts->id;
            $this->view->title = $title;
        }
    }

    /**
     * 编辑表单
     *
     * @return void
     */
    public function editor()
    {
        $id = $this->request->getQuery("id");
        //读取记录详情
        $row = PluPost::findFirst(
            [
                "conditions" => "id = ?1",
                "bind" => [
                    1 => $id,
                ],
            ]
        );
        if (empty($row)) {
            echo 'not found.';
            die;
        }
        $row = $row->toArray();
        //读取固定链接
        $url = '';
        $PluRouteMap = PluRouteMap::findFirst(
            [
                "conditions" => "post_id = ?1",
                "bind" => [
                    1 => $id,
                ],
            ]
        );
        if (!empty($PluRouteMap)) {
            $PluRouteMap = $PluRouteMap->toArray();
            if (!empty($PluRouteMap['url'])) {
                $url = $PluRouteMap['url'];
            }
        }
        if (!empty($row['tag'])) {
            $tag = unserialize($row['tag']);
            $row['tag'] = implode(',', $tag);
        }
        $row['text'] = html_entity_decode($row['text']);
        $row['draft'] && $row['draft'] = html_entity_decode($row['draft']);
        $text = $row['text'];
        if (!empty($row['draft'])) {
            $text = $row['draft'];
        }
        $this->view->row = $row;
        $this->view->text = $text;
        $this->view->id = $id;
        $this->view->url = !empty($PluRouteMap['url']) ? $PluRouteMap['url'] : '';
    }

    /**
     * 保存
     *
     * @return void
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $id = $this->request->getPost('id', ['trim', 'int']);
            $title = $this->request->getPost('title', ['trim', 'string']);
            $text = $this->request->getPost('text');
            $postTag = $this->request->getPost('tag', ['trim', 'string']);
            $description = $this->request->getPost('description', ['trim', 'string']);
            if (empty($title)) {
                $json = ['code' => 400010101, 'msg' => '标题不能为空']; //错误码：400代表参数错误，01代码文章模块，01保存操作，01代表标题不能为空
                $this->response->setJsonContent($json);
                $this->response->send();
            }
            if (!empty($postTag)) {
                $postTag = str_replace('，', ',', $postTag);
                $postTag = array_filter(array_unique(explode(',', $postTag)));
                $tag = serialize($postTag);
            } else {
                $tag = '';
                $postTag = [];
            }

            if (!empty($description)) {
                $description = mb_substr($description, 0, 500);
            }
            $rowTag = [];
            if ($id) {
                $posts = PluPost::findFirst(
                    [
                        "conditions" => "id = ?1",
                        "bind" => [
                            1 => $id,
                        ],
                    ]
                );
                if (!empty($posts->tag)) {
                    $rowTag = unserialize($posts->tag);
                }
                $posts->modify_at = time();
                /**
                 * 如果有修改Url，则比对库中的Url和提交的是否一致，不一致则进行修改。
                 */
                $url = $this->request->getPost('url', ['trim', 'string']); //url
                $flag = 0; //Url是否冲突，默认不冲突
                //判断url是否冲突
                $routeMap = PluRouteMap::findFirst(
                    [
                        "conditions" => "url = ?1",
                        "bind" => [
                            1 => $url,
                        ],
                    ]
                );
                if ($routeMap) {
                    if ($routeMap->post_id != $id) {
                        $flag = 1;
                    }
                }
                if ($flag) {
                    $json = ['code' => 400010102, 'msg' => '固定链接已存在，请更换']; //错误码：400代表参数错误，01代码文章模块，01保存操作，01代表链接冲突
                    $this->response->setJsonContent($json);
                    $this->response->send();
                }
                $routeMap = PluRouteMap::findFirst(
                    [
                        "conditions" => "post_id = ?1",
                        "bind" => [
                            1 => $id,
                        ],
                    ]
                );
                if ($routeMap && $url) {
                    if ($routeMap->url != $url) {
                        $routeMap->url = $url;
                        $routeMap->modify_at = time();
                    }
                } else {
                    $routeMap = new PluRouteMap();
                    $routeMap->post_id = $id;
                    $routeMap->url = $url;
                    $routeMap->created_at = time();
                }
                $routeMap->save();
            } else {
                $posts = new PluPost();
                $posts->uid = $this->view->auth['id'];
                $posts->text = $text;
                $posts->created_at = time();
                $posts->status = 1;
            }
            $posts->title = $title;
            $posts->draft = $text;
            $posts->tag = $tag;
            $posts->description = $description;
            if ($posts->save() === false) {
                //$this->logger->error('文章更新失败 ' . __LINE__); //将错误记录日志，便于今后分析。
                $json = ['code' => 500010101, 'msg' => 'fail']; //错误码：500代表内部错误，01代码文章模块，01保存操作，01代表保存失败
            } else {
                //$this->shareCacheKey(1);
                $json = ['code' => 0, 'msg' => 'success', 'data' => ['url' => '/' . date('Y/m/d/', $posts->created_at) . $url . '/']];
                $intersect = array_intersect($rowTag, $postTag);
                $tagAddArr = array_diff($postTag, $intersect); //新增
                $tagDelArr = array_diff($rowTag, $intersect); //删除
                if ($tagAddArr) {
                    $this->_saveTagAndMap($posts->id, $tagAddArr);
                }
                if ($tagDelArr) {
                    $this->_saveTagAndMap($posts->id, $tagDelArr, 'DEL');
                }
            }
        } else {
            $json = ['code' => 500010103, 'msg' => '请求方式错误'];
        }
        $this->response->setJsonContent($json);
        $this->response->send();
    }

    /**
     * 发布
     *
     * @return void
     */
    public function pub()
    {
        if ($this->request->isPost()) {
            $id = $this->request->getPost('id', ['trim', 'int']);
            $text = $this->request->getPost('text');
            $posts = PluPost::findFirst(
                [
                    "conditions" => "id = ?1",
                    "bind" => [
                        1 => $id,
                    ],
                ]
            );
            $posts->text = $text;
            $posts->draft = '';
            if ($posts->save() === false) {
                //$this->logger->error('文章更新失败 ' . __LINE__); //将错误记录日志，便于今后分析。
                $json = ['code' => 500010101, 'msg' => 'fail']; //错误码：500代表内部错误，01代码文章模块，01保存操作，01代表保存失败
            } else {
                //$this->shareCacheKey(1);
                $json = ['code' => 0, 'msg' => 'success'];
            }
        } else {
            $json = ['code' => 500010103, 'msg' => '请求方式错误'];
        }
        $this->response->setJsonContent($json);
        $this->response->send();
    }

    /**
     * 垃圾箱
     *
     * @return void
     */
    public function recycle()
    {

    }

    /**
     * 保存标签并创建对应关系
     *
     * 查询标签是否存在，如果不存在则创建，存在则拿到id判断映射是否存在，如果不存在则创建，存在则丢弃。
     *
     * 调用举例：$this->_saveTagAndMap(3,['a','b','c']);
     *
     * 错误码：500代表程序内部运行错误，001代表文章模块，
     * 500001001，001：标签创建失败
     * 500001002，002：标签映射创建失败
     *
     * @param  int     $postId   文章id
     * @param  array   $tagArr   标签
     * @param  string  $type     类型。ADD代表新增标签，DEL代表删除标签
     * @return mixed
     */
    private function _saveTagAndMap($postId, $tagArr, $type = 'ADD')
    {
        //添加标签及其映射
        if ($type == 'ADD') {
            foreach ($tagArr as $value) {
                $tags = PluTag::findFirst(
                    [
                        "conditions" => "name = ?1",
                        "bind" => [
                            1 => $value,
                        ],
                    ]
                );
                if (!$tags) {
                    $tags = new PluTag();
                    $tags->name = $value;
                    $tags->created_at = time();
                    if ($tags->save() === false) {
                        //$this->logger->error('标签创建失败 ' . __LINE__); //将错误记录日志，便于今后分析。
                        return ['code' => 500020101, 'msg' => '标签创建失败']; //错误码：500代表内部错误，02代码标签模块，01保存操作，01代表保存失败
                    } else {
                        $tagId = $tags->id;
                    }
                } else {
                    $tagId = $tags->id;
                }
                $tagMap = PluTagMap::findFirst(
                    [
                        "conditions" => "tag_id = ?1 and post_id = ?2",
                        "bind" => [
                            1 => $tagId,
                            2 => $postId,
                        ],
                    ]
                );
                if (!$tagMap) {
                    $tagMap = new PluTagMap();
                    $tagMap->tag_id = $tagId;
                    $tagMap->post_id = $postId;
                    $tagMap->created_at = time();
                    if ($tagMap->save() === false) {
                        //$this->logger->error('标签映射创建失败 ' . __LINE__); //将错误记录日志，便于今后分析。
                        return ['code' => 500030101, 'msg' => '标签映射创建失败']; //错误码：500代表内部错误，02代表标签映射，01保存操作，01代表保存失败
                    }
                }
            }
        }
        //删除映射及其标签
        if ($type == 'DEL') {
            foreach ($tagArr as $value) {
                $tags = PluTag::findFirst(
                    [
                        "conditions" => "name = ?1",
                        "bind" => [
                            1 => $value,
                        ],
                    ]
                );
                if ($tags) {
                    $tagId = $tags->id;
                }
                PluTagMap::findFirst(
                    [
                        "conditions" => "tag_id = ?1 and post_id = ?2",
                        "bind" => [
                            1 => $tagId,
                            2 => $postId,
                        ],
                    ]
                )->delete();
                $PluTagMap = PluTagMap::findFirst(
                    [
                        "conditions" => "tag_id = ?1",
                        "bind" => [
                            1 => $tagId,
                        ],
                    ]
                );
                if (!$PluTagMap) {
                    PluTag::findFirst(
                        [
                            "conditions" => "id = ?1",
                            "bind" => [
                                1 => $tagId,
                            ],
                        ]
                    )->delete();
                }
            }
        }
    }

    /**
     * 删除文件
     *
     * @return void
     */
    public function delete()
    {
        echo $this->request->getQuery("file");
    }
}
