{{ partial("partials/main_nav", ['title': '文章']) }}
<table class="table table-hover">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">标题</th>
            <th scope="col">创建时间</th>
            <th scope="col">操作</th>
        </tr>
    </thead>
    <tbody>
        {% set rows = post(['func': 'getPostRows','argv' : { 'page' :currpage, 'limit' : 10, 'order': 'id DESC', 'columns':['id','title','created_at','description','text', 'uid']}]) %}
        {% for k,v in rows %}
        <tr>
            <td><input type="checkbox" id="exampleCheck1"> </td>
            <td>{{ v['title'] }}</td>
            <td>{{ date("Y-m-d H:i:s", v['created_at']) }}</td>
            <td><a href="/console/plugins-app/post?action=editor&id={{ v['id'] }}">编辑</a> <a
                    href="/plugins-app/post?action=delete&file={{ v['id'] }}">删除</a></td>
        </tr>
        {% endfor %}
    </tbody>
</table>
{% set total = post(['func': 'getPostCount', 'argv' : {'status': 1}]) %}
{{ pager(['currpage':currpage, 'total':ceil(total/10), 'url': '/console/plugins/post']) }}