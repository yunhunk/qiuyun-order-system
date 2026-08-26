<!--公告-->
        <div class="modal fade" id="anounce2" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="block block-themed block-transparent remove-margin-b">
                        <div class="block-header bg-primary-dark">
                            <h3 class="block-title">商品通知时间：<span id=localtime></span><script type="text/javascript">function showLocale(objD)
                                    {var str,colorhead,colorfoot;var yy = objD.getYear();
                                        if(yy<1900) yy = yy+1900;
                                        var MM = objD.getMonth()+1;
                                        if(MM<10) MM = '0' + MM;
                                        var dd = objD.getDate();
                                        var ww = objD.getDay();
                                        if  ( ww==0 )  colorhead="<font color=\"#FFFFFF \">";
                                        if  ( ww > 0 && ww < 6 )  colorhead="<font color=\"#FFFFFF \">";
                                        if  ( ww==6 )  colorhead="<font color=\"#FFFFFF \">";
                                        var hh = objD.getHours();
                                        str = colorhead + yy + "/" + MM + "/" + dd;
                                        return(str);}function tick()
                                    {var today;today = new Date();document.getElementById("localtime").innerHTML = showLocale(today);window.setTimeout("tick()", 1000);}
                                    tick();</script></h3>
                        </div>
                        <?php echo $conf['anounce'] ?>
                   </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-default" type="button" data-dismiss="modal">关闭</button>
                    </div>
                </div>
            </div>
        </div>
        <!--公告-->