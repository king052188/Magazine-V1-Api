<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Order</title>
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
    $invoice     = $data["invoice"];
 $is_digital     = $data["is_digital"];
   $proposal     = $data["proposal_uid"];
       $paid     = $data["paid"];
    $version     = $data["version"];
?>
<div style="width: 100%; margin-top: 10px;">
    <div style="margin: 0 auto; width: 720px; height: 60px;">
        <button id="btnDownloadPDF" class="btn">Download PDF</button>
    </div>
    <div style="margin: 0 auto; width: 750px; height: 900px; background: #ffffff;">
        <div id="pdf_web_view" class="pdf-web-view"></div>
        <div id="loading" class="loading">
            <div class="loading-div">
                <img src="{{ asset('img/ripple.gif') }}" style="width: 90px;"  />
            </div>
        </div>
    </div>
</div>
<!-- Mainly scripts -->
<script src="{{ asset('js/jquery-2.1.1.js')}}"></script>
<!-- Custom and plugin javascript -->
<script>
    $(document).ready(function() {

        var invoice = "{{ $invoice }}";
        var is_digital = "{{ $is_digital }}";
        var proposal = "{{ $proposal }}";
        var paid = "{{ $paid }}";
        var version = {{ $version }};

        var isShow = paid == "" ? "" : "/" + paid;

        var url = null;
        if(is_digital == "DIGITAL") {
            url = "/kpa/work/transaction/generate/invoice-digital-order/"+invoice + isShow;
        }
        else {
            url = "/kpa/work/transaction/generate/invoice-order-v2/"+invoice + isShow;
        }

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
            if(is_digital == "DIGITAL") {
                window.location.href="/kpa/work/transaction/generate/invoice-digital-order/download/"+invoice;
                return;
            }
            window.location.href="/kpa/work/transaction/generate/invoice-order-v2/download/"+invoice;
        })

    })
</script>
</body>
</html>
