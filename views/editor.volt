<!DOCTYPE html>
<html lang="zh">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ row['title'] is not empty ? '编辑 - ' ~ row['title'] : '新建' }}</title>
    <link href="https://cdn.bootcss.com/twitter-bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.6/styles/default.min.css">
    <link rel="stylesheet" href="/static/plugins/post/css/split-pane.css" />
    <link rel="stylesheet" href="/static/plugins/post/css/pretty-split-pane.css" />
    <link href="/static/plugins/post/css/bootoast.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/static/plugins/post/css/simplemde.min.css">
    <style type="text/css">
        html,
        body {
            height: 100%;
            min-height: 100%;
            margin: 0;
            padding: 0;
        }

        #left-component {
            width: 20em;
            /* Same as divider left */
        }

        #my-divider {
            left: 20em;
            width: 5px;
        }

        #right-component {
            left: 20em;
            /* Same as divider left */
        }

        .editor-preview-active-side blockquote {
            padding: 0px 0px 0px 14px;
            line-height: 1.6em;
            border-left: 6px solid #f1f1f1;
            color: #666;
        }

        .editor-preview-active-side pre {
            background: #F5F8FA;
            border: 1px dashed #CBD3D6;
            white-space: pre-wrap;
            word-wrap: break-word;
            padding: 10px;
        }

        .hljs,
        .lang-shell {
            background: #F5F8FA;
            font-size: 14px;
            padding: 0 10px;
            font-family: 'Courier New', Courier, monospace;
        }

        .fileinput-button {
            position: relative;
            overflow: hidden;
            display: inline-block;
            font-size: 14px;
        }

        .fileinput-button input {
            position: absolute;
            top: 0;
            right: 0;
            margin: 0;
            opacity: 0;
            -ms-filter: 'alpha(opacity=0)';
            font-size: 200px !important;
            direction: ltr;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="pretty-split-pane-frame">
        <div class="split-pane fixed-left">
            <div class="split-pane-component" id="left-component">
                <div class="pretty-split-pane-component-inner" style="padding-top: 10px; background: #f9f9f9">
                    <div class="form-group">
                        <label for="title">标题</label>
                        <input value="{{ row['title'] is not empty ? row['title'] : '' }}" type="text" name="title"
                            class="form-control" id="title">
                        <small id="emailHelp" class="form-text text-muted">最多80个字符。</small>
                    </div>
                    <div class="form-group">
                        <label for="title">标签</label>
                        <input value="{{ row['tag'] is not empty ? row['tag'] : '' }}" type="text" name="tag"
                            class="form-control" id="tag">
                        <small id="emailHelp" class="form-text text-muted">标签用英文逗号隔开，最多5个标签。</small>
                    </div>
                    <div class="form-group">
                        <label for="title">摘要</label>
                        <textarea id="description" class="form-control"
                            rows="8">{{ row['description'] is not empty ? row['description'] : '' }}</textarea>
                    </div>
                    <p align="right">
                        {{ id is not empty ? '<a href="javascript:;" id="submit" class="btn btn-default">发布</a>' : '' }}
                        <a href="javascript:;" id="save" class="btn  btn-primary">保存</a></p>
                    <p style="border-top: 1px solid #f1f1f1; line-height: 35px; font-size: 14px;" align="center">
                        {% if row['id'] is not empty %}
                        <a id="view-bt" href="{{ date('/Y/m/d/', row['created_at']) }}{{ url }}/"
                            style="color:#ccc">查看文章</a>
                        {% else %}
                        <a href="/" style="color: #ccc">返回首页</a>
                        {% endif %}
                    </p>
                </div>
            </div>
            <div class="split-pane-divider" id="my-divider"></div>
            <div class="split-pane-component" id="right-component">
                <div class="pretty-split-pane-component-inner" style="padding: 10px 10px 0 0; background: #f9f9f9">
                    {{ id is not empty and row['text'] != row['draft'] and row['draft'] is not empty ? '<p>Tip:当前的内容与发布的内容不一致，点击下面的按钮发布。</p>' : '' }}
                    <div class="form-group"></div>
                    <div class="form-group">
                        <textarea id="text">{{ text }}</textarea>
                    </div>
                    <div>
                        {{ id is not empty ? '<a href="javascript:;" id="url-bt" class="fileinput-button">修改固定链接</a>' : '' }}<a
                            href="javascript:;" class="fileinput-button" style="float: right">上传图片&附件<input
                                id="fileupload" type="file" name="file" multiple></a></div>
                    {% if id is not empty %}
                    <div class="form-group" id="url-block" style="display: none">
                        <label for="url">固定链接</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">{{ date('/Y/m/d/', row['created_at']) }}</div>
                            </div>
                            <input type="text" class="form-control" id="url" value="{{ url }}">
                            <div class="input-group-append">
                                <span class="input-group-text">/</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">如果没有设定值，则资源不能访问。如果已经设定了，再次修改之前的url则不能访问。</small>
                    </div>
                    {% endif %}
                    <input type="hidden" name="id" id="id" value="{{ row['id'] is not empty ? row['id'] : '' }}">
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
    <script src="/static/plugins/post/js/split-pane.js"></script>
    <script src="/static/plugins/post/js/bootoast.js"></script>
    <script src="/static/plugins/post/js/simplemde.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.6/highlight.min.js"></script>
    <script src="/static/plugins/post/js/jquery.ui.widget.js"></script>
    <script src="/static/plugins/post/js/jquery.fileupload.js"></script>
    <script src="/static/plugins/post/js/default.js"></script>
</body>

</html>