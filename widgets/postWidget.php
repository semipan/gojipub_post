<?php
/**
 * 小部件
 */
namespace Plugins\Post\Widgets;

use Core\Library\Widget;
use Core\Models\CoreMember;
use Plugins\Post\Models\PluPost;
use Plugins\Post\Models\PluRouteMap;
use Plugins\Post\Models\PluTag;
use Plugins\Post\Models\PluTagMap;

class postWidget extends Widget
{
    /**
     * 读取文章记录
     *
     * @param [type] $argv
     * @return void
     */
    public function getPostRows($argv)
    {
        $where = [];
        if (isset($argv['columns'])) {
            $where['columns'] = $argv['columns'];
        }
        if (isset($argv['page']) && isset($argv['limit'])) {
            $where['offset'] = ($argv['page'] - 1) * $argv['limit'];
        }
        if (isset($argv['limit'])) {
            $where['limit'] = $argv['limit'];
        }
        if (isset($argv['order'])) {
            $where['order'] = $argv['order'];
        }
        $result = PluPost::find($where);
        $result = $result->toArray();
        if ($result) {
            foreach ($result as &$val) {
                $val['text'] = \Michelf\MarkdownExtra::defaultTransform($val['text']);
                unset($val);
            }
            if (isset($argv['columns']) && in_array('uid', $argv['columns'])) {
                $uidIds = []; //用户id数组
                $userArr = []; //用户
                $urlArr = []; //url
                $postIds = []; //文章id数组
                foreach ($result as $val) {
                    $uidIds[] = $val['uid'];
                    $postIds[] = $val['id'];
                }
                $uidIds = array_unique($uidIds);
                $postIds = array_unique($postIds);
                $user = CoreMember::find(
                    [
                        "columns" => ["id", "username", "email"],
                        "conditions" => "id IN (" . implode(',', $uidIds) . ")",
                    ]
                );
                if ($user) {
                    $user = $user->toArray();
                    foreach ($user as $val) {
                        $userArr[$val['id']] = $val;
                    }
                }
                $route = PluRouteMap::find(
                    [
                        "conditions" => "post_id IN (" . implode(',', $postIds) . ")",
                    ]
                );
                if ($route) {
                    $route = $route->toArray();
                    foreach ($route as $val) {
                        $urlArr[$val['post_id']] = $val['url'];
                    }
                }
                foreach ($result as &$val) {
                    if ($userArr) {
                        if (!empty($userArr[$val['uid']])) {
                            $val['uid'] = $userArr[$val['uid']];
                            $val['uid']['avatar'] = \Core\Library\Common::getGravatar($val['uid']['email']);
                        }
                    }
                    if ($urlArr) {
                        if (!empty($urlArr[$val['id']])) {
                            $val['url'] = '/' . date('Y/m/d/', $val['created_at']) . $urlArr[$val['id']] . '/';
                        } else {
                            $val['url'] = '';
                        }
                    }
                    unset($val);
                }
            }
        }
        return $result;
    }

