var simplemde = new SimpleMDE({
  element: document.getElementById("text"),
  autofocus: true,
  spellChecker: false,
  status: false,
  toolbar: ["bold", "italic", "strikethrough", "|", "heading-1", "heading-2", "heading-3", "|", "unordered-list", "ordered-list", "|", "code", "quote", "horizontal-rule", "|", "link", "image", "table", "|", "clean-block", "preview", "side-by-side", "fullscreen"],
  placeholder: "请开始您的写作吧...",
  renderingConfig: {
    singleLineBreaks: false,
    codeSyntaxHighlighting: true,
  }
});
$(function () {
  $("#url-bt").click(function () {
    $("#url-block").toggle();
  })
  //文章保存
  $('#save').on('click', function (event) {
    var text = htmlEncode(simplemde.value());
    $.ajax({
      type: "post",
      dataType: "json",
      url: "/console/plugins-app/post?action=save",
      data: { id: $("#id").val(), url: $("#url").val(), title: $("#title").val(), tag: $("#tag").val(), description: $("#description").val(), text: text },
      beforeSend: function (XMLHttpRequest) {
        $('#save').text("保存中...");
      },
      success: function (result) {
        if (result.code == 0) {
          bootoast({
            message: '保存成功',
            type: 'success',
            position: 'top-right',
            timeout: 2
          });
          $('#save').text("保存");
          $('#view-bt').attr('href', result.data.url);
        } else {
          bootoast({
            message: result.msg,
            type: 'danger',
            position: 'top-right',
            timeout: 2
          });
          $('#save').text("保存");
        }
      },
      error: function () {
        bootoast({
          message: '接口异常',
          type: 'warning',
          position: 'top-right',
          timeout: 2
        });
        $('#save').text("保存");
      }
    });
    return false;
  })
  //文章发布
  $('#submit').on('click', function (event) {
    var text = htmlEncode(simplemde.value());
    $.ajax({
      type: "post",
      dataType: "json",
      url: "/console/plugins-app/post?action=pub",
      data: { id: $("#id").val(), url: $("#url").val(), title: $("#title").val(), tag: $("#tag").val(), description: $("#description").val(), text: text },
      beforeSend: function (XMLHttpRequest) {
        $('#submit').text("发布中...");
      },
      success: function (result) {
        if (result.code == 0) {
          bootoast({
            message: '发布成功',
            type: 'success',
            position: 'top-right',
            timeout: 2
          });
          $('#submit').text("发布");
        } else {
          bootoast({
            message: result.msg,
            type: 'danger',
            position: 'top-right',
            timeout: 2
          });
          $('#submit').text("发布");
        }
      },
      error: function () {
        bootoast({
          message: '接口异常',
          type: 'warning',
          position: 'top-right',
          timeout: 2
        });
        $('#submit').text("发布");
      }
    });
    return false;
  })
  //上传
  $('#fileupload').fileupload({
    url: '/console/upload',
    dataType: 'json',
    done: function (e, data) {
      var editor = simplemde.value();
      if (!isEmpty(editor)) {
        editor += "\n";
      }
      editor += "![" + data.result.name + "](" + data.result.url + ")";
      simplemde.value(editor);
    }
  });
})
//转实体
function htmlEncode(str) {
  var s = "";
  if (str.length === 0) {
    return "";
  }
  s = str.replace(/&/g, "&amp;");
  s = s.replace(/</g, "&lt;");
  s = s.replace(/>/g, "&gt;");
  s = s.replace(/\'/g, "&#39;");
  s = s.replace(/\"/g, "&quot;");
  return s;
}
//判断是否为空
function isEmpty(obj) {
  if (typeof obj == "undefined" || obj == null || obj == "") {
    return true;
  } else {
    return false;
  }
}