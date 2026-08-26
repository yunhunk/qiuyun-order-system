<!--
 *短信验证模态框
 * 秋云
 * -->
<!-- model -->
<div class="modal fade" align="left" id="sendCodeModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">请发送验证码验证手机</h4>
      </div>
      <div class="modal-body">
         <div class="form-group">
            <label class="col-sm-2 control-label">您的手机号</label>
            <div class="col-sm-10">
             <div class="input-group">
               <input type="text" name="tel" id="tel" onblur="change()" value="<?php echo $userrow['tel'] ?>" class="form-control" disabled/>
               <span id="sendCode" onclick="sendCode()" class="input-group-addon btn btn-primary btn-sm">发送验证码</span>
             </div>
            </div>
          </div><br/>
          <div class="form-group" id="code_display" style="display:none">
            <label class="col-sm-2 control-label">填写验证码</label>
            <div class="col-sm-10"><input type="text" id="code" value="" class="form-control" placeholder="填写验证码"/></div>
          </div><br/>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
            <a onclick="submit_code()" class="btn btn-primary form-control">提交验证</a><br/>
           </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- model end -->
</body>
<script type="text/javascript">
window.onload = function() {
    var items = $("select[default]");
    for (i = 0; i < items.length; i++) {
        $(items[i]).val($(items[i]).attr("default") || 0);
    }
}

function sendCode() {
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=sendCode",
        data: "tel=" + adm_tel,
        dataType: "json",
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                $("#code_display").show();
                $("#sendCode").html("已发送");
                setTimeout(function() {
                    $("#sendCode").html("发送验证码");
                }, 1500);
                layer.msg(data.msg);
            } else {
                layer.alert(data.msg);
            }
        }
    });
}

function submit_code() {
    var code = $("#code").val();
    if (code != "") {
        return layer.alert("验证码不能为空！");
    }
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=checkCode",
        data: "code=" + code,
        dataType: "json",
        success: function(data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg(data.msg);
                $("#sendCodeModel").model('hide');
            } else {
                layer.alert(data.msg);
            }
        }
    });
}
</script>
</html>