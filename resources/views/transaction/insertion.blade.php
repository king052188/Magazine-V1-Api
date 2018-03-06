<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Order Contract</title>
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        .pdf-web-view {
            width: 100%;
            height: 900px;
        }
        .loading {
            display: table;
            width: 100%;
            height: 850px;
        }
        .loading-div {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
        }
        .btn {
            background: #3498db;
            background-image: -webkit-linear-gradient(top, #3498db, #2980b9);
            background-image: -moz-linear-gradient(top, #3498db, #2980b9);
            background-image: -ms-linear-gradient(top, #3498db, #2980b9);
            background-image: -o-linear-gradient(top, #3498db, #2980b9);
            background-image: linear-gradient(to bottom, #3498db, #2980b9);
            -webkit-border-radius: 28;
            -moz-border-radius: 28;
            border-radius: 28px;
            font-family: Tahoma, Segoe UI;
            color: #ffffff;
            font-size: 1.2em;
            padding: 10px 20px 10px 20px;
            text-decoration: none;
            float: right;
        }

        .btn:hover {
            background: #3cb0fd;
            background-image: -webkit-linear-gradient(top, #3cb0fd, #3498db);
            background-image: -moz-linear-gradient(top, #3cb0fd, #3498db);
            background-image: -ms-linear-gradient(top, #3cb0fd, #3498db);
            background-image: -o-linear-gradient(top, #3cb0fd, #3498db);
            background-image: linear-gradient(to bottom, #3cb0fd, #3498db);
            text-decoration: none;
        }
    </style>
</head>
<body>
<?php
    $trans = $data["trans_number"];
    $type = $data["trans_type"];
?>
<div style="width: 100%; margin-top: 10px;">
    <div style="margin: 0 auto; width: 720px; height: 60px;">
        <button id="btnDownloadPDF" class="btn">Download PDF</button>
    </div>
    <div style="margin: 0 auto; width: 720px; height: 870px; border: 1px solid #3f3f3f;">
        <div id="pdf_web_view" class="pdf-web-view"> </div>
        <div id="loading" class="loading">
            <div class="loading-div">
                <img src="/img/ripple.gif" style="width: 90px;"  />
            </div>
        </div>
    </div>
</div>
<!-- Mainly scripts -->
<script src="{{ asset('js/jquery-2.1.1.js')}}"></script>
<script src="{{ asset('js/bootstrap.min.js')}}"></script>
<script src="{{ asset('js/plugins/metisMenu/jquery.metisMenu.js')}}"></script>
<script src="{{ asset('js/plugins/slimscroll/jquery.slimscroll.min.js')}}"></script>
<!-- Custom and plugin javascript -->
<script src="{{ asset('js/inspinia.js')}}"></script>
<script>
    $(document).ready(function() {
        var trans = "{{ $trans }}";
        var type = "{{ $type }}";
        var url = "/kpa/work/generate/insertion-order/"+trans;
        if(type == "DIGITAL") { url = "/kpa/work/generate/insertion-digital-order/"+trans; }

        $('#pdf_web_view').hide();
        $('#loading').show();
        $.ajax({
            url: url,
            dataType: "text",
            beforeSend: function () {
            },
            success: function(data) {
                $('#pdf_web_view').html(data);
                $('#pdf_web_view').show();
                $('#loading').hide();
            }
        });
        
        $("#btnDownloadPDF").click(function () {
            if(type == "DIGITAL") {
                window.location.href="/kpa/work/generate/insertion-digital-pdf/"+trans;
                return;
            }
            window.location.href="/kpa/work/generate/insertion-order-pdf/"+trans;
        })
    })
</script>
</body>
</html>