    /**
     * 根据标签映射获取文章记录
     *
     * @param [type] $argv
     * @return void
     */
    public function getPostRowsByTag($argv)
    {
        $result = [];
        $tag = PluTag::findFirst(
            [
                "conditions" => "name = ?1",
                "bind" => [
                    1 => $argv['tagname'],
                ],
            ]
        );
        if (!$tag) {
            $this->error404();
        }
        $where['conditions'] = "tag_id = ?1";
        $where['bind'] = [
            1 => $tag->id,
        ];
        if (isset($argv['page']) && isset($argv['limit'])) {
            $where['offset'] = ($argv['page'] - 1) * $argv['limit'];
        }
        if (isset($argv['limit'])) {
            $where['limit'] = $argv['limit'];
        }
        if (isset($argv['order'])) {
            $where['order'] = $argv['order'];
        }
        $tagMap = PluTagMap::find($where);
        if ($tagMap) {
            $tagMap = $tagMap->toArray();
            $ids = [];
            foreach ($tagMap as $value) {
                $ids[] = $value['post_id'];
            }
            $where = [];
            $where['conditions'] = "id IN (" . implode(',', $ids) . ")";
            if (isset($argv['columns'])) {
                $where['columns'] = $argv['columns'];
            }
            $result = PluPost::find($where);
            if ($result) {
                $result = $result->toArray();
                foreach ($result as &$val) {
                    $val['text'] = \Michelf\MarkdownExtra::defaultTransform($val['text']);
                    unset($val);
                }
                if (isset($argv['columns']) && in_array('uid', $argv['columns'])) {
                    $uidIds = []; //用户id数组
                    $userArr = []; //用户
                    $urlArr = []; //url
                    $postIds = []; //文章id数组
                    foreach ($result as $val) {
                        $uidIds[] = $val['uid'];
                        $postIds[] = $val['id'];
                    }
                    $uidIds = array_unique($uidIds);
                    $postIds = array_unique($postIds);
                    $user = CoreMember::find(
                        [
                            "columns" => ["id", "username", "email"],
                            "conditions" => "id IN (" . implode(',', $uidIds) . ")",
                        ]
                    );
                    if ($user) {
                        $user = $user->toArray();
                        foreach ($user as $val) {
                            $userArr[$val['id']] = $val;
                        }
                    }
                    $route = PluRouteMap::find(
                        [
                            "conditions" => "post_id IN (" . implode(',', $postIds) . ")",
                        ]
                    );
                    if ($route) {
                        $route = $route->toArray();
                        foreach ($route as $val) {
                            $urlArr[$val['post_id']] = $val['url'];
                        }
                    }
                    foreach ($result as &$val) {
                        if ($userArr) {
                            if (!empty($userArr[$val['uid']])) {
                                $val['uid'] = $userArr[$val['uid']];
                                $val['uid']['avatar'] = \Core\Library\Common::getGravatar($val['uid']['email']);
                            }
                        }
                        if ($urlArr) {
                            if (!empty($urlArr[$val['id']])) {
                                $val['url'] = '/' . date('Y/m/d/', $val['created_at']) . $urlArr[$val['id']] . '/';
                            } else {
                                $val['url'] = '';
                            }
                        }
                        unset($val);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 获取文章表的记录数
     *
     * @param [type] $argv
     * @return void
     */
    public function getPostCount($argv)
    {
        $where = [
            "conditions" => "status = ?1",
            "bind" => [
                1 => $argv['status'],
            ],
        ];
        return PluPost::count($where);
    }

    /**
     * 获取标签表的记录数
     *
     * @param [type] $argv
     * @return void
     */
    public function getTagMapCountByTagId($argv)
    {
        $tag = PluTag::findFirst(
            [
                "conditions" => "name = ?1",
                "bind" => [
                    1 => $argv['tagname'],
                ],
            ]
        );
        if ($tag) {
            $where['conditions'] = "tag_id = ?1";
            $where['bind'] = [
                1 => $tag->id,
            ];
            return PluTagMap::count($where);
        }
    }

    /**
     * 根据标识获取记录详情
     *
     * @param [type] $argv
     * @return void
     */
    public function getRowById($argv)
    {
        $row = [];
        $routeMap = PluRouteMap::findFirst(
            [
                "conditions" => "url = ?1",
                "bind" => [
                    1 => $argv['id'],
                ],
            ]
        );
        if (!$routeMap) {
            $this->error404();
        }
        $row = PluPost::findFirst(
            [
                "conditions" => "id = ?1",
                "bind" => [
                    1 => $routeMap->post_id,
                ],
            ]
        );
        $row = $row->toArray();
        $row['text'] = \Michelf\MarkdownExtra::defaultTransform($row['text']);
        $row['text'] = html_entity_decode($row['text']);
        $user = CoreMember::findFirst(
            [
                "conditions" => "id = ?1",
                "bind" => [
                    1 => $row['uid'],
                ],
            ]
        );
        if ($user) {
            $user = $user->toArray();
            $row['uid'] = $user;
            $row['uid']['avatar'] = \Core\Library\Common::getGravatar($row['uid']['email']);
        }
        $row['tag'] = unserialize($row['tag']);
        return $row;
    }

    /**
     * 归档
     *
     * @return void
     */
    public function getArchives()
    {
        $ret = PluPost::find([
            'columns' => ["id", "title", "created_at", "strftime('%Y',datetime(created_at, 'unixepoch')) AS year"],
            "order" => "id DESC",
        ]);
        $archives = [];
        $postIds = [];
        if (!$ret) {
            $this->error404();
        }
        $ret = $ret->toArray();
        foreach ($ret as $v) {
            $archives[$v['year']][] = $v;
            $postIds[] = $v['id'];
        }
        if ($postIds) {
            $route = PluRouteMap::find(
                [
                    "conditions" => "post_id IN (" . implode(',', $postIds) . ")",
                ]
            );
            if ($route) {
                $route = $route->toArray();
                foreach ($route as $val) {
                    $urlArr[$val['post_id']] = $val['url'];
                }
            }
        }
        foreach ($archives as $key => $value) {
            foreach ($value as $k => $v) {
                if ($urlArr) {
                    if (!empty($urlArr[$v['id']])) {
                        $archives[$key][$k]['url'] = '/' . date('Y/m/d/', $v['created_at']) . $urlArr[$v['id']] . '/';
                    } else {
                        $archives[$key][$k]['url'] = '';
                    }
                }
            }
        }
        return $archives;
    }
}
